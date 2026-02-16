<script setup lang="ts">
import { watch, onMounted, computed } from 'vue';
import { useFormSettings } from '@/composables/useFormSettings';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

const props = defineProps<{ formCode: string }>();
const emit = defineEmits<{ (e: 'saved'): void }>();

const {
  settings, computedStatus, submissionsCount,
  loading, error, fetchSettings, saveSettings
} = useFormSettings(props.formCode);

onMounted(() => fetchSettings());
watch(() => props.formCode, (code) => { if (code) fetchSettings(); });

const statusBadgeClass = computed(() => {
  const map: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    scheduled: 'bg-blue-100 text-blue-700',
    open: 'bg-green-100 text-green-700',
    closed: 'bg-red-100 text-red-700',
  };
  return map[computedStatus.value] ?? map.draft;
});

function submit() {
  if (!settings.value) return;

  saveSettings({
    publish_mode: settings.value.publish_mode,
    is_published: !!settings.value.is_published,
    open_at: settings.value.open_at,
    close_at: settings.value.close_at,
    max_submissions: settings.value.max_submissions,
    sharing_type: settings.value.sharing_type,
    allow_duplicate_responses: !!settings.value.allow_duplicate_responses,
    allow_response_editing: !!settings.value.allow_response_editing,
    confirmation_email: settings.value.confirmation_email,
    confirmation_message: settings.value.confirmation_message,
  }).then(() => emit('saved'));
}

function togglePublished() {
  if (!settings.value) return;
  settings.value.is_published = !settings.value.is_published;
  submit();
}
</script>

