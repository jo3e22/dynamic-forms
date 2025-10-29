<template>
  <div :style="{ backgroundColor: formColors.background }" class="min-h-screen pb-8">

  <!--:style="{ backgroundColor: formColors.background }"-->

    <header :style="{ backgroundColor: formColors.white }" class="shadow-md px-6 py-4 flex items-center justify-between sticky top-0 z-10">
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

    <main class="mx-auto w-[70%] mt-8 p-6">
      <!-- Title and Description -->
      <div :style="{ backgroundColor: formColors.white }" class="mb-6 rounded-md">
        <div :style="{ backgroundColor: formColors.primary }" class="h-2 w-full rounded-t-md"></div>
        <div class="p-4 space-y-4">
          <input
            v-model="form.title"
            placeholder="Form Title"
            class="mt-2 block w-full text-4xl border-b-1 focus:border-b-2 focus:outline-none"
            :style="{
              '--tw-border-opacity': '1',
              borderColor: 'transparent',
            }"
            @focus="(e) => e.target.style.borderColor = formColors.primary"
            @blur="(e) => e.target.style.borderColor = formColors.gray"
          />

          <textarea
            v-model="form.description"
            id="descriptionTextarea"
            placeholder="Form Description"
            class="mt-1 block w-full border-b-1 focus:border-b-2 focus:outline-none resize-none overflow-hidden"
            :style="{
              '--tw-border-opacity': '1',
              borderColor: 'transparent',
            }"
            @focus="(e) => e.target.style.borderColor = formColors.primary"
            @blur="(e) => e.target.style.borderColor = formColors.gray"
            @input="adjustTextareaHeight"
          />
        </div>
      </div>

      <!-- Questions Section -->
      <div class="space-y-4">
        <div v-for="(field, index) in fields"
        :key="index"
        @click="handleFocus(index)"
        tabindex="0"
        >
          <FieldRenderer
            v-if="selectedQuestionId === index"
            :field="field"
            :index="index"
            :submissionField="getFakeSubmissionField(field)"
            :total="fields.length"
            :formColors="formColors"
            @copy="copyfield"
            @delete="handleDelete"
            @moveUp="moveFieldUp"
            @moveDown="moveFieldDown"
          />

          <QuestionRenderer
            v-else
            :field="field"
            :index="index"
            :submissionField="getFakeSubmissionField(field)"
            :mode="'preview'"
            :form-colors="formColors"
            @click="handleFocus(index)"
          />

        </div>

        <div>
          <!-- Toggle Button -->
          <button id="toggle-buttons" @click="toggleButtons" class="toggle-button">
            <span id="toggle-icon">{{ toggleIcon }}</span>
          </button>

          <!-- Buttons Container -->
          <div
            id="buttons-container"
            v-show="buttonsVisible"
            class="grid grid-cols-3 gap-4 mt-4"
          >
            <button @click="addField_text" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
              Short text
            </button>
            <button @click="addField_textlong" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Long text
            </button>
            <button @click="addField_multiplechoice" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
              Multiple choice
            </button>
          </div>
        </div>


      </div>
      <div v-if="showSuccess" class="success-message">
        {{ page.props.flash.success }}
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, watch, reactive, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import FieldRenderer from '../../components/FieldRenderer.vue';
import QuestionRenderer from '@/components/QuestionRenderer.vue';

const props = defineProps({
  form: Object,
  fields: Array
});

const form = ref({ ...props.form });
const fields = ref([...props.fields]);
const page = usePage();
const showSuccess = ref(false);

const formColors = reactive({
  primary: 'rgb(120, 110, 127)', // purple-500
  secondary: 'rgb(255, 255, 255)', // purple-600
  background: 'rgb(240, 220, 255)', // blue-500
  white: 'rgb(255, 255, 255)', // white
  gray: 'rgb(243, 244, 246)', // gray-100
  success: 'rgb(16, 185, 129)', // green-500
  danger: 'rgb(239, 68, 68)', // red-500
  warning: 'rgb(245, 158, 11)', // yellow-500
  info: 'rgb(96, 165, 250)', // blue-300
});

//console.log(`${fields.value[1].type}`)

watch(() => page.props.flash?.success, (message) => {
  if (message) {
    showSuccess.value = true;
    setTimeout(() => showSuccess.value = false, 4000);
  }
});

const selectedQuestionId = ref(null);

function handleFocus(questionId) {
  selectedQuestionId.value = questionId;
}

function handleClickOutside(event) {
  // Check if the click is outside the currently selected question
  const clickedInside = event.target.closest('.p-4'); // Adjust the selector to match your question container
  if (!clickedInside) {
    selectedQuestionId.value = null; // Clear the focus
  }
}

// Add a global click listener to detect clicks outside the component
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

// Remove the global click listener when the component is destroyed
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

const getFakeSubmissionField = (field) => {
  return {
    id: null, // No real ID
    form_field_id: field.id,
    submission_id: null,
    answer: '', // Default empty answer
  };
};

const buttonsVisible = ref(false);
const toggleIcon = ref('+');

function toggleButtons() {
  buttonsVisible.value = !buttonsVisible.value;
  toggleIcon.value = buttonsVisible.value ? '×' : '+';
}

function addField_text() {
  fields.value.push({
    label: '',
    type: 'text',
    required: false,
    field_order: fields.value.length + 1,
  });
}

function addField_textlong() {
  fields.value.push({
    label: '',
    type: 'textarea',
    required: false,
    field_order: fields.value.length + 1,
  });
}

function addField_multiplechoice() {
  fields.value.push({
    label: '',
    type: 'multiplechoice',
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





const descriptionTextarea = ref(null);

function adjustTextareaHeight() {
  const textarea = document.getElementById('descriptionTextarea'); // Access the textarea using its id
  if (textarea) {
    textarea.style.height = 'auto'; // Reset height to auto to calculate the new height
    textarea.offsetHeight; // This line forces the browser to recalculate layout
    textarea.style.height = `${textarea.scrollHeight}px`; // Set the height to match the content
    console.log('Adjusted height:', textarea.style.height); // Debugging log
  }
}

// Adjust height on mount
onMounted(() => {
  adjustTextareaHeight();
});

</script>
