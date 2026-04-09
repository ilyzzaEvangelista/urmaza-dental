<template>
  <v-snackbar
      v-model="snackbar"
      :color="snackbarColor"
      location="top"
      :timeout="4000"
      rounded="lg"
  >
      {{ snackbarText }}
  </v-snackbar>

  <v-dialog v-model="internalValue" max-width="600" scrollable persistent theme="light">
      <v-card class="rounded-lg shadow-xl overflow-hidden">
          <!-- Header -->
          <v-card-title class="pa-6 text-center position-relative border-b">
              <span class="text-h4 font-weight-medium text-grey-darken-3">Appointment Request Form</span>
              <v-btn
                  icon="mdi-close"
                  variant="text"
                  class="position-absolute"
                  style="right: 16px; top: 16px"
                  @click="close"
              ></v-btn>
          </v-card-title>

          <v-card-text class="pa-8">
              <v-form ref="form" v-model="valid" lazy-validation>
                  <v-text-field
                      v-model="formData.name"
                      label="Name"
                      variant="outlined"
                      density="comfortable"
                      class="mb-4"
                      placeholder="Full Name"
                      persistent-placeholder
                      :rules="[v => !!v || 'Name is required']"
                  ></v-text-field>

                  <v-text-field
                      v-model="formData.age"
                      label="Age"
                      type="number"
                      variant="outlined"
                      density="comfortable"
                      class="mb-4"
                      placeholder="Age"
                      persistent-placeholder
                      :rules="[v => !!v || 'Age is required']"
                  ></v-text-field>

                  <v-text-field
                      v-model="formData.email"
                      label="Email"
                      type="email"
                      variant="outlined"
                      density="comfortable"
                      class="mb-4"
                      placeholder="Email Address"
                      persistent-placeholder
                      :rules="[v => !!v || 'Email is required', v => /.+@.+\..+/.test(v) || 'E-mail must be valid']"
                  ></v-text-field>

                  <v-text-field
                      v-model="formData.contact_number"
                      label="Contact Number"
                      variant="outlined"
                      density="comfortable"
                      class="mb-4"
                      placeholder="Mobile or Phone Number"
                      persistent-placeholder
                      :rules="[v => !!v || 'Contact number is required']"
                  ></v-text-field>

                  <v-select
                      v-model="formData.service"
                      label="Select Service"
                      :items="services"
                      item-title="name"
                      item-value="name"
                      variant="outlined"
                      density="comfortable"
                      class="mb-4"
                      placeholder="Choose a service"
                      persistent-placeholder
                      :rules="[v => !!v || 'Please select a service']"
                  ></v-select>

                  <v-text-field
                      v-model="formData.appointment_date"
                      label="Appointment date & time"
                      type="datetime-local"
                      variant="outlined"
                      density="comfortable"
                      class="mb-2"
                      hint="Philippine time (Asia/Manila). Choose when you would like to come in."
                      persistent-hint
                      :loading="slotAvailability === 'checking'"
                      :rules="[v => !!v || 'Date and time are required']"
                  ></v-text-field>

                  <v-alert
                      v-if="slotAvailability === 'taken_pending'"
                      type="warning"
                      variant="tonal"
                      density="compact"
                      rounded="lg"
                      class="mb-4 text-body-2"
                  >
                      This slot already has a pending appointment. Please choose another time.
                  </v-alert>
                  <v-alert
                      v-else-if="slotAvailability === 'taken'"
                      type="warning"
                      variant="tonal"
                      density="compact"
                      rounded="lg"
                      class="mb-4 text-body-2"
                  >
                      This date and time is already reserved. Please pick another slot.
                  </v-alert>
                  <v-alert
                      v-else-if="slotAvailability === 'error'"
                      type="error"
                      variant="tonal"
                      density="compact"
                      rounded="lg"
                      class="mb-4 text-body-2"
                  >
                      Could not verify availability. Check your connection and try again.
                  </v-alert>

                  <v-textarea
                      v-model="formData.note"
                      label="Notes (Optional)"
                      variant="outlined"
                      density="comfortable"
                      rows="3"
                      class="mb-6"
                      placeholder="Any special requests or details"
                      persistent-placeholder
                      prepend-inner-icon="mdi-note-text-outline"
                  ></v-textarea>

                  <v-file-input
                      v-model="formData.image"
                      label="Attach Image/X-ray (Optional)"
                      variant="outlined"
                      density="comfortable"
                      prepend-inner-icon="mdi-camera"
                      prepend-icon=""
                      accept="image/*"
                      class="mb-8"
                      rounded="lg"
                      placeholder="Select a file"
                      persistent-placeholder
                  ></v-file-input>

                  <v-btn
                      block
                      color="primary-blue"
                      size="large"
                      class="font-weight-bold py-4 text-white"
                      elevation="2"
                      :loading="loading"
                      :disabled="submitDisabled"
                      @click="submit"
                  >
                      Submit
                  </v-btn>
              </v-form>
          </v-card-text>
      </v-card>
  </v-dialog>
