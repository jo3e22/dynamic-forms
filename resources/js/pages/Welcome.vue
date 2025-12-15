<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { FileText, Users, BarChart3, Zap, Shield, Smartphone } from 'lucide-vue-next';

const features = [
  {
    icon: FileText,
    title: 'Easy Form Building',
    description: 'Create beautiful forms with our intuitive drag-and-drop builder'
  },
  {
    icon: Users,
    title: 'Collect Responses',
    description: 'Gather submissions from your users seamlessly'
  },
  {
    icon: BarChart3,
    title: 'View Analytics',
    description: 'Analyze responses with powerful table and card views'
  },
  {
    icon: Zap,
    title: 'Real-time Updates',
    description: 'See submissions as they come in, instantly'
  },
  {
    icon: Shield,
    title: 'Secure & Private',
    description: 'Your data is encrypted and protected'
  },
  {
    icon: Smartphone,
    title: 'Mobile Responsive',
    description: 'Works perfectly on any device'
  }
];
</script>

<template>
  <Head title="Welcome to Dynamic Forms" />
  
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <!-- Navigation -->
    <nav class="border-b bg-white/80 backdrop-blur-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <FileText class="w-8 h-8 text-blue-600" />
            <span class="text-2xl font-bold text-gray-900">Dynamic Forms</span>
          </div>
          
          <div class="flex items-center gap-4">
            <template v-if="$page.props.auth.user">
              <Link href="/dashboard">
                <Button variant="ghost">Dashboard</Button>
              </Link>
              <Link href="/forms">
                <Button>My Forms</Button>
              </Link>
            </template>
            <template v-else>
              <Link href="/login">
                <Button variant="ghost">Log in</Button>
              </Link>
              <Link href="/register">
                <Button>Get Started</Button>
              </Link>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-6 py-20 text-center">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-6xl font-bold text-gray-900 mb-6 leading-tight">
          Build Forms That
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
            Work For You
          </span>
        </h1>
        <p class="text-xl text-gray-600 mb-8">
          Create, customize, and manage beautiful forms in minutes. 
          Collect responses, analyze data, and streamline your workflow.
        </p>
        <div class="flex gap-4 justify-center">
          <Link v-if="$page.props.auth.user" href="/forms">
            <Button size="lg" class="gap-2 text-lg px-8 py-6">
              <FileText :size="20" />
              Go to Forms
            </Button>
          </Link>
          <template v-else>
            <Link href="/register">
              <Button size="lg" class="gap-2 text-lg px-8 py-6">
                Get Started Free
              </Button>
            </Link>
            <Link href="/login">
              <Button size="lg" variant="outline" class="text-lg px-8 py-6">
                Sign In
              </Button>
            </Link>
          </template>
        </div>
      </div>

      <!-- Feature Preview Image/Animation Placeholder -->
      <div class="mt-16 rounded-xl shadow-2xl overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-br from-blue-500 to-purple-600 p-1">
          <div class="bg-white rounded-lg p-8">
            <div class="grid grid-cols-3 gap-4">
              <div class="h-32 bg-gray-100 rounded animate-pulse"></div>
              <div class="h-32 bg-gray-100 rounded animate-pulse"></div>
              <div class="h-32 bg-gray-100 rounded animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="bg-white py-20">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            Everything You Need
          </h2>
          <p class="text-xl text-gray-600">
            Powerful features to create and manage forms effortlessly
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <Card v-for="feature in features" :key="feature.title" class="border-2 hover:shadow-lg transition-shadow">
            <CardHeader>
              <div 
                class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4"
              >
                <component :is="feature.icon" class="w-6 h-6 text-blue-600" />
              </div>
              <CardTitle class="text-xl">{{ feature.title }}</CardTitle>
              <CardDescription class="text-base">{{ feature.description }}</CardDescription>
            </CardHeader>
          </Card>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20">
      <div class="max-w-4xl mx-auto px-6 text-center">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-12 text-white">
          <h2 class="text-4xl font-bold mb-4">
            Ready to Get Started?
          </h2>
          <p class="text-xl mb-8 text-blue-100">
            Join thousands of users creating amazing forms today
          </p>
          <Link v-if="!$page.props.auth.user" href="/register">
            <Button size="lg" variant="secondary" class="text-lg px-8 py-6">
              Create Your Free Account
            </Button>
          </Link>
          <Link v-else href="/forms/create">
            <Button size="lg" variant="secondary" class="text-lg px-8 py-6 gap-2">
              <FileText :size="20" />
              Create Your First Form
            </Button>
          </Link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t bg-gray-50 py-8">
      <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <FileText class="w-6 h-6 text-blue-600" />
            <span class="font-semibold text-gray-900">Dynamic Forms</span>
          </div>
          <p class="text-sm text-gray-600">
            © 2025 Dynamic Forms. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>