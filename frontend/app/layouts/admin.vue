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

          <v-menu
              v-model="notifMenuOpen"
              location="bottom end"
              :close-on-content-click="false"
              content-class="admin-notif-menu-root"
          >
              <template #activator="{ props: menuProps }">
                  <v-badge
                      class="admin-notif-main-badge"
                      :model-value="unreadPreviewCount > 0"
                      :content="unreadPreviewBadge"
                      offset-x="10"
                      offset-y="10"
                  >
                      <v-btn
                          v-bind="menuProps"
                          icon="mdi-bell-outline"
                          :color="notifMenuOpen ? 'white' : 'grey-darken-1'"
                          class="mr-2 admin-notif-bell"
                          :class="{ 'admin-notif-bell--open': notifMenuOpen }"
                          aria-label="Appointment notifications"
                      />
                  </v-badge>
              </template>

              <div class="admin-notif-panel" :style="{ '--admin-notif-accent': notifAccent }">
                  <header class="admin-notif-panel__top">
                      <span class="admin-notif-panel__title">Notifications</span>
                      <v-menu location="bottom end">
                          <template #activator="{ props: dotsProps }">
                              <v-btn
                                  v-bind="dotsProps"
                                  icon="mdi-dots-horizontal"
                                  variant="text"
                                  size="small"
                                  class="admin-notif-panel__dots"
                                  aria-label="Notification options"
                              />
                          </template>
                          <v-list density="compact" class="admin-notif-dots-menu pa-1" rounded="lg">
                              <v-list-item
                                  class="admin-notif-dots-menu__item rounded"
                                  :disabled="pendingLoading"
                                  @click="fetchPendingConfirmCount"
                              >
                                  <v-list-item-title>Refresh</v-list-item-title>
                              </v-list-item>
                          </v-list>
                      </v-menu>
                  </header>

                  <div class="admin-notif-tabs" role="tablist">
                      <button
                          type="button"
                          role="tab"
                          class="admin-notif-tab"
                          :class="{ 'admin-notif-tab--active': notifTab === 'all' }"
                          :aria-selected="notifTab === 'all'"
                          @click="notifTab = 'all'"
                      >
                          All
                      </button>
                      <button
                          type="button"
                          role="tab"
                          class="admin-notif-tab"
                          :class="{ 'admin-notif-tab--active': notifTab === 'unread' }"
                          :aria-selected="notifTab === 'unread'"
                          @click="notifTab = 'unread'"
                      >
                          Unread
                          <span v-if="unreadPreviewCount > 0" class="admin-notif-tab__badge">{{ unreadPreviewBadge }}</span>
                      </button>
                  </div>

                  <div class="admin-notif-section-head">
                      <span class="admin-notif-section-head__label">Pending confirmations</span>
                      <button
                          type="button"
                          class="admin-notif-mark-all"
                          :disabled="unreadPreviewCount === 0"
                          @click="markAllPendingNotifsSeen"
                      >
                          Mark all as read
                      </button>
                  </div>

                  <div class="admin-notif-list-wrap">
                      <p v-if="pendingFetchError" class="admin-notif-error mb-0">{{ pendingFetchError }}</p>
                      <template v-else-if="pendingLoading && pendingPreview.length === 0">
                          <div v-for="n in 4" :key="n" class="admin-notif-skel">
                              <div class="admin-notif-skel__avatar" />
                              <div class="admin-notif-skel__lines">
                                  <div class="admin-notif-skel__line admin-notif-skel__line--short" />
                                  <div class="admin-notif-skel__line" />
                              </div>
                          </div>
                      </template>
                      <p v-else-if="displayedPendingItems.length === 0" class="admin-notif-empty mb-0">
                          <template v-if="notifTab === 'unread'">You're caught up — no unread requests.</template>
                          <template v-else>No appointments waiting for confirmation.</template>
                      </p>
                      <ul v-else class="admin-notif-list pa-0 ma-0">
                          <li v-for="item in displayedPendingItems" :key="item.id">
                              <button
                                  type="button"
                                  class="admin-notif-row"
                                  @click="onPendingNotifClick(item.id)"
                              >
                                  <div class="admin-notif-row__avatar-wrap">
                                      <v-avatar size="56" class="admin-notif-avatar text-caption font-weight-bold">
                                          {{ item.initials }}
                                      </v-avatar>
                                      <span class="admin-notif-row__badge-icon" aria-hidden="true">
                                          <v-icon icon="mdi-calendar-clock" size="14" />
                                      </span>
                                  </div>
                                  <div class="admin-notif-row__body text-start">
                                      <span class="admin-notif-row__name">{{ item.name }}</span>
                                      <span class="admin-notif-row__msg">{{ item.snippet }}</span>
                                      <span class="admin-notif-row__time">{{ item.timeShort }}</span>
                                  </div>
                                  <span
                                      v-if="!isPendingNotifSeen(item.id)"
                                      class="admin-notif-row__dot"
                                      aria-label="Unread"
                                  />
                              </button>
                          </li>
                      </ul>
                  </div>

                  <footer class="admin-notif-panel__footer">
                      <NuxtLink
                          to="/admin/appointments"
                          class="admin-notif-footer-btn"
                          @click="notifMenuOpen = false"
                      >
                          See previous notifications
                      </NuxtLink>
                  </footer>
              </div>
          </v-menu>

          <v-snackbar v-model="pendingSnackbar" color="primary-blue" location="top" :timeout="8000">
              {{ pendingSnackbarText }}
              <template #actions>
                  <v-btn variant="text" color="white" to="/admin/appointments" @click="pendingSnackbar = false">
                      View
                  </v-btn>
                  <v-btn variant="text" color="white" @click="pendingSnackbar = false">Dismiss</v-btn>
              </template>
          </v-snackbar>

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
  import { appointmentPartsFromApi } from "~/utils/appointmentDatetime";

  const apiBase = usePublicApiBase();
  const authHeaders = useAuthFetchHeaders();
  const runtimeConfig = useRuntimeConfig();

  /** Match API max `per_page` so “mark all read” and the bell count cover the full pending list. */
  const PENDING_PREVIEW_LIMIT = 100;
  const SEEN_STORAGE_KEY = "urmaza-admin-pending-notif-seen";
  /** Facebook-style accent for this panel */
  const notifAccent = "#1877f2";

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

  /** Pending appointments (awaiting staff confirmation). */
  const pendingPreview = ref([]);
  const pendingLoading = ref(false);
  const pendingFetchError = ref("");
  const notifMenuOpen = ref(false);
  const notifTab = ref("all");
  const seenPendingIds = ref(new Set());
  const pendingSnackbar = ref(false);
  const pendingSnackbarText = ref("");
  /** After first successful fetch; used so we don’t toast existing backlog on page load. */
  const pendingBaseline = ref(null);

  function loadSeenFromStorage() {
      if (!import.meta.client) {
          return;
      }
      try {
          const raw = localStorage.getItem(SEEN_STORAGE_KEY);
          const arr = raw ? JSON.parse(raw) : [];
          seenPendingIds.value = new Set((Array.isArray(arr) ? arr : []).map(String));
      } catch {
          seenPendingIds.value = new Set();
      }
  }

  function persistSeen() {
      if (!import.meta.client) {
          return;
      }
      localStorage.setItem(SEEN_STORAGE_KEY, JSON.stringify([...seenPendingIds.value]));
  }

  function isPendingNotifSeen(id) {
      return seenPendingIds.value.has(String(id));
  }

  function markPendingNotifSeen(id) {
      const next = new Set(seenPendingIds.value);
      next.add(String(id));
      seenPendingIds.value = next;
      persistSeen();
  }

  function markAllPendingNotifsSeen() {
      const next = new Set(seenPendingIds.value);
      for (const item of pendingPreview.value) {
          next.add(String(item.id));
      }
      seenPendingIds.value = next;
      persistSeen();
  }

  function onPendingNotifClick(id) {
      markPendingNotifSeen(id);
      notifMenuOpen.value = false;
      navigateTo("/admin/appointments");
  }

  function initialsFromName(name) {
      if (!name || typeof name !== "string") {
          return "?";
      }
      const parts = name.trim().split(/\s+/).filter(Boolean);
      if (parts.length === 0) {
          return "?";
      }
      if (parts.length === 1) {
          return parts[0].slice(0, 2).toUpperCase();
      }
      return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function formatRelativeShort(iso) {
      if (!iso) {
          return "";
      }
      const t = new Date(iso).getTime();
      if (Number.isNaN(t)) {
          return "";
      }
      let sec = Math.floor((Date.now() - t) / 1000);
      if (sec < 0) {
          sec = 0;
      }
      if (sec < 60) {
          return "now";
      }
      const min = Math.floor(sec / 60);
      if (min < 60) {
          return `${min}m`;
      }
      const hr = Math.floor(min / 60);
      if (hr < 24) {
          return `${hr}h`;
      }
      const day = Math.floor(hr / 24);
      if (day < 7) {
          return `${day}d`;
      }
      const wk = Math.floor(day / 7);
      if (wk < 5) {
          return `${wk}w`;
      }
      return `${Math.floor(day / 30)}mo`;
  }

  function mapPendingNotifRow(row) {
      const parts = appointmentPartsFromApi(row.appointment_date);
      const created = row.created_at || row.updated_at || row.appointment_date;
      const name = String(row.name || "Patient");
      const svc = row.service ? String(row.service) : "an appointment";
      const sortKey = new Date(created).getTime();
      return {
          id: row.id,
          name,
          initials: initialsFromName(name),
          snippet: `Requested ${svc} — ${parts.dateLabel}${parts.timeLabel ? ` · ${parts.timeLabel}` : ""}`,
          timeShort: formatRelativeShort(created),
          sortKey: Number.isNaN(sortKey) ? 0 : sortKey,
      };
  }

  /** Newest request first (by created / updated time), then higher id. */
  function comparePendingLatestFirst(a, b) {
      const ka = a.sortKey ?? 0;
      const kb = b.sortKey ?? 0;
      if (kb !== ka) {
          return kb - ka;
      }
      return (Number(b.id) || 0) - (Number(a.id) || 0);
  }

  const displayedPendingItems = computed(() => {
      const list = pendingPreview.value;
      const seen = seenPendingIds.value;

      if (notifTab.value === "unread") {
          return [...list].filter((p) => !seen.has(String(p.id))).sort(comparePendingLatestFirst);
      }

      /* All: same rows as unread + read; unread on top (newest first), then read (newest first). */
      const unread = list.filter((p) => !seen.has(String(p.id))).sort(comparePendingLatestFirst);
      const read = list.filter((p) => seen.has(String(p.id))).sort(comparePendingLatestFirst);
      return [...unread, ...read];
  });

  const unreadPreviewCount = computed(() => {
      return pendingPreview.value.filter((p) => !seenPendingIds.value.has(String(p.id))).length;
  });

  const unreadPreviewBadge = computed(() => {
      const n = unreadPreviewCount.value;
      return n > 99 ? "99+" : String(n);
  });

  async function fetchPendingConfirmCount() {
      pendingFetchError.value = "";
      pendingLoading.value = true;
      try {
          const res = await $fetch(`${apiBase.value}/api/appointments`, {
              query: { status: "pending", per_page: PENDING_PREVIEW_LIMIT },
              headers: authHeaders.value,
          });
          const total = Math.max(0, Number(res?.total ?? 0));
          if (pendingBaseline.value !== null && total > pendingBaseline.value) {
              const delta = total - pendingBaseline.value;
              pendingSnackbarText.value =
                  delta === 1
                      ? "New appointment request — confirm it in Appointments."
                      : `${delta} new appointment requests — confirm them in Appointments.`;
              pendingSnackbar.value = true;
          }
          pendingBaseline.value = total;
          const rows = Array.isArray(res?.data) ? res.data : [];
          pendingPreview.value = rows.map(mapPendingNotifRow);
      } catch {
          pendingFetchError.value = "Could not check pending appointments.";
          pendingPreview.value = [];
      } finally {
          pendingLoading.value = false;
      }
  }

  watch(notifMenuOpen, (open) => {
      if (open) {
          fetchPendingConfirmCount();
      }
  });

  provide("refreshAdminPendingConfirmCount", fetchPendingConfirmCount);

  let pendingPollTimer;

  function onDocumentVisibilityChange() {
      if (document.visibilityState === "visible") {
          fetchPendingConfirmCount();
      }
  }

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
      if (token.value && !authRole.value) {
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
      }

      loadSeenFromStorage();
      await fetchPendingConfirmCount();
      pendingPollTimer = setInterval(fetchPendingConfirmCount, 50_000);
      document.addEventListener("visibilitychange", onDocumentVisibilityChange);
  });

  onUnmounted(() => {
      if (pendingPollTimer) {
          clearInterval(pendingPollTimer);
      }
      document.removeEventListener("visibilitychange", onDocumentVisibilityChange);
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

<style scoped lang="scss">
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

  :deep(.admin-notif-main-badge .v-badge__badge) {
      background-color: #1a237e !important;
      color: #fff !important;
  }

  .admin-notif-bell {
      transition:
          background-color 0.18s ease,
          color 0.18s ease;
  }

  .admin-notif-bell--open {
      background-color: #1a237e !important;
      color: #fff !important;
      box-shadow: 0 2px 8px rgba(26, 35, 126, 0.35);
  }

  :deep(.admin-notif-menu-root) {
      border-radius: 12px !important;
      overflow: hidden;
      box-shadow:
          0 8px 28px rgba(0, 0, 0, 0.12),
          0 2px 8px rgba(0, 0, 0, 0.06) !important;
  }

  .admin-notif-panel {
      --admin-notif-accent: #1a237e;
      width: 360px;
      max-width: calc(100vw - 24px);
      background: #fff;
      color: rgba(0, 0, 0, 0.87);
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid rgba(0, 0, 0, 0.08);
  }

  .admin-notif-panel__top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 12px 10px 16px;
  }

  .admin-notif-panel__title {
      font-size: 1.25rem;
      font-weight: 700;
      color: #212121;
      letter-spacing: -0.02em;
  }

  .admin-notif-panel__dots {
      color: #616161 !important;
  }

  .admin-notif-tabs {
      display: flex;
      gap: 8px;
      padding: 0 12px 12px;
  }

  .admin-notif-tab {
      flex: 1;
      border: none;
      border-radius: 999px;
      padding: 8px 12px;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      background: #f5f5f5;
      color: #616161;
      transition:
          background 0.15s ease,
          color 0.15s ease;
  }

  .admin-notif-tab--active {
      background: rgba(26, 35, 126, 0.12);
      color: #1a237e;
  }

  .admin-notif-tab__badge {
      display: inline-block;
      margin-left: 6px;
      min-width: 1.25rem;
      padding: 0 5px;
      font-size: 0.7rem;
      line-height: 1.25rem;
      border-radius: 999px;
      background: var(--admin-notif-accent);
      color: #fff;
      vertical-align: middle;
  }

  .admin-notif-section-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 4px 16px 8px;
  }

  .admin-notif-section-head__label {
      font-size: 0.8rem;
      font-weight: 600;
      color: #757575;
      text-transform: capitalize;
  }

  .admin-notif-link {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--admin-notif-accent);
      text-decoration: none;
  }

  .admin-notif-link:hover {
      text-decoration: underline;
  }

  .admin-notif-mark-all {
      border: none;
      padding: 0;
      margin: 0;
      font: inherit;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--admin-notif-accent);
      background: none;
      cursor: pointer;
      text-decoration: none;
  }

  .admin-notif-mark-all:hover:not(:disabled) {
      text-decoration: underline;
  }

  .admin-notif-mark-all:disabled {
      color: #bdbdbd;
      cursor: not-allowed;
      text-decoration: none;
  }

  .admin-notif-list-wrap {
      max-height: min(360px, 52vh);
      overflow-x: hidden;
      overflow-y: auto;
      padding: 0 4px 8px;

      &::-webkit-scrollbar {
          width: 6px;
      }
      &::-webkit-scrollbar-thumb {
          background: rgba(0, 0, 0, 0.15);
          border-radius: 6px;
      }
  }

  .admin-notif-error {
      padding: 16px;
      font-size: 0.875rem;
      color: #c62828;
  }

  .admin-notif-empty {
      padding: 20px 16px;
      font-size: 0.875rem;
      color: #757575;
      text-align: center;
      line-height: 1.45;
  }

  .admin-notif-list {
      list-style: none;
  }

  .admin-notif-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      width: 100%;
      padding: 10px 8px;
      margin: 0 0 2px;
      border: none;
      border-radius: 10px;
      background: transparent;
      cursor: pointer;
      text-align: inherit;
      color: inherit;
      transition: background 0.12s ease;
  }

  .admin-notif-row:hover {
      background: #f5f5f5;
  }

  .admin-notif-row__avatar-wrap {
      position: relative;
      flex-shrink: 0;
  }

  .admin-notif-avatar {
      background: #e8eaf6 !important;
      color: #1a237e !important;
  }

  .admin-notif-row__badge-icon {
      position: absolute;
      right: -2px;
      bottom: -2px;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      background: var(--admin-notif-accent);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #fff;
  }

  .admin-notif-row__body {
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
      gap: 2px;
      padding-top: 2px;
  }

  .admin-notif-row__name {
      font-size: 0.9375rem;
      font-weight: 700;
      color: #212121;
      line-height: 1.25;
  }

  .admin-notif-row__msg {
      font-size: 0.8125rem;
      color: #616161;
      line-height: 1.35;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
  }

  .admin-notif-row__time {
      font-size: 0.75rem;
      color: #9e9e9e;
      margin-top: 2px;
  }

  .admin-notif-row__dot {
      flex-shrink: 0;
      width: 10px;
      height: 10px;
      margin-top: 8px;
      margin-right: 4px;
      border-radius: 50%;
      background: var(--admin-notif-accent);
  }

  .admin-notif-panel__footer {
      padding: 8px 12px 12px;
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      background: #fafafa;
  }

  .admin-notif-footer-btn {
      display: block;
      width: 100%;
      text-align: center;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 0.9375rem;
      font-weight: 600;
      color: #424242;
      text-decoration: none;
      background: #eeeeee;
      transition: background 0.15s ease;
  }

  .admin-notif-footer-btn:hover {
      background: #e0e0e0;
  }

  .admin-notif-skel {
      display: flex;
      gap: 12px;
      padding: 12px 12px 8px;
  }

  .admin-notif-skel__avatar {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: rgba(0, 0, 0, 0.06);
      animation: admin-notif-pulse 1.2s ease-in-out infinite;
  }

  .admin-notif-skel__lines {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 8px;
      padding-top: 8px;
  }

  .admin-notif-skel__line {
      height: 10px;
      border-radius: 4px;
      background: rgba(0, 0, 0, 0.06);
      animation: admin-notif-pulse 1.2s ease-in-out infinite;
  }

  .admin-notif-skel__line--short {
      width: 45%;
  }

  @keyframes admin-notif-pulse {
      0%,
      100% {
          opacity: 1;
      }
      50% {
          opacity: 0.45;
      }
  }

  :deep(.admin-notif-dots-menu) {
      background: #fff !important;
      border: 1px solid rgba(0, 0, 0, 0.08);
      min-width: 180px;
      box-shadow:
          0 8px 24px rgba(0, 0, 0, 0.1),
          0 2px 6px rgba(0, 0, 0, 0.06) !important;
  }

  :deep(.admin-notif-dots-menu__item) {
      color: #212121 !important;
      min-height: 40px !important;
  }

  :deep(.admin-notif-dots-menu__item:hover) {
      background: #f5f5f5 !important;
  }
</style>