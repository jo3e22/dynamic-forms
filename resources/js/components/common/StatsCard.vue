<script setup lang="ts">
import DashboardCard from '@/components/common/DashboardCard.vue';
import { CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { Component } from 'vue';

interface Props {
  title: string;
  value: string | number;
  description?: string;
  icon?: Component;
}

defineProps<Props>();
</script>

<template>
  <DashboardCard>
    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
      <CardTitle class="text-sm font-medium">{{ title }}</CardTitle>
      <component v-if="icon" :is="icon" class="h-4 w-4 text-muted-foreground" />
    </CardHeader>
    <CardContent>
      <div class="text-2xl font-bold">{{ value }}</div>
      <p v-if="description" class="text-xs text-muted-foreground">
        {{ description }}
      </p>
    </CardContent>
  </DashboardCard>
</template>