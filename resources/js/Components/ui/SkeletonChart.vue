<script setup>
defineProps({
    height: { type: String, default: '280px' },
    variant: { type: String, default: 'bar' }, // 'bar' | 'line' | 'circle'
});
</script>

<template>
    <div
        class="flex flex-col justify-between rounded-xl border border-dashed border-garis bg-permukaan-redup/60 p-4"
        :style="{ height }"
        role="status"
        aria-label="Memuat visualisasi data..."
    >
        <!-- Top Legend Skeleton -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="kerangka h-3 w-20"></div>
                <div class="kerangka h-3 w-16"></div>
            </div>
            <div class="kerangka h-3 w-14"></div>
        </div>

        <!-- Body Visual Skeleton -->
        <div v-if="variant === 'circle'" class="my-auto flex items-center justify-center">
            <div class="kerangka h-40 w-40 rounded-full"></div>
        </div>

        <div v-else class="my-auto flex h-44 items-end justify-between gap-2.5 pt-4 px-2">
            <div class="kerangka w-full rounded-t-md" style="height: 42%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 68%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 54%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 86%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 62%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 92%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 75%"></div>
            <div class="kerangka w-full rounded-t-md" style="height: 58%"></div>
        </div>

        <!-- X-Axis Skeleton -->
        <div class="flex items-center justify-between gap-2 pt-2 border-t border-garis/60">
            <div class="kerangka h-2.5 w-10"></div>
            <div class="kerangka h-2.5 w-10"></div>
            <div class="kerangka h-2.5 w-10"></div>
            <div class="kerangka h-2.5 w-10"></div>
            <div class="kerangka h-2.5 w-10"></div>
        </div>
    </div>
</template>

