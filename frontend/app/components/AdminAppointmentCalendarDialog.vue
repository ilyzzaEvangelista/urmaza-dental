<template>
  <v-dialog :model-value="modelValue" max-width="760" scrollable @update:model-value="$emit('update:modelValue', $event)">
    <v-card class="calendar-dialog-card pa-5">
      <v-card-title class="text-h6 font-weight-bold text-grey-darken-4 d-flex align-center justify-space-between">
        <span>{{ title }}</span>
        <v-btn icon="mdi-close" variant="text" density="comfortable" @click="close" />
      </v-card-title>
      <v-card-text class="pa-4">
        <v-progress-linear v-if="calendarLoading" indeterminate color="primary-blue" class="mb-2 rounded" />
        <div class="d-flex justify-center overflow-x-auto">
          <v-date-picker
            v-model="calendarPickerModel"
            v-model:month="calendarMonthZero"
            v-model:year="calendarYear"
            readonly
            show-adjacent-months
            color="primary-blue"
            bg-color="white"
            view-mode="month"
            :first-day-of-week="1"
            hide-title
            hide-header
            class="calendar-dialog-picker"
            @update:month="onCalendarMonthYearChange"
            @update:year="onCalendarMonthYearChange"
          >
            <template #day="{ item, props: dayBtnProps }">
              <v-tooltip
                :disabled="calendarDayAppointments(item).length === 0"
                location="top"
                max-width="380"
                :open-delay="150"
                transition="fade-transition"
                theme="light"
                content-class="calendar-tooltip-light"
              >
                <template #activator="{ props: tipProps }">
                  <v-btn v-bind="mergeProps(dayBtnProps, tipProps)" class="calendar-day-btn">
                    <span class="calendar-day-num">{{ item.localized }}</span>
                    <div class="calendar-day-events">
                      <v-badge
                        v-for="(color, idx) in calendarEventDotColorsIso(item.isoDate)"
                        :key="idx"
                        dot
                        :color="color"
                        class="calendar-day-badge"
                      />
                    </div>
                  </v-btn>
                </template>
                <div class="calendar-tooltip-inner text-body-2">
                  <div
                    v-for="(row, idx) in calendarDayAppointments(item)"
                    :key="idx"
                    :class="idx > 0 ? 'mt-2' : ''"
                  >
                    <div class="font-weight-bold">{{ row.name }}</div>
                    <div class="calendar-tooltip-service">{{ row.service }}</div>
                    <div class="calendar-tooltip-status">{{ row.statusLabel }}</div>
                  </div>
                </div>
              </v-tooltip>
            </template>
          </v-date-picker>
        </div>
        <div class="d-flex flex-wrap justify-center ga-6 mt-3 text-caption text-grey-darken-2">
          <div class="d-flex align-center ga-2">
            <span class="calendar-legend-dot calendar-legend-dot--reserved" />
            <span>Reserved</span>
          </div>
          <div class="d-flex align-center ga-2">
            <span class="calendar-legend-dot calendar-legend-dot--confirmed" />
            <span>Confirmed</span>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { mergeProps, watch } from "vue";
  import { CLINIC_TIMEZONE } from "~/utils/appointmentDatetime";
  import { resolveApiBaseString } from "~/utils/apiBase";

  const props = defineProps({
    modelValue: {
      type: Boolean,
      default: false,
    },
    apiBase: {
      type: String,
      default: "",
    },
    /** e.g. "April 2026" — from the week board. */
    title: {
      type: String,
      default: "",
    },
    /** Calendar month to show (1–12). */
    month: {
      type: Number,
      required: true,
    },
    year: {
      type: Number,
      required: true,
    },
  });

  const emit = defineEmits(["update:modelValue"]);

  const calendarLoading = ref(false);
  const calendarMarkedDates = ref({});
  const calendarAppointmentsByDate = ref({});
  const calendarMonthZero = ref(0);
  const calendarYear = ref(new Date().getFullYear());
  const calendarPickerModel = ref(null);

  const base = computed(() => resolveApiBaseString(props.apiBase));

  function todayDateKey() {
    return new Date().toLocaleDateString("en-CA", { timeZone: CLINIC_TIMEZONE });
  }

  function pickerDateToYmd(date) {
    if (date == null) return "";
    const d =
      typeof date === "string" || typeof date === "number"
        ? new Date(date)
        : date instanceof Date
          ? date
          : new Date(date);
    if (Number.isNaN(d.getTime())) return "";
    return d.toLocaleDateString("en-CA", { timeZone: CLINIC_TIMEZONE });
  }

  function calendarEventColorFn(date) {
    const key = pickerDateToYmd(date);
    const m = calendarMarkedDates.value[key];
    if (!m) return undefined;
    if (m.pending && m.confirmed) return ["amber-darken-2", "teal-lighten-1"];
    if (m.confirmed) return "teal-lighten-1";
    if (m.pending) return "amber-darken-2";
    return undefined;
  }

  function calendarEventDotColorsIso(isoDate) {
    const c = calendarEventColorFn(isoDate);
    if (!c) return [];
    return Array.isArray(c) ? c : [c];
  }

  function apptStatusLabel(status) {
    const s = String(status || "").toLowerCase();
    if (s === "pending") return "Reserved";
    if (s === "confirmed") return "Confirmed";
    return s || "—";
  }

  function calendarDayAppointments(item) {
    const key = pickerDateToYmd(item?.isoDate);
    const rows = calendarAppointmentsByDate.value[key];
    return Array.isArray(rows) ? rows : [];
  }

  async function loadCalendarMarks(year, month) {
    calendarLoading.value = true;
    try {
      const data = await $fetch(`${base.value}/api/appointments/calendar-month`, {
        query: { year, month },
      });
      calendarMarkedDates.value =
        data?.marked_dates && typeof data.marked_dates === "object" ? data.marked_dates : {};

      const raw = data?.appointments_by_date;
      const mapped = {};
      if (raw && typeof raw === "object") {
        for (const [dateKey, rows] of Object.entries(raw)) {
          if (!Array.isArray(rows)) continue;
          mapped[dateKey] = rows.map((r) => ({
            name: r.name ?? "",
            service: r.service ?? "",
            status: r.status ?? "",
            statusLabel: apptStatusLabel(r.status),
          }));
        }
      }
      calendarAppointmentsByDate.value = mapped;
    } catch (e) {
      console.error(e);
      calendarMarkedDates.value = {};
      calendarAppointmentsByDate.value = {};
    } finally {
      calendarLoading.value = false;
    }
  }

  function syncCalendarPickerModelToVisibleMonth() {
    const y = calendarYear.value;
    const m0 = calendarMonthZero.value;
    if (y == null || m0 == null) return;
    const month = m0 + 1;
    const [ty, tm, td] = todayDateKey().split("-").map(Number);
    if (ty === y && tm === month) {
      calendarPickerModel.value = new Date(y, m0, td);
    } else {
      calendarPickerModel.value = null;
    }
  }

  function onCalendarMonthYearChange() {
    if (!props.modelValue) return;
    if (calendarYear.value == null || calendarMonthZero.value == null) return;
    syncCalendarPickerModelToVisibleMonth();
    loadCalendarMarks(calendarYear.value, calendarMonthZero.value + 1);
  }

  function close() {
    emit("update:modelValue", false);
  }

  function initFromProps() {
    calendarYear.value = props.year;
    calendarMonthZero.value = props.month - 1;
    syncCalendarPickerModelToVisibleMonth();
    loadCalendarMarks(props.year, props.month);
  }

  watch(
    () => props.modelValue,
    (open) => {
      if (open) {
        initFromProps();
      }
    },
  );
