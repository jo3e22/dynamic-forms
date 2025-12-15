<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Eye, Share2, Trash2, Palette, Save } from 'lucide-vue-next';
import type { FormDTO } from '@/types/forms';

const props = defineProps<{
    form: FormDTO;
}>();

const emit = defineEmits<{
    openColorPicker: [];
    saveForm: [];
    formjson: [];
    tempPrint: [];
}>();

const isDev = computed(() => import.meta.env.DEV);

function goBack() {
    router.visit('/forms');
}

function openColorPicker() {
    emit('openColorPicker');
}

function saveForm() {
    emit('saveForm');
}

function previewForm() {
    // Open preview in new tab
    window.open(`/forms/${props.form.code}/viewform`, '_blank');
}

function shareForm() {
    // Copy link to clipboard
    const url = `${window.location.origin}/forms/${props.form.code}/viewform`;
    navigator.clipboard.writeText(url).then(() => {
        alert('Form link copied to clipboard!');
    }).catch(() => {
        alert(`Share this link: ${url}`);
    });
}

function deleteForm() {
    if (confirm('Are you sure you want to delete this form? This action cannot be undone.')) {
        router.delete(`/forms/${props.form.code}`, {
            onSuccess: () => {
                router.visit('/forms');
            },
        });
    }
}

// Debug helpers
function formjson() {
    emit('formjson');
}

function tempPrint() {
    emit('tempPrint');
}
</script>

<template>
    <header class="bg-white shadow-md px-6 py-4 flex items-center justify-between sticky top-0 z-20">
        <!-- Left: Back Arrow and Title -->
        <div class="flex items-center gap-4">
            <button 
                @click="goBack"
                class="text-gray-600 hover:text-gray-800 text-2xl leading-none"
                title="Back to forms list"
            >
                ←
            </button>
            <h1 class="text-xl font-semibold text-gray-800">
                {{ form.code }}
            </h1>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-2">
            <!-- Debug buttons (remove in production) -->
            <button 
                v-if="isDev"
                @click="formjson" 
                class="px-3 py-1.5 text-sm bg-purple-500 text-white rounded hover:bg-purple-600 transition-colors"
            >
                JSON
            </button>
            <button 
                v-if="isDev"
                @click="tempPrint" 
                class="px-3 py-1.5 text-sm bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
            >
                Debug
            </button>

            <!-- Main action buttons -->
            <Button
                @click="saveForm"
                variant="default"
                size="sm"
                class="gap-2"
            >
                <Save :size="16" />
                Save
            </Button>

            <Button
                @click="openColorPicker"
                variant="outline"
                size="sm"
                class="gap-2"
            >
                <Palette :size="16" />
                Style
            </Button>

            <Button
                @click="previewForm"
                variant="outline"
                size="sm"
                class="gap-2"
            >
                <Eye :size="16" />
                Preview
            </Button>

            <Button
                @click="shareForm"
                variant="outline"
                size="sm"
                class="gap-2"
            >
                <Share2 :size="16" />
                Share
            </Button>

            <Button
                @click="deleteForm"
                variant="destructive"
                size="sm"
                class="gap-2"
            >
                <Trash2 :size="16" />
                Delete
            </Button>
        </div>
    </header>
</template>