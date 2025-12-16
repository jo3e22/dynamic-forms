<script setup lang="ts">
import NavFooter from '@/components/layout/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { urlIsActive } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, FileText, ChevronRight, Bell } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLogo from './AppLogo.vue';
import { Badge } from '@/components/ui/badge';

const page = usePage();

const forms = computed(() => page.props.forms as any[] || []);
const unreadCount = computed(() => page.props.unreadNotificationsCount as number || 0);

// Persist the collapsible state in sessionStorage
const formsExpanded = ref(sessionStorage.getItem('forms-expanded') !== 'false');

watch(formsExpanded, (newValue) => {
    sessionStorage.setItem('forms-expanded', String(newValue));
});

const footerNavItems = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/forms">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Platform</SidebarGroupLabel>
                <SidebarMenu>
                    <!-- Notifications -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="urlIsActive('/notifications', page.url)"
                            :tooltip="'Notifications'"
                        >
                            <Link href="/notifications" class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Bell />
                                    <span>Notifications</span>
                                </div>
                                <Badge v-if="unreadCount > 0" variant="destructive" class="ml-auto">
                                    {{ unreadCount }}
                                </Badge>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Forms with collapsible sub-items -->
                    <Collapsible
                        v-model:open="formsExpanded"
                        as-child
                        class="group/collapsible"
                    >
                        <SidebarMenuItem>
                            <div class="flex items-center w-full">
                                <SidebarMenuButton
                                    as-child
                                    :is-active="urlIsActive('/forms', page.url)"
                                    :tooltip="'Forms'"
                                    class="flex-1"
                                >
                                    <Link href="/forms">
                                        <FileText />
                                        <span>Forms</span>
                                    </Link>
                                </SidebarMenuButton>
                                <CollapsibleTrigger as-child>
                                    <button
                                        class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground"
                                        @click.stop
                                    >
                                        <ChevronRight 
                                            class="h-4 w-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                        />
                                    </button>
                                </CollapsibleTrigger>
                            </div>
                            
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem v-for="form in forms" :key="form.id">
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="urlIsActive(`/forms/${form.code}`, page.url)"
                                        >
                                            <Link :href="`/forms/${form.code}`">
                                                <span class="truncate">{{ form.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>