<template>
  <v-container fluid class="pa-8 appointments-page">
      <div class="d-flex align-center flex-wrap ga-4 mb-6">
          <h1 class="text-h5 font-weight-bold text-grey-darken-4">Appointments</h1>
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
          @saved="loadAppointments"
          @deleted="onAppointmentDeleted"
          @error="fetchError = $event"
      />

      <v-alert v-if="fetchError" type="error" variant="tonal" class="mb-6" closable @click:close="fetchError = ''">
          {{ fetchError }}
      </v-alert>

      <AdminAppointmentsTable
          :appointments="appointments"
          :loading="loading"
          :page="appointmentsPage"
          :total-pages="appointmentsLastPage"
          @refresh="loadAppointments"
          @change-page="onAppointmentsPageChange"
          @view="openView"
          @edit="openEdit"
          @delete="openDeleteConfirm"
      />
  </v-container>
</template>

<script setup>
  import { appointmentPartsFromApi } from "~/utils/appointmentDatetime";

  definePageMeta({
      layout: "admin",
  });

  const showBooking = ref(false);
  const fetchError = ref("");
  const loading = ref(false);
  const appointments = ref([]);
  const appointmentsPage = ref(1);
  const appointmentsLastPage = ref(1);

  /** Empty string = load all statuses. Set to e.g. `pending` to filter like the admin table. */
  const appointmentsTableStatusFilter = "";

  const viewOpen = ref(false);
  const viewing = ref(null);
  const editOpen = ref(false);
  const editing = ref(null);

  const deleteConfirmOpen = ref(false);
  const itemToDelete = ref(null);

  const apiBase = usePublicApiBase();

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
          const query = { page: appointmentsPage.value };
          if (appointmentsTableStatusFilter) {
              query.status = appointmentsTableStatusFilter;
          }
          const res = await $fetch(`${apiBase.value}/api/appointments`, { query });
          if (res && Array.isArray(res.data)) {
              appointments.value = res.data.map(mapAppointment);
              appointmentsLastPage.value = Math.max(1, Number(res.last_page) || 1);
              if (res.current_page != null) {
                  appointmentsPage.value = Number(res.current_page);
              }
          } else {
              appointments.value = [];
              appointmentsLastPage.value = 1;
          }
      } catch (e) {
          console.error(e);
          fetchError.value = "Could not load appointments. Is the API running?";
          appointments.value = [];
          appointmentsLastPage.value = 1;
      } finally {
          loading.value = false;
      }
  }

  function onAppointmentsPageChange(page) {
      appointmentsPage.value = page;
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
      loadAppointments();
  }

  function onAppointmentSaved() {
      appointmentsPage.value = 1;
      loadAppointments();
  }

  onMounted(() => {
      loadAppointments();
  });
</script>

<style scoped>
  .appointments-page {
      min-height: 100%;
  }
</style>
