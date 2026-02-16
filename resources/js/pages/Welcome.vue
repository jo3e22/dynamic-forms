<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Edit, BarChart3 } from 'lucide-vue-next';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
  <Head title="" />
  
  <div class="min-h-screen bg-white">
    <!-- Navigation -->
    <nav class="border-b">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-primary rounded-md flex items-center justify-center">
            <AppLogoIcon class="w-5 h-5 text-white" checkColor="var(--secondary)" />
          </div>
          <div class="flex flex-col">
            <span class="text-xl font-extrabold leading-tight">
              <span class="text-black">Dynamic</span>
              <span class="text-secondary">Forms</span>
            </span>
            <span class="text-xs text-muted-foreground">by jdev.ie</span>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <template v-if="$page.props.auth.user">
            <Link href="/forms">
              <Button>My Forms</Button>
            </Link>
          </template>
          <template v-else>
            <Link href="/login">
              <Button variant="ghost">Sign in</Button>
            </Link>
            <Link href="/register">
              <Button>Sign up</Button>
            </Link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-4xl mx-auto px-6 py-24">
      <h1 class="text-5xl font-extrabold text-gray-900 mb-6">
        <span class="">Create and manage</span>
        <span class="text-secondary"> Forms</span>
      </h1>
      <p class="text-xl text-gray-600 mb-8 max-w-2xl">
        Build custom forms, collect responses, and view submissions all in one place.
      </p>
      
      <div class="flex gap-4">
        <Link v-if="$page.props.auth.user" href="/forms">
          <Button size="lg">Go to Forms</Button>
        </Link>
        <Link v-else href="/register">
          <Button size="lg">Get Started</Button>
        </Link>
      </div>

      <!-- Simple preview -->
      <div class="mt-16 border rounded-lg p-8 bg-gray-50">
        <div class="space-y-4">
          <div class="h-10 bg-white border rounded w-3/4"></div>
          <div class="h-10 bg-white border rounded w-full"></div>
          <div class="h-10 bg-white border rounded w-2/3"></div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="border-t py-20 bg-gray-50">
      <div class="max-w-4xl mx-auto px-6">
        <div class="grid md:grid-cols-3 gap-12">
          <div>
            <div class="w-10 h-10 bg-[#bfd0e4] rounded flex items-center justify-center mb-4">
              <Edit class="w-5 h-5 text-primary" />
            </div>
            <h3 class="font-semibold mb-2">Easy to build</h3>
            <p class="text-gray-600">Create forms with a simple interface</p>
          </div>
          
          <div>
            <div class="w-10 h-10 bg-[#bfd0e4] rounded flex items-center justify-center mb-4">
              <AppLogoIcon class="w-5 h-5 text-primary" />
            </div>
            <h3 class="font-semibold mb-2">Collect responses</h3>
            <p class="text-gray-600">Share your forms and gather submissions</p>
          </div>
          
          <div>
            <div class="w-10 h-10 bg-[#bfd0e4] rounded flex items-center justify-center mb-4">
              <BarChart3 class="w-5 h-5 text-primary" />
            </div>
            <h3 class="font-semibold mb-2">View results</h3>
            <p class="text-gray-600">See all responses in table or card view</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t py-6">
      <div class="max-w-6xl mx-auto px-6 flex items-center justify-between text-sm text-gray-600">
        <span>© {{ new Date().getFullYear() }} dynamicforms.eu</span>
        <div class="flex items-center gap-2">
          <AppLogoIcon class="w-4 h-4" />
          <span>Dynamic Forms</span>
        </div>
      </div>
    </footer>
  </div>
</template>