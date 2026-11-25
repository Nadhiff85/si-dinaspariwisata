<script setup>
import { Link } from '@inertiajs/vue3';
import { Home, ChevronRight } from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';

defineProps({
    title: { type: String, required: true },
    description: { type: String, default: '' },
    section: { type: String, default: '' },
});

const { t } = useAppContext();
</script>

<template>
    <div class="page-heading">
        <nav class="page-breadcrumb" :aria-label="t('Jejak navigasi', 'Breadcrumb')">
            <Link href="/" class="inline-flex items-center gap-1.5 hover:text-utama-teks"><Home class="h-3.5 w-3.5" />{{ t('Beranda', 'Home') }}</Link>
            <ChevronRight class="h-3 w-3 text-tinta-samar" />
            <span aria-current="page">{{ section || title }}</span>
        </nav>
        <div class="page-heading-row">
            <div class="min-w-0">
                <h1 class="font-heading text-tinta">{{ title }}</h1>
                <p v-if="description" class="page-description">{{ description }}</p>
                <div v-if="$slots.summary" class="mt-3 text-sm text-tinta-redup"><slot name="summary" /></div>
            </div>
            <div v-if="$slots.actions" class="page-heading-actions"><slot name="actions" /></div>
        </div>
    </div>
</template>
