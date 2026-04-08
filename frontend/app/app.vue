<template>
  <v-app>
    <!-- Navigation Bar -->
    <v-app-bar color="primary-blue" flat height="70">
      <v-container class="d-flex align-center">
        <div class="d-flex align-center">
          <v-img src="/icon.png" width="40" height="40" contain class="mr-2"></v-img>
          <v-img src="/cover.png" width="150" height="30" contain theme="dark" class="brightness-white"></v-img>
        </div>
        
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

