<template>
    <v-container fluid class="pa-8 audit-logs-page">
        <div class="d-flex align-center flex-wrap ga-4 mb-6">
            <h1 class="text-h5 font-weight-bold text-grey-darken-4">Audit logs</h1>
        </div>

        <div class="d-flex align-center flex-wrap ga-4 mb-6">
            <v-text-field
                v-model="search"
                density="comfortable"
                variant="outlined"
                hide-details
                placeholder="Search action, description, user, or IP"
                prepend-inner-icon="mdi-magnify"
                clearable
                class="audit-search"
                style="max-width: 520px"
            />
        </div>

        <v-alert v-if="fetchError" type="error" variant="tonal" class="mb-6" closable @click:close="fetchError = ''">
            {{ fetchError }}
        </v-alert>

        <v-card class="rounded-xl border shadow-sm" flat>
            <v-card-title class="pa-6 d-flex align-center flex-wrap ga-4">
                <span class="text-h6 font-weight-bold text-grey-darken-4">Activity</span>
                <v-spacer></v-spacer>
                <v-btn color="primary-blue" variant="outlined" density="comfortable" @click="loadLogs">
                    <v-icon class="mr-2" icon="mdi-refresh" size="small" />
                    Refresh
                </v-btn>
            </v-card-title>

            <v-data-table
                class="audit-table px-4 pb-4"
                :headers="headers"
                :items="logs"
                :loading="loading"
                item-value="id"
                :items-per-page="-1"
                hide-default-footer
            >
                <template #item.created_at="{ item }">
                    <div class="text-body-2">{{ formatWhen(item.created_at) }}</div>
                </template>

                <template #item.user="{ item }">
                    <div v-if="item.user" class="text-body-2">
                        <div class="font-weight-medium">{{ item.user.name }}</div>
                        <div class="text-caption text-medium-emphasis">{{ item.user.email }}</div>
                    </div>
                    <span v-else class="text-medium-emphasis">—</span>
                </template>

                <template #item.action="{ item }">
                    <v-chip size="small" variant="tonal" color="primary-blue" class="font-weight-medium">
                        {{ formatAuditAction(item.action) }}
                    </v-chip>
                </template>

                <template #item.description="{ item }">
                    <span class="text-body-2 text-medium-emphasis text-wrap">{{ item.description || "—" }}</span>
                </template>

                <template #item.ip_address="{ item }">
                    <code class="text-body-2">{{ item.ip_address || "—" }}</code>
                </template>

                <template #no-data>
                    <div v-if="!loading" class="text-center py-8 text-grey-darken-1">
                        {{ total === 0 && !search.trim() ? "No audit entries yet." : "No entries match your search." }}
                    </div>
                </template>
            </v-data-table>

            <v-card-actions v-show="lastPage > 1" class="justify-center pb-6 pt-0 flex-wrap pagination-actions">
                <v-pagination
                    :model-value="page"
                    :length="lastPage"
                    density="comfortable"
                    rounded="circle"
                    color="primary-blue"
                    :total-visible="7"
                    @update:model-value="onPageChange"
                />
            </v-card-actions>
        </v-card>
    </v-container>
</template>

<script setup>
    definePageMeta({
        layout: "admin",
    });

    const apiBase = usePublicApiBase();
    const authHeaders = useAuthFetchHeaders();

    const loading = ref(false);
    const fetchError = ref("");
    const logs = ref([]);
    const search = ref("");
    const page = ref(1);
    const lastPage = ref(1);
    const total = ref(0);
    const perPage = 15;

    let searchDebounce = null;

    const headers = [
        { title: "When", key: "created_at", sortable: false, width: "180px" },
        { title: "User", key: "user", sortable: false, minWidth: "200px" },
        { title: "Action", key: "action", sortable: false, width: "160px" },
        { title: "Description", key: "description", sortable: false },
        //   { title: "IP", key: "ip_address", sortable: false, width: "140px" },
    ];

    function formatWhen(iso) {
        if (!iso) return "—";
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return String(iso);
        return d.toLocaleString(undefined, {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    function formatAuditAction(action) {
        if (action == null || action === "") return "—";
        const s = String(action).replace(/\./g, " ").trim().toLowerCase();
        if (!s) return "—";
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    async function loadLogs() {
        fetchError.value = "";
        loading.value = true;
        try {
            const query = {
                page: page.value,
                per_page: perPage,
            };
            const q = search.value?.trim();
            if (q) {
                query.search = q;
            }
            const res = await $fetch(`${apiBase.value}/api/audit-logs`, {
                query,
                headers: { ...authHeaders.value },
            });
            const rows = res?.data;
            logs.value = Array.isArray(rows) ? rows : [];
            lastPage.value = Math.max(1, Number(res?.last_page) || 1);
            total.value = Number(res?.total) || 0;
            if (res?.current_page != null) {
                page.value = Number(res.current_page);
            }
        } catch (e) {
            console.error(e);
            fetchError.value =
                e?.data?.message || e?.response?._data?.message || "Could not load audit logs. Is the API running?";
            logs.value = [];
            lastPage.value = 1;
            total.value = 0;
        } finally {
            loading.value = false;
        }
    }

    function onPageChange(value) {
        page.value = value;
        loadLogs();
    }

    watch(search, () => {
        if (searchDebounce) clearTimeout(searchDebounce);
        searchDebounce = setTimeout(() => {
            page.value = 1;
            loadLogs();
        }, 400);
    });

    onMounted(() => {
        loadLogs();
    });
</script>

<style scoped>
    .audit-logs-page {
        min-height: 100%;
    }

    .audit-table :deep(th) {
        font-weight: 700 !important;
        color: #616161 !important;
    }

    .shadow-sm {
        box-shadow:
            0 1px 3px 0 rgba(0, 0, 0, 0.1),
            0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .pagination-actions {
        min-height: 48px;
    }

    .pagination-actions :deep(.v-pagination__list) {
        flex-wrap: wrap;
    }
</style>