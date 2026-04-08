<template>
  <div>
    <!-- Hero Section -->
    <section class="hero-section py-16">
      <v-container>
        <v-row align="center" justify="center">
          <v-col cols="12" md="5" class="d-flex justify-center">
            <v-img
              src="/icon.png"
              max-width="350"
              contain
              class="hero-logo"
            ></v-img>
          </v-col>
          <v-col cols="12" md="6" class="text-center text-md-left">
            <v-img
              src="/cover.png"
              max-width="450"
              contain
              class="mx-auto mx-md-0 mb-6"
            ></v-img>
            <v-btn
              color="primary"
              size="large"
              class="mt-2 rounded-lg px-8"
              elevation="2"
            >
              BOOK AN APPOINTMENT
            </v-btn>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section py-16 bg-white">
      <v-container>
        <v-row>
          <v-col v-for="(testimonial, index) in testimonials" :key="index" cols="12" md="4">
            <v-card class="card-testimonial pa-6 text-center" flat>
              <p class="text-body-1 italic mb-6 text-grey-darken-2">
                "{{ testimonial.text }}"
              </p>
              <v-divider class="mb-4"></v-divider>
              <h3 class="text-h6 font-weight-bold mb-1">{{ testimonial.name }}</h3>
              <p class="text-caption text-grey-darken-1">{{ testimonial.title }}</p>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- About Section -->
    <section class="about-section py-16">
      <v-container>
        <v-row align="center">
          <v-col cols="12" md="7" class="text-center text-md-left">
            <p class="text-h6 text-grey-darken-3 leading-relaxed mb-0">
              Lorem ipsum dolor sit amet consectetur adipisicing elit dictumst tempus,
              condimentum massa habitasse commodo primis feugiat sagittis id, scelerisque
              mus montes aptent pharetra gravida nostra molestie.
            </p>
          </v-col>
          <v-col cols="12" md="5" class="d-flex justify-center">
            <v-img
              src="/icon.png"
              max-width="250"
              contain
              class="about-logo opacity-60"
            ></v-img>
          </v-col>
        </v-row>
      </v-container>
    </section>

    <!-- Contact Section -->
    <section class="contact-section text-center">
      <v-container>
        <h2 class="text-h3 font-weight-medium mb-10">Contact Us</h2>
        <v-row justify="center">
          <v-col cols="12" md="8" lg="6">
            <v-form>
              <v-text-field
                label="Your Name"
                variant="outlined"
                class="mb-4"
                rounded="lg"
              ></v-text-field>
              <v-text-field
                label="Your Email"
                variant="outlined"
                class="mb-8"
                rounded="lg"
              ></v-text-field>
              
              <div class="social-icons d-flex justify-center gap-6 mb-8">
                <v-icon icon="mdi-facebook" size="x-large" color="grey-darken-1" class="cursor-pointer"></v-icon>
                <v-icon icon="mdi-instagram" size="x-large" color="grey-darken-1" class="cursor-pointer"></v-icon>
                <v-icon icon="mdi-twitter" size="x-large" color="grey-darken-1" class="cursor-pointer"></v-icon>
              </div>

              <p class="text-caption text-grey-darken-1">
                © 2025 URMAZA Dental Clinic. All rights reserved.
              </p>
            </v-form>
          </v-col>
        </v-row>
      </v-container>
    </section>
  </div>
</template>

<script setup>
const testimonials = [
  {
    text: "Dignissim class mus facilisis suscipit pulvinar.",
    name: "John Smith",
    title: "Founder, Tech Corp"
  },
  {
    text: "Eget orci dapibus mus accumsan nisi.",
    name: "Jane Doe",
    title: "Marketing Director, Bright Ideas"
  },
  {
    text: "Congue cum ridiculus platea nostra malesuada.",
    name: "Kim Chiu",
    title: "CEO, Global Solutions"
  }
]
</script>

<style scoped lang="scss">
.hero-section {
  .clinic-name {
    line-height: 1.1;
    .lower {
      font-size: 3.5rem;
      font-weight: 300;
      letter-spacing: 2px;
      text-transform: lowercase;
    }
    .upper {
      font-size: 2.5rem;
      font-weight: 700;
      letter-spacing: 4px;
    }
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

.cursor-pointer {
  cursor: pointer;
  transition: color 0.2s;
  &:hover {
    color: var(--primary-blue) !important;
  }
}

.about-logo {
  filter: grayscale(0.2);
}
</style>
