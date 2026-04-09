<template>
    <div class="landing-page">
        <!-- Hero -->
        <section class="hero-section d-flex align-center">
            <v-container fluid class="hero-container py-14 py-md-16 py-lg-20">
                <v-row class="hero-row align-center" dense>
                    <v-col cols="12" lg="6" class="text-center text-lg-start">
                        <div class="hero-visual d-flex justify-center justify-lg-center">
                            <img
                                :src="heroMarkSrc"
                                alt="Urmaza Dental Clinic"
                                width="400"
                                height="400"
                                class="hero-mark-img"
                                loading="eager"
                                decoding="async"
                            />
                        </div>
                    </v-col>
                    <v-col cols="12" lg="6">
                        <div class="hero-copy mx-auto mx-lg-0">
                            <div class="hero-accent mx-auto mx-lg-0" aria-hidden="true" />
                            <h1 class="hero-headline text-primary-blue mb-6 mb-lg-8">
                                <span class="sr-only">Your family's home for dental health.</span>
                                <span aria-hidden="true">{{ heroTagline }}</span>
                            </h1>
                            <div class="hero-cta-row d-flex">
                                <v-btn
                                    color="primary-blue"
                                    size="x-large"
                                    class="hero-cta hero-cta-gradient rounded-pill px-10 text-white"
                                    elevation="3"
                                    @click="showBooking = true"
                                >
                                    Book an Appointment
                                </v-btn>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </section>

        <!-- Services (from API — active only) -->
        <section class="services-section py-12 py-md-16">
            <v-container>
                <h2 class="text-h5 font-weight-bold text-center mb-2 text-grey-darken-4">Our services</h2>
                <p class="text-body-2 text-center text-medium-emphasis mb-8">Treatments and procedures we offer</p>

                <v-progress-linear v-if="servicesLoading" indeterminate color="primary-blue" rounded class="mb-6" />

                <v-alert v-if="servicesError" type="error" variant="tonal" class="mb-6" density="compact">
                    {{ servicesError }}
                </v-alert>

                <v-row v-if="!servicesLoading && servicesList.length" class="ga-4" dense>
                    <v-col v-for="s in servicesList" :key="s.id" cols="12" sm="6" lg="4">
                        <v-card variant="outlined" rounded="lg" class="service-card h-100 d-flex flex-column">
                            <v-card-item>
                                <template #prepend>
                                    <v-avatar color="primary-blue-lighten-4" size="44" rounded="lg">
                                        <v-icon icon="mdi-tooth-outline" color="primary-blue" size="26" />
                                    </v-avatar>
                                </template>
                                <v-card-title class="text-wrap text-subtitle-1 font-weight-bold pt-0">
                                    {{ s.name }}
                                </v-card-title>
                            </v-card-item>
                            <v-card-text v-if="s.description" class="text-body-2 text-medium-emphasis pt-0 flex-grow-1">
                                {{ s.description }}
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <p
                    v-if="!servicesLoading && !servicesError && servicesList.length === 0"
                    class="text-center text-body-2 text-medium-emphasis mb-0"
                >
                    Services will appear here once they are added in the admin.
                </p>
            </v-container>
        </section>

        <section class="ads-section py-12 py-md-16">
            <v-container>
                <h2 class="text-h5 font-weight-bold text-center mb-2 text-grey-darken-4">Highlights</h2>
                <p class="text-body-2 text-center text-medium-emphasis mb-8">Current offers and clinic updates</p>
                <v-row class="ga-4 justify-center" dense>
                    <v-col v-for="src in adImages" :key="src" cols="12" sm="6" md="3">
                        <v-card
                            flat
                            rounded="lg"
                            class="ad-card ad-card-highlight overflow-hidden border elevation-sm h-100"
                        >
                            <v-img :src="src" alt="" cover aspect-ratio="3/4" class="bg-grey-lighten-3" />
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </section>

        <!-- Contact -->
        <section class="contact-strip py-12 py-md-16">
            <v-container>
                <div class="text-center mb-8 mb-md-10">
                    <h2 class="text-h5 font-weight-bold text-grey-darken-4 mb-2">Visit &amp; contact</h2>
                    <p class="text-body-2 text-medium-emphasis mb-0">Location, phone hours, and updates on Facebook</p>
                </div>

                <v-row class="justify-center align-stretch" dense>
                    <v-col v-for="block in contactBlocks" :key="block.key" cols="12" sm="6" lg="3">
                        <v-card
                            class="contact-card h-100 rounded-xl pa-5 d-flex flex-column"
                            elevation="0"
                            border
                        >
                            <div class="d-flex align-start ga-4">
                                <v-avatar color="primary-blue-lighten-4" size="48" rounded="lg">
                                    <v-icon :icon="block.icon" color="primary-blue" size="26" />
                                </v-avatar>
                                <div class="flex-grow-1 min-width-0">
                                    <p class="text-overline text-primary-blue font-weight-bold mb-2 contact-strip-label">
                                        {{ block.label }}
                                    </p>
                                    <p
                                        v-for="(line, idx) in block.lines"
                                        :key="idx"
                                        class="text-body-2 text-grey-darken-3 mb-0"
                                        :class="{ 'mt-1': idx > 0 }"
                                    >
                                        {{ line }}
                                    </p>
                                </div>
                            </div>
                        </v-card>
                    </v-col>

                    <v-col cols="12" sm="6" lg="3">
                        <v-card
                            class="contact-card contact-card--social h-100 rounded-xl pa-5 d-flex flex-column"
                            elevation="0"
                            border
                        >
                            <div class="d-flex align-start ga-4">
                                <v-avatar color="primary-blue-lighten-4" size="48" rounded="lg">
                                    <v-icon icon="mdi-facebook" color="primary-blue" size="26" />
                                </v-avatar>
                                <div class="flex-grow-1 min-width-0">
                                    <p class="text-overline text-primary-blue font-weight-bold mb-2 contact-strip-label">
                                        Social
                                    </p>
                                    <a
                                        :href="facebookPageUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="contact-fb-link text-body-2 font-weight-bold text-primary-blue d-inline-flex align-center flex-wrap ga-1"
                                    >
                                        Follow us on Facebook
                                        <v-icon icon="mdi-arrow-top-right" size="18" class="contact-fb-icon" />
                                    </a>
                                </div>
                            </div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </section>

        <AppointmentDialog v-model="showBooking" />
    </div>
