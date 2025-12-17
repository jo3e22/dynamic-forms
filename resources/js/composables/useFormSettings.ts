import { ref } from 'vue';
import type { FormSettingsDTO } from '@/types/formSettings.d.ts';

export function useFormSettings(formCode: string) {
  const settings = ref<FormSettingsDTO | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchSettings() {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(`/forms/${formCode}/settings`);
      if (!res.ok) throw new Error('Failed to fetch settings');
      settings.value = await res.json();
    } catch (e: any) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  }

  async function saveSettings(method: 'POST' | 'PUT' = 'PUT') {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(`/forms/${formCode}/settings`, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(settings.value),
      });
      if (!res.ok) throw new Error('Failed to save settings');
      settings.value = await res.json();
    } catch (e: any) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  }

  return { settings, loading, error, fetchSettings, saveSettings };
}