<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Settings, Building2, Check, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User;
}

defineProps<Props>();

const page = usePage();

interface Organisation {
    id: number;
    name: string;
    slug: string;  // Make sure slug is included
    short_name?: string;
    branding?: {
        primary_color?: string;
        logo_url?: string;
    };
}

const organisations = computed(() => (page.props.organisations as Organisation[]) || []);
const currentOrganisation = computed(() => page.props.currentOrganisation as Organisation | null);

const handleLogout = () => {
    router.flushAll();
};

function switchOrganisation(org: Organisation) {
    router.post(`/organisations/${org.slug}/switch`, {}, {
        preserveState: false,
        preserveScroll: true,
    });
}

function clearOrganisation() {
    router.post('/organisations/clear', {}, {
        preserveState: false,
        preserveScroll: true,
    });
}
</script>

<template>
    <!-- User Profile (Always shown with email) -->
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    
    <DropdownMenuSeparator />
    
    <!-- Active Workspace Section -->
    <DropdownMenuLabel class="text-xs text-muted-foreground">Active Workspace</DropdownMenuLabel>
    
    <!-- Personal Workspace -->
    <DropdownMenuItem 
        @click="clearOrganisation"
        class="cursor-pointer"
        :class="!currentOrganisation ? 'bg-accent' : ''"
    >
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                {{ user.name[0] }}
            </div>
            <div class="flex flex-col min-w-0 flex-1">
                <span class="text-sm font-medium truncate">Personal</span>
                <span class="text-xs text-muted-foreground truncate">{{ user.email }}</span>
            </div>
            <Check v-if="!currentOrganisation" class="w-4 h-4 text-primary flex-shrink-0" />
        </div>
    </DropdownMenuItem>
    
    <!-- Organisation Workspaces -->
    <DropdownMenuItem 
        v-for="org in organisations"
        :key="org.id"
        @click="switchOrganisation(org)"
        class="cursor-pointer"
        :class="currentOrganisation?.id === org.id ? 'bg-accent' : ''"
    >
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <div 
                class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                :style="{ backgroundColor: org.branding?.primary_color || '#3B82F6' }"
            >
                {{ org.short_name?.[0] || org.name[0] }}
            </div>
            <div class="flex flex-col min-w-0 flex-1">
                <span class="text-sm font-medium truncate">{{ org.name }}</span>
                <span class="text-xs text-muted-foreground truncate">{{ user.email }}</span>
            </div>
            <Check v-if="currentOrganisation?.id === org.id" class="w-4 h-4 text-primary flex-shrink-0" />
        </div>
    </DropdownMenuItem>
    
    <DropdownMenuSeparator />
    
    <!-- Organisation Actions -->
    <DropdownMenuGroup v-if="user.is_admin">
        <DropdownMenuItem as-child>
            <Link href="/admin/organisations/create" class="cursor-pointer">
                <Plus class="mr-2 h-4 w-4" />
                Create Organisation
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem as-child>
            <Link href="/admin/organisations" class="cursor-pointer">
                <Building2 class="mr-2 h-4 w-4" />
                Admin Panel
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator v-if="user.is_admin" />
    
    <!-- Settings & Logout -->
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="edit()" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>