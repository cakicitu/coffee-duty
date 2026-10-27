<script setup lang="ts">
import { appUrl } from '@/lib/utils';
</script>

<template>
    <img :src="appUrl('/images/coffee-duty-logo.png')" alt="Coffee Duty" class="h-9 w-auto" />
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">Coffee Duty</span>
    </div>
</template>
