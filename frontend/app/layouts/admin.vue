<template>
  <v-app theme="light">
      <!-- Sidebar -->
      <v-navigation-drawer v-model="drawer" color="white" elevation="1" width="280">
          <div class="pa-8 sidebar-brand d-flex align-center justify-center">
              <img
                  :src="sidebarLogoSrc"
                  alt="Urmaza Dental Clinic"
                  class="admin-sidebar-logo"
                  loading="eager"
                  decoding="async"
              />
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
  const runtimeConfig = useRuntimeConfig();

  /** Public folder asset; respects `app.baseURL` when not served from `/`. */
  const sidebarLogoSrc = computed(() => {
      const base = runtimeConfig.app.baseURL || "/";
      if (base === "/" || base === "") {
          return "/cover.png";
      }
      return `${String(base).replace(/\/$/, "")}/cover.png`;
  });

  const drawer = ref(true);

  const authRole = useCookie("auth_role");

  const navItems = computed(() => {
      const items = [
          { title: "Dashboard", icon: "mdi-view-dashboard", to: "/admin" },
          { title: "Appointments", icon: "mdi-calendar-clock", to: "/admin/appointments" },
          { title: "Patients", icon: "mdi-account-group", to: "/admin/patients" },
      ];
      if (authRole.value === "admin") {
          items.push({ title: "Services", icon: "mdi-stethoscope", to: "/admin/services" });
      }
      items.push({ title: "Audit Logs", icon: "mdi-file-chart", to: "/admin/audit-logs" });
      return items;
  });

  onMounted(async () => {
      const token = useCookie("auth_token");
      if (!token.value || authRole.value) {
          return;
      }
      try {
          const user = await $fetch(`${apiBase.value}/api/user`, {
              headers: { Authorization: `Bearer ${token.value}` },
          });
          if (user?.role) {
              authRole.value = user.role;
          }
      } catch {
          /* ignore */
      }
  });

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
          authRole.value = null;
          navigateTo("/");
      }
  };
</script>

<style scoped>
  .text-primary-blue {
      color: #1a237e;
  }

  .sidebar-brand {
      width: 100%;
      box-sizing: border-box;
  }

  /* Full drawer width (280px); height follows aspect ratio — no cropping */
  .admin-sidebar-logo {
      display: block;
      width: 100%;
      height: auto;
      object-fit: contain;
      object-position: center;
  }
</style>