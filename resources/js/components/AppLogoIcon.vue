<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg width="122px" height="22px" viewBox="0 0 122 22" version="1.1" xmlns="http://www.w3.org/2000/svg">
        <title>Coffee Duty</title>
        <g fill="currentColor" fill-rule="nonzero">
            <!-- coffee cup icon -->
            <path d="M3,5 h12 v8 a5,5 0 0 1 -5,5 h-2 a5,5 0 0 1 -5,-5 z M15,7 h2.5 a2.5,2.5 0 0 1 0,5 H15 z" fill="none" stroke="currentColor" stroke-width="1.5"/>
            <path d="M6,1.5 c0,1 1,1 1,2 M9,1.5 c0,1 1,1 1,2 M12,1.5 c0,1 1,1 1,2" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
            <text x="24" y="16" font-family="ui-sans-serif, system-ui, sans-serif" font-size="14" font-weight="600">Coffee Duty</text>
        </g>
    </svg>
</template>
