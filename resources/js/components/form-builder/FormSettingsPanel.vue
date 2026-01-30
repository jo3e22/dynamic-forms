<script setup lang="ts">
import { ref } from 'vue';
import { useFormSettings } from '@/composables/useFormSettings';

const props = defineProps<{ formCode: string, initialSettings?: any }>();
const { settings, loading, error, fetchSettings, saveSettings } = useFormSettings(props.formCode);

// Use initial settings if provided
if (props.initialSettings) {
  settings.value = props.initialSettings;
} else {
  fetchSettings();
}

function submit() {
  saveSettings(settings.value?.id ? 'PUT' : 'POST');
}
</script>

<template>
  <h2 class="text-center text-xl font-semibold mb-4">Form Settings</h2>
  <form @submit.prevent="submit" v-if="settings">
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Sharing Type</label>
      <select v-model="settings.sharing_type" class="min-w-[180px]">
        <option value="authenticated_only">Authenticated Only</option>
        <option value="guest_allowed">Guest Allowed</option>
        <option value="guest_email_required">Guest Email Required</option>
      </select>
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Allow Duplicate Responses</label>
      <input type="checkbox" v-model="settings.allow_duplicate_responses" />
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Confirmation Email</label>
      <select v-model="settings.confirmation_email" class="min-w-[180px]">
        <option value="none">None</option>
        <option value="confirmation_only">Confirmation Only</option>
        <option value="linked_copy_of_responses">Linked Copy of Responses</option>
        <option value="detailed_copy_of_responses">Detailed Copy of Responses</option>
      </select>
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Open At</label>
      <input type="datetime-local" v-model="settings.open_at" class="min-w-[180px]" />
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Close At</label>
      <input type="datetime-local" v-model="settings.close_at" class="min-w-[180px]" />
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Max Submissions</label>
      <input type="number" v-model="settings.max_submissions" min="1" class="min-w-[100px]" />
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Allow Response Editing</label>
      <input type="checkbox" v-model="settings.allow_response_editing" />
    </div>
    <div class="flex items-center justify-between mb-4">
      <label class="mr-4">Confirmation Message</label>
      <textarea v-model="settings.confirmation_message" class="min-w-[180px]" />
    </div>
    <div class="text-center text-underline mb-4">
    <button type="submit" class="text-center " :disabled="loading">Save Settings</button>
    <div v-if="error" class="text-red-500">{{ error }}</div>
    </div>
  </form>
  <div v-else-if="loading">Loading...</div>
  <div v-else-if="error" class="text-red-500">{{ error }}</div>
</template>