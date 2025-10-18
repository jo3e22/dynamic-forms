<script lang="ts" setup>
import { ref } from 'vue';
import QuestionRenderer from '../components/QuestionRenderer.vue';
import { router } from '@inertiajs/vue3';

function submitForm() {
  router.post('/form/' + props.formId + '/submit', {
    answers: answers.value
  });
}

interface Question {
  type: string;
  label: string;
  options?: string[];
  required?: boolean;
}

const props = defineProps<{
  formId: number;
  questions: Question[];
  mode: 'fill' | 'edit' | 'preview';
}>();


const answers = ref<string[]>(props.questions.map(() => ''));
</script>


<template>
  <div>
    <QuestionRenderer
      v-for="(question, index) in props.questions"
      :key="index"
      :question="question"
      :mode="mode"
      v-model="answers[index]"
    />
    <button @click="submitForm">Submit</button>
  </div>
</template>
