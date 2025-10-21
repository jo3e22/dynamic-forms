<script lang="ts" setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const title = ref('');
const questions = ref([
  { type: 'text', label: 'Question 1', required: false }
]);

function addQuestion() {
  questions.value.push({ type: 'text', label: '', required: false });
}

function saveForm() {
  router.post('/forms', {
    title: title.value,
    fields: questions.value
  });
}
</script>

<template>
  <div>
    <h1>Create a New Form</h1>
    <input v-model="title" placeholder="Form title" />

    <div v-for="(question, index) in questions" :key="index">
      <input v-model="question.label" placeholder="Question label" />
      <select v-model="question.type">
        <option value="text">Text</option>
        <option value="select">Select</option>
        <option value="checkbox">Checkbox</option>
      </select>
      <label>
        <input type="checkbox" v-model="question.required" />
        Required
      </label>
    </div>

    <button @click="addQuestion">Add Question</button>
    <button @click="saveForm">Save Form</button>
  </div>
</template>
