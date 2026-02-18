<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import HeadingSmall from '@/components/common/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import GDPRPrivacyPanel from '@/components/GDPR/GDPRPrivacyPanel.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Privacy & Security',
        href: '#',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Privacy & Security settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    title="Privacy & Security"
                    description="Manage your data, export your information, and delete your account"
                />

                <GDPRPrivacyPanel />

                <!-- Privacy Policy Link -->
                <Card>
                    <CardHeader>
                        <CardTitle>Privacy Policy</CardTitle>
                        <CardDescription>
                            Learn more about how we handle your data
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-gray-600 mb-4">
                            Our comprehensive privacy policy explains how we collect, use, and protect your personal data in compliance with GDPR.
                        </p>
                        <Link href="/privacy-policy" class="inline">
                            <Button variant="outline">
                                Read Privacy Policy
                            </Button>
                        </Link>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
