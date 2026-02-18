<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { AlertCircle, Download, Trash2, Clock } from 'lucide-vue-next';

interface UserData {
    user: {
        id: number;
        name: string;
        email: string;
        created_at: string;
    };
    forms: Array<{
        id: number;
        title: string;
        submissions_count: number;
    }>;
    gdpr: {
        consent_gdpr_at: string | null;
        consent_marketing_at: string | null;
        preferences: Record<string, any> | null;
    };
}

const isLoading = ref(false);
const isDeleting = ref(false);
const userData = ref<UserData | null>(null);
const deleteConfirmed = ref(false);

onMounted(async () => {
    await fetchUserData();
});

const fetchUserData = async () => {
    isLoading.value = true;
    try {
        const response = await fetch('/api/profile/gdpr/data', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        if (response.ok) {
            userData.value = await response.json();
        }
    } finally {
        isLoading.value = false;
    }
};

const exportData = async () => {
    isLoading.value = true;
    try {
        const response = await fetch('/api/profile/gdpr/export', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `personal-data-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }
    } finally {
        isLoading.value = false;
    }
};

const requestDeletion = async () => {
    if (!deleteConfirmed.value) {
        alert('Please confirm you understand the consequences of account deletion');
        return;
    }

    if (!confirm('Are you absolutely sure? This action cannot be undone. All your forms, submissions, and data will be permanently deleted.')) {
        return;
    }

    isDeleting.value = true;
    try {
        const response = await fetch('/api/profile/gdpr/delete', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reason: 'user_requested',
            }),
        });

        if (response.ok) {
            // Redirect to goodbye page or home
            window.location.href = '/';
        }
    } finally {
        isDeleting.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Privacy Overview -->
        <Card>
            <CardHeader>
                <CardTitle>Your Privacy & Data</CardTitle>
                <CardDescription>
                    Manage your personal information and exercise your GDPR rights
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <Alert class="border-blue-200 bg-blue-50">
                    <AlertCircle class="h-4 w-4 text-blue-600" />
                    <AlertDescription class="text-sm">
                        We take your privacy seriously. You have full control over your data and can 
                        exercise your rights at any time.
                    </AlertDescription>
                </Alert>
            </CardContent>
        </Card>

        <!-- Data Access & Export -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Download class="h-5 w-5" />
                    Your Data
                </CardTitle>
                <CardDescription>
                    GDPR Article 15: Right of Access
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div>
                    <h4 class="font-medium mb-2">Account Information</h4>
                    <div class="space-y-1 text-sm text-gray-600" v-if="userData">
                        <p><strong>Name:</strong> {{ userData.user.name }}</p>
                        <p><strong>Email:</strong> {{ userData.user.email }}</p>
                        <p><strong>Account Created:</strong> {{ new Date(userData.user.created_at).toLocaleDateString() }}</p>
                        <p><strong>Forms:</strong> {{ userData.forms.length }}</p>
                        <p><strong>Total Submissions:</strong> {{ userData.forms.reduce((sum, f) => sum + f.submissions_count, 0) }}</p>
                    </div>
                </div>

                <div>
                    <h4 class="font-medium mb-2">Export Your Data</h4>
                    <p class="text-sm text-gray-600 mb-3">
                        Download a JSON file containing all your personal data, forms, and submissions. 
                        You can use this to back up your data or transfer it to another service.
                    </p>
                    <Button 
                        variant="outline" 
                        :disabled="isLoading"
                        @click="exportData"
                    >
                        <Download class="h-4 w-4 mr-2" />
                        {{ isLoading ? 'Exporting...' : 'Download My Data' }}
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Consent History -->
        <Card v-if="userData?.gdpr">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Clock class="h-5 w-5" />
                    Consent History
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
                <div class="p-3 rounded-lg border">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">GDPR Data Processing</p>
                            <p class="text-xs text-gray-600" v-if="userData.gdpr.consent_gdpr_at">
                                Given on {{ new Date(userData.gdpr.consent_gdpr_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <div v-if="userData.gdpr.consent_gdpr_at" class="text-green-600 text-sm">✓ Consented</div>
                    </div>
                </div>

                <div class="p-3 rounded-lg border">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">Marketing Emails</p>
                            <p class="text-xs text-gray-600" v-if="userData.gdpr.consent_marketing_at">
                                Given on {{ new Date(userData.gdpr.consent_marketing_at).toLocaleDateString() }}
                            </p>
                            <p class="text-xs text-gray-500" v-else>Not consented</p>
                        </div>
                        <div v-if="userData.gdpr.consent_marketing_at" class="text-green-600 text-sm">✓ Consented</div>
                        <div v-else class="text-gray-600 text-sm">✗ Not consented</div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Account Deletion -->
        <Card class="border-red-200 bg-red-50">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-red-700">
                    <Trash2 class="h-5 w-5" />
                    Delete Account
                </CardTitle>
                <CardDescription>
                    GDPR Article 17: Right to Erasure (Right to be Forgotten)
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <Alert class="border-red-200 bg-white">
                    <AlertCircle class="h-4 w-4 text-red-600" />
                    <AlertDescription class="text-sm text-red-800">
                        <strong>Warning:</strong> Deleting your account is permanent and cannot be undone. 
                        All your forms, submissions, and data will be permanently removed.
                    </AlertDescription>
                </Alert>

                <div class="space-y-3">
                    <label class="flex items-start space-x-2 p-3 rounded-lg border border-red-200">
                        <input
                            type="checkbox"
                            v-model="deleteConfirmed"
                            class="mt-1"
                        />
                        <span class="text-sm">
                            I understand that this action is permanent and will delete all my data
                        </span>
                    </label>

                    <Button
                        variant="destructive"
                        :disabled="!deleteConfirmed || isDeleting"
                        class="w-full"
                        @click="requestDeletion"
                    >
                        <Trash2 class="h-4 w-4 mr-2" />
                        {{ isDeleting ? 'Deleting Account...' : 'Delete My Account' }}
                    </Button>
                </div>

                <p class="text-xs text-gray-600 mt-4">
                    <strong>What happens next:</strong> We'll immediately mark your account for deletion. 
                    Your data will be permanently removed within 30 days. If you change your mind within 30 days, 
                    contact us immediately at 
                    <a href="mailto:privacy@example.com" class="text-blue-600 hover:underline">privacy@example.com</a>
                </p>
            </CardContent>
        </Card>
    </div>
</template>
