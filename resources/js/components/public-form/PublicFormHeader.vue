<!-- resources/js/components/public-form/PublicFormHeader.vue -->
<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Props {
  formTitle: string;
  primaryColor: string;
  showBackButton?: boolean;
  backRoute?: string;
}

const props = withDefaults(defineProps<Props>(), {
  showBackButton: true,
  backRoute: '/forms',
});

function goBack() {
  router.visit(props.backRoute);
}
</script>

<template>
  <header 
    class="w-full py-4 px-6 shadow-sm sticky top-0 z-10"
    :style="{ backgroundColor: primaryColor }"
  >
    <div class="max-w-4xl mx-auto flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-white">
        {{ formTitle }}
      </h1>
      
      <slot name="actions">
        <Button
          v-if="showBackButton"
          variant="ghost"
          size="sm"
          @click="goBack"
          class="text-white hover:bg-white/10"
        >
          <ArrowLeft class="w-4 h-4 mr-2" />
          Back to Forms
        </Button>
      </slot>
    </div>
  </header>
</template>