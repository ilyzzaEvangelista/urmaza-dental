<template>
  <v-card class="upcoming-board rounded-xl border shadow-sm" flat>
    <v-card-title class="d-flex align-center pa-6 pb-4 flex-nowrap ga-2">
      <v-btn
        icon
        variant="text"
        density="comfortable"
        color="grey-darken-2"
        aria-label="Previous week"
        @click="changeWeek(-1)"
      >
        <v-icon icon="mdi-chevron-left" size="28" />
      </v-btn>
      <span class="text-h6 font-weight-bold text-grey-darken-4 flex-grow-1 text-center text-truncate">
        {{ monthLabel }}
      </span>
      <v-btn
        icon
        variant="text"
        density="comfortable"
        color="grey-darken-2"
        aria-label="Next week"
        @click="changeWeek(1)"
      >
        <v-icon icon="mdi-chevron-right" size="28" />
      </v-btn>
    </v-card-title>

    <div class="px-4 px-sm-6 pb-2 d-flex justify-space-between ga-1 ga-sm-2 overflow-x-auto week-strip">
      <button
        v-for="day in days"
        :key="day.date"
        type="button"
        class="day-pill flex-grow-1 flex-sm-grow-0"
        :class="{ 'day-pill--active': selectedDate === day.date, 'day-pill--today': day.is_today }"
        @click="selectedDate = day.date"
      >
        <span class="day-pill__dow text-caption">{{ day.weekday_short }}</span>
        <span class="day-pill__num text-body-1 font-weight-bold">{{ day.day }}</span>
      </button>
    </div>

    <v-divider class="mx-4 mx-sm-6" />

    <v-card-title class="text-subtitle-1 font-weight-bold text-grey-darken-4 pa-6 pb-2">
      Upcoming Appointments
    </v-card-title>

    <v-card-text class="pa-6 pt-2">
      <v-progress-linear v-if="loading" indeterminate color="primary-blue" class="mb-4 rounded" />

      <div v-if="!loading && fetchError" class="text-body-2 text-error mb-4">
        {{ fetchError }}
      </div>

      <div v-if="!loading && !fetchError && orderedGroupKeys.length === 0" class="text-center py-10 text-grey-darken-1">
        <v-icon icon="mdi-calendar-blank" size="48" class="mb-2 d-block mx-auto opacity-60" />
        No appointments this week.
      </div>

      <div v-for="dateKey in orderedGroupKeys" :key="dateKey" class="mb-6">
        <div class="text-body-2 text-grey-darken-1 font-weight-medium mb-3">
          {{ sectionTitle(dateKey) }}
        </div>

        <div
          v-for="item in mappedGroups[dateKey]"
          :key="item.id"
          class="d-flex align-stretch ga-3 mb-3"
        >
          <div class="time-pill text-body-2 font-weight-medium flex-shrink-0 text-grey-darken-2">
            {{ item.timeShort }}
          </div>

          <v-card
            flat
            class="appt-card flex-grow-1 rounded-xl pa-4 d-flex align-center"
            :class="{ 'appt-card--active': selectedId === item.id }"
            @click="selectedId = item.id"
          >
            <v-avatar
              class="flex-shrink-0 rounded-lg"
              size="48"
              :color="selectedId === item.id ? 'white' : 'primary-blue-lighten-4'"
              :class="{ 'avatar-on-active': selectedId === item.id }"
            >
              <v-img v-if="item.imageUrl" :src="item.imageUrl" cover />
              <span
                v-else
                class="text-caption font-weight-bold"
                :class="selectedId === item.id ? 'text-white' : 'text-primary-blue'"
              >
                {{ item.initials }}
              </span>
            </v-avatar>

            <div class="flex-grow-1 min-width-0 ms-3">
              <div class="d-flex align-center flex-wrap ga-2">
                <span
                  class="text-body-1 font-weight-bold text-truncate"
                  :class="selectedId === item.id ? 'text-white' : 'text-grey-darken-4'"
                >
                  {{ item.name }}
                </span>
                <v-chip
                  v-if="item.showMemberBadge"
                  size="x-small"
                  class="font-weight-bold px-2"
                  color="deep-orange-lighten-1"
                  variant="flat"
                >
                  Member
                </v-chip>
              </div>
              <div
                class="text-body-2 text-truncate mt-0.5"
                :class="selectedId === item.id ? 'text-white text-opacity-90' : 'text-grey-darken-1'"
              >
                {{ item.service }}
              </div>
            </div>

            <v-menu location="bottom end">
              <template #activator="{ props: menuProps }">
                <v-btn
                  v-bind="menuProps"
                  icon
                  variant="text"
                  size="small"
                  class="flex-shrink-0"
                  :color="selectedId === item.id ? 'white' : 'grey-darken-1'"
                  @click.stop
                >
                  <v-icon icon="mdi-dots-vertical" />
                </v-btn>
              </template>
              <v-list density="compact" min-width="140">
                <v-list-item title="View" @click="$emit('view', item)" />
                <v-list-item title="Edit" @click="$emit('edit', item)" />
                <v-list-item title="Delete" base-color="error" @click="$emit('delete', item)" />
              </v-list>
            </v-menu>
          </v-card>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
  import { appointmentPartsFromApi, CLINIC_TIMEZONE } from "~/utils/appointmentDatetime";
  import { resolveApiBaseString } from "~/utils/apiBase";

  const props = defineProps({
    apiBase: {
      type: String,
      default: "",
    },
  });

  defineEmits(["view", "edit", "delete"]);

  const loading = ref(false);
  const fetchError = ref("");
  const weekOffset = ref(0);
  const monthLabel = ref("");
  const days = ref([]);
  const groupedRaw = ref({});
  const selectedId = ref(null);
  const selectedDate = ref("");

  const base = computed(() => resolveApiBaseString(props.apiBase));

  const statusColors = {
    pending: "amber-darken-2",
    confirmed: "primary-blue",
    completed: "success",
    cancelled: "error",
  };

  function todayDateKey() {
    return new Date().toLocaleDateString("en-CA", { timeZone: CLINIC_TIMEZONE });
  }

  function resolvedStorageUrl(path) {
    if (!path) return "";
    const p = String(path).trim();
    if (/^https?:\/\//i.test(p)) return p;
    const key = p.replace(/^\/+/, "").replace(/^storage\//, "");
    return `${String(base.value).replace(/\/$/, "")}/storage/${key}`;
  }

  function initialsFromName(name) {
    if (!name || typeof name !== "string") return "?";
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return "?";
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function formatTimeShort(iso) {
    if (!iso) return "—";
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return "—";
    return d.toLocaleTimeString("en-PH", {
      timeZone: CLINIC_TIMEZONE,
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
    });
  }

  function mapRow(row) {
    const status = String(row.status || "pending").toLowerCase();
    const label = status.charAt(0).toUpperCase() + status.slice(1);
    const parts = appointmentPartsFromApi(row.appointment_date);
    const imageUrl = row.image ? resolvedStorageUrl(row.image) : "";
    return {
      id: row.id,
      name: row.name,
      phone: row.contact_number,
      contact_number: row.contact_number,
      email: row.email,
      age: row.age,
      service: row.service,
      date: parts.dateLabel,
      time: parts.timeLabel,
      timeShort: formatTimeShort(row.appointment_date),
      status: label,
      statusColor: statusColors[status] || "grey",
      initials: initialsFromName(row.name),
      note: row.note || "",
      image: row.image || null,
      imageUrl,
      appointment_date_raw: parts.rawLocal,
      statusKey: status,
      showMemberBadge: status === "confirmed",
    };
  }

  const mappedGroups = computed(() => {
    const g = groupedRaw.value || {};
    const out = {};
    for (const key of Object.keys(g)) {
      const rows = g[key];
      out[key] = Array.isArray(rows) ? rows.map(mapRow) : [];
    }
    return out;
  });

  const orderedGroupKeys = computed(() => Object.keys(mappedGroups.value).sort());

  function sectionTitle(dateKey) {
    const t = todayDateKey();
    const noon = new Date(`${dateKey}T12:00:00+08:00`);
    const short = noon.toLocaleDateString("en-PH", {
      timeZone: CLINIC_TIMEZONE,
      month: "short",
      day: "numeric",
    });
    if (dateKey === t) {
      return `Today, ${short}`;
    }
    return noon.toLocaleDateString("en-PH", {
      timeZone: CLINIC_TIMEZONE,
      weekday: "long",
      month: "short",
      day: "numeric",
    });
  }

  function changeWeek(delta) {
    weekOffset.value += delta;
    load();
  }

  async function load() {
    fetchError.value = "";
    loading.value = true;
    try {
      const data = await $fetch(`${base.value}/api/appointments/week`, {
        query: { week_offset: weekOffset.value },
      });
      monthLabel.value = data?.month_label || "";
      days.value = Array.isArray(data?.days) ? data.days : [];
      groupedRaw.value = data?.grouped_appointments && typeof data.grouped_appointments === "object"
        ? data.grouped_appointments
        : {};

      const today = todayDateKey();
      const inWeek = days.value.some((x) => x.date === today);
      selectedDate.value = inWeek ? today : (days.value[0]?.date || "");
      selectedId.value = null;
    } catch (e) {
      console.error(e);
      fetchError.value = "Could not load this week’s appointments.";
      monthLabel.value = "";
      days.value = [];
      groupedRaw.value = {};
    } finally {
      loading.value = false;
    }
  }

  onMounted(() => {
    load();
  });

  defineExpose({
    reload: load,
  });
</script>

<style scoped>
  .upcoming-board {
    border-color: #ededed !important;
    background-color: white !important;
  }

  .shadow-sm {
    box-shadow:
      0 1px 3px 0 rgba(0, 0, 0, 0.1),
      0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }

  .week-strip {
    scrollbar-width: thin;
  }

  .day-pill {
    min-width: 44px;
    max-width: 72px;
    padding: 8px 4px;
    border: none;
    border-radius: 12px;
    background: transparent;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    color: #616161;
    transition:
      background 0.15s ease,
      color 0.15s ease;
  }

  .day-pill:hover {
    background: rgba(26, 35, 126, 0.06);
  }

  .day-pill--active {
    background: var(--primary-blue, #1a237e) !important;
    color: white !important;
  }

  .day-pill--active .day-pill__dow {
    color: rgba(255, 255, 255, 0.85);
  }

  .day-pill__dow {
    text-transform: capitalize;
    opacity: 0.9;
  }

  .time-pill {
    width: 52px;
    text-align: right;
    padding-top: 10px;
  }

  .appt-card {
    border: 1px solid #ededed !important;
    box-shadow:
      0 1px 3px 0 rgba(0, 0, 0, 0.06),
      0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
    cursor: pointer;
    transition:
      background 0.15s ease,
      border-color 0.15s ease,
      box-shadow 0.15s ease;
  }

  .appt-card--active {
    background: var(--primary-blue, #1a237e) !important;
    border-color: var(--primary-blue, #1a237e) !important;
    box-shadow: 0 4px 14px rgba(26, 35, 126, 0.35) !important;
  }

  .avatar-on-active {
    opacity: 0.95;
    background: rgba(255, 255, 255, 0.22) !important;
  }

  .appt-card--active :deep(.v-avatar) {
    border: 2px solid rgba(255, 255, 255, 0.35);
  }
</style>
