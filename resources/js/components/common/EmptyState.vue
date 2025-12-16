<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Component } from 'vue';

interface Props {
  icon?: Component;
  title: string;
  description?: string;
  actionLabel?: string;
}

defineProps<Props>();

const emit = defineEmits<{
  action: [];
}>();
</script>

<template>
  <div class="text-center py-16">
    <div class="w-20 h-20 rounded-full bg-muted mx-auto mb-4 flex items-center justify-center">
      <component v-if="icon" :is="icon" class="w-10 h-10 text-muted-foreground" />
    </div>
    <h3 class="text-xl font-semibold mb-2">{{ title }}</h3>
    <p v-if="description" class="text-muted-foreground mb-6">{{ description }}</p>
    <Button v-if="actionLabel" @click="emit('action')" size="lg" class="gap-2">
      <slot name="action-icon" />
      {{ actionLabel }}
    </Button>
  </div>
</template>