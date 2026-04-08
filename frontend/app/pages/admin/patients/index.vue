<template>
  <v-container fluid class="pa-8 patients-page">
      <div class="d-flex align-center flex-wrap ga-4 mb-6">
          <h1 class="text-h5 font-weight-bold text-grey-darken-4">Patients</h1>
          <p class="text-body-2 text-medium-emphasis mb-0">
              Grouped by email — each patient can have multiple appointments. Add internal notes under
              <strong>Doctor&rsquo;s comment</strong>.
          </p>
          <v-spacer />
          <v-text-field
              v-model="search"
              density="comfortable"
              variant="outlined"
              hide-details
              clearable
              placeholder="Search name or email"
              prepend-inner-icon="mdi-magnify"
              class="patients-search"
              style="max-width: 280px"
          />
      </div>

      <v-snackbar v-model="toastOpen" :color="toastColor" location="top" :timeout="3500" rounded="lg">
          {{ toastMessage }}
      </v-snackbar>

      <v-alert v-if="fetchError" type="error" variant="tonal" class="mb-6" closable @click:close="fetchError = ''">
          {{ fetchError }}
      </v-alert>

      <v-progress-linear v-if="loading" indeterminate color="primary-blue" rounded class="mb-4" />

      <p
          v-if="!loading && patients.length === 0"
          class="text-body-2 text-medium-emphasis text-center py-12"
      >
          {{ totalPatients === 0 && !search.trim() ? "No appointments yet." : "No patients match your search." }}
      </p>

      <v-expansion-panels v-else multiple variant="accordion" class="patient-panels">
          <v-expansion-panel v-for="p in patients" :key="p.email" rounded="lg" class="mb-2 border">
              <v-expansion-panel-title class="text-subtitle-1 font-weight-bold py-4">
                  <div class="d-flex flex-column flex-sm-row flex-wrap align-sm-center ga-2 w-100">
                      <span class="text-primary-blue">{{ p.name }}</span>
                      <span class="text-body-2 text-medium-emphasis font-weight-regular">{{ p.email }}</span>
                      <v-chip size="small" variant="tonal" color="primary-blue" class="font-weight-medium">
                          {{ p.appointment_count }} visit{{ p.appointment_count === 1 ? "" : "s" }}
                      </v-chip>
                      <span class="text-caption text-medium-emphasis d-none d-sm-inline ms-sm-auto">
                          {{ p.contact_number }}
                      </span>
                  </div>
              </v-expansion-panel-title>
              <v-expansion-panel-text class="pa-0 pt-2">
                  <v-data-table
                      :headers="appointmentHeaders"
                      :items="p.appointments"
                      :items-per-page="-1"
                      hide-default-footer
                      density="comfortable"
                      class="appointments-nested-table"
                      item-value="id"
                  >
                      <template #item.datetime="{ item }">
                          <div class="text-body-2">{{ formatVisit(item).dateLabel }}</div>
                          <div class="text-caption text-medium-emphasis">{{ formatVisit(item).timeLabel }}</div>
                      </template>
                      <template #item.service="{ item }">
                          <span class="text-body-2">{{ item.service }}</span>
                      </template>
                      <template #item.status="{ item }">
                          <v-chip
                              :color="statusColor(item.status)"
                              size="small"
                              variant="flat"
                              class="font-weight-medium"
                          >
                              {{ formatStatus(item.status) }}
                          </v-chip>
                      </template>
                      <template #item.note="{ item }">
                          <p class="text-body-2 text-medium-emphasis text-wrap">{{ item.note || "N/A" }}</p>
                      </template>
                      <template #item.doctor_comment="{ item }">
                          <v-textarea
                              :model-value="draftComment[item.id]"
                              density="compact"
                              variant="outlined"
                              rows="2"
                              hide-details
                              placeholder="Internal notes for staff"
                              class="mt-0 pa-2"
                              @update:model-value="(v) => setDraft(item.id, v)"
                          />
                      </template>
                      <template #item.actions="{ item }">
                          <div class="d-flex justify-end">
                              <v-btn
                                  color="primary-blue"
                                  size="small"
                                  variant="flat"
                                  rounded="lg"
                                  class="text-white"
                                  :loading="savingId === item.id"
                                  :disabled="!isDirty(item)"
                                  @click="saveDoctorComment(item.id)"
                              >
                                  Save
                              </v-btn>
                          </div>
                      </template>
                  </v-data-table>
              </v-expansion-panel-text>
          </v-expansion-panel>
      </v-expansion-panels>

      <div v-if="!loading" class="d-flex justify-center mt-6">
          <v-pagination
              v-model="page"
              :length="lastPage"
              :total-visible="10"
              color="primary-blue"
              rounded="lg"
              @update:model-value="loadPatients"
          />
      </div>
  </v-container>
</template>

