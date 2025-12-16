<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/organisations' },
    { title: 'Organisations', href: '/admin/organisations' },
    { title: 'Create', href: '/admin/organisations/create' },
];

const form = ref({
    name: '',
    slug: '',
    short_name: '',
    type: 'club',
    visibility: 'private',
    description: '',
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});

function generateSlug() {
    form.value.slug = form.value.name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function submit() {
    processing.value = true;
    
    router.post('/admin/organisations', form.value, {
        onSuccess: () => {
            // Redirect happens automatically
        },
        onError: (errs) => {
            errors.value = errs;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems" title="Create Organisation">
        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Create Organisation</h1>
                <p class="text-muted-foreground mt-1">
                    Set up a new organisation for teams to collaborate
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Organisation Details</CardTitle>
                    <CardDescription>Basic information about the organisation</CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="name">Organisation Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                @blur="generateSlug"
                                placeholder="e.g. BULSCA"
                                required
                            />
                            <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <Label for="slug">URL Slug</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                placeholder="bulsca"
                            />
                            <p class="text-xs text-muted-foreground">
                                Used in URLs. Leave blank to auto-generate from name.
                            </p>
                            <p v-if="errors.slug" class="text-sm text-destructive">{{ errors.slug }}</p>
                        </div>

                        <!-- Short Name -->
                        <div class="space-y-2">
                            <Label for="short_name">Short Name/Acronym</Label>
                            <Input
                                id="short_name"
                                v-model="form.short_name"
                                placeholder="e.g. BULSCA"
                                maxlength="100"
                            />
                            <p class="text-xs text-muted-foreground">
                                Optional abbreviation (shown in avatars and tight spaces)
                            </p>
                        </div>

                        <!-- Type -->
                        <div class="space-y-2">
                            <Label for="type">Organisation Type *</Label>
                            <Select v-model="form.type">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="club">Club</SelectItem>
                                    <SelectItem value="university">University</SelectItem>
                                    <SelectItem value="regional_body">Regional Body</SelectItem>
                                    <SelectItem value="national_body">National Body</SelectItem>
                                    <SelectItem value="company">Company</SelectItem>
                                    <SelectItem value="non_profit">Non-Profit</SelectItem>
                                    <SelectItem value="other">Other</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.type" class="text-sm text-destructive">{{ errors.type }}</p>
                        </div>

                        <!-- Visibility -->
                        <div class="space-y-2">
                            <Label for="visibility">Visibility *</Label>
                            <Select v-model="form.visibility">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select visibility" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="private">Private (Members only)</SelectItem>
                                    <SelectItem value="unlisted">Unlisted (Anyone with link)</SelectItem>
                                    <SelectItem value="public">Public (Discoverable)</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="errors.visibility" class="text-sm text-destructive">{{ errors.visibility }}</p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Brief description of the organisation..."
                                rows="4"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-4">
                            <Button type="submit" :disabled="processing">
                                {{ processing ? 'Creating...' : 'Create Organisation' }}
                            </Button>
                            <Button variant="outline" type="button" as-child>
                                <a href="/admin/organisations">Cancel</a>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>