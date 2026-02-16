<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';
import { Mail, CheckCircle, AlertCircle, Clock } from 'lucide-vue-next';

interface EmailTemplate {
    id: number;
    event: string;
    name: string;
    enabled: boolean;
    is_default: boolean;
}

interface EmailLog {
    id: number;
    recipient_email: string;
    event: string;
    status: 'queued' | 'sent' | 'failed';
    sent_at?: string;
    form?: string;
    error_message?: string;
}

interface Props {
    templates: EmailTemplate[];
    logs: EmailLog[];
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
    { title: 'Email Templates', href: '/admin/email' },
];

function getStatusIcon(status: string) {
    switch (status) {
        case 'sent': return CheckCircle;
        case 'failed': return AlertCircle;
        case 'queued': return Clock;
        default: return Mail;
    }
}

function getStatusVariant(status: string) {
    switch (status) {
        case 'sent': return 'default';
        case 'failed': return 'destructive';
        case 'queued': return 'secondary';
        default: return 'outline';
    }
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems" title="Email Templates">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Email Templates</h1>
                <p class="text-muted-foreground mt-1">
                    Manage email templates for form submissions and notifications
                </p>
            </div>

            <!-- Templates -->
            <Card>
                <CardHeader>
                    <CardTitle>Templates</CardTitle>
                    <CardDescription>Customize emails sent to respondents and managers</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div 
                            v-for="template in templates" 
                            :key="template.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-accent transition-colors"
                        >
                            <div class="flex-1">
                                <h3 class="font-semibold">{{ template.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ template.event }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <Badge v-if="template.enabled" variant="default">Enabled</Badge>
                                    <Badge v-else variant="secondary">Disabled</Badge>
                                    <Badge v-if="template.is_default" variant="outline">Default</Badge>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="`/admin/email/${template.id}`">
                                    Edit
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Recent Email Logs -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Emails</CardTitle>
                    <CardDescription>Last 20 emails sent</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div 
                            v-for="log in logs" 
                            :key="log.id"
                            class="flex items-center justify-between p-3 border rounded-lg text-sm"
                        >
                            <div class="flex-1 min-w-0">
                                <p class="font-medium truncate">{{ log.recipient_email }}</p>
                                <p class="text-xs text-muted-foreground">{{ log.event }}</p>
                                <p v-if="log.form" class="text-xs text-muted-foreground">Form: {{ log.form }}</p>
                                <p v-if="log.error_message" class="text-xs text-destructive mt-1">{{ log.error_message }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <Badge :variant="getStatusVariant(log.status)">
                                    {{ log.status }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>