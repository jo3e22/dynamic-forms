<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow-md px-6 py-4 flex items-center justify-between">
      <!-- Left: Back Arrow and Title -->
      <div class="flex items-center gap-4">
        <button class="text-gray-600 hover:text-gray-800">
          ←
        </button>
        <h1 class="text-xl font-semibold text-gray-800">{{ form.title ?? 'Untitled Form' }}</h1>
      </div>

      <!-- Right: Buttons -->
      <div class="flex items-center gap-3">
        <button @click="saveForm" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Save</button>
        <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Preview</button>
        <button class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Share</button>
        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
      </div>
    </header>

    <main class="mx-auto w-[70%] mt-8 bg-black p-6 rounded shadow">
      <!-- Title and Description -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700">Form Title</label>
        <input v-model="form.title" placeholder="Form Title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />

        <label class="block mt-4 text-sm font-medium text-gray-700">Description</label>
        <textarea v-model="form.description" placeholder="Form Description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
      </div>

      <!-- Questions Section -->
      <div class="space-y-4">
        <FieldRenderer
          v-for="(field, index) in fields"
          :key="field.uuid"
          :field="field"
          :index="index"
          :total="fields.length"
          @copy=""
          @delete="handleDelete"
          @moveUp="moveFieldUp"
          @moveDown="moveFieldDown"
        />

        <!-- Add field button -->
        <button @click="addField" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
          Add Field
        </button>

      </div>
      <div v-if="showSuccess" class="success-message">
        {{ page.props.flash.success }}
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import FieldRenderer from '../../components/FieldRenderer.vue';

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
    field_order: fields.value.length + 1,
    uuid: crypto.randomUUID()
  });
}

function handleDelete(fieldToDelete) {
  const index = fields.value.findIndex(f => f.uuid === fieldToDelete.uuid);
  if (index !== -1) {
    fields.value.splice(index, 1);
  }
}

function moveFieldUp(field) {
  const index = fields.value.findIndex(f => f.uuid === field.uuid);
  if (index > 0) {
    const temp = fields.value[index - 1];
    fields.value[index - 1] = fields.value[index];
    fields.value[index] = temp;
    updateFieldOrder();
  }
}

function moveFieldDown(field) {
  const index = fields.value.findIndex(f => f.uuid === field.uuid);
  if (index < fields.value.length - 1) {
    const temp = fields.value[index + 1];
    fields.value[index + 1] = fields.value[index];
    fields.value[index] = temp;
    updateFieldOrder();
  }
}

function updateFieldOrder() {
  fields.value.forEach((f, i) => {
    f.field_order = i + 1;
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
