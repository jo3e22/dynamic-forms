<template>
    <div class="mb-6 rounded-md bg-white">
        <div :style="{ backgroundColor: form.primary_color }" class="h-3 w-full rounded-t-md"></div>

        <div class="p-6 space-y-4">
            <input
                v-model="section.title"
                placeholder="Form Title"
                class="mt-2 block w-full text-4xl border-b-2 focus:border-b-2 focus:outline-none"
                :style="{
                    '--tw-border-opacity': '1',
                    borderColor: 'transparent',
                }"
            />

            <textarea
                ref="descRef"
                v-model="section.description"
                rows="1"
                placeholder="Form Description"
                class="mt-1 block w-full border-b-2 focus:outline-none resize-none overflow-hidden"
                :style="{
                '--tw-border-opacity': '1',
                borderTop: 'none',
                borderLeft: 'none',
                borderRight: 'none',
                borderColor: 'transparent',
                backgroundColor: 'transparent',
                }"
                @input="adjustTextareaHeight"
            />
        </div>
    </div>
</template>
  
<script lang="ts" setup>
import { ref, watch, onMounted, nextTick} from 'vue';

const props = defineProps({
    form: Object,
    section: Object,
    index: Number
})

const form = props.form;
const section = props.section;

const descRef = ref<HTMLTextAreaElement | null>(null);

function adjustTextareaHeight(e?: Event) {
  const el = (e?.target as HTMLTextAreaElement) ?? descRef.value;
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = `${el.scrollHeight}px`;
}


onMounted(() => {
  // initialize height
  nextTick(() => adjustTextareaHeight());
});

// react to external description changes
watch(
  () => section.description,
  () => nextTick(() => adjustTextareaHeight()),
  { immediate: true }
);
</script>
  