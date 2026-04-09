<template>
  <v-app theme="light">
      <!-- Navigation Bar -->
      <v-app-bar color="primary-blue" flat height="70">
          <v-container class="d-flex align-center">
              <div class="appbar-brand flex-shrink-0 mr-3" aria-hidden="true">
                  <v-img src="/icon2.png" alt="" width="40" height="40" contain />
              </div>
              <v-app-bar-title class="font-weight-bold text-white text-uppercase" style="letter-spacing: 1px">
                Dental Clinic
              </v-app-bar-title>

              <v-spacer></v-spacer>

              <!-- <v-btn icon="mdi-menu" color="white" class="ml-2"></v-btn> -->
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

<style scoped>
  .appbar-brand {
      background: rgba(255, 255, 255, 0.98);
      border-radius: 50%;
      padding: 6px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
  }
</style>
