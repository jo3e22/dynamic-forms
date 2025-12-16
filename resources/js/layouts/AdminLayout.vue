<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ShieldAlert } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    title: 'Admin Panel',
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="title" />
        
        <!-- Admin Warning Banner -->
        <Alert variant="destructive" class="mb-6">
            <ShieldAlert class="h-4 w-4" />
            <AlertTitle>Administrator Mode</AlertTitle>
            <AlertDescription>
                You are viewing the admin panel. Changes made here affect all organisations and users.
            </AlertDescription>
        </Alert>

        <slot />
    </AppLayout>
</template>