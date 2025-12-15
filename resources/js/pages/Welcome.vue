<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { FileText, Edit, BarChart3 } from 'lucide-vue-next';
</script>

<template>
  <Head title="Dynamic Forms" />
  
  <div class="min-h-screen bg-white">
    <!-- Navigation -->
    <nav class="border-b">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <FileText class="w-6 h-6 text-blue-600" />
          <span class="text-xl font-semibold">Dynamic Forms</span>
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
      <h1 class="text-5xl font-bold text-gray-900 mb-6">
        Create and manage forms
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
            <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center mb-4">
              <Edit class="w-5 h-5 text-blue-600" />
            </div>
            <h3 class="font-semibold mb-2">Easy to build</h3>
            <p class="text-gray-600">Create forms with a simple interface</p>
          </div>
          
          <div>
            <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center mb-4">
              <FileText class="w-5 h-5 text-blue-600" />
            </div>
            <h3 class="font-semibold mb-2">Collect responses</h3>
            <p class="text-gray-600">Share your forms and gather submissions</p>
          </div>
          
          <div>
            <div class="w-10 h-10 bg-blue-100 rounded flex items-center justify-center mb-4">
              <BarChart3 class="w-5 h-5 text-blue-600" />
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
        <span>© 2025 Dynamic Forms</span>
        <div class="flex items-center gap-2">
          <FileText class="w-4 h-4" />
          <span>Dynamic Forms</span>
        </div>
      </div>
    </footer>
  </div>
</template>