</script>

<style scoped>
  .calendar-dialog-card {
    min-width: min(100%, 720px);
  }

  .calendar-dialog-picker {
    box-shadow: none !important;
    width: 100%;
    min-width: min(100%, 680px);
  }

  .calendar-dialog-picker :deep(.v-picker__title),
  .calendar-dialog-picker :deep(.v-picker-title) {
    display: none;
  }

  .calendar-dialog-picker :deep(.v-date-picker-header),
  .calendar-dialog-picker :deep(.v-date-picker__header) {
    display: none !important;
  }

  .calendar-dialog-picker :deep(.v-date-picker-controls) {
    padding: 12px 8px;
    font-size: 1.05rem;
  }

  .calendar-dialog-picker :deep(.v-date-picker-month__day) {
    min-height: 52px;
  }

  .calendar-dialog-picker :deep(.v-date-picker-month__weekday) {
    font-size: 0.85rem;
    font-weight: 600;
  }

  .calendar-day-btn {
    min-height: 48px !important;
    flex-direction: column !important;
    gap: 2px;
    padding: 6px 4px !important;
  }

  .calendar-day-num {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.2;
  }

  .calendar-day-events {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 4px;
    min-height: 12px;
  }

  .calendar-day-badge :deep(.v-badge__badge) {
    width: 10px !important;
    height: 10px !important;
    min-width: 10px !important;
  }

  .calendar-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
  }

  .calendar-legend-dot--reserved {
    background: rgb(var(--v-theme-amber-darken-2, 245 124 0));
  }

  .calendar-legend-dot--confirmed {
    background: #4caf50;
  }
</style>

<style>
  .calendar-tooltip-light.v-overlay__content,
  .v-overlay__content.calendar-tooltip-light {
    background: #ffffff !important;
    color: #212121 !important;
    border: 1px solid rgba(0, 0, 0, 0.1) !important;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12) !important;
  }

  .calendar-tooltip-light .calendar-tooltip-inner {
    color: #212121;
  }

  .calendar-tooltip-light .calendar-tooltip-service {
    color: #424242;
    margin-top: 2px;
  }

  .calendar-tooltip-light .calendar-tooltip-status {
    color: #616161;
    font-size: 0.75rem;
    margin-top: 4px;
  }
</style>
