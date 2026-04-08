<template>
  <v-app theme="light">
      <!-- Navigation Bar -->
      <v-app-bar color="primary-blue" flat height="70">
          <v-container class="d-flex align-center">
              <v-app-bar-title class="font-weight-bold text-white text-uppercase" style="letter-spacing: 1px">
                  Urmaza Dental Clinic
              </v-app-bar-title>

              <v-spacer></v-spacer>

              <v-btn icon="mdi-menu" color="white" class="ml-2"></v-btn>
              <v-btn icon="mdi-logout" color="white" class="ml-2" @click="showLogin = true"></v-btn>
          </v-container>
      </v-app-bar>

      <v-main>
          <slot />
      </v-main>

      <LoginDialog v-model="showLogin" />
  </v-app>
</template>

<script setup>
  const showLogin = ref(false);
</script>
