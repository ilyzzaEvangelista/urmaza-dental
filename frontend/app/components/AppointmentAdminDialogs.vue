<template>
  <v-snackbar v-model="toastOpen" :color="toastColor" location="top" :timeout="4000" rounded="lg">
      {{ toastMessage }}
  </v-snackbar>

  <!-- View appointment -->
  <v-dialog v-model="viewOpen" max-width="560" scrollable>
      <v-card v-if="viewing" rounded="lg">
          <v-card-title class="d-flex align-center pa-5 border-b">
              <span class="text-h6 font-weight-bold">Appointment Details</span>
              <v-spacer></v-spacer>
              <v-btn icon="mdi-close" variant="text" density="comfortable" @click="viewOpen = false"></v-btn>
          </v-card-title>
          <v-card-text class="pa-5">
              <v-list density="compact" class="bg-transparent pa-0">
                  <v-list-item class="px-0">
                      <v-list-item-title class="text-caption text-grey-darken-1">Patient</v-list-item-title>
                      <v-list-item-subtitle class="text-body-1 text-wrap text-high-emphasis opacity-100 mt-1">
                          {{ viewing.name }}
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item class="px-0 mt-3">
                      <v-list-item-title class="text-caption text-grey-darken-1">Age</v-list-item-title>
                      <v-list-item-subtitle class="text-body-1 text-high-emphasis opacity-100 mt-1">
                          {{ viewing.age }}
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item class="px-0 mt-3">
                      <v-list-item-title class="text-caption text-grey-darken-1">Email</v-list-item-title>
                      <v-list-item-subtitle class="text-body-1 text-wrap text-high-emphasis opacity-100 mt-1">
                          {{ viewing.email }}
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item class="px-0 mt-3">
                      <v-list-item-title class="text-caption text-grey-darken-1">Contact</v-list-item-title>
                      <v-list-item-subtitle class="text-body-1 text-high-emphasis opacity-100 mt-1">
                          {{ viewing.contact_number }}
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item class="px-0 mt-3">
                      <v-list-item-title class="text-caption text-grey-darken-1">Service</v-list-item-title>
                      <v-list-item-subtitle class="text-body-1 text-wrap text-high-emphasis opacity-100 mt-1">
                          {{ viewing.service }}
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item class="px-0 mt-3">
                      <v-list-item-title class="text-caption text-grey-darken-1"
                          >Appointment date & time</v-list-item-title
                      >
                      <v-list-item-subtitle class="text-body-1 text-high-emphasis opacity-100 mt-1">
                          <div>{{ viewing.date }}</div>
                          <div v-if="viewing.time" class="text-caption text-medium-emphasis mt-1">
                              {{ viewing.time }}
                          </div>
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item class="px-0 mt-3">
                      <v-list-item-title class="text-caption text-grey-darken-1">Status</v-list-item-title>
                      <v-list-item-subtitle class="mt-1">
                          <v-chip :color="viewing.statusColor" size="small" class="font-weight-bold" variant="flat">
                              {{ viewing.status }}
                          </v-chip>
                      </v-list-item-subtitle>
                  </v-list-item>
                  <v-list-item v-if="viewing.note" class="px-0 mt-3 align-start">
                      <v-list-item-title class="text-caption text-grey-darken-1">Notes</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2 text-wrap text-high-emphasis opacity-100 mt-1">
                          {{ viewing.note }}
                      </v-list-item-subtitle>
                  </v-list-item>
                  <div v-if="viewing.image" class="mt-4">
                      <div class="text-caption text-grey-darken-1 mb-2">Attachment</div>
                      <v-img
                          :src="resolvedStorageUrl(viewing.image)"
                          max-height="240"
                          rounded="lg"
                          cover
                          class="border"
                          @error="attachmentLoadError = true"
                          @load="attachmentLoadError = false"
                      ></v-img>
                      <p v-if="attachmentLoadError" class="text-caption text-error mt-2 mb-0">
                          Could not load this image. Run <code>php artisan storage:link</code> in the Laravel project
                          so <code>/storage</code> serves files from <code>storage/app/public</code>.
                      </p>
                  </div>
              </v-list>
          </v-card-text>
      </v-card>
  </v-dialog>

  <!-- Edit appointment -->
  <v-dialog v-model="editOpen" max-width="560" scrollable persistent>
      <v-card v-if="editing" rounded="lg">
          <v-card-title class="d-flex align-center pa-5 border-b">
              <span class="text-h6 font-weight-bold">Edit appointment</span>
              <v-spacer></v-spacer>
              <v-btn
                  icon="mdi-close"
                  variant="text"
                  density="comfortable"
                  :disabled="editSaving"
                  @click="editOpen = false"
              ></v-btn>
          </v-card-title>
          <v-card-text class="pa-5">
              <v-form ref="editFormRef" @submit.prevent="saveEdit">
                  <v-text-field
                      v-model="editing.name"
                      label="Name"
                      variant="outlined"
                      density="comfortable"
                      class="mb-3"
                      :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                  <v-text-field
                      v-model.number="editing.age"
                      label="Age"
                      type="number"
                      variant="outlined"
                      density="comfortable"
                      class="mb-3"
                      :rules="[v => v != null && v !== '' || 'Required']"
                  ></v-text-field>
                  <v-text-field
                      v-model="editing.email"
                      label="Email"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      class="mb-3"
                      :rules="[v => !!v || 'Required', v => /.+@.+\..+/.test(v) || 'Invalid email']"
                  ></v-text-field>
                  <v-text-field
                      v-model="editing.contact_number"
                      label="Contact number"
                      variant="outlined"
                      density="comfortable"
                      class="mb-3"
                      :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                  <v-select
                      v-model="editing.service"
                      label="Service"
                      :items="editServiceItems"
                      variant="outlined"
                      density="comfortable"
                      class="mb-3"
                      :rules="[v => !!v || 'Required']"
                  ></v-select>
                  <v-text-field
                      v-model="editing.appointment_date_raw"
                      label="Appointment date & time"
                      type="datetime-local"
                      variant="outlined"
                      density="comfortable"
                      class="mb-2"
                      hint="Philippine time (Asia/Manila)."
                      persistent-hint
                      :loading="editSlotAvailability === 'checking'"
                      :rules="[v => !!v || 'Required']"
                  ></v-text-field>
                  <v-alert
                      v-if="editSlotAvailability === 'taken_pending'"
                      type="warning"
                      variant="tonal"
                      density="compact"
                      rounded="lg"
                      class="mb-4 text-body-2"
                  >
                      This slot already has a pending appointment. Choose another time.
                  </v-alert>
                  <v-alert
                      v-else-if="editSlotAvailability === 'taken'"
                      type="warning"
                      variant="tonal"
                      density="compact"
                      rounded="lg"
                      class="mb-4 text-body-2"
                  >
                      This date and time is already reserved. Please pick another slot.
                  </v-alert>
                  <v-alert
                      v-else-if="editSlotAvailability === 'error'"
                      type="error"
                      variant="tonal"
                      density="compact"
                      rounded="lg"
                      class="mb-4 text-body-2"
                  >
                      Could not verify availability. Check your connection and try again.
                  </v-alert>
                  <v-select
                      v-model="editing.statusKey"
                      label="Status"
                      :items="statusOptions"
                      item-title="title"
                      item-value="value"
                      variant="outlined"
                      density="comfortable"
                      class="mb-3"
                  ></v-select>
                  <v-textarea
                      v-model="editing.note"
                      label="Notes"
                      variant="outlined"
                      density="comfortable"
                      rows="3"
                      class="mb-2"
                  ></v-textarea>
              </v-form>
          </v-card-text>
          <v-card-actions class="pa-4 border-t">
              <v-spacer></v-spacer>
              <v-btn variant="text" rounded="lg" :disabled="editSaving" @click="editOpen = false">Cancel</v-btn>
              <v-btn
                  color="primary-blue"
                  variant="flat"
                  class="text-white"
                  rounded="lg"
                  :loading="editSaving"
                  :disabled="editSaveDisabled"
                  @click="saveEdit"
              >
                  Save
              </v-btn>
          </v-card-actions>
      </v-card>
  </v-dialog>

  <!-- Delete confirmation -->
  <v-dialog v-model="deleteConfirmOpen" max-width="420" persistent>
      <v-card rounded="lg">
          <v-card-title class="text-h6 font-weight-bold pa-5">Delete appointment?</v-card-title>
          <v-card-text class="text-body-2 text-medium-emphasis px-5 pt-0">
              This will permanently remove the appointment
              <span v-if="itemToDelete"> for <strong>{{ itemToDelete.name }}</strong></span
              >. This cannot be undone.
          </v-card-text>
          <v-card-actions class="pa-4 border-t">
              <v-spacer></v-spacer>
              <v-btn variant="text" rounded="lg" :disabled="deleteSaving" @click="deleteConfirmOpen = false">
                  Cancel
              </v-btn>
              <v-btn color="error" variant="flat" rounded="lg" :loading="deleteSaving" @click="executeDelete">
                  Delete
              </v-btn>
          </v-card-actions>
      </v-card>
  </v-dialog>
