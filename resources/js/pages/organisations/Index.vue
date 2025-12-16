<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

interface Organisation {
    id: number;
    name: string;
    slug: string;
    short_name?: string;
    type: string;
    visibility: string;
    member_count?: number;
    form_count?: number;
    user_role?: string;
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
    { title: 'Organisations', href: '/organisations' },
];

function getRoleBadgeVariant(role?: string) {
    switch (role) {
        case 'owner': return 'default';
        case 'admin': return 'secondary';
        case 'editor': return 'outline';
        default: return 'outline';
    }
}

function getTypeLabel(type: string) {
    return type.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Organisations" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Organisations</h1>
                    <p class="text-muted-foreground mt-1">
                        Manage your organisations and collaborate with teams
                    </p>
                </div>
                <Button as-child>
                    <Link href="/organisations/create">
                        Create Organisation
                    </Link>
                </Button>
            </div>

            <!-- Organisations Grid -->
            <div v-if="organisations.length > 0" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card 
                    v-for="org in organisations" 
                    :key="org.id"
                    class="hover:shadow-md transition-shadow cursor-pointer"
                >
                    <Link :href="`/organisations/${org.id}`" class="block">
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <!-- Logo or Icon -->
                                    <div 
                                        v-if="org.branding?.logo_url"
                                        class="w-12 h-12 rounded-lg overflow-hidden"
                                    >
                                        <img :src="org.branding.logo_url" :alt="org.name" class="w-full h-full object-cover" />
                                    </div>
                                    <div 
                                        v-else
                                        class="w-12 h-12 rounded-lg flex items-center justify-center text-white font-bold text-lg"
                                        :style="{ backgroundColor: org.branding?.primary_color || '#3B82F6' }"
                                    >
                                        {{ org.short_name?.[0] || org.name[0] }}
                                    </div>
                                    
                                    <div>
                                        <CardTitle class="text-lg">{{ org.name }}</CardTitle>
                                        <CardDescription v-if="org.short_name">
                                            {{ org.short_name }}
                                        </CardDescription>
                                    </div>
                                </div>
                                
                                <Badge :variant="getRoleBadgeVariant(org.user_role)">
                                    {{ org.user_role }}
                                </Badge>
                            </div>
                        </CardHeader>
                        
                        <CardContent>
                            <div class="space-y-2 text-sm text-muted-foreground">
                                <div class="flex items-center justify-between">
                                    <span>Type:</span>
                                    <span class="font-medium text-foreground">{{ getTypeLabel(org.type) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Forms:</span>
                                    <span class="font-medium text-foreground">{{ org.form_count || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Members:</span>
                                    <span class="font-medium text-foreground">{{ org.member_count || 0 }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Link>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-else class="border-dashed">
                <CardContent class="flex flex-col items-center justify-center py-12">
                    <div class="text-center space-y-4">
                        <div class="text-5xl">🏢</div>
                        <div>
                            <h3 class="text-lg font-semibold">No organisations yet</h3>
                            <p class="text-muted-foreground mt-1">
                                Create your first organisation to start collaborating
                            </p>
                        </div>
                        <Button as-child>
                            <Link href="/organisations/create">
                                Create Organisation
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>