</template>

<script setup>
    import { computed, ref } from "vue";

    const showBooking = ref(false);

    const runtimeConfig = useRuntimeConfig();
    const heroMarkSrc = computed(() => {
        const base = runtimeConfig.app.baseURL || "/";
        if (base === "/" || base === "") {
            return "/icon2.png";
        }
        return `${String(base).replace(/\/$/, "")}/icon2.png`;
    });

    const heroTagline = "𝙔𝙤𝙪𝙧 𝙛𝙖𝙢𝙞𝙡𝙮'𝙨 𝙝𝙤𝙢𝙚 𝙛𝙤𝙧 𝙙𝙚𝙣𝙩𝙖𝙡 𝙝𝙚𝙖𝙡𝙩𝙝.";

    const facebookPageUrl = computed(() => String(runtimeConfig.public.facebookUrl || "https://www.facebook.com/"));

    const contactBlocks = [
        {
            key: "address",
            label: "Location",
            icon: "mdi-map-marker-outline",
            lines: ["720 NHA Ave., Brgy. Dela Paz,", "Antipolo City, Rizal"],
        },
        {
            key: "phone",
            label: "Phone",
            icon: "mdi-phone-outline",
            lines: ["0995-906-4972", "(02) 8563-9248"],
        },
        {
            key: "hours",
            label: "Hours",
            icon: "mdi-clock-outline",
            lines: ["Monday–Saturday", "9am–6pm"],
        },
    ];

    /** Served from `public/ads/` — add or rename files here when you change assets. */
    const adImages = ["/ads/1.jpg", "/ads/2.jpg", "/ads/3.jpg", "/ads/4.jpg"];

    const apiBase = usePublicApiBase();
    const servicesList = ref([]);
    const servicesLoading = ref(false);
    const servicesError = ref("");

    async function loadServices() {
        servicesLoading.value = true;
        servicesError.value = "";
        try {
            const rows = await $fetch(`${apiBase.value}/api/services`);
            servicesList.value = Array.isArray(rows) ? rows : [];
        } catch (e) {
            console.error(e);
            servicesError.value = "Could not load services. Please try again later.";
            servicesList.value = [];
        } finally {
            servicesLoading.value = false;
        }
    }

    onMounted(() => {
        loadServices();
    });
