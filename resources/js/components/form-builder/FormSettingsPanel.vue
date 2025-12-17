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
  <form @submit.prevent="submit" v-if="settings">
    <div>
      <label>Sharing Type</label>
      <select v-model="settings.sharing_type">
        <option value="authenticated_only">Authenticated Only</option>
        <option value="guest_allowed">Guest Allowed</option>
        <option value="guest_email_required">Guest Email Required</option>
      </select>
    </div>
    <div>
      <label>
        <input type="checkbox" v-model="settings.allow_duplicate_responses" />
        Allow Duplicate Responses
      </label>
    </div>
    <div>
      <label>Confirmation Email</label>
      <select v-model="settings.confirmation_email">
        <option value="none">None</option>
        <option value="confirmation_only">Confirmation Only</option>
        <option value="linked_copy_of_responses">Linked Copy of Responses</option>
        <option value="detailed_copy_of_responses">Detailed Copy of Responses</option>
      </select>
    </div>
    <div>
      <label>Open At</label>
      <input type="datetime-local" v-model="settings.open_at" />
    </div>
    <div>
      <label>Close At</label>
      <input type="datetime-local" v-model="settings.close_at" />
    </div>
    <div>
      <label>Max Submissions</label>
      <input type="number" v-model="settings.max_submissions" min="1" />
    </div>
    <div>
      <label>
        <input type="checkbox" v-model="settings.allow_response_editing" />
        Allow Response Editing
      </label>
    </div>
    <div>
      <label>Confirmation Message</label>
      <textarea v-model="settings.confirmation_message" />
    </div>
    <button type="submit" :disabled="loading">Save Settings</button>
    <div v-if="error" class="text-red-500">{{ error }}</div>
  </form>
  <div v-else-if="loading">Loading...</div>
  <div v-else-if="error" class="text-red-500">{{ error }}</div>
</template>