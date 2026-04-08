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
                      label="Appointment Date"
                      type="date"
                      variant="outlined"
                      density="comfortable"
                      class="mb-4"
                      placeholder="mm/dd/yyyy"
                      persistent-placeholder
                      :rules="[v => !!v || 'Date is required']"
                  ></v-text-field>

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

  const internalValue = computed({
      get: () => props.modelValue,
      set: (val) => emit("update:modelValue", val),
  });

  const form = ref(null);
  const valid = ref(false);
  const loading = ref(false);

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

  const submit = async () => {
      const { valid: isFormValid } = await form.value.validate();

      if (!isFormValid) return;

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

          // Assuming Laravel is running on localhost:8000
          const response = await $fetch("http://localhost:8000/api/appointments", {
              method: "POST",
              body: data,
          });

          showToast("Appointment request submitted successfully!");
          emit("success", response.data);
          close();
      } catch (error) {
          console.error("Submission failed:", error);
          showToast("Failed to submit appointment. Please try again.", "error");
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