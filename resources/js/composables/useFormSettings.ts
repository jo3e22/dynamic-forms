import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
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
    return new Promise<void>((resolve, reject) => {
      router[method.toLowerCase()](
        `/forms/${formCode}/settings`,
        settings.value,
        {
          onSuccess: () => {
            loading.value = false;
            resolve();
          },
          onError: (errors) => {
            error.value = 'Failed to save settings';
            loading.value = false;
            reject(errors);
          },
          onFinish: () => {
            loading.value = false;
          }
        }
      );
    });
  }

  return { settings, loading, error, fetchSettings, saveSettings };
}