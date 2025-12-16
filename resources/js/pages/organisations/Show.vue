<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';
import { Building2, Users, FileText, Settings, Mail, Globe, Phone, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';

interface Organisation {
    id: number;
    name: string;
    slug: string;
    short_name?: string;
    type: string;
    visibility: string;
    description?: string;
    created_at: string;
    owner?: {
        id: number;
        name: string;
        email: string;
    };
    branding?: {
        primary_color?: string;
        logo_url?: string;
    };
    details?: {
        website?: string;
        email?: string;
        phone?: string;
        address?: string;
    };
}

interface Form {
    id: number;
    code: string;
    title: string;
    status: string;
    created_at: string;
}

interface Member {
    id: number;
    name: string;
    email: string;
    role: string;
    joined_at: string;
}

interface Props {
    organisation: Organisation;
    userRole: string;
    forms: Form[];
    members: Member[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Organisations', href: '/organisations' },
    { title: props.organisation.name, href: `/organisations/${props.organisation.slug}` },
];

const canEdit = computed(() => {
    return ['owner', 'admin'].includes(props.userRole);
});

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

function getRoleBadgeVariant(role: string) {
    switch (role) {
        case 'owner': return 'default';
        case 'admin': return 'secondary';
        case 'member': return 'outline';
        default: return 'outline';
    }
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems" :title="organisation.name">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div 
                            v-if="organisation.branding?.logo_url"
                            class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0"
                        >
                            <img :src="organisation.branding.logo_url" :alt="organisation.name" class="w-full h-full object-cover" />
                        </div>
                        <div 
                            v-else
                            class="w-16 h-16 rounded-lg flex items-center justify-center text-white font-bold text-2xl flex-shrink-0"
                            :style="{ backgroundColor: organisation.branding?.primary_color || '#3B82F6' }"
                        >
                            {{ organisation.short_name?.[0] || organisation.name[0] }}
                        </div>
                        
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">{{ organisation.name }}</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <Badge :variant="getVisibilityVariant(organisation.visibility)">
                                    {{ organisation.visibility }}
                                </Badge>
                                <Badge variant="outline">
                                    {{ getTypeLabel(organisation.type) }}
                                </Badge>
                                <Badge :variant="getRoleBadgeVariant(userRole)">
                                    {{ userRole }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <div v-if="canEdit" class="flex items-center gap-2">
                        <Button variant="outline" as-child>
                            <Link :href="`/organisations/${organisation.slug}/edit`">
                                <Settings class="mr-2 h-4 w-4" />
                                Settings
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Description -->
                <Card v-if="organisation.description">
                    <CardHeader>
                        <CardTitle>About</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-muted-foreground">{{ organisation.description }}</p>
                    </CardContent>
                </Card>

                <!-- Contact Details -->
                <Card v-if="organisation.details">
                    <CardHeader>
                        <CardTitle>Contact Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="organisation.details.website" class="flex items-center gap-2">
                            <Globe class="h-4 w-4 text-muted-foreground" />
                            <a :href="organisation.details.website" target="_blank" class="text-primary hover:underline">
                                {{ organisation.details.website }}
                            </a>
                        </div>
                        <div v-if="organisation.details.email" class="flex items-center gap-2">
                            <Mail class="h-4 w-4 text-muted-foreground" />
                            <a :href="`mailto:${organisation.details.email}`" class="text-primary hover:underline">
                                {{ organisation.details.email }}
                            </a>
                        </div>
                        <div v-if="organisation.details.phone" class="flex items-center gap-2">
                            <Phone class="h-4 w-4 text-muted-foreground" />
                            <span>{{ organisation.details.phone }}</span>
                        </div>
                        <div v-if="organisation.details.address" class="flex items-center gap-2">
                            <MapPin class="h-4 w-4 text-muted-foreground" />
                            <span>{{ organisation.details.address }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Stats -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Forms</CardTitle>
                            <FileText class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ forms.length }}</div>
                            <p class="text-xs text-muted-foreground">Organisation forms</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-sm font-medium">Members</CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ members.length }}</div>
                            <p class="text-xs text-muted-foreground">Active members</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Forms List -->
                <Card>
                    <CardHeader>
                        <CardTitle>Forms</CardTitle>
                        <CardDescription>Forms created by this organisation</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="forms.length > 0" class="space-y-2">
                            <div 
                                v-for="form in forms" 
                                :key="form.id"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-accent transition-colors"
                            >
                                <div>
                                    <h4 class="font-medium">{{ form.title }}</h4>
                                    <p class="text-sm text-muted-foreground">{{ form.code }}</p>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/forms/${form.code}`">View</Link>
                                </Button>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            No forms yet
                        </div>
                    </CardContent>
                </Card>

                <!-- Members List -->
                <Card>
                    <CardHeader>
                        <CardTitle>Members</CardTitle>
                        <CardDescription>People in this organisation</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="members.length > 0" class="space-y-2">
                            <div 
                                v-for="member in members" 
                                :key="member.id"
                                class="flex items-center justify-between p-3 border rounded-lg"
                            >
                                <div>
                                    <h4 class="font-medium">{{ member.name }}</h4>
                                    <p class="text-sm text-muted-foreground">{{ member.email }}</p>
                                </div>
                                <Badge :variant="getRoleBadgeVariant(member.role)">
                                    {{ member.role }}
                                </Badge>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-muted-foreground">
                            No members yet
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>