<template>
  <v-container fluid class="pa-8 dashboard-container">
      <div class="d-flex align-center mb-8">
          <v-spacer></v-spacer>
          <v-btn
              color="primary-blue"
              prepend-icon="mdi-plus"
              size="large"
              rounded="lg"
              class="text-white"
              @click="showBooking = true"
          >
              New Appointment
          </v-btn>
      </div>

      <AppointmentDialog v-model="showBooking" @success="loadAppointments" />

      <AppointmentAdminDialogs
          v-model:view-open="viewOpen"
          v-model:edit-open="editOpen"
          v-model:delete-confirm-open="deleteConfirmOpen"
          :api-base="apiBase"
          :viewing="viewing"
          :editing="editing"
          :item-to-delete="itemToDelete"
          @saved="loadAppointments"
          @deleted="onAppointmentDeleted"
          @error="fetchError = $event"
      />

      <v-alert v-if="fetchError" type="error" variant="tonal" class="mb-6" closable @click:close="fetchError = ''">
          {{ fetchError }}
      </v-alert>

      <!--  Recent Appointments Table -->
      <v-card class="appointments-card rounded-xl border shadow-sm" flat >
          <v-card-title class="pa-6 d-flex align-center">
              <span class="text-h6 font-weight-bold text-grey-darken-4">Recent Appointments</span>
              <v-spacer></v-spacer>
              <v-btn color="primary-blue" density="comfortable" variant="outlined" size="default" @click="refreshAppointments">
                <v-icon class="mr-2" icon="mdi-refresh" color="primary-blue" density="comfortable" variant="flat" size="small"></v-icon>
                Refresh
              </v-btn>
          </v-card-title>

          <v-data-table
              class="appointments-table px-4 text-grey-darken-3"
              :headers="tableHeaders"
              :items="appointments"
              :loading="loading"
              item-value="id"
              :items-per-page="-1"
              hide-default-footer
              no-data-text=""
          >
              <template #no-data>
                  <div v-if="!loading" class="text-center py-8 text-grey-darken-1">
                      <v-icon icon="mdi-calendar-blank" size="large" class="mb-2 d-block mx-auto"></v-icon>
                      No appointments found.
                  </div>
              </template>

              <template #item.name="{ item }">
                  <div class="d-flex align-center py-2">
                      <v-avatar color="primary-blue-lighten-4" size="36" class="mr-3">
                          <span class="text-caption font-weight-bold text-primary-blue">{{ item.initials }}</span>
                      </v-avatar>
                      <div>
                          <div class="font-weight-bold">{{ item.name }}</div>
                          <div class="text-caption text-grey-darken-1">
                            <v-icon icon="mdi-phone" color="primary-blue" size="small" class="mr-1"></v-icon>
                            Contact: {{ item.phone }}</div>
                      </div>
                  </div>
              </template>

              <template #item.date="{ item }">
                  <div class="font-weight-medium">{{ item.date }}</div>
                  <div v-if="item.time" class="text-caption text-grey-darken-1">{{ item.time }}</div>
              </template>

              <template #item.status="{ item }">
                  <v-chip :color="item.statusColor" size="small" class="font-weight-bold px-4" variant="flat">
                      {{ item.status }}
                  </v-chip>
              </template>

              <template #item.actions="{ item }">
                  <div class="text-end d-flex flex-wrap ga-1 justify-end">
                      <v-btn
                          size="small"
                          variant="text"
                          color="primary-blue"
                          prepend-icon="mdi-eye-outline"
                          class="text-none"
                          @click="openView(item)"
                      >
                          View
                      </v-btn>
                      <v-btn
                          size="small"
                          variant="text"
                          color="grey-darken-2"
                          prepend-icon="mdi-pencil-outline"
                          class="text-none"
                          @click="openEdit(item)"
                      >
                          Edit
                      </v-btn>
                      <v-btn
                          size="small"
                          variant="text"
                          color="error"
                          prepend-icon="mdi-delete-outline"
                          class="text-none"
                          @click="openDeleteConfirm(item)"
                      >
                          Delete
                      </v-btn>
                  </div>
              </template>
          </v-data-table>
      </v-card>
  </v-container>
