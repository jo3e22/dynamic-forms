<template>
  <div :style="{ backgroundColor: formColors.background }" class="min-h-screen pb-8">
    <header :class="`bg-${formColors.secondary} shadow-md px-6 py-4 flex items-center justify-between`">
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

    <main class="mx-auto w-[70%] mt-8 bg-gray-400 p-6 rounded shadow">
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
          :key="field.id"
          :field="field"
          :index="index"
          :total="fields.length"
          @copy="copyfield"
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
import { reactive } from 'vue';
import FieldRenderer from '../../components/FieldRenderer.vue';

const props = defineProps({
  form: Object,
  fields: Array
});

const form = ref({ ...props.form });
const fields = ref([...props.fields]);
const page = usePage();
const showSuccess = ref(false);

const formColors = reactive({
  primary: 'rgb(99, 102, 241)', // purple-500
  secondary: 'rgb(76, 81, 191)', // purple-600
  background: 'rgb(30, 65, 123)', // blue-500
  success: 'rgb(16, 185, 129)', // green-500
  danger: 'rgb(239, 68, 68)', // red-500
  warning: 'rgb(245, 158, 11)', // yellow-500
  info: 'rgb(96, 165, 250)', // blue-300
});

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
  });
}

function copyfield(field, index) {
  fields.value.push({
    label: field.label,
    type: field.type,
    required: field.required,
    field_order: 0,
  })
  const clone = fields.value[fields.value.length-1]
  //const index = fields.value.findIndex(f => f.id === field.id);
  var i = fields.value.length-1
  while (i > index+1) {
    fields.value[i] = fields.value[i-1];
    i-=1
  }
  fields.value[index+1] = clone  
}

function handleDelete(fieldToDelete, index) {
  //const index = fields.value.findIndex(f => f.id === fieldToDelete.id);
  if (index !== -1) {
    fields.value.splice(index, 1);
  }
}

function moveFieldUp(field, index) {
  //const index = fields.value.findIndex(f => f.id === field.id);
  if (index > 0) {
    const temp = fields.value[index - 1];
    fields.value[index - 1] = fields.value[index];
    fields.value[index] = temp;
  }
}

function moveFieldDown(field, index) {
  //const index = fields.value.findIndex(f => f.id === field.id);
  if (index < fields.value.length - 1) {
    const temp = fields.value[index + 1];
    fields.value[index + 1] = fields.value[index];
    fields.value[index] = temp;
  }
}

function updateFieldOrder() {
  fields.value.forEach((f, i) => {
    f.field_order = i + 1;
  });
}

function saveForm() {
  updateFieldOrder()
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
