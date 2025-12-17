<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { AlertCircle, Send } from 'lucide-vue-next';

interface Template {
    id: number;
    key: string;
    event: string;
    name: string;
    subject: string;
    body: string;
    type: 'html' | 'markdown';
    enabled: boolean;
    is_default: boolean;
    variables?: Record<string, string>;
}

interface Props {
    template: Template;
    availableVariables: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Email Templates', href: '/admin/email' },
    { title: props.template.name, href: `/admin/email/${props.template.id}` },
];

const form = ref({
    name: props.template.name,
    subject: props.template.subject,
    body: props.template.body,
    type: props.template.type,
    enabled: props.template.enabled,
});

const testEmail = ref('');
const processing = ref(false);
const errors = ref<Record<string, string>>({});

function insertVariable(variable: string) {
    form.value.body += `{${variable}}`;
}

function submit() {
    processing.value = true;
    
    router.put(`/admin/email/${props.template.id}`, form.value, {
        onError: (errs) => {
            errors.value = errs;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}

function sendTest() {
    if (!testEmail.value) return;
    
    processing.value = true;
    
    router.post(`/admin/email/${props.template.id}/test`, { recipient_email: testEmail.value }, {
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems" :title="`Edit: ${template.name}`">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="max-w-4xl mx-auto w-full space-y-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ template.name }}</h1>
                    <p class="text-muted-foreground mt-1">{{ template.event }}</p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Template Content</CardTitle>
                        <CardDescription>Customize this email template</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div class="space-y-2">
                                <Label for="name">Template Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g. Submission Received"
                                    disabled
                                />
                            </div>

                            <!-- Subject -->
                            <div class="space-y-2">
                                <Label for="subject">Email Subject</Label>
                                <Input
                                    id="subject"
                                    v-model="form.subject"
                                    placeholder="e.g. Thank you for submitting {form_title}"
                                />
                                <p v-if="errors.subject" class="text-sm text-destructive">{{ errors.subject }}</p>
                            </div>

                            <!-- Type -->
                            <div class="space-y-2">
                                <Label for="type">Content Type</Label>
                                <Select v-model="form.type">
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="html">HTML</SelectItem>
                                        <SelectItem value="markdown">Markdown</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Body -->
                            <div class="space-y-2">
                                <Label for="body">Email Body</Label>
                                <Textarea
                                    id="body"
                                    v-model="form.body"
                                    placeholder="Email content..."
                                    rows="12"
                                />
                                <p v-if="errors.body" class="text-sm text-destructive">{{ errors.body }}</p>
                            </div>

                            <!-- Available Variables -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-semibold text-sm mb-3 flex items-center gap-2">
                                    <AlertCircle class="h-4 w-4" />
                                    Available Variables
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <button
                                        v-for="(description, variable) in availableVariables"
                                        :key="variable"
                                        type="button"
                                        @click="insertVariable(variable)"
                                        class="text-left text-sm p-2 hover:bg-blue-100 rounded border border-blue-100 transition"
                                    >
                                        <code class="font-mono">{{ '{' + variable + '}' }}</code>
                                        <p class="text-xs text-muted-foreground">{{ description }}</p>
                                    </button>
                                </div>
                            </div>

                            <!-- Enabled -->
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="enabled"
                                    v-model:checked="form.enabled"
                                />
                                <Label for="enabled" class="font-normal">Enable this template</Label>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-4">
                                <Button type="submit" :disabled="processing">
                                    {{ processing ? 'Saving...' : 'Save Template' }}
                                </Button>
                                <Button variant="outline" type="button" as-child>
                                    <a href="/admin/email">Cancel</a>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Test Email -->
                <Card>
                    <CardHeader>
                        <CardTitle>Send Test Email</CardTitle>
                        <CardDescription>Test this template by sending it to an email address</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="sendTest" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="test-email">Recipient Email</Label>
                                <div class="flex gap-2">
                                    <Input
                                        id="test-email"
                                        v-model="testEmail"
                                        type="email"
                                        placeholder="test@example.com"
                                    />
                                    <Button type="submit" :disabled="!testEmail || processing">
                                        <Send class="mr-2 h-4 w-4" />
                                        Send
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>