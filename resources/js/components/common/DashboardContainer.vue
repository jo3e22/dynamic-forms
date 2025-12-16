<script setup lang="ts">
import DashboardCard from '@/components/common/DashboardCard.vue';

interface Props {
  title?: string;
  description?: string;
}

defineProps<Props>();
</script>

<template>
  <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card">
    <div class="p-6">
      <div v-if="title || $slots.header" class="flex items-center justify-between mb-6">
        <slot name="header">
          <div v-if="title">
            <h2 class="text-2xl font-bold">{{ title }}</h2>
            <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
          </div>
        </slot>
        <slot name="actions" />
      </div>
      
      <slot />
    </div>
  </div>
</template>