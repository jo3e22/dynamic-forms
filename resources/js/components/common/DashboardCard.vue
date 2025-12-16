<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  hover?: boolean;
  clickable?: boolean;
}

withDefaults(defineProps<Props>(), {
  hover: false,
  clickable: false,
});
</script>

<template>
  <Card 
    :class="[
      'border-sidebar-border/70 dark:border-sidebar-border',
      hover && 'hover:shadow-md transition-shadow',
      clickable && 'cursor-pointer'
    ]"
  >
    <slot />
  </Card>
</template>