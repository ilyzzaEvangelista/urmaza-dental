<template>
  <v-container fluid class="pa-8 services-admin">
      <div class="d-flex align-center flex-wrap ga-4 mb-6">
          <h1 class="text-h5 font-weight-bold text-grey-darken-4">Services</h1>
          <v-spacer></v-spacer>
          <v-btn
              color="primary-blue"
              prepend-icon="mdi-plus"
              size="large"
              rounded="lg"
              class="text-white"
              @click="openCreate"
          >
              Add service
          </v-btn>
      </div>

      <v-alert v-if="fetchError" type="error" variant="tonal" class="mb-6" closable @click:close="fetchError = ''">
          {{ fetchError }}
      </v-alert>

      <v-card class="rounded-xl border shadow-sm" flat>
          <v-card-title class="pa-6 d-flex align-center flex-wrap ga-4">
              <span class="text-h6 font-weight-bold text-grey-darken-4">All services</span>
              <v-spacer></v-spacer>
              <v-btn color="primary-blue" variant="outlined" density="comfortable" @click="loadServices">
                  <v-icon class="mr-2" icon="mdi-refresh" size="small" />
                  Refresh
              </v-btn>
          </v-card-title>

          <v-data-table
              class="services-table px-4 pb-4"
              :headers="headers"
              :items="pagedServices"
              :loading="loading"
              item-value="id"
              :items-per-page="-1"
              hide-default-footer
          >
              <template #item.is_active="{ item }">
                  <v-chip
                      :color="item.is_active ? 'success' : 'grey'"
                      size="small"
                      variant="tonal"
                      class="font-weight-medium"
                  >
                      {{ item.is_active ? "Active" : "Inactive" }}
                  </v-chip>
              </template>

              <template #item.actions="{ item }">
                  <div class="d-flex flex-wrap ga-1 justify-end">
                      <v-btn size="small" variant="text" color="primary-blue" @click="openEdit(item)"> Edit </v-btn>
                      <v-btn size="small" variant="text" color="error" @click="openDelete(item)"> Delete </v-btn>
                  </div>
              </template>

              <template #no-data>
                  <div v-if="!loading" class="text-center py-8 text-grey-darken-1">
                      No services yet. Add one to get started.
                  </div>
              </template>
          </v-data-table>

          <v-card-actions v-show="totalPages > 1" class="justify-center pb-4 pt-0 flex-wrap pagination-actions">
              <v-pagination
                  :model-value="page"
                  :length="totalPages"
                  density="comfortable"
                  rounded="circle"
                  color="primary-blue"
                  :total-visible="7"
                  @update:model-value="onPageChange"
              />
          </v-card-actions>
      </v-card>

      <!-- Create / Edit -->
      <v-dialog v-model="formOpen" max-width="520" persistent scrollable>
          <v-card rounded="lg">
              <v-card-title class="d-flex align-center pa-5 border-b">
                  <span class="text-h6 font-weight-bold">{{ editingId ? "Edit service" : "New service" }}</span>
                  <v-spacer></v-spacer>
                  <v-btn icon="mdi-close" variant="text" :disabled="saving" @click="formOpen = false"></v-btn>
              </v-card-title>
              <v-card-text class="pa-5">
                  <v-form ref="formRef" @submit.prevent="save">
                      <v-text-field
                          v-model="form.name"
                          label="Name"
                          variant="outlined"
                          density="comfortable"
                          class="mb-3"
                          :rules="[(v) => !!v || 'Required']"
                      />
                      <v-text-field
                          v-model="form.description"
                          label="Description"
                          variant="outlined"
                          density="comfortable"
                          class="mb-3"
                          hide-details="auto"
                      />
                      <v-switch
                          v-model="form.is_active"
                          color="primary-blue"
                          label="Active (shown for new bookings)"
                          hide-details
                      />
                  </v-form>
              </v-card-text>
              <v-card-actions class="pa-5 pt-0">
                  <v-spacer></v-spacer>
                  <v-btn variant="text" :disabled="saving" @click="formOpen = false">Cancel</v-btn>
                  <v-btn color="primary-blue" :loading="saving" class="text-white" @click="save"> Save </v-btn>
              </v-card-actions>
          </v-card>
      </v-dialog>

      <!-- Delete -->
      <v-dialog v-model="deleteOpen" max-width="400">
          <v-card rounded="lg">
              <v-card-title class="text-h6 font-weight-bold">Delete service?</v-card-title>
              <v-card-text class="text-body-2 text-medium-emphasis">
                  {{ deleteTarget?.name ? `“${deleteTarget.name}” will be removed.` : "This cannot be undone." }}
              </v-card-text>
              <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn variant="text" :disabled="deleting" @click="deleteOpen = false">Cancel</v-btn>
                  <v-btn color="error" variant="flat" :loading="deleting" @click="confirmDelete">Delete</v-btn>
              </v-card-actions>
          </v-card>
      </v-dialog>
  </v-container>
</template>

<script setup>
  definePageMeta({
      layout: "admin",
      middleware: ["admin-only-services"],
  });

  const apiBase = usePublicApiBase();
  const authHeaders = useAuthFetchHeaders();
  const fetchError = ref("");
  const loading = ref(false);
  const services = ref([]);

  const perPage = 10;
  const page = ref(1);

  const totalPages = computed(() => {
      const n = services.value.length;
      return Math.max(1, Math.ceil(n / perPage));
  });

  const pagedServices = computed(() => {
      const list = services.value;
      const start = (page.value - 1) * perPage;
      return list.slice(start, start + perPage);
  });

  const pageRangeLabel = computed(() => {
      const total = services.value.length;
      if (!total) return "";
      const start = (page.value - 1) * perPage + 1;
      const end = Math.min(page.value * perPage, total);
      return `Showing ${start}–${end} of ${total}`;
  });

  /** Clamp after data changes only — avoids fighting `v-pagination` internal state (blink). */
  function clampPageToData() {
      const max = Math.max(1, Math.ceil(services.value.length / perPage));
      if (page.value > max) {
          page.value = max;
      }
  }

  function onPageChange(value) {
      page.value = value;
  }

  const headers = [
      { title: "Name", key: "name", sortable: true },
      { title: "Description", key: "description", sortable: false },
      { title: "Status", key: "is_active", sortable: true },
      { title: "Actions", key: "actions", align: "end", sortable: false, width: "160px" },
  ];

  const formOpen = ref(false);
  const formRef = ref(null);
  const editingId = ref(null);
  const saving = ref(false);
  const form = ref({
      name: "",
      description: "",
      is_active: true,
  });

  const deleteOpen = ref(false);
  const deleteTarget = ref(null);
  const deleting = ref(false);

  async function loadServices() {
      fetchError.value = "";
      loading.value = true;
      try {
          const rows = await $fetch(`${apiBase.value}/api/services`, {
              query: { include_inactive: 1 },
              headers: { ...authHeaders.value },
          });
          services.value = Array.isArray(rows) ? rows : [];
          clampPageToData();
      } catch (e) {
          console.error(e);
          fetchError.value = "Could not load services. Is the API running?";
          services.value = [];
          page.value = 1;
      } finally {
          loading.value = false;
      }
  }

  function openCreate() {
      editingId.value = null;
      form.value = { name: "", description: "", is_active: true };
      formOpen.value = true;
  }

  function openEdit(row) {
      editingId.value = row.id;
      form.value = {
          name: row.name || "",
          description: row.description || "",
          is_active: Boolean(row.is_active),
      };
      formOpen.value = true;
  }

  async function save() {
      const f = formRef.value;
      if (f) {
          const { valid } = await f.validate();
          if (!valid) return;
      }

      saving.value = true;
      fetchError.value = "";
      try {
          const body = {
              name: form.value.name.trim(),
              description: form.value.description?.trim() || null,
              is_active: form.value.is_active,
          };

          if (editingId.value) {
              await $fetch(`${apiBase.value}/api/services/${editingId.value}`, {
                  method: "PUT",
                  body,
              });
          } else {
              await $fetch(`${apiBase.value}/api/services`, {
                  method: "POST",
                  body,
              });
          }
          formOpen.value = false;
          await loadServices();
      } catch (e) {
          console.error(e);
          fetchError.value =
              e?.data?.message ||
              e?.response?._data?.message ||
              "Could not save service.";
      } finally {
          saving.value = false;
      }
  }

  function openDelete(row) {
      deleteTarget.value = row;
      deleteOpen.value = true;
  }

  async function confirmDelete() {
      const row = deleteTarget.value;
      if (!row?.id) {
          deleteOpen.value = false;
          return;
      }
      deleting.value = true;
      fetchError.value = "";
      try {
          await $fetch(`${apiBase.value}/api/services/${row.id}`, {
              method: "DELETE",
              headers: { ...authHeaders.value },
          });
          deleteOpen.value = false;
          deleteTarget.value = null;
          await loadServices();
      } catch (e) {
          console.error(e);
          fetchError.value = "Could not delete service.";
      } finally {
          deleting.value = false;
      }
  }

  onMounted(() => {
      loadServices();
  });
</script>

<style scoped>
  .services-admin {
      min-height: 100%;
  }

  .services-table :deep(th) {
      font-weight: 700 !important;
      color: #616161 !important;
  }

  .shadow-sm {
      box-shadow:
          0 1px 3px 0 rgba(0, 0, 0, 0.1),
          0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }

  .range-line {
      min-height: 1.25rem;
  }

  .pagination-actions {
      min-height: 48px;
  }

  .pagination-actions :deep(.v-pagination__list) {
      flex-wrap: wrap;
  }
</style>
