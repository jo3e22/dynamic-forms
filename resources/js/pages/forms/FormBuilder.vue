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
        <button @click="tempPrint" class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600">Temp Print</button>
        <button @click="saveForm" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Save</button>
        <button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">Preview</button>
        <button class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Share</button>
        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
      </div>
    </header>

    <main class="mx-auto w-[70%] mt-8">
      <!-- Title and Description -->
      <div v-for="(section, sIdx) in sections" :key="section.id">


        <div
          class="selectable"
          :data-select-key="`section:${section.id}`"
          :class="isSelected(`section:${section.id ?? sIdx}`) ? 'shadow-lg' : ''"
          @click.stop="select(`section:${section.id}`)"
          tabindex="0"
          @keydown.enter.stop.prevent="select(`section:${section.id}`)"
        >
          <TitleSec
            :form="form"
            :section="section"
            :index="sIdx"
            :selected="isSelected(`section:${section.id}`)"
          />
        </div>

        <div class="space-y-4">
          <div v-for="(field, fIdx) in fields"
            class="selectable"
            :key="field.id ?? fIdx"
            :data-field-id="field.uuid"
            :data-select-key="`field:${field.id ?? fIdx}`"
            :class="isSelected(`field:${field.id ?? fIdx}`) ? 'shadow-lg' : ''"
            @click.stop="select(`field:${field.id ?? fIdx}`)"
            tabindex="0"
            @keydown.enter.stop.prevent="select(`field:${field.id ?? fIdx}`)"
          >

            <div class=" bg-white rounded shadow relative " :data-field-id="field.uuid">
              <div
                v-if="isSelected(fieldKey(field, fIdx))"
                class="bg-green-500 absolute h-full w-2 rounded-l-md">
              </div>
              <div class="p-4 space-y-4">
                <Toolbar
                  v-if="isSelected(fieldKey(field, fIdx))"
                  :field="field"
                  :index="fIdx"
                  :total="fields.length"
                  @copy="copyfield"
                  @delete="handleDelete"
                  @moveUp="moveFieldUp"
                  @moveDown="moveFieldDown"
                />

                <!-- Question + Answer -->
                <div class="flex-1 space-y-3">
                  <!-- Question Label -->
                  <input
                    v-model="field.label"
                    :style="{backgroundColor: formColors.white }"
                    class="w-full px-3 py-2"
                    :class="'focus:outline-none focus:border-b-2 focus:border-blue-500'"
                    :placeholder="field.label || 'question'"
                  />

                  <!-- Answer Input -->
                  <component
                    :is="getComponent(field.type)"
                    :field="field"
                    :submissionField="getFakeSubmissionField(field)"
                    :mode="'edit'"
                  />
                </div>

                <Optionsbar
                  v-if="isSelected(fieldKey(field, fIdx))"
                  :field="field"
                  :index="fIdx"
                  :total="fields.length"
                />
              </div>
            </div>

          </div>

          <div>
            
            <button id="toggle-buttons" @click="toggleButtons" class="toggle-button">
              <span id="toggle-icon">{{ toggleIcon }}</span>
            </button>

            
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


      </div>

        <!--
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
        -->

      <!-- Questions Section -->

      <!--
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
          
          <button id="toggle-buttons" @click="toggleButtons" class="toggle-button">
            <span id="toggle-icon">{{ toggleIcon }}</span>
          </button>

          
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
      -->
      <div v-if="showSuccess" class="success-message">
        {{ page.props.flash.success }}
      </div>
    </main>
  </div>
</template>

<script lang=ts setup>
import { ref, watch, reactive, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import TitleSec from '../../components/editfields/TitleSec.vue';
import Toolbar from '../../components/editfields/Toolbar.vue';
import Optionsbar from '../../components/editfields/Optionsbar.vue';
import FieldRenderer from '../../components/FieldRenderer.vue';
import QuestionRenderer from '@/components/QuestionRenderer.vue';
import TextQuestion from '../../components/questions/TextInput.vue';
import TextareaQuestion from '../../components/questions/TextareaInput.vue';
import MultipleChoiceQuestion from '../../components/editfields/MultipleChoiceQuestion.vue';

const props = defineProps({
  form: Object,
  sections: Array,
  fields: Array
});

const form = ref({ ...props.form });
const sections = ref([...props.sections]);
const fields = ref([...props.fields]);
const page = usePage();
const showSuccess = ref(false);

form.value.primary_color = form.value.primary_color || 'rgb(120, 110, 127)'; // default purple-500
form.value.secondary_color = form.value.secondary_color || 'rgb(255, 255, 255)'; // default purple-600

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


const selectedKey = ref<string | null>(null);
const select = (key: string) => { selectedKey.value = key; };
const isSelected = (key: string) => selectedKey.value === key;
const fieldKey = (field, idx) => `field:${field.id ?? idx}`;
const sectionKey = (section) => `section:${section.id}`;




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

// Map field types to components
const componentMap = {
  text: TextQuestion,
  textarea: TextareaQuestion,
  multiplechoice: MultipleChoiceQuestion,
  // Add more mappings for other field types
};

const getComponent = (type) => componentMap[type] || null;

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

function tempPrint() {
  console.log("form: ", form.value)
  console.log("sections: ", sections.value)
  console.log("fields: ", fields.value)
}




</script>