</template>

<script setup>
  import { resolveApiBaseString } from "~/utils/apiBase";

  const props = defineProps({
      apiBase: {
          type: String,
          required: true,
      },
      viewing: {
          type: Object,
          default: null,
      },
      editing: {
          type: Object,
          default: null,
      },
      itemToDelete: {
          type: Object,
          default: null,
      },
  });

  const viewOpen = defineModel("viewOpen", { type: Boolean, default: false });
  const editOpen = defineModel("editOpen", { type: Boolean, default: false });
  const deleteConfirmOpen = defineModel("deleteConfirmOpen", { type: Boolean, default: false });

  const emit = defineEmits(["saved", "deleted", "error"]);

  const apiOrigin = computed(() => resolveApiBaseString(props.apiBase));
  const authHeaders = useAuthFetchHeaders();

  const attachmentLoadError = ref(false);
  const editSaving = ref(false);
  const deleteSaving = ref(false);
  const editFormRef = ref(null);

  /** null | 'checking' | 'available' | 'taken' | 'taken_pending' | 'error' */
  const editSlotAvailability = ref(null);
  let editAvailabilityDebounce = null;

  const editSaveDisabled = computed(() => {
      const s = editSlotAvailability.value;
      return s === "taken" || s === "taken_pending" || s === "checking";
  });

  const serviceOptions = [
      "Oral Prophylaxis",
      "Tooth Restoration",
      "Tooth Extraction",
      "Wisdom Tooth Extraction",
      "Root Canal Treatment",
      "Crowns, Bridges, & Dentures",
      "Dental Implants",
      "Orthodontics",
      "Cosmetic Dentistry",
      "Pediatric Dentistry",
      "Periapical Xray",
  ];

  const statusOptions = [
      { title: "Reserved", value: "pending" },
      { title: "Confirmed", value: "confirmed" },
      { title: "Completed", value: "completed" },
      { title: "Cancelled", value: "cancelled" },
      { title: "No Show", value: "no_show" },
  ];

  const editServiceItems = computed(() => {
      const s = props.editing?.service;
      const base = [...serviceOptions];
      if (s && !base.includes(s)) base.unshift(s);
      return base;
  });

  watch(
      () => viewOpen.value,
      (open) => {
          if (open) attachmentLoadError.value = false;
      }
  );

  async function checkEditSlotAvailability(datetime, exceptId) {
      if (!datetime) {
          editSlotAvailability.value = null;
          return;
      }
      editSlotAvailability.value = "checking";
      try {
          const result = await $fetch(`${props.apiBase}/api/appointments/availability`, {
              query: {
                  datetime,
                  except_id: exceptId,
              },
          });
          if (result.available) {
              editSlotAvailability.value = "available";
          } else if (result.blocked_by_pending) {
              editSlotAvailability.value = "taken_pending";
          } else {
              editSlotAvailability.value = "taken";
          }
      } catch {
          editSlotAvailability.value = "error";
      }
  }

  watch([editOpen, () => props.editing?.appointment_date_raw, () => props.editing?.id], ([open, dt, id]) => {
      if (editAvailabilityDebounce) clearTimeout(editAvailabilityDebounce);
      if (!open) {
          editSlotAvailability.value = null;
          return;
      }
      if (!dt || id == null) {
          editSlotAvailability.value = null;
          return;
      }
      editSlotAvailability.value = null;
      editAvailabilityDebounce = setTimeout(() => {
          checkEditSlotAvailability(dt, id);
      }, 450);
  });

  function resolvedStorageUrl(path) {
      if (!path) return "";
      const p = String(path).trim();
      if (/^https?:\/\//i.test(p)) return p;
      const key = p.replace(/^\/+/, "").replace(/^storage\//, "");
      return `${apiOrigin.value}/storage/${key}`;
  }

  const toastOpen = ref(false);
  const toastMessage = ref("");
  const toastColor = ref("primary");

  function showToast(message, color = "primary") {
      toastMessage.value = message;
      toastColor.value = color;
      toastOpen.value = true;
  }

  async function saveEdit() {
      const form = editFormRef.value;
      if (form) {
          const { valid } = await form.validate();
          if (!valid) return;
      }

      const e = props.editing;
      if (!e) return;

      if (e.appointment_date_raw) {
          await checkEditSlotAvailability(e.appointment_date_raw, e.id);
      }

      if (editSlotAvailability.value === "taken_pending") {
          const msg = "That slot already has a pending appointment. Choose another time.";
          showToast(msg, "warning");
          emit("error", msg);
          return;
      }

      if (editSlotAvailability.value === "taken") {
          const msg = "That time slot is already reserved. Choose another date and time.";
          showToast(msg, "warning");
          emit("error", msg);
          return;
      }

      if (editSlotAvailability.value === "error") {
          const msg = "Could not verify the time slot. Try again.";
          showToast(msg, "error");
          emit("error", msg);
          return;
      }

      editSaving.value = true;
      emit("error", "");
      try {
          await $fetch(`${apiOrigin.value}/api/appointments/${e.id}`, {
              method: "PUT",
              headers: { ...authHeaders.value },
              body: {
                  name: e.name,
                  age: Number(e.age),
                  email: e.email,
                  contact_number: e.contact_number,
                  service: e.service,
                  appointment_date: e.appointment_date_raw,
                  note: e.note || "",
                  status: e.statusKey,
              },
          });
          editOpen.value = false;
          editSlotAvailability.value = null;
          showToast("Appointment updated.", "success");
          emit("saved");
      } catch (err) {
          console.error(err);
          const msg = err?.data?.message || err?.response?._data?.message || "Could not save appointment.";
          showToast(msg, "error");
          emit("error", msg);
      } finally {
          editSaving.value = false;
      }
  }

  async function executeDelete() {
      const row = props.itemToDelete;
      if (!row) return;

      deleteSaving.value = true;
      emit("error", "");
      try {
          await $fetch(`${apiOrigin.value}/api/appointments/${row.id}`, {
              method: "DELETE",
              headers: { ...authHeaders.value },
          });
          deleteConfirmOpen.value = false;
          showToast("Appointment deleted.", "success");
          emit("deleted", row.id);
      } catch (err) {
          console.error(err);
          const msg = "Could not delete appointment.";
          showToast(msg, "error");
          emit("error", msg);
      } finally {
          deleteSaving.value = false;
      }
  }
</script>