</template>

<script setup>
  const props = defineProps({
      modelValue: Boolean,
  });

  const emit = defineEmits(["update:modelValue", "success"]);

  const refreshAdminPendingConfirmCount = inject("refreshAdminPendingConfirmCount", null);

  const apiBase = usePublicApiBase();

  const internalValue = computed({
      get: () => props.modelValue,
      set: (val) => emit("update:modelValue", val),
  });

  const form = ref(null);
  const valid = ref(false);
  const loading = ref(false);

  /** null = not checked, 'checking', 'available', 'taken', 'error' */
  const slotAvailability = ref(null);
  let availabilityDebounce = null;

  const submitDisabled = computed(() => {
      return slotAvailability.value === "taken" || slotAvailability.value === "checking";
  });

  const snackbar = ref(false);
  const snackbarText = ref("");
  const snackbarColor = ref("success");

  const showToast = (message, color = "success") => {
      snackbarText.value = message;
      snackbarColor.value = color;
      snackbar.value = true;
  };

  const formData = ref({
      name: "",
      age: "",
      email: "",
      contact_number: "",
      service: null,
      appointment_date: "",
      note: "",
      image: null,
  });

  const services = [
      { name: "Oral Prophylaxis" },
      { name: "Tooth Restoration" },
      { name: "Tooth Extraction" },
      { name: "Wisdom Tooth Extraction" },
      { name: "Root Canal Treatment" },
      { name: "Crowns, Bridges, & Dentures" },
      { name: "Dental Implants" },
      { name: "Orthodontics" },
      { name: "Cosmetic Dentistry" },
      { name: "Pediatric Dentistry" },
      { name: "Periapical Xray" },
  ];

  const close = () => {
      internalValue.value = false;
      resetForm();
  };

  const resetForm = () => {
      if (form.value) form.value.reset();
      slotAvailability.value = null;
      formData.value = {
          name: "",
          age: "",
          email: "",
          contact_number: "",
          service: null,
          appointment_date: "",
          note: "",
          image: null,
      };
  };

  async function checkSlotAvailability(datetime) {
      if (!datetime) {
          slotAvailability.value = null;
          return;
      }
      slotAvailability.value = "checking";
      try {
          const result = await $fetch(`${apiBase.value}/api/appointments/availability`, {
              query: { datetime },
          });
          if (result.available) {
              slotAvailability.value = "available";
          } else if (result.blocked_by_pending) {
              slotAvailability.value = "taken_pending";
          } else {
              slotAvailability.value = "taken";
          }
      } catch {
          slotAvailability.value = "error";
      }
  }

  watch(
      () => formData.value.appointment_date,
      (val) => {
          slotAvailability.value = null;
          if (availabilityDebounce) clearTimeout(availabilityDebounce);
          if (!val) return;
          availabilityDebounce = setTimeout(() => {
              checkSlotAvailability(val);
          }, 450);
      },
  );

  const submit = async () => {
      const { valid: isFormValid } = await form.value.validate();

      if (!isFormValid) return;

      if (formData.value.appointment_date) {
          await checkSlotAvailability(formData.value.appointment_date);
      }

      if (slotAvailability.value === "taken_pending") {
          showToast("That slot already has a pending appointment. Please choose another time.", "error");
          return;
      }

      if (slotAvailability.value === "taken") {
          showToast("That time slot is already reserved. Please choose another.", "error");
          return;
      }

      if (slotAvailability.value === "error") {
          showToast("Could not verify the time slot. Try again.", "error");
          return;
      }

      loading.value = true;

      try {
          const data = new FormData();
          data.append("name", formData.value.name);
          data.append("age", formData.value.age);
          data.append("email", formData.value.email);
          data.append("contact_number", formData.value.contact_number);
          data.append("service", formData.value.service);
          data.append("appointment_date", formData.value.appointment_date);
          data.append("note", formData.value.note || "");

          if (formData.value.image) {
              data.append("image", formData.value.image);
          }

          const response = await $fetch(`${apiBase.value}/api/appointments`, {
              method: "POST",
              body: data,
          });

          showToast("Appointment request submitted successfully!");
          emit("success", response.data);
          if (typeof refreshAdminPendingConfirmCount === "function") {
              refreshAdminPendingConfirmCount();
          }
          close();
      } catch (error) {
          console.error("Submission failed:", error);
          const msg =
              error?.data?.message ||
              error?.response?._data?.message ||
              "Failed to submit appointment. Please try again.";
          showToast(msg, "error");
      } finally {
          loading.value = false;
      }
  };
</script>

<style scoped>
  .shadow-xl {
      box-shadow:
          0 20px 25px -5px rgba(0, 0, 0, 0.1),
          0 10px 10px -5px rgba(0, 0, 0, 0.04);
  }
</style>