<template>
  <div v-if="loading && !settings" class="flex justify-center py-8">
    <p class="text-muted-foreground">Loading settings...</p>
  </div>

  <div v-else-if="error && !settings" class="text-red-500 text-center py-8">{{ error }}</div>

  <div v-else-if="settings" class="space-y-4">
    <!-- Status Banner -->
    <div class="flex items-center justify-between px-1">
      <div class="flex items-center gap-3">
        <span class="text-sm font-medium text-muted-foreground">Current Status:</span>
        <Badge :class="statusBadgeClass" class="text-sm capitalize">
          {{ computedStatus }}
        </Badge>
      </div>

      <!-- Quick toggle for manual mode -->
      <Button
        v-if="settings.publish_mode === 'manual'"
        @click="togglePublished"
        :variant="settings.is_published ? 'destructive' : 'default'"
        size="sm"
      >
        {{ settings.is_published ? 'Close Form' : 'Open Form' }}
      </Button>
    </div>

    <Separator />

    <Tabs default-value="publishing" class="w-full">
      <TabsList class="grid w-full grid-cols-4">
        <TabsTrigger value="publishing">Publishing</TabsTrigger>
        <TabsTrigger value="sharing">Sharing</TabsTrigger>
        <TabsTrigger value="submissions">Submissions</TabsTrigger>
        <TabsTrigger value="confirmation">Confirmation</TabsTrigger>
      </TabsList>

      <!-- ═══ PUBLISHING TAB ═══ -->
      <TabsContent value="publishing">
        <Card>
          <CardHeader>
            <CardTitle>Publishing</CardTitle>
            <CardDescription>Control how and when your form accepts responses.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Publish Mode -->
            <div class="space-y-2">
              <Label>Publish Mode</Label>
              <div class="flex gap-3">
                <label
                  class="flex-1 flex items-center gap-3 rounded-lg border p-4 cursor-pointer transition-colors"
                  :class="settings.publish_mode === 'manual' ? 'border-primary bg-primary/5' : 'border-muted'"
                >
                  <input type="radio" v-model="settings.publish_mode" value="manual" class="accent-primary" />
                  <div>
                    <p class="font-medium">Manual</p>
                    <p class="text-sm text-muted-foreground">Toggle the form open/closed yourself.</p>
                  </div>
                </label>
                <label
                  class="flex-1 flex items-center gap-3 rounded-lg border p-4 cursor-pointer transition-colors"
                  :class="settings.publish_mode === 'scheduled' ? 'border-primary bg-primary/5' : 'border-muted'"
                >
                  <input type="radio" v-model="settings.publish_mode" value="scheduled" class="accent-primary" />
                  <div>
                    <p class="font-medium">Scheduled</p>
                    <p class="text-sm text-muted-foreground">Set open and close times.</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- Manual: Toggle -->
            <div v-if="settings.publish_mode === 'manual'" class="rounded-lg border p-4 space-y-3">
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium">Accept Responses</p>
                  <p class="text-sm text-muted-foreground">
                    {{ settings.is_published ? 'Your form is currently open.' : 'Your form is not accepting responses.' }}
                  </p>
                </div>
                <Button
                  @click="settings.is_published = !settings.is_published"
                  :variant="settings.is_published ? 'default' : 'outline'"
                  size="sm"
                >
                  {{ settings.is_published ? 'On' : 'Off' }}
                </Button>
              </div>
            </div>

            <!-- Scheduled: Date pickers -->
            <div v-if="settings.publish_mode === 'scheduled'" class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="open_at">Open At</Label>
                  <Input id="open_at" type="datetime-local" v-model="settings.open_at" />
                </div>
                <div class="space-y-2">
                  <Label for="close_at">Close At</Label>
                  <Input id="close_at" type="datetime-local" v-model="settings.close_at" />
                </div>
              </div>
              <p class="text-sm text-muted-foreground">
                Leave blank to have no start/end restriction.
              </p>
            </div>

            <!-- Max submissions (both modes) -->
            <div class="space-y-2">
              <Label for="max_submissions">Maximum Submissions</Label>
              <div class="flex items-center gap-3">
                <Input
                  id="max_submissions"
                  type="number"
                  v-model="settings.max_submissions"
                  min="1"
                  placeholder="Unlimited"
                  class="max-w-[200px]"
                />
                <span class="text-sm text-muted-foreground">
                  {{ submissionsCount }} received so far
                </span>
              </div>
              <p class="text-sm text-muted-foreground">
                Leave blank for unlimited. The form will close automatically when this limit is reached.
              </p>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- ═══ SHARING TAB ═══ -->
      <TabsContent value="sharing">
        <Card>
          <CardHeader>
            <CardTitle>Sharing & Access</CardTitle>
            <CardDescription>Control who can fill out your form.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <label
                v-for="option in [
                  { value: 'authenticated_only', label: 'Authenticated Only', desc: 'Respondents must be logged in.' },
                  { value: 'guest_allowed', label: 'Anyone (Guest)', desc: 'No login required. Fully anonymous.' },
                  { value: 'guest_email_required', label: 'Anyone (Email Required)', desc: 'No login required, but an email address must be provided.' },
                ]"
                :key="option.value"
                class="flex items-start gap-3 rounded-lg border p-4 cursor-pointer transition-colors"
                :class="settings.sharing_type === option.value ? 'border-primary bg-primary/5' : 'border-muted'"
              >
                <input
                  type="radio"
                  v-model="settings.sharing_type"
                  :value="option.value"
                  class="mt-1 accent-primary"
                />
                <div>
                  <p class="font-medium">{{ option.label }}</p>
                  <p class="text-sm text-muted-foreground">{{ option.desc }}</p>
                </div>
              </label>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- ═══ SUBMISSIONS TAB ═══ -->
      <TabsContent value="submissions">
        <Card>
          <CardHeader>
            <CardTitle>Submission Rules</CardTitle>
            <CardDescription>Control how responses behave.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">Allow Duplicate Responses</p>
                <p class="text-sm text-muted-foreground">Let the same person submit more than once.</p>
              </div>
              <Button
                @click="settings.allow_duplicate_responses = !settings.allow_duplicate_responses"
                :variant="settings.allow_duplicate_responses ? 'default' : 'outline'"
                size="sm"
              >
                {{ settings.allow_duplicate_responses ? 'On' : 'Off' }}
              </Button>
            </div>
            <Separator />
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">Allow Response Editing</p>
                <p class="text-sm text-muted-foreground">Let respondents edit their submission after submitting.</p>
              </div>
              <Button
                @click="settings.allow_response_editing = !settings.allow_response_editing"
                :variant="settings.allow_response_editing ? 'default' : 'outline'"
                size="sm"
              >
                {{ settings.allow_response_editing ? 'On' : 'Off' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- ═══ CONFIRMATION TAB ═══ -->
      <TabsContent value="confirmation">
        <Card>
          <CardHeader>
            <CardTitle>Confirmation</CardTitle>
            <CardDescription>What happens after someone submits.</CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="space-y-2">
              <Label>Confirmation Email</Label>
              <Select v-model="settings.confirmation_email">
                <SelectTrigger>
                  <SelectValue placeholder="Select option" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">None</SelectItem>
                  <SelectItem value="confirmation_only">Confirmation Only</SelectItem>
                  <SelectItem value="linked_copy_of_responses">Linked Copy of Responses</SelectItem>
                  <SelectItem value="detailed_copy_of_responses">Detailed Copy of Responses</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label for="confirmation_message">Confirmation Message</Label>
              <textarea
                id="confirmation_message"
                v-model="settings.confirmation_message"
                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm min-h-[100px]"
                placeholder="Thank you for your response!"
              />
              <p class="text-sm text-muted-foreground">
                Shown to respondents after they submit.
              </p>
            </div>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>

    <!-- Save Button -->
    <div class="flex items-center justify-between pt-2">
      <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
      <div v-else />
      <Button @click="submit" :disabled="loading">
        {{ loading ? 'Saving...' : 'Save Settings' }}
      </Button>
    </div>
  </div>
</template>