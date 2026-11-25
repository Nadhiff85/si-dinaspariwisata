<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
    activeKey: { type: String, required: true },
    label: { type: String, required: true },
});

const container = ref(null);
const revealActive = async () => {
    await nextTick();
    const active = container.value?.querySelector('button[aria-pressed="true"]');
    if (active) {
        container.value.scrollLeft = Math.max(0, active.offsetLeft - (container.value.clientWidth - active.offsetWidth) / 2);
    }
};

onMounted(revealActive);
watch(() => props.activeKey, revealActive);
</script>

<template>
    <div ref="container" role="group" :aria-label="label" class="workspace-tabs"><slot /></div>
</template>
