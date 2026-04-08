<template>
  <v-app>
    <v-app-bar color="primary" flat>
      <v-container class="d-flex align-center">
        <v-app-bar-title class="font-weight-bold text-white">Urmaza Dental</v-app-bar-title>
        <v-spacer></v-spacer>
        <v-btn variant="text" color="white" class="font-weight-medium">Features</v-btn>
        <v-btn variant="text" color="white" class="font-weight-medium">Pricing</v-btn>
        <v-btn variant="outlined" color="white" class="ml-4 rounded-pill">Book Appointment</v-btn>
      </v-container>
    </v-app-bar>

    <v-main>
      <v-container class="fill-height">
        <v-row class="align-center justify-center text-center">
          <v-col cols="12" md="8" lg="6">
            <h1 class="text-h2 font-weight-black mb-6 text-primary">Smile Brighter With Us</h1>
            <p class="text-h6 text-grey-darken-1 mb-8">
              Experience modern dental care with state-of-the-art facilities and a compassionate team dedicated to your oral health journey.
            </p>
            <div class="d-flex justify-center gap-4">
              <v-btn size="x-large" color="primary" class="rounded-pill px-8" elevation="2">
                Get Started
              </v-btn>
              <v-btn size="x-large" variant="outlined" color="primary" class="rounded-pill px-8">
                Learn More
              </v-btn>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { useI18n } from '#imports'

const { t } = useI18n()
</script>

<style scoped>
.gap-4 {
  gap: 16px;
}
</style>
