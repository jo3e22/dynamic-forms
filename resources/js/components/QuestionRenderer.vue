<template>
    <component
      :is="getComponent(question.type)"
      :question="question"
      :mode="mode"
      v-model="modelValue"
    />
  </template>
  
  <script lang="ts">
  import { defineComponent } from 'vue';
  import TextQuestion from './questions/TextQuestion.vue';
  import SelectQuestion from './questions/SelectQuestion.vue';
  
  export default defineComponent({
    name: 'QuestionRenderer',
    components: { TextQuestion, SelectQuestion },
    props: {
      question: Object,
      mode: String,
      modelValue: String
    },
    emits: ['update:modelValue'],
    methods: {
      getComponent(type: string) {
        const map: Record<string, string> = {
          text: 'TextQuestion',
          select: 'SelectQuestion'
        };
        return map[type] || 'TextQuestion';
      }
    }
  });
  </script>
  