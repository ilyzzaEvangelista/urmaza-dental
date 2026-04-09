<template>
  <v-card class="appointments-card rounded-xl border shadow-sm" flat>
      <v-card-title class="pa-6 d-flex flex-column flex-sm-row align-stretch align-sm-center flex-wrap ga-4">
          <v-text-field
              v-model="searchQuery"
              density="comfortable"
              variant="outlined"
              hide-details
              placeholder="Search here..."
              prepend-inner-icon="mdi-magnify"
              color="primary-blue"
              class="search-field flex-grow-1 flex-sm-grow-0" 
              style="min-width: min(100%, 500px); max-width: 500px"
          />
          <v-spacer></v-spacer>
          <v-btn
              color="primary-blue"
              density="comfortable"
              variant="outlined"
              size="default"
              @click="emit('refresh')"
          >
              <v-icon
                  class="mr-2"
                  icon="mdi-refresh"
                  color="primary-blue"
                  density="comfortable"
                  variant="flat"
                  size="small"
              ></v-icon>
              Refresh
          </v-btn>
      </v-card-title>

      <v-data-table
          class="appointments-table px-4 text-grey-darken-3"
          :headers="tableHeaders"
          :items="filteredAppointments"
          :loading="loading"
          item-value="id"
          :items-per-page="-1"
          hide-default-footer
          no-data-text=""
      >
          <template #no-data>
              <div v-if="!loading" class="text-center py-8 text-grey-darken-1">
                  <v-icon
                      :icon="searchActive ? 'mdi-magnify' : 'mdi-calendar-blank'"
                      size="large"
                      class="mb-2 d-block mx-auto"
                  />
                  {{ emptyMessage }}
              </div>
          </template>

          <template #item.name="{ item }">
              <div class="d-flex align-center py-2">
                  <v-avatar color="primary-blue-lighten-4" size="36" class="mr-3">
                      <span class="text-caption font-weight-bold text-primary-blue">{{ item.initials }}</span>
                  </v-avatar>
                  <div>
                      <div class="font-weight-bold">{{ item.name }}</div>
                      <div class="text-caption text-grey-darken-1">
                          <v-icon icon="mdi-phone" color="primary-blue" size="small" class="mr-1"></v-icon>
                          <p class="text-caption text-grey-darken-1">Phone No. : {{ item.phone }}</p>
                      </div>
                  </div>
              </div>
          </template>

          <template #item.date="{ item }">
              <div class="font-weight-medium">{{ item.date }}</div>
              <div v-if="item.time" class="text-caption text-grey-darken-1">{{ item.time }}</div>
          </template>

          <template #item.status="{ item }">
              <v-chip :color="item.statusColor" size="small" class="font-weight-bold px-4" variant="flat">
                  <p v-if="item.status == 'Pending'" class="text-caption">Reserved</p>
                  <p v-if="item.status == 'Confirmed'" class="text-caption">Confirmed</p>
                  <p v-if="item.status == 'Completed'" class="text-caption">Completed</p>
                  <p v-if="item.status == 'Cancelled'" class="text-caption">Cancelled</p>
                  <p v-if="item.status == 'No Show'" class="text-caption">No Show</p>
              </v-chip>
          </template>

          <template #item.actions="{ item }">
              <div class="text-end d-flex flex-wrap ga-1 justify-end">
                  <v-btn
                      size="small"
                      variant="text"
                      color="primary-blue"
                      prepend-icon="mdi-eye-outline"
                      class="text-none"
                      @click="emit('view', item)"
                  >
                      View
                  </v-btn>
                  <v-btn
                      size="small"
                      variant="text"
                      color="grey-darken-2"
                      prepend-icon="mdi-pencil-outline"
                      class="text-none"
                      @click="emit('edit', item)"
                  >
                      Edit
                  </v-btn>
                  <v-btn
                      size="small"
                      variant="text"
                      color="error"
                      prepend-icon="mdi-delete-outline"
                      class="text-none"
                      @click="emit('delete', item)"
                  >
                      Delete
                  </v-btn>
              </div>
          </template>
      </v-data-table>

      <v-card-actions v-if="totalPages > 1" class="justify-center pb-4 pt-0 flex-wrap">
          <v-pagination
              :model-value="page"
              :length="totalPages"
              :total-visible="7"
              density="comfortable"
              rounded="circle"
              color="primary-blue"
              @update:model-value="emit('change-page', $event)"
          />
      </v-card-actions>
  </v-card>
</template>

<script setup>
  const props = defineProps({
      appointments: {
          type: Array,
          required: true,
      },
      loading: {
          type: Boolean,
          default: false,
      },
      /** Current page from Laravel pagination (1-based). */
      page: {
          type: Number,
          default: 1,
      },
      /** Total page count from Laravel `last_page`. `length` on v-pagination = pages, not row count. */
      totalPages: {
          type: Number,
          default: 1,
      },
  });

  const emit = defineEmits(["refresh", "view", "edit", "delete", "change-page"]);

  const searchQuery = ref("");

  const searchActive = computed(() => Boolean(searchQuery.value?.trim()));

  function compareAppointmentsLatestFirst(a, b) {
      const ka = Number(a.createdAtMs) || 0;
      const kb = Number(b.createdAtMs) || 0;
      if (kb !== ka) {
          return kb - ka;
      }
      return (Number(b.id) || 0) - (Number(a.id) || 0);
  }

  const filteredAppointments = computed(() => {
      const rows = props.appointments || [];
      const q = searchQuery.value.trim().toLowerCase();
      let list;
      if (!q) {
          list = rows;
      } else {
          const tokens = q.split(/\s+/).filter(Boolean);
          list = rows.filter((item) => {
              const haystack = [
                  item.name,
                  item.phone,
                  item.contact_number,
                  item.email,
                  item.service,
                  item.date,
                  item.time,
                  item.status,
                  item.note,
              ]
                  .filter((v) => v != null && String(v).trim() !== "")
                  .map((v) => String(v).toLowerCase())
                  .join(" ");
              return tokens.every((t) => haystack.includes(t));
          });
      }
      return [...list].sort(compareAppointmentsLatestFirst);
  });

  const emptyMessage = computed(() => {
      if (searchActive.value && props.appointments?.length) {
          return "No appointments match your search.";
      }
      return "No appointments found.";
  });

  const tableHeaders = [
      { title: "Patient", key: "name", sortable: true },
      { title: "Service", key: "service", sortable: true },
      { title: "Date & Time", key: "date", sortable: true },
      { title: "Status", key: "status", sortable: true },
      { title: "Actions", key: "actions", align: "end", sortable: false, minWidth: "300px" },
  ];
</script>

<style scoped>
  .appointments-card,
  .appointments-table,
  .appointments-table :deep(.v-data-table__thead),
  .appointments-table :deep(.v-data-table__tbody) {
      background-color: white !important;
      color: #333333 !important;
  }

  .appointments-table :deep(th) {
      font-weight: 700 !important;
      color: #616161 !important;
  }

  .appointments-card {
      border-color: #ededed !important;
  }

  .shadow-sm {
      box-shadow:
          0 1px 3px 0 rgba(0, 0, 0, 0.1),
          0 1px 2px 0 rgba(0, 0, 0, 0.06);
  }
</style>