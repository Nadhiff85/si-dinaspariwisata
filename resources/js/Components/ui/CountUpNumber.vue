<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useAppContext } from '@/composables/useAppContext';

const props = defineProps({
    value: { type: [Number, String], default: 0 },
    duration: { type: Number, default: 850 },
    decimals: { type: Number, default: 0 },
    prefix: { type: String, default: '' },
    suffix: { type: String, default: '' },
    currency: { type: Boolean, default: false },
});

const { locale } = useAppContext();
const displayVal = ref(0);
let rafId = null;

function parseNumeric(v) {
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
}

function animateTo(target, fromVal = 0) {
    if (typeof window === 'undefined') {
        displayVal.value = target;
        return;
    }

    const prefersReduced = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
    if (prefersReduced || props.duration <= 0) {
        displayVal.value = target;
        return;
    }

    if (rafId) cancelAnimationFrame(rafId);

    const startTime = performance.now();
    const startVal = fromVal;
    const diff = target - startVal;

    const step = (now) => {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / props.duration, 1);
        // easeOutExpo for snappy, natural deceleration
        const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        displayVal.value = startVal + diff * eased;

        if (progress < 1) {
            rafId = requestAnimationFrame(step);
        } else {
            displayVal.value = target;
        }
    };

    rafId = requestAnimationFrame(step);
}

onMounted(() => {
    animateTo(parseNumeric(props.value), 0);
});

watch(
    () => props.value,
    (newVal, oldVal) => {
        animateTo(parseNumeric(newVal), parseNumeric(oldVal));
    }
);

const formattedText = computed(() => {
    const num = displayVal.value;
    const loc = locale.value === 'en' ? 'en-US' : 'id-ID';
    if (props.currency) {
        return new Intl.NumberFormat(loc, {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(Math.round(num));
    }
    const formatted = new Intl.NumberFormat(loc, {
        minimumFractionDigits: props.decimals,
        maximumFractionDigits: props.decimals,
    }).format(props.decimals > 0 ? Number(num.toFixed(props.decimals)) : Math.round(num));

    return `${props.prefix}${formatted}${props.suffix}`;
});
</script>

<template>
    <span class="tabular-nums">{{ formattedText }}</span>
</template>

