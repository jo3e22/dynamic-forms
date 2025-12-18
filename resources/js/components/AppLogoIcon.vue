<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
    checkColor?: string;
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Document outline -->
        <rect 
            x="5" 
            y="2" 
            width="14" 
            height="20" 
            rx="2" 
            stroke="currentColor" 
            stroke-width="2"
            fill="none"
        />
        
        <!-- Form lines -->
        <line x1="8" y1="7" x2="16" y2="7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <line x1="8" y1="11" x2="16" y2="11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        <line x1="8" y1="15" x2="13" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        
        <!-- Checkmark accent -->
        <polyline 
            points="14.5,17 15.5,18 17.5,16" 
            :stroke="checkColor || 'currentColor'"
            stroke-width="2" 
            stroke-linecap="round" 
            stroke-linejoin="round"
            fill="none"
        />
    </svg>
</template>