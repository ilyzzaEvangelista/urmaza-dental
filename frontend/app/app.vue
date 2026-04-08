<template>
  <v-app>
    <!-- Navigation Bar -->
    <header class="bg-primary-blue h-[80px] flex items-center shadow-md sticky top-0 z-50">
      <div class="container mx-auto px-4 flex items-center justify-between">
        <h1 class="text-white text-2xl font-bold tracking-wider uppercase">
          Urmaza Dental Clinic
        </h1>
        
        <div class="flex items-center gap-6">
          <nav class="hidden md:flex items-center gap-8">
            <NuxtLink to="/" class="text-white hover:text-blue-200 transition-colors font-medium">Home</NuxtLink>
          </nav>
          
          <div class="flex items-center gap-3">
            <button class="p-2 text-white hover:bg-white/10 rounded-full transition-colors">
              <v-icon icon="mdi-menu" size="large"></v-icon>
            </button>
            <button class="p-2 text-white hover:bg-white/10 rounded-full transition-colors">
              <v-icon icon="mdi-logout" size="large"></v-icon>
            </button>
          </div>
        </div>
      </div>
    </header>

    <v-main>
      <NuxtPage />
    </v-main>
  </v-app>
</template>

<script setup>
</script>

<style>
/* Global Tailwind Base */
@tailwind base;
@tailwind components;
@tailwind utilities;

.v-application {
  background-color: transparent !important;
}
</style>


