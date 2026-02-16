<script setup lang="ts">
import FormSettingsPanel from '@/components/form-builder/FormSettingsPanel.vue';

const props = defineProps<{
  form: any,
  settings: any
}>();
</script>

<template>
  <div>
    <h1>Form Settings</h1>
    <FormSettingsPanel :formCode="form.code" :initialSettings="form.settings || {}" />
  </div>
</template>