<template>
  <v-row class="mb-6" dense>
    <v-col cols="12" sm="4">
      <v-card class="rounded-xl border shadow-sm pa-5" flat>
        <div class="d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-grey-darken-1 text-uppercase letter-spacing-1">Patients today</div>
            <div class="text-h4 font-weight-bold text-grey-darken-4 mt-1">
              <v-progress-circular v-if="loading" indeterminate size="28" width="3" color="primary-blue" />
              <span v-else>{{ stats.patients_today }}</span>
            </div>
          </div>
          <v-avatar color="primary-blue-lighten-4" size="48" rounded="lg">
            <v-icon icon="mdi-calendar-today" color="primary-blue" size="28" />
          </v-avatar>
        </div>
      </v-card>
    </v-col>
    <v-col cols="12" sm="4">
      <v-card class="rounded-xl border shadow-sm pa-5" flat>
        <div class="d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-grey-darken-1 text-uppercase letter-spacing-1">This week</div>
            <div class="text-h4 font-weight-bold text-grey-darken-4 mt-1">
              <v-progress-circular v-if="loading" indeterminate size="28" width="3" color="primary-blue" />
              <span v-else>{{ stats.patients_this_week }}</span>
            </div>
          </div>
          <v-avatar color="teal-lighten-4" size="48" rounded="lg">
            <v-icon icon="mdi-calendar-week" color="teal-darken-2" size="28" />
          </v-avatar>
        </div>
      </v-card>
    </v-col>
    <v-col cols="12" sm="4">
      <v-card class="rounded-xl border shadow-sm pa-5" flat>
        <div class="d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-grey-darken-1 text-uppercase letter-spacing-1">Total patients</div>
            <div class="text-h4 font-weight-bold text-grey-darken-4 mt-1">
              <v-progress-circular v-if="loading" indeterminate size="28" width="3" color="primary-blue" />
              <span v-else>{{ stats.patients_total }}</span>
            </div>
          </div>
          <v-avatar color="blue-grey-lighten-4" size="48" rounded="lg">
            <v-icon icon="mdi-account-group-outline" color="blue-grey-darken-2" size="28" />
          </v-avatar>
        </div>
      </v-card>
    </v-col>
  </v-row>
</template>

<script setup>
  import { resolveApiBaseString } from "~/utils/apiBase";

  const props = defineProps({
      apiBase: {
          type: String,
          default: "",
      },
  });

  const loading = ref(false);
  const stats = ref({
      patients_today: 0,
      patients_this_week: 0,
      patients_total: 0,
  });

  const base = computed(() => resolveApiBaseString(props.apiBase));

  async function load() {
      loading.value = true;
      try {
          const data = await $fetch(`${base.value}/api/appointments/analytics/patients`);
          stats.value = {
              patients_today: Number(data?.patients_today ?? 0),
              patients_this_week: Number(data?.patients_this_week ?? 0),
              patients_total: Number(data?.patients_total ?? 0),
          };
      } catch (e) {
          console.error(e);
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
