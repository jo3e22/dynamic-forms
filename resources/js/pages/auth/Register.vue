<script setup lang="ts">
import { ref } from 'vue';
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/common/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

const gdprConsent = ref(false);
const marketingConsent = ref(false);
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="RegisteredUserController.store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <!-- GDPR Consent -->
                <div class="space-y-3 pt-2 border-t">
                    <div class="flex items-start space-x-3">
                        <Checkbox
                            id="gdpr-consent"
                            v-model="gdprConsent"
                            class="mt-1"
                        />
                        <div class="space-y-1 flex-1">
                            <Label 
                                for="gdpr-consent" 
                                class="text-sm font-medium cursor-pointer"
                            >
                                I consent to data processing *
                            </Label>
                            <p class="text-xs text-gray-600">
                                I agree to the processing of my personal data according to the privacy policy
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <Checkbox
                            id="marketing-consent"
                            v-model="marketingConsent"
                            class="mt-1"
                        />
                        <div class="space-y-1 flex-1">
                            <Label 
                                for="marketing-consent" 
                                class="text-sm font-medium cursor-pointer"
                            >
                                Send me product updates
                            </Label>
                            <p class="text-xs text-gray-600">
                                I'd like to receive emails about new features and product updates
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="gdpr_consent" :value="gdprConsent ? '1' : '0'" />
                <input type="hidden" name="marketing_consent" :value="marketingConsent ? '1' : '0'" />

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="!gdprConsent || processing"
                    data-test="register-user-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