<script setup>
  import { appointmentPartsFromApi } from "~/utils/appointmentDatetime";

  definePageMeta({
      layout: "admin",
  });

  const apiBase = usePublicApiBase();
  const loading = ref(false);
  const fetchError = ref("");
  const patients = ref([]);
  const search = ref("");
  const page = ref(1);
  const lastPage = ref(1);
  const totalPatients = ref(0);
  const perPage = 10;
  let searchDebounce = null;
  const draftComment = reactive({});
  const originalComment = reactive({});
  const savingId = ref(null);

  const toastOpen = ref(false);
  const toastMessage = ref("");
  const toastColor = ref("success");

  function showToast(message, color = "success") {
      toastMessage.value = message;
      toastColor.value = color;
      toastOpen.value = true;
  }

  const statusColors = {
      pending: "amber-darken-2",
      confirmed: "primary-blue",
      completed: "success",
      cancelled: "error",
      no_show: "grey-darken-1",
  };

  const appointmentHeaders = [
      { title: "Date & time", key: "datetime", sortable: false, minWidth: "140px" },
      { title: "Service", key: "service", sortable: false },
      { title: "Status", key: "status", sortable: false, width: "120px" },
      { title: "Patient note", key: "note", sortable: false, minWidth: "140px" },
      { title: "Doctor's comment", key: "doctor_comment", sortable: false, minWidth: "220px" },
      { title: "", key: "actions", sortable: false, align: "end", width: "88px" },
  ];

  function formatStatus(status) {
      const s = String(status || "pending").toLowerCase();
      if (s === "no_show") return "No show";
      return s ? s.charAt(0).toUpperCase() + s.slice(1) : "—";
  }

  function statusColor(status) {
      const s = String(status || "pending").toLowerCase();
      return statusColors[s] || "grey";
  }

  function formatVisit(a) {
      return appointmentPartsFromApi(a.appointment_date);
  }

  function setDraft(id, value) {
      draftComment[id] = value ?? "";
  }

  function isDirty(a) {
      const d = draftComment[a.id];
      const o = originalComment[a.id] ?? "";
      return String(d ?? "") !== String(o);
  }

  function seedDrafts(list) {
      Object.keys(draftComment).forEach((k) => delete draftComment[k]);
      Object.keys(originalComment).forEach((k) => delete originalComment[k]);
      for (const p of list) {
          for (const a of p.appointments) {
              const c = a.doctor_comment ?? "";
              draftComment[a.id] = c;
              originalComment[a.id] = c;
          }
      }
  }

  async function loadPatients() {
      fetchError.value = "";
      loading.value = true;
      try {
          const query = {
              page: page.value,
              per_page: perPage,
          };
          const q = search.value.trim();
          if (q) {
              query.search = q;
          }
          const res = await $fetch(`${apiBase.value}/api/appointments/patients`, { query });
          const rows = res?.data;
          patients.value = Array.isArray(rows) ? rows : [];
          lastPage.value = Math.max(1, Number(res?.last_page) || 1);
          totalPatients.value = Number(res?.total) || 0;
          if (res?.current_page != null) {
              page.value = Number(res.current_page);
          }
          seedDrafts(patients.value);
      } catch (e) {
          console.error(e);
          fetchError.value = "Could not load patients. Is the API running?";
          patients.value = [];
          lastPage.value = 1;
          totalPatients.value = 0;
      } finally {
          loading.value = false;
      }
  }

  watch(search, () => {
      if (searchDebounce) clearTimeout(searchDebounce);
      searchDebounce = setTimeout(() => {
          page.value = 1;
          loadPatients();
      }, 400);
  });

  async function saveDoctorComment(appointmentId) {
      savingId.value = appointmentId;
      try {
          await $fetch(`${apiBase.value}/api/appointments/${appointmentId}`, {
              method: "PATCH",
              body: {
                  doctor_comment: draftComment[appointmentId] ?? "",
              },
          });
          originalComment[appointmentId] = draftComment[appointmentId] ?? "";
          showToast("Doctor's comment saved.", "success");
      } catch (e) {
          console.error(e);
          const msg =
              e?.data?.message ||
              e?.response?._data?.message ||
              "Could not save comment.";
          showToast(msg, "error");
      } finally {
          savingId.value = null;
      }
  }

  onMounted(() => {
      loadPatients();
  });
</script>

<style scoped>
  .patients-page {
      min-height: 100%;
  }

  .patient-panels :deep(.v-expansion-panel) {
      border-color: rgba(0, 0, 0, 0.08) !important;
  }

  .appointments-nested-table {
      background: transparent;
  }

  .appointments-nested-table :deep(.v-data-table__th) {
      font-weight: 600 !important;
  }

  .appointments-nested-table :deep(.v-data-table-footer) {
      display: none;
  }
</style>
