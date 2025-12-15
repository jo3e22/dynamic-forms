<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { CheckCircle, XCircle, AlertCircle } from 'lucide-vue-next';

interface Props {
  message: string;
  type?: 'success' | 'error' | 'info';
  duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'success',
  duration: 3000,
});

const show = ref(false);
let timeout: ReturnType<typeof setTimeout> | null = null;

const bgColor = {
  success: 'bg-green-500',
  error: 'bg-red-500',
  info: 'bg-blue-500',
}[props.type];

const icon = {
  success: CheckCircle,
  error: XCircle,
  info: AlertCircle,
}[props.type];

function showToast() {
  if (timeout) {
    clearTimeout(timeout);
  }
  
  show.value = true;
  
  timeout = setTimeout(() => {
    show.value = false;
  }, props.duration);
}

watch(() => props.message, (newMessage) => {
  if (newMessage) {
    showToast();
  }
}, { immediate: true });

onMounted(() => {
  if (props.message) {
    showToast();
  }
});
</script>

<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 translate-y-[-10px]"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-[-10px]"
  >
    <div
      v-if="show"
      :class="[bgColor, 'fixed top-20 left-1/2 transform -translate-x-1/2 z-50 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2']"
    >
      <component :is="icon" class="w-5 h-5" />
      <span>{{ message }}</span>
    </div>
  </Transition>
</template>