</script>

<style scoped lang="scss">
    /* One viewport-anchored backdrop so section boundaries don’t “restart” the gradient. */
    .landing-page {
        position: relative;
        isolation: isolate;

        &::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-color: #f5f7fc;
            background-image:
                radial-gradient(ellipse 95% 72% at 90% 4%, rgba(253, 184, 19, 0.34), transparent 48%),
                radial-gradient(ellipse 78% 58% at -2% 102%, rgba(26, 35, 126, 0.18), transparent 46%),
                radial-gradient(ellipse 70% 52% at 102% 48%, rgba(26, 35, 126, 0.08), transparent 50%),
                radial-gradient(ellipse 65% 50% at 0% 88%, rgba(253, 184, 19, 0.1), transparent 52%),
                linear-gradient(
                    168deg,
                    rgba(26, 35, 126, 0.2) 0%,
                    rgba(197, 208, 255, 0.48) 16%,
                    #ffffff 38%,
                    rgba(236, 240, 255, 0.72) 58%,
                    rgba(255, 255, 255, 0.97) 78%,
                    rgba(249, 250, 251, 1) 92%,
                    rgba(253, 184, 19, 0.1) 100%
                );
        }

        > section {
            position: relative;
            z-index: 1;
        }
    }

    .hero-section {
        position: relative;
        overflow: hidden;
        min-height: min(88vh, 56rem);
        background: transparent;

        @media (max-width: 1279px) {
            min-height: 0;
        }

        .hero-container {
            position: relative;
            z-index: 1;
            max-width: 1480px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1.25rem;
            padding-right: 1.25rem;

            @media (min-width: 960px) {
                padding-left: 2rem;
                padding-right: 2rem;
            }

            @media (min-width: 1280px) {
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
        }

        .hero-row {
            @media (min-width: 1280px) {
                min-height: min(78vh, 44rem);
            }
        }

        .hero-copy {
            position: relative;
            max-width: 38rem;

            @media (min-width: 1280px) {
                max-width: 42rem;
            }
        }

        .hero-visual {
            width: 100%;
            max-width: none;

            .hero-mark-img {
                display: block;
                width: auto;
                max-width: min(100%, 440px);
                max-height: min(58vh, 440px);
                height: auto;
                object-fit: contain;
            }

            @media (min-width: 1280px) {
                .hero-mark-img {
                    max-width: min(100%, 480px);
                    max-height: min(62vh, 480px);
                }
            }
        }

        .hero-accent {
            width: 3.5rem;
            height: 5px;
            border-radius: 999px;
            background: linear-gradient(
                90deg,
                var(--secondary-yellow, #fdb813) 0%,
                #ffe082 45%,
                var(--secondary-yellow, #fdb813) 100%
            );
            margin-bottom: 1.5rem;
        }

        .hero-headline {
            font-size: clamp(1.9rem, 1.1rem + 3.8vw, 3.35rem);
            line-height: 1.22;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .hero-cta {
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .hero-cta-gradient {
            background-image: linear-gradient(
                135deg,
                #12185c 0%,
                #1a237e 38%,
                #303f9f 72%,
                #1a237e 100%
            ) !important;
            box-shadow:
                0 6px 22px rgba(26, 35, 126, 0.42),
                0 2px 8px rgba(253, 184, 19, 0.18) !important;
        }

        .hero-cta-row {
            justify-content: center;

            @media (min-width: 1280px) {
                justify-content: flex-start;
            }
        }

        .hero-brand-card {
            background: #fff;
            border: 1px solid rgba(26, 35, 126, 0.08);
            box-shadow:
                0 16px 40px rgba(26, 35, 126, 0.1),
                0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .hero-brand-card-title {
            font-size: clamp(2.75rem, 8vw, 4.25rem);
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: -0.02em;
            text-transform: lowercase;
            font-family: inherit;
        }

        .hero-brand-card-bar {
            width: min(100%, 13rem);
            height: 7px;
            border-radius: 999px;
            background: var(--secondary-yellow, #fdb813);
            margin-top: 0.85rem;
            margin-bottom: 0.85rem;
        }

        .hero-brand-card-sub {
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.42em;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
            font-family: Poppins;
        }
    }

    .card-testimonial {
        background-color: white;
        .italic {
            font-style: italic;
        }
    }

    .leading-relaxed {
        line-height: 1.8;
    }

    .gap-6 {
        gap: 24px;
    }

    .social-icon-hover {
        cursor: pointer;
        transition: all 0.2s ease;
        &:hover {
            color: var(--primary-blue) !important;
            transform: scale(1.1);
        }
    }

    .about-logo {
        filter: grayscale(0.2);
    }

    .ads-section {
        overflow: hidden;
        background: transparent;

        .ad-card {
            border-color: rgba(26, 35, 126, 0.14) !important;
        }

        /* Full width of column — four cards per row from `md` up (matches wide before/after tiles). */
        .ad-card-highlight {
            width: 100%;
            max-width: 100%;
            position: relative;
            background: linear-gradient(
                165deg,
                rgba(228, 234, 253, 0.85) 0%,
                #ffffff 35%,
                rgba(253, 184, 19, 0.12) 100%
            ) !important;
        }

        .ad-card-highlight::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(
                180deg,
                rgba(26, 35, 126, 0.06) 0%,
                transparent 35%,
                transparent 62%,
                rgba(26, 35, 126, 0.08) 100%
            );
            pointer-events: none;
            z-index: 1;
        }

        .ad-card-highlight :deep(.v-img) {
            position: relative;
            z-index: 2;
        }

        .ad-card-highlight :deep(.v-img__img) {
            object-position: center top;
        }
    }

    .elevation-sm {
        box-shadow:
            0 1px 3px 0 rgba(0, 0, 0, 0.08),
            0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
    }

    .services-section {
        overflow: hidden;
        background: transparent;

        .service-card {
            border-color: rgba(26, 35, 126, 0.16) !important;
            background: linear-gradient(
                158deg,
                rgba(255, 255, 255, 0.98) 0%,
                rgba(220, 228, 255, 0.72) 42%,
                rgba(255, 255, 255, 0.96) 72%,
                rgba(253, 184, 19, 0.07) 100%
            ) !important;
            transition: box-shadow 0.2s ease;
            &:hover {
                box-shadow:
                    0 6px 20px rgba(26, 35, 126, 0.16),
                    0 2px 8px rgba(253, 184, 19, 0.1) !important;
            }
        }
    }

    .contact-strip {
        overflow: hidden;
        background: transparent;

        .contact-strip-label {
            letter-spacing: 0.08em;
            line-height: 1.2;
        }

        .contact-card {
            border-color: rgba(26, 35, 126, 0.18) !important;
            background: linear-gradient(
                148deg,
                rgba(255, 255, 255, 0.99) 0%,
                rgba(228, 234, 253, 0.78) 38%,
                #ffffff 72%,
                rgba(253, 184, 19, 0.1) 100%
            ) !important;
            transition:
                box-shadow 0.22s ease,
                transform 0.22s ease,
                border-color 0.22s ease;

            &:hover {
                box-shadow:
                    0 12px 32px rgba(26, 35, 126, 0.14),
                    0 4px 12px rgba(253, 184, 19, 0.12) !important;
                transform: translateY(-3px);
                border-color: rgba(26, 35, 126, 0.28) !important;
            }
        }

        .contact-card--social {
            background: linear-gradient(
                152deg,
                rgba(26, 35, 126, 0.14) 0%,
                rgba(255, 255, 255, 0.96) 45%,
                rgba(253, 184, 19, 0.16) 100%
            ) !important;
        }

        .contact-fb-link {
            text-decoration: none;
            transition: color 0.15s ease;

            &:hover {
                color: var(--secondary-yellow, #fdb813) !important;
            }

            &:hover .contact-fb-icon {
                transform: translate(2px, -2px);
            }
        }

        .contact-fb-icon {
            transition: transform 0.18s ease;
        }

        .min-width-0 {
            min-width: 0;
        }
    }
</style>