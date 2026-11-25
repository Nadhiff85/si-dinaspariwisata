<script setup>
import { useAppContext } from '@/composables/useAppContext';

defineProps({ photo: { type: Object, default: null } });
const { t } = useAppContext();
</script>

<template>
    <p v-if="photo" class="flex flex-wrap items-center gap-x-1 text-[10px] leading-relaxed" @click.stop>
        <a :href="photo.source" target="_blank" rel="noopener noreferrer" class="underline decoration-current/40 underline-offset-2 hover:decoration-current" :title="t(photo.caption_id, photo.caption_en)">
            {{ t('Foto:', 'Photo:') }} {{ photo.credit }}
        </a>
        <template v-if="photo.license">
            <span aria-hidden="true">·</span>
            <a :href="photo.license_url" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2" :title="t('Ukuran dan format dioptimalkan; potongan mengikuti layar.', 'Size and format optimised; crop varies with screen size.')">{{ photo.license }}</a>
        </template>
    </p>
</template>
