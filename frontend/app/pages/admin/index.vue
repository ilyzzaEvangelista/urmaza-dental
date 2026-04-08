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

      <AppointmentDialog v-model="showBooking" @success="onAppointmentSaved" />

      <AppointmentAdminDialogs
          v-model:view-open="viewOpen"
          v-model:edit-open="editOpen"
          v-model:delete-confirm-open="deleteConfirmOpen"
          :api-base="apiBase"
          :viewing="viewing"
          :editing="editing"
          :item-to-delete="itemToDelete"
          @saved="refreshDashboard"
          @deleted="onAppointmentDeleted"
          @error="fetchError = $event"
      />

      <v-alert v-if="fetchError" type="error" variant="tonal" class="mb-6" closable @click:close="fetchError = ''">
          {{ fetchError }}
      </v-alert>

      <AdminPatientAnalytics ref="analyticsRef" :api-base="apiBase" />

      <v-row class="mt-6 align-stretch" dense>
          <v-col cols="12" md="8">
              <RecentAppointmentTable
                  :appointments="appointments"
                  :loading="loading"
                  :status-filter="appointmentsTableStatusFilter"
                  @refresh="refreshAppointments"
                  @view="openView"
                  @edit="openEdit"
                  @delete="openDeleteConfirm"
              />
          </v-col>
          <v-col cols="12" md="4">
              <AdminUpcomingAppointmentsBoard
                  ref="upcomingRef"
                  :api-base="apiBase"
                  @view="openView"
                  @edit="openEdit"
                  @delete="openDeleteConfirm"
              />
          </v-col>
      </v-row>
  </v-container>
</template>

<script setup>
  import { appointmentPartsFromApi } from "~/utils/appointmentDatetime";

  definePageMeta({
      layout: "admin",
  });

  const showBooking = ref(false);
  const analyticsRef = ref(null);
  const upcomingRef = ref(null);
  const fetchError = ref("");

  const viewOpen = ref(false);
  const viewing = ref(null);
  const editOpen = ref(false);
  const editing = ref(null);

  const deleteConfirmOpen = ref(false);
  const itemToDelete = ref(null);

  const apiBase = usePublicApiBase();

  const loading = ref(false);
  const appointments = ref([]);

  /** Passed to the table and `GET /api/appointments`. Empty array = all statuses. */
  const appointmentsTableStatusFilter = ["pending", "confirmed"];

  const statusColors = {
      pending: "amber-darken-2",
      confirmed: "teal-lighten-1",
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

  async function loadAppointments() {
      fetchError.value = "";
      loading.value = true;
      try {
          const query = { page: 1, per_page: 10 };
          const sf = appointmentsTableStatusFilter;
          if (Array.isArray(sf) && sf.length > 0) {
              // PHP/Laravel need `status[]=` so duplicate keys are not collapsed to one value.
              query["status[]"] = sf;
          } else if (typeof sf === "string" && sf.trim()) {
              query.status = sf.trim();
          }
          const res = await $fetch(`${apiBase.value}/api/appointments`, { query });
          if (res && Array.isArray(res.data)) {
              appointments.value = res.data.map(mapAppointment);
          } else {
              appointments.value = [];
          }
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
      refreshDashboard();
  }

  function refreshAnalytics() {
      analyticsRef.value?.reload?.();
  }

  function refreshDashboard() {
      upcomingRef.value?.reload?.();
      refreshAnalytics();
      loadAppointments();
  }

  function onAppointmentSaved() {
      refreshDashboard();
  }

  onMounted(() => {
      loadAppointments();
  });
</script>

<style scoped>
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
