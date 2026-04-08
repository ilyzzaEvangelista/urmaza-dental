<template>
    <v-dialog v-model="internalValue" max-width="500" persistent theme="light">
        <v-card class="pa-4 rounded-lg">
            <v-card-title class="d-flex justify-center align-center">
                <span class="text-h4 font-weight-medium">Staff login</span>
                <v-spacer></v-spacer>
                <v-btn icon="mdi-close" variant="text" @click="close"></v-btn>
            </v-card-title>

            <v-divider></v-divider>

            <v-card-text class="pt-6">
                <v-form @submit.prevent="login">
                    <v-text-field
                        v-model="form.email"
                        label="Email"
                        variant="outlined"
                        density="comfortable"
                        class="mb-4"
                    ></v-text-field>

                    <v-text-field
                        v-model="form.password"
                        label="Password"
                        type="password"
                        variant="outlined"
                        density="comfortable"
                        class="mb-6"
                    ></v-text-field>

                    <v-divider class="mb-4"></v-divider>

                    <div class="d-flex align-center justify-center">
                        <v-btn
                            type="submit"
                            color="primary-blue"
                            size="default"
                            class="px-8 font-weight-bold text-white"
                            :loading="loading"
                        >
                            Login
                        </v-btn>
                    </div>
                </v-form>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script setup>
    const props = defineProps({
        modelValue: Boolean,
    });

    const emit = defineEmits(["update:modelValue"]);

    const internalValue = computed({
        get: () => props.modelValue,
        set: (val) => emit("update:modelValue", val),
    });

    const form = ref({
        email: "",
        username: "",
        password: "",
    });

    const loading = ref(false);

    const apiBase = usePublicApiBase();
    const token = useCookie("auth_token");
    const roleCookie = useCookie("auth_role");

    const close = () => {
        internalValue.value = false;
    };

    const login = async () => {
        loading.value = true;
        try {
            const response = await $fetch(`${apiBase.value}/api/login`, {
                method: "POST",
                body: {
                    email: form.value.email,
                    password: form.value.password,
                },
            });

            token.value = response.access_token;
            roleCookie.value = response?.user?.role ?? null;

            navigateTo("/admin");
            close();
        } catch (error) {
            console.error("Login failed:", error);
            alert("Login failed. Please check your credentials.");
        } finally {
            loading.value = false;
        }
    };
</script>