</template>

<script setup>
  import { appointmentPartsFromApi } from "~/utils/appointmentDatetime";

  definePageMeta({
      layout: "admin",
  });

  const showBooking = ref(false);
  const loading = ref(false);
  const fetchError = ref("");

  const viewOpen = ref(false);
  const viewing = ref(null);
  const editOpen = ref(false);
  const editing = ref(null);

  const deleteConfirmOpen = ref(false);
  const itemToDelete = ref(null);

  const config = useRuntimeConfig();
  const apiBase = config.public.apiBase || "http://localhost:8000";

  const statusColors = {
      pending: "amber-darken-2",
      confirmed: "primary-blue",
      completed: "success",
      cancelled: "error",
  };

  function initialsFromName(name) {
      if (!name || typeof name !== "string") return "?";
      const parts = name.trim().split(/\s+/).filter(Boolean);
      if (parts.length === 0) return "?";
      if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function mapAppointment(row) {
      const status = String(row.status || "pending").toLowerCase();
      const label = status.charAt(0).toUpperCase() + status.slice(1);
      const parts = appointmentPartsFromApi(row.appointment_date);
      return {
          id: row.id,
          name: row.name,
          phone: row.contact_number,
          contact_number: row.contact_number,
          email: row.email,
          age: row.age,
          service: row.service,
          date: parts.dateLabel,
          time: parts.timeLabel,
          status: label,
          statusColor: statusColors[status] || "grey",
          initials: initialsFromName(row.name),
          note: row.note || "",
          image: row.image || null,
          appointment_date_raw: parts.rawLocal,
          statusKey: status,
      };
  }

  function openView(item) {
      viewing.value = item;
      viewOpen.value = true;
  }

  function openEdit(item) {
      editing.value = {
          ...item,
          note: item.note ?? "",
          age: Number(item.age),
      };
      editOpen.value = true;
  }

  function openDeleteConfirm(item) {
      itemToDelete.value = item;
      deleteConfirmOpen.value = true;
  }

  function onAppointmentDeleted(id) {
      if (viewing.value?.id === id) {
          viewOpen.value = false;
          viewing.value = null;
      }
      if (editing.value?.id === id) {
          editOpen.value = false;
          editing.value = null;
      }
      itemToDelete.value = null;
      loadAppointments();
  }

  const appointments = ref([]);

  const tableHeaders = [
      { title: "Patient", key: "name", sortable: true },
      { title: "Service", key: "service", sortable: true },
      { title: "Date & Time", key: "date", sortable: true },
      { title: "Status", key: "status", sortable: true },
      { title: "Actions", key: "actions", align: "end", sortable: false, minWidth: "300px" },
  ];

  async function loadAppointments() {
      fetchError.value = "";
      loading.value = true;
      try {
          const rows = await $fetch(`${apiBase}/api/appointments`, {
              query: { limit: 50 },
          });
          appointments.value = Array.isArray(rows) ? rows.map(mapAppointment) : [];
      } catch (e) {
          console.error(e);
          fetchError.value = "Could not load appointments. Is the API running?";
          appointments.value = [];
      } finally {
          loading.value = false;
      }
  }

  function refreshAppointments() {
      loadAppointments();
  }

  onMounted(() => {
      loadAppointments();
  });
</script>

<style scoped>
  .stat-card,
  .appointments-card,
  .appointments-table,
  .appointments-table :deep(.v-data-table__thead),
  .appointments-table :deep(.v-data-table__tbody) {
      background-color: white !important;
      color: #333333 !important;
  }

  .appointments-table :deep(th) {
      font-weight: 700 !important;
      color: #616161 !important;
  }

  .stat-card,
  .appointments-card {
      border-color: #ededed !important;
  }

  .dashboard-container {
      background-color: #f9fafb !important;
      min-height: 100vh;
  }

  .shadow-sm {
      box-shadow:
          0 1px 3px 0 rgba(0, 0, 0, 0.1),
          0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }
</style>