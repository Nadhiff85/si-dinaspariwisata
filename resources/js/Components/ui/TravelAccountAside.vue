<script setup>
import { Heart, Route, Ticket } from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';

const { t } = useAppContext();
</script>

<template>
    <aside class="account-aside kartu-togean overflow-hidden">
        <img src="/images/kadidiri-togean.jpg" :alt="t('Perairan Kepulauan Togean', 'Waters of the Togean Islands')" class="aspect-[16/9] w-full object-cover" loading="lazy" />
        <div class="p-6 sm:p-7">
            <h2 class="font-heading text-xl font-bold text-tinta">{{ t('Satu akun untuk perjalanan Anda', 'One account for your journey') }}</h2>
            <p class="mt-2 text-sm text-tinta-redup">{{ t('Dari tempat yang ingin dikunjungi hingga tiket yang siap digunakan.', 'From places you want to visit to tickets ready for your trip.') }}</p>
            <ul class="mt-5 divide-y divide-garis text-sm text-tinta">
                <li class="flex items-center gap-3 py-3"><Heart class="h-4 w-4 shrink-0 text-utama-teks" />{{ t('Simpan destinasi favorit', 'Save your favourite destinations') }}</li>
                <li class="flex items-center gap-3 py-3"><Route class="h-4 w-4 shrink-0 text-utama-teks" />{{ t('Susun rute dan estimasi biaya', 'Plan your route and admission budget') }}</li>
                <li class="flex items-center gap-3 py-3"><Ticket class="h-4 w-4 shrink-0 text-utama-teks" />{{ t('Akses tiket perjalanan Anda', 'Access your trip tickets') }}</li>
            </ul>
            <p class="mt-3 text-xs text-tinta-redup">{{ t('Foto Kepulauan Togean:', 'Togean Islands photo:') }} <a href="https://travelingyuk.com/pulau-kadidiri-di-sulawesi-tengah/88800/" target="_blank" rel="noopener noreferrer" class="underline underline-offset-2">Travelingyuk</a></p>
        </div>
    </aside>
</template>
