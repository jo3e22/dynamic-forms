<template>
  <div class="max-w-4xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-4">
      {{ form.title ?? 'Untitled Form' }}
    </h1>

    <input v-model="form.title" placeholder="Form Title" class="mb-4 w-full border px-3 py-2" />
    <textarea v-model="form.description" placeholder="Form Description" class="mb-4 w-full border px-3 py-2" />

    <!-- Render fields -->
    <div v-for="(field, index) in fields" :key="field.uuid" class="mb-4">
      <input v-model="field.label" placeholder="Question label" class="w-full border px-3 py-2" />
    </div>

    <!-- Add field button -->
    <button @click="addField" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
      Add Field
    </button>

    <!-- Save button -->
    <button @click="saveForm" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Save Form
    </button>
  </div>
  <div v-if="showSuccess" class="success-message">
    {{ page.props.flash.success }}
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
  form: Object,
  fields: Array
});

const form = ref({ ...props.form });
const fields = ref([...props.fields]);
const page = usePage();
const showSuccess = ref(false);

watch(() => page.props.flash?.success, (message) => {
  if (message) {
    showSuccess.value = true;
    setTimeout(() => showSuccess.value = false, 4000);
  }
});

function addField() {
  fields.value.push({
    label: '',
    type: 'text',
    required: false,
    uuid: crypto.randomUUID()
  });
}

function saveForm() {
  try {
    console.log('Save button clicked');
    router.put(`/forms/${form.value.code}/edit`, {
      title: form.value.title,
      description: form.value.description,
      fields: fields.value
    }, {
      onSuccess: () => {
        console.log('Form updated!');
        // Show toast here
      },
      onError: (errors) => {
        console.error('Validation failed:', errors);
      }
    });
  }
  catch (error) {
    console.error('An error occurred while saving the form:', error);
  }
}


</script>
