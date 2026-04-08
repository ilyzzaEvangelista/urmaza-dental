<template>
  <v-app>
    <!-- Navigation Bar -->
    <v-app-bar color="primary-blue" flat height="70">
      <v-container class="d-flex align-center">
        <v-app-bar-title class="font-weight-bold text-white text-uppercase" style="letter-spacing: 1px">
          Urmaza Dental Clinic
        </v-app-bar-title>
        
        <v-spacer></v-spacer>
        
        <div class="hidden-sm-and-down">
          <v-btn variant="text" color="white" to="/">Home</v-btn>
        </div>
        
        <v-btn icon="mdi-menu" color="white" class="ml-2"></v-btn>
        <v-btn icon="mdi-logout" color="white" class="ml-2"></v-btn>
      </v-container>
    </v-app-bar>

    <v-main>
      <NuxtPage />
    </v-main>
  </v-app>
</template>

<script setup>
// Header buttons and logic
</script>

<style>
/* Global styles if needed */
.v-application {
  background-color: var(--bg-light) !important;
}
</style>

