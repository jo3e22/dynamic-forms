<script setup lang="ts">
import { ref } from 'vue';
import GDPRPrivacyPanel from '@/components/GDPR/GDPRPrivacyPanel.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

defineProps<{
    user?: any;
}>();
</script>

<template>
    <div class="max-w-4xl mx-auto py-10">
        <Tabs defaultValue="privacy" class="w-full">
            <TabsList class="grid w-full grid-cols-3">
                <TabsTrigger value="privacy">Privacy & Security</TabsTrigger>
                <TabsTrigger value="profile">Profile</TabsTrigger>
                <TabsTrigger value="notifications">Notifications</TabsTrigger>
            </TabsList>

            <!-- Privacy & GDPR Tab -->
            <TabsContent value="privacy" class="space-y-4">
                <GDPRPrivacyPanel />
            </TabsContent>

            <!-- Profile Tab -->
            <TabsContent value="profile">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-4">Profile Settings</h2>
                    <!-- Add profile editing here -->
                </div>
            </TabsContent>

            <!-- Notifications Tab -->
            <TabsContent value="notifications">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-4">Notification Preferences</h2>
                    <!-- Add notification preferences here -->
                </div>
            </TabsContent>
        </Tabs>
    </div>
</template>
