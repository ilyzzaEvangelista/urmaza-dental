<template>
  <div class="bg-gray-50 min-h-screen">
    <!-- Hero Section -->
    <section class="py-20">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-center gap-12">
          <div class="w-full md:w-1/2 flex justify-center">
            <img
              src="~/assets/images/logo.png"
              alt="Urmaza Dental Logo"
              class="max-w-[300px] md:max-w-[400px] object-contain drop-shadow-xl"
            />
          </div>
          <div class="w-full md:w-1/2 text-center md:text-left">
            <h1 class="text-primary-blue leading-tight mb-8">
              <span class="text-5xl md:text-7xl font-light tracking-wider lowercase block mb-2">urmaza</span>
              <span class="text-4xl md:text-5xl font-bold tracking-[0.2em] uppercase block">DENTAL CLINIC</span>
            </h1>
            <button
              class="bg-primary-blue hover:bg-blue-700 text-white text-lg font-semibold py-4 px-10 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
            >
              BOOK AN APPOINTMENT
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(testimonial, index) in testimonials" :key="index" 
            class="p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300 bg-white group"
          >
            <div class="mb-6 text-gray-400 group-hover:text-secondary-yellow transition-colors">
              <span class="text-4xl">"</span>
            </div>
            <p class="text-gray-600 italic text-lg mb-8 leading-relaxed">
              {{ testimonial.text }}
            </p>
            <div class="border-t border-gray-50 pt-6">
              <h3 class="text-xl font-bold text-gray-900">{{ testimonial.name }}</h3>
              <p class="text-sm text-gray-500 font-medium">{{ testimonial.title }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="py-24">
      <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center gap-16">
          <div class="w-full md:w-3/5">
            <p class="text-2xl text-gray-700 leading-relaxed font-light">
              Experience modern dental care with state-of-the-art facilities and a compassionate team dedicated to your oral health journey. We use the latest technology to ensure your comfort and the best results for your smile.
            </p>
          </div>
          <div class="w-full md:w-2/5 flex justify-center">
            <img
              src="~/assets/images/logo.png"
              alt="About Logo"
              class="max-w-[200px] opacity-40 grayscale hover:grayscale-0 transition-all duration-500"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="py-24 bg-gray-50 text-center">
      <div class="container mx-auto px-4 max-w-2xl">
        <h2 class="text-5xl font-medium text-gray-900 mb-16">Contact Us</h2>
        <form class="space-y-6">
          <input
            type="text"
            placeholder="Your Name"
            class="w-full px-6 py-4 rounded-xl border border-gray-200 focus:border-primary-blue focus:ring-2 focus:ring-blue-100 outline-none transition-all text-lg"
          />
          <input
            type="email"
            placeholder="Your Email"
            class="w-full px-6 py-4 rounded-xl border border-gray-200 focus:border-primary-blue focus:ring-2 focus:ring-blue-100 outline-none transition-all text-lg"
          />
          
          <div class="flex justify-center gap-8 py-8">
            <a href="#" class="text-gray-400 hover:text-primary-blue transition-colors duration-300">
              <v-icon icon="mdi-facebook" size="x-large"></v-icon>
            </a>
            <a href="#" class="text-gray-400 hover:text-primary-blue transition-colors duration-300">
              <v-icon icon="mdi-instagram" size="x-large"></v-icon>
            </a>
            <a href="#" class="text-gray-400 hover:text-primary-blue transition-colors duration-300">
              <v-icon icon="mdi-twitter" size="x-large"></v-icon>
            </a>
          </div>

          <p class="text-sm text-gray-400 tracking-wide pt-8">
            © 2025 URMAZA Dental Clinic. All rights reserved.
          </p>
        </form>
      </div>
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

<style scoped>
/* No extra SCSS needed! Tailwind handles it all. */
</style>
