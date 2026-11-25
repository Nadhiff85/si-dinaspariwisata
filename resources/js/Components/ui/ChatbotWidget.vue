<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { MessageSquare, X, Send, Sparkles, MapPin, Star } from 'lucide-vue-next';
import { useAppContext } from '@/composables/useAppContext';
import PhotoCredit from '@/Components/destinasi/PhotoCredit.vue';

const { locale, t, formatCurrency } = useAppContext();
const isOpen = ref(false);
const inputMsg = ref('');
const isLoading = ref(false);
const messageContainer = ref(null);

const messages = ref([
    {
        sender: 'bot',
        greeting: true,
        text: '',
        destinations: [],
    },
]);

const quickPrompts = computed(() => [
    t('Wisata bahari & snorkeling terbaik', 'Marine tourism and snorkelling'),
    t('Destinasi yang sedang sepi hari ini', 'Quiet destinations'),
    t('Wisata sejarah & megalitikum di Poso', 'History and megaliths in Poso'),
    t('Destinasi dengan tiket di bawah 15 ribu', 'Admission below 15k'),
]);

watch(() => [messages.value.length, isLoading.value, isOpen.value], async () => {
    await nextTick();
    if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
});

const sendPrompt = async (textOverride = null) => {
    const query = (textOverride ?? inputMsg.value).trim();
    if (!query || isLoading.value) return;

    messages.value.push({ sender: 'user', text: query, destinations: [] });
    if (!textOverride) inputMsg.value = '';
    isLoading.value = true;

    try {
        const csrfCookie = document.cookie.split('; ').find((cookie) => cookie.startsWith('XSRF-TOKEN='));
        const response = await fetch('/chatbot/ask', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(csrfCookie?.slice('XSRF-TOKEN='.length) || ''),
            },
            body: JSON.stringify({ message: query, locale: locale.value }),
            signal: AbortSignal.timeout(15000),
        });
        if (!response.ok) {
            throw new Error(`Chat request failed: ${response.status}`);
        }
        const data = await response.json();

        messages.value.push({
            sender: 'bot',
            text: data.reply,
            destinations: data.destinations || [],
        });
    } catch {
        messages.value.push({
            sender: 'bot',
            text: t('Katalog belum dapat diakses. Coba kirim ulang pertanyaan Anda. Jika sesi berakhir, muat ulang halaman.', 'The catalogue is unavailable. Please retry your question. If your session expired, reload the page.'),
            destinations: [],
        });
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="fixed right-4 bottom-4 z-40 sm:right-6 sm:bottom-6">
        <!-- Panel Percakapan Chatbot -->
        <div
            v-if="isOpen"
            class="mb-3 flex h-[min(480px,70dvh)] w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-[var(--warna-garis)] bg-[var(--warna-permukaan)] shadow-2xl sm:w-[380px]"
        >
            <div class="flex items-center justify-between bg-[#12344D] px-4 py-3 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0E7C86]">
                        <Sparkles class="h-4 w-4 text-white" />
                    </div>
                    <div>
                        <h4 class="font-heading text-sm font-bold">{{ t('Asisten Wisata Sulteng', 'Sulteng Travel Assistant') }}</h4>
                        <p class="text-[11px] text-[#D5EEF0]">{{ t('Rekomendasi berdasarkan katalog', 'Recommendations from our catalogue') }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="isOpen = false"
                    :aria-label="t('Tutup chatbot', 'Close chatbot')"
                    class="rounded-lg p-1 text-gray-300 hover:bg-white/10 hover:text-white"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Isi Pesan -->
            <div ref="messageContainer" role="log" aria-live="polite" :aria-label="t('Percakapan wisata', 'Travel conversation')" class="flex-1 space-y-3 overflow-y-auto p-3.5 text-xs">
                <div
                    v-for="(m, idx) in messages"
                    :key="idx"
                    :class="m.sender === 'user' ? 'flex justify-end' : 'flex flex-col items-start'"
                >
                    <div
                        :class="[
                            'max-w-[88%] rounded-xl px-3.5 py-2.5 leading-relaxed',
                            m.sender === 'user'
                                ? 'bg-[#0E7C86] text-white'
                                : 'border border-[var(--warna-garis)] bg-[var(--warna-pasir)] text-[var(--warna-teks)]',
                        ]"
                    >
                        {{ m.greeting ? t('Halo! Tanyakan rekomendasi berdasarkan kategori wisata, wilayah, atau anggaran tiket masuk.', 'Hello! Ask for recommendations by tourism category, region, or admission budget.') : m.text }}
                    </div>

                    <!-- Kartu Destinasi Hasil Rekomendasi -->
                    <div v-if="m.destinations?.length" class="mt-2 w-full space-y-2">
                        <div v-for="d in m.destinations" :key="d.id" class="space-y-1">
                        <Link
                            :href="`/destinasi/${d.slug}`"
                            class="flex items-center gap-2.5 rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-permukaan)] p-2 transition hover:border-[#0E7C86]"
                        >
                            <img :src="d.foto_utama" :alt="d.nama_destinasi" class="h-12 w-14 shrink-0 rounded-lg object-cover" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-bold text-[var(--warna-teks)]">{{ d.nama_destinasi }}</div>
                                <div class="flex items-center gap-1 text-[11px] text-[var(--warna-teks-redup)]">
                                    <MapPin class="h-3 w-3 text-[#0E7C86]" />
                                    <span class="truncate">{{ d.wilayah }}</span>
                                </div>
                                <div class="mt-0.5 flex items-center justify-between text-[11px]">
                                    <span class="font-semibold text-[#0A5F67] tabular-nums">{{ formatCurrency(d.harga_dewasa) }}</span>
                                    <span class="inline-flex items-center gap-0.5 font-semibold text-[#E0A422] tabular-nums">
                                        <Star class="h-3 w-3 fill-[#E0A422]" /> {{ d.rating }}
                                    </span>
                                </div>
                            </div>
                        </Link>
                        <PhotoCredit :photo="d.foto_attribution" class="px-1 text-tinta-redup" />
                        </div>
                    </div>
                </div>

                <p v-if="isLoading" role="status" class="text-[var(--warna-teks-redup)]">{{ t('Mencari destinasi yang sesuai…', 'Finding matching destinations…') }}</p>

                <!-- Chip Pertanyaan Cepat -->
                <div class="pt-1">
                    <p class="mb-1.5 text-[11px] font-medium text-[var(--warna-teks-redup)]">{{ t('Tanya cepat:', 'Quick questions:') }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="qp in quickPrompts"
                            :key="qp"
                            type="button"
                            @click="sendPrompt(qp)"
                            :disabled="isLoading"
                            class="rounded-lg border border-[#0E7C86]/30 bg-[#D5EEF0]/60 px-2.5 py-1 text-[11px] font-medium text-[#0A5F67] transition hover:bg-[#0E7C86] hover:text-white"
                        >
                            {{ qp }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Input -->
            <form @submit.prevent="sendPrompt()" class="flex items-center gap-2 border-t border-[var(--warna-garis)] p-2.5">
                <input
                    v-model="inputMsg"
                    type="text"
                    :placeholder="t('Ketik pertanyaan wisata...', 'Ask about your trip...')"
                    :aria-label="t('Pertanyaan wisata', 'Travel question')"
                    maxlength="500"
                    class="h-10 flex-1 rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3 text-xs text-[var(--warna-teks)] focus:border-[#0E7C86] focus:outline-none"
                />
                <button
                    type="submit"
                    :disabled="isLoading"
                    :aria-label="t('Kirim pertanyaan', 'Send question')"
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#0E7C86] text-white transition hover:bg-[#0A5F67] disabled:opacity-50"
                >
                    <Send class="h-4 w-4" />
                </button>
            </form>
        </div>

        <!-- Tombol Pemicu Chatbot -->
        <button
            type="button"
            @click="isOpen = !isOpen"
            :aria-label="isOpen ? t('Tutup asisten wisata', 'Close travel assistant') : t('Tanya wisata', 'Ask about travel')"
            :aria-expanded="isOpen"
            class="ml-auto flex h-12 items-center justify-center gap-2 rounded-full border border-[#4FC3CC]/60 bg-[#12344D] px-3.5 text-xs font-semibold text-white shadow-md transition hover:bg-[#0A5F67] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0E7C86] sm:px-4"
        >
            <MessageSquare class="h-5 w-5 text-[#4FC3CC]" />
            <span class="hidden sm:inline">{{ t('Tanya Wisata', 'Travel Assistant') }}</span>
        </button>
    </div>
</template>

