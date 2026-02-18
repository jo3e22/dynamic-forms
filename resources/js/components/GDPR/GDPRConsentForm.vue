<script setup lang="ts">
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { AlertCircle, CheckCircle2 } from 'lucide-vue-next';

interface Props {
    /**
     * Show in standalone mode (as a dialog)
     * vs embedded in a form
     */
    standalone?: boolean;
    
    /**
     * Callback when consent is recorded
     */
    onConsent?: (consented: boolean) => void;
}

defineProps<Props>();

const page = usePage();
const isLoading = ref(false);
const gdprConsentGiven = ref(false);
const marketingConsentGiven = ref(false);

const handleConsentSubmit = async () => {
    isLoading.value = true;
    
    try {
        // Submit consent to backend
        await fetch('/api/gdpr/consent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                gdpr_consent: gdprConsentGiven.value,
                marketing_consent: marketingConsentGiven.value,
            }),
        });
        
        // Redirect or callback
        if (page.props.auth?.user) {
            router.visit('/forms');
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="space-y-4">
        <!-- GDPR Notice -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <AlertCircle class="h-5 w-5 text-blue-600" />
                    Data Protection & Privacy
                </CardTitle>
                <CardDescription>
                    Please review and consent to our data processing practices
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <!-- GDPR Consent Section -->
                <Alert class="border-blue-200 bg-blue-50">
                    <AlertDescription class="text-sm text-gray-700">
                        <strong>Required for Account:</strong> We process your personal data to provide our service, 
                        including form creation, submission handling, and customer support, as necessary for 
                        our contract with you under GDPR Article 6(1)(b).
                    </AlertDescription>
                </Alert>

                <div class="flex items-start space-x-3 p-3 rounded-lg border border-blue-200 bg-blue-50/50">
                    <Checkbox
                        id="gdpr-consent"
                        v-model:checked="gdprConsentGiven"
                        class="mt-1"
                    />
                    <div class="space-y-1">
                        <Label 
                            for="gdpr-consent" 
                            class="text-sm font-medium cursor-pointer"
                        >
                            I consent to the processing of my personal data
                        </Label>
                        <p class="text-xs text-gray-600">
                            We process your data according to GDPR Article 5 (lawfulness, fairness, transparency, 
                            data minimization, storage limitation). You have rights to access, rectify, or delete 
                            your data. 
                            <a href="/privacy-policy#gdpr-rights" class="text-blue-600 hover:underline">
                                Learn more about your rights
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Marketing Consent Section -->
                <Alert class="border-purple-200 bg-purple-50">
                    <AlertDescription class="text-sm text-gray-700">
                        <strong>Optional:</strong> We'd like to send you emails about new features, 
                        tips, and updates. This is optional and requires your explicit consent under 
                        GDPR Article 7.
                    </AlertDescription>
                </Alert>

                <div class="flex items-start space-x-3 p-3 rounded-lg border border-purple-200 bg-purple-50/50">
                    <Checkbox
                        id="marketing-consent"
                        v-model:checked="marketingConsentGiven"
                        class="mt-1"
                    />
                    <div class="space-y-1">
                        <Label 
                            for="marketing-consent" 
                            class="text-sm font-medium cursor-pointer"
                        >
                            Send me marketing emails
                        </Label>
                        <p class="text-xs text-gray-600">
                            We'll send occasional emails about features, updates, and special offers. 
                            You can unsubscribe anytime from email preferences.
                        </p>
                    </div>
                </div>

                <!-- Data Rights Notice -->
                <div class="p-3 rounded-lg border border-gray-200 bg-gray-50">
                    <p class="text-xs text-gray-700 space-y-2">
                        <strong>Your GDPR Rights:</strong>
                        <ul class="list-disc pl-5 space-y-1 mt-1">
                            <li><strong>Right to Access:</strong> Request a copy of your data</li>
                            <li><strong>Right to Rectification:</strong> Correct inaccurate data</li>
                            <li><strong>Right to Erasure:</strong> Delete your account and data ("right to be forgotten")</li>
                            <li><strong>Right to Restrict Processing:</strong> Limit how we use your data</li>
                            <li><strong>Data Portability:</strong> Export your data in a portable format</li>
                        </ul>
                    </p>
                    <p class="text-xs text-gray-600 mt-2">
                        To exercise these rights, contact us at 
                        <a href="mailto:privacy@example.com" class="text-blue-600 hover:underline">
                            privacy@example.com
                        </a>
                    </p>
                </div>

                <!-- Submission Button -->
                <Button
                    type="button"
                    :disabled="!gdprConsentGiven || isLoading"
                    :class="{ 'opacity-50 cursor-not-allowed': !gdprConsentGiven }"
                    class="w-full"
                    @click="handleConsentSubmit"
                >
                    <CheckCircle2 v-if="gdprConsentGiven" class="h-4 w-4 mr-2" />
                    {{ isLoading ? 'Processing...' : 'I Agree & Continue' }}
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
