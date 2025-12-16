<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';
import { Building2, Users, FileText, Calendar } from 'lucide-vue-next';

interface Organisation {
    id: number;
    name: string;
    slug: string;
    short_name?: string;
    type: string;
    visibility: string;
    users_count?: number;
    forms_count?: number;
    created_at: string;
    owner?: {
        name: string;
        email: string;
    };
    branding?: {
        primary_color?: string;
        logo_url?: string;
    };
}

interface Props {
    organisations: Organisation[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin/organisations' },
    { title: 'Organisations', href: '/admin/organisations' },
];

function getTypeLabel(type: string) {
    return type.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}

function getVisibilityVariant(visibility: string) {
    switch (visibility) {
        case 'public': return 'default';
        case 'private': return 'secondary';
        case 'unlisted': return 'outline';
        default: return 'outline';
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems" title="Organisations - Admin">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Organisations Management</h1>
                    <p class="text-muted-foreground mt-1">
                        Create and manage all organisations
                    </p>
                </div>
                <Button as-child>
                    <Link href="/admin/organisations/create">
                        <Building2 class="mr-2 h-4 w-4" />
                        Create Organisation
                    </Link>
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Organisations</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ organisations.length }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Members</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ organisations.reduce((sum, org) => sum + (org.users_count || 0), 0) }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Forms</CardTitle>
                        <FileText class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ organisations.reduce((sum, org) => sum + (org.forms_count || 0), 0) }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Organisations Table -->
            <Card>
                <CardHeader>
                    <CardTitle>All Organisations</CardTitle>
                    <CardDescription>A complete list of all organisations in the system</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="organisations.length > 0" class="space-y-4">
                        <div 
                            v-for="org in organisations" 
                            :key="org.id"
                            class="flex items-center justify-between p-4 border rounded-lg hover:bg-accent transition-colors"
                        >
                            <div class="flex items-center gap-4 flex-1 min-w-0">
                                <!-- Logo/Avatar -->
                                <div 
                                    v-if="org.branding?.logo_url"
                                    class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0"
                                >
                                    <img :src="org.branding.logo_url" :alt="org.name" class="w-full h-full object-cover" />
                                </div>
                                <div 
                                    v-else
                                    class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                                    :style="{ backgroundColor: org.branding?.primary_color || '#3B82F6' }"
                                >
                                    {{ org.short_name?.[0] || org.name[0] }}
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold truncate">{{ org.name }}</h3>
                                        <Badge :variant="getVisibilityVariant(org.visibility)" class="text-xs">
                                            {{ org.visibility }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm text-muted-foreground mt-1">
                                        <span class="flex items-center gap-1">
                                            <Users class="h-3 w-3" />
                                            {{ org.users_count || 0 }} members
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <FileText class="h-3 w-3" />
                                            {{ org.forms_count || 0 }} forms
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <Calendar class="h-3 w-3" />
                                            {{ formatDate(org.created_at) }}
                                        </span>
                                    </div>
                                    <p v-if="org.owner" class="text-xs text-muted-foreground mt-1">
                                        Owner: {{ org.owner.name }} ({{ org.owner.email }})
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/organisations/${org.id}`">
                                        View
                                    </Link>
                                </Button>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/organisations/${org.id}/edit`">
                                        Edit
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-12">
                        <Building2 class="mx-auto h-12 w-12 text-muted-foreground" />
                        <h3 class="mt-4 text-lg font-semibold">No organisations yet</h3>
                        <p class="text-muted-foreground mt-1">Create your first organisation to get started</p>
                        <Button class="mt-4" as-child>
                            <Link href="/admin/organisations/create">
                                Create Organisation
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>
</template>