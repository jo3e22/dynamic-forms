import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import type { FormSettingsDTO } from '@/types/formSettings.d.ts';

export function useFormSettings(formCode: string) {
  const settings = ref<FormSettingsDTO | null>(null);
  const computedStatus = ref<string>('draft');
  const submissionsCount = ref<number>(0);
  const loading = ref(false);
  const error = ref<string | null>(null);

  async function fetchSettings() {
    loading.value = true;
    error.value = null;
    try {
      const res = await fetch(`/forms/${formCode}/settings/json`);
      if (!res.ok) throw new Error('Failed to fetch settings');
      const data = await res.json();
      settings.value = data.settings;
      computedStatus.value = data.computed_status;
      submissionsCount.value = data.submissions_count;
    } catch (e: any) {
      error.value = e.message;
    } finally {
      loading.value = false;
    }
  }

  async function saveSettings(payload?: Record<string, any>) {
    loading.value = true;
    error.value = null;

    const data = payload ?? settings.value;
    return new Promise<void>((resolve, reject) => {
      router.put(`/forms/${formCode}/settings`, data, {
        preserveScroll: true,
        onSuccess: () => {
          loading.value = false;
          fetchSettings(); // Re-fetch to get updated computed_status
          resolve();
        },
        onError: (errors) => {
          error.value = 'Failed to save settings';
          loading.value = false;
          reject(errors);
        },
        onFinish: () => {
          loading.value = false;
        },
      });
    });
  }

  return { settings, computedStatus, submissionsCount, loading, error, fetchSettings, saveSettings };
}