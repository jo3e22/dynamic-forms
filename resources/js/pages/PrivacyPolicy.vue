<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Privacy Policy" />
    
    <div class="min-h-screen bg-white">
        <!-- Navigation -->
        <nav class="border-b sticky top-0 z-50 bg-white">
            <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3 hover:opacity-80">
                    <div class="w-8 h-8 bg-primary rounded-md flex items-center justify-center">
                        <AppLogoIcon class="w-5 h-5 text-white" checkColor="var(--secondary)" />
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-bold">Dynamic Forms</span>
                    </div>
                </Link>
            </div>
        </nav>

        <div class="max-w-4xl mx-auto px-6 py-12">
            <h1 class="text-4xl font-bold mb-2">Privacy Policy</h1>
            <p class="text-gray-600 mb-8">Last updated: February 18, 2026</p>

            <!-- Table of Contents -->
            <div class="bg-gray-50 p-6 rounded-lg mb-8">
                <h2 class="font-semibold mb-4">Contents</h2>
                <ul class="space-y-2 text-sm">
                    <li><a href="#introduction" class="text-blue-600 hover:underline">1. Introduction</a></li>
                    <li><a href="#data-processing" class="text-blue-600 hover:underline">2. Data Processing & GDPR</a></li>
                    <li><a href="#gdpr-rights" class="text-blue-600 hover:underline">3. Your GDPR Rights</a></li>
                    <li><a href="#data-collection" class="text-blue-600 hover:underline">4. What Data We Collect</a></li>
                    <li><a href="#deletion" class="text-blue-600 hover:underline">5. Account Deletion</a></li>
                    <li><a href="#contact" class="text-blue-600 hover:underline">6. Contact Us</a></li>
                </ul>
            </div>

            <!-- Content Sections -->
            <div class="space-y-8">
                <!-- Introduction -->
                <section id="introduction">
                    <h2 class="text-2xl font-semibold mb-4">1. Introduction</h2>
                    <p class="text-gray-700 mb-4">
                        Welcome to Dynamic Forms ("we", "us", "our", or "Company"). We respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website and services.
                    </p>
                    <p class="text-gray-700">
                        As a European-based company, we comply with the General Data Protection Regulation (GDPR) and treat all user data with the highest standards of protection.
                    </p>
                </section>

                <!-- Data Processing & GDPR -->
                <section id="data-processing" class="border-t pt-8">
                    <h2 class="text-2xl font-semibold mb-4">2. Data Processing & GDPR</h2>
                    <h3 class="text-lg font-semibold mb-2">Lawful Basis for Processing</h3>
                    <p class="text-gray-700 mb-4">
                        We process your personal data under the following lawful bases as defined in GDPR Article 6:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 mb-4">
                        <li><strong>Contract (Article 6(1)(b)):</strong> We process your data to provide and maintain our services, including form creation, submission handling, and customer support.</li>
                        <li><strong>Consent (Article 6(1)(a)):</strong> We process marketing data and analytics only with your explicit consent, which you can withdraw at any time.</li>
                        <li><strong>Legal Obligation (Article 6(1)(c)):</strong> We may process data to comply with legal obligations, such as fraud prevention and security.</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-2">Data Retention</h3>
                    <p class="text-gray-700 mb-4">
                        We retain your personal data only for as long as necessary to provide our services:
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 mb-4">
                        <li><strong>Active Accounts:</strong> Your account data is retained while your account is active.</li>
                        <li><strong>Deleted Accounts:</strong> After account deletion, your data is permanently removed within 30 days.</li>
                        <li><strong>Form Submissions:</strong> Submissions are retained for 1 year by default, then automatically deleted (configurable per form).</li>
                        <li><strong>Audit Logs:</strong> GDPR compliance logs are retained for 7 years for legal compliance.</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-2">Data Protection Measures</h3>
                    <p class="text-gray-700 mb-4">
                        We implement security measures to protect your data (GDPR Article 32):
                    </p>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700">
                        <li>All data transmitted via HTTPS/TLS encryption</li>
                        <li>Database encryption at rest</li>
                        <li>Regular security audits and penetration testing</li>
                        <li>Strict access controls and authentication</li>
                        <li>Immutable audit logs of all data access</li>
                    </ul>
                </section>

                <!-- GDPR Rights -->
                <section id="gdpr-rights" class="border-t pt-8">
                    <h2 class="text-2xl font-semibold mb-4">3. Your GDPR Rights</h2>
                    <p class="text-gray-700 mb-4">
                        Under GDPR, you have the following rights regarding your personal data:
                    </p>

                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-2">Right of Access (Article 15)</h4>
                            <p class="text-blue-900 text-sm">
                                You have the right to access your personal data. You can download all your data in JSON format from your account settings at any time.
                            </p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-2">Right to Rectification (Article 16)</h4>
                            <p class="text-blue-900 text-sm">
                                You can correct or update your personal information through your account settings.
                            </p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-2">Right to Erasure (Article 17) - "Right to be Forgotten"</h4>
                            <p class="text-blue-900 text-sm mb-2">
                                You can request permanent deletion of your account and all associated data. After your request:
                            </p>
                            <ul class="list-disc pl-5 text-blue-900 text-sm space-y-1">
                                <li>Your account is marked for deletion immediately</li>
                                <li>All personal data is removed within 30 days</li>
                                <li>You cannot undo this after 30 days</li>
                                <li>Contact us immediately if you change your mind within the 30-day period</li>
                            </ul>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-2">Right to Restrict Processing (Article 18)</h4>
                            <p class="text-blue-900 text-sm">
                                You can request that we limit how we use your data while we investigate disputes.
                            </p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-2">Right to Data Portability (Article 20)</h4>
                            <p class="text-blue-900 text-sm">
                                You can request your data in a portable, machine-readable format (JSON) and transfer it to another service.
                            </p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                            <h4 class="font-semibold text-blue-900 mb-2">Right to Object (Article 21)</h4>
                            <p class="text-blue-900 text-sm">
                                You can object to marketing communications and certain data processing. You can manage preferences in account settings.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <h4 class="font-semibold text-yellow-900 mb-2">How to Exercise Your Rights</h4>
                        <p class="text-yellow-900 text-sm mb-2">
                            To exercise any of these rights, contact us at:
                        </p>
                        <p class="text-yellow-900 text-sm font-mono bg-white p-2 rounded inline-block">
                            privacy@dynamicforms.eu
                        </p>
                        <p class="text-yellow-900 text-sm mt-2">
                            We will respond within 30 days. If you're unsatisfied, you have the right to lodge a complaint with your local data protection authority.
                        </p>
                    </div>
                </section>

                <!-- Data Collection -->
                <section id="data-collection" class="border-t pt-8">
                    <h2 class="text-2xl font-semibold mb-4">4. What Data We Collect</h2>
                    
                    <h3 class="text-lg font-semibold mb-3">Account Information</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 mb-6">
                        <li>Name and email address</li>
                        <li>Password (hashed, not stored in plain text)</li>
                        <li>Account creation date</li>
                        <li>GDPR and marketing consent records</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-3">Form & Submission Data</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 mb-6">
                        <li>Forms you create and their configuration</li>
                        <li>Form submissions and field answers</li>
                        <li>Submission metadata (timestamp, responder email if requested)</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-3">Usage Data</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700 mb-6">
                        <li>IP address (logged for security and fraud prevention)</li>
                        <li>Browser type and device information</li>
                        <li>Pages visited and actions taken</li>
                        <li>Timestamps of activity</li>
                    </ul>

                    <h3 class="text-lg font-semibold mb-3">Communications</h3>
                    <ul class="list-disc pl-6 space-y-2 text-gray-700">
                        <li>Emails you send us (support requests, feedback)</li>
                        <li>Communications sent to your email (only with consent)</li>
                    </ul>
                </section>

                <!-- Account Deletion -->
                <section id="deletion" class="border-t pt-8">
                    <h2 class="text-2xl font-semibold mb-4">5. Account Deletion</h2>
                    <div class="bg-red-50 p-4 rounded-lg border border-red-200 mb-4">
                        <h3 class="font-semibold text-red-900 mb-2">Important: This is Permanent</h3>
                        <p class="text-red-900 text-sm mb-3">
                            Deleting your account is a permanent action that cannot be undone after 30 days.
                        </p>
                        <h4 class="font-semibold text-red-900 mb-2">What happens when you delete:</h4>
                        <ul class="list-disc pl-5 text-red-900 text-sm space-y-1">
                            <li>Your account is immediately marked for deletion</li>
                            <li>You are logged out of all sessions</li>
                            <li>All forms and submissions are queued for deletion</li>
                            <li>After 30 days, everything is permanently deleted from our servers</li>
                            <li>GDPR audit logs are retained for 7 years for legal compliance</li>
                        </ul>
                        <p class="text-red-900 text-sm mt-3">
                            <strong>If you change your mind:</strong> Contact us within 30 days at privacy@dynamicforms.eu to cancel the deletion.
                        </p>
                    </div>
                </section>

                <!-- Contact -->
                <section id="contact" class="border-t pt-8">
                    <h2 class="text-2xl font-semibold mb-4">6. Contact Us</h2>
                    <p class="text-gray-700 mb-4">
                        If you have questions about this Privacy Policy or your rights, please contact us:
                    </p>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p class="text-gray-800 mb-2"><strong>Email:</strong> <span class="font-mono">privacy@dynamicforms.eu</span></p>
                        <p class="text-gray-800 mb-2"><strong>Data Protection Officer:</strong> Available upon request</p>
                        <p class="text-gray-800"><strong>Response Time:</strong> 30 days maximum</p>
                    </div>

                    <h3 class="text-lg font-semibold mt-6 mb-3">Supervisory Authority</h3>
                    <p class="text-gray-700 mb-4">
                        You have the right to lodge a complaint with your local data protection authority if you believe we are processing your data unlawfully.
                    </p>
                </section>

                <!-- Changes to Policy -->
                <section class="border-t pt-8">
                    <h2 class="text-2xl font-semibold mb-4">Changes to This Policy</h2>
                    <p class="text-gray-700">
                        We may update this Privacy Policy from time to time. We will notify you of significant changes via email or by prominently displaying a notice on our website. Your continued use of the service after such modifications constitutes your acceptance of the updated Privacy Policy.
                    </p>
                </section>
            </div>

            <!-- Footer -->
            <div class="border-t mt-12 pt-8 text-center text-gray-600">
                <p class="text-sm">© {{ currentYear }} Dynamic Forms. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>
