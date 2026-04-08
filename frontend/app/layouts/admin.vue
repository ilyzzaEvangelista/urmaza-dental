<template>
  <v-app theme="light">
      <!-- Sidebar -->
      <v-navigation-drawer v-model="drawer" color="white" elevation="1" width="280">
          <div class="pa-2 d-flex align-center">
              <v-img src="/cover.png" width="100" height="100" contain class="mr-2"></v-img>
          </div>
          <v-divider></v-divider>

          <v-list density="comfortable" nav class="px-4">
              <v-list-item
                  v-for="(item, i) in navItems"
                  :key="i"
                  :value="item"
                  :to="item.to"
                  color="primary-blue"
                  rounded="lg"
                  class="mb-1"
              >
                  <template v-slot:prepend>
                      <v-icon :icon="item.icon"></v-icon>
                  </template>
                  <v-list-item-title class="font-weight-medium">{{ item.title }}</v-list-item-title>
              </v-list-item>
          </v-list>
      </v-navigation-drawer>

      <!-- Header -->
      <v-app-bar flat color="white" border="b">
          <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>
          <v-spacer></v-spacer>
          <v-btn icon="mdi-bell-outline" color="grey-darken-1" class="mr-2"></v-btn>
          <v-btn color="error" variant="text" prepend-icon="mdi-logout" class="font-weight-bold" @click="logout">
              Logout
          </v-btn>
          <!-- <v-btn icon="mdi-account-circle-outline" color="grey-darken-1" class="ml-2 mr-2"></v-btn> -->
      </v-app-bar>

      <!-- Content -->
      <v-main class="bg-grey-lighten-4">
          <slot />
      </v-main>
  </v-app>
</template>

<script setup>
  const apiBase = usePublicApiBase();

  const drawer = ref(true);

  const navItems = [
      { title: "Dashboard", icon: "mdi-view-dashboard", to: "/admin" },
      { title: "Appointments", icon: "mdi-calendar-clock", to: "/admin/appointments" },
      { title: "Patients", icon: "mdi-account-group", to: "/admin/patients" },
      { title: "Services", icon: "mdi-stethoscope", to: "/admin/services" },
      { title: "Settings", icon: "mdi-cog-outline", to: "/admin/settings" },
  ];

  const logout = async () => {
      const token = useCookie("auth_token");

      try {
          await $fetch(`${apiBase.value}/api/logout`, {
              method: "POST",
              headers: {
                  Authorization: `Bearer ${token.value}`,
              },
          });
      } catch (error) {
          console.error("Logout failed:", error);
      } finally {
          token.value = null;
          navigateTo("/");
      }
  };
</script>

<style scoped>
  .text-primary-blue {
      color: #1a237e;
  }
</style>