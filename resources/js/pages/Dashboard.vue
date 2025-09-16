<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3'

interface User {
    id: number;
    name: string;
    email: string;
    selected: boolean;
    finished: boolean;
}

const page = usePage()
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// let users = ["Andres D.", "Andreas P.", "Sascha", "Moritz", "Türker", "Volker"]
let me = computed(() => page.props.auth.user)
// let users = computed(() => page.props.users)
let users = ref<User[]>(page.props.users as User[])
let coffeeGetter = computed(() => users.value.find((user: User) => user.selected))

const selectNewCoffeeGetter = async () => {
    try {
        const response = await fetch('/api/user/job/select', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify({})
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            // Update users data with the response
            users.value = data.users;
            console.log('New coffee getter selected successfully');
        } else {
            console.error('Error selecting new coffee getter:', data);
        }
    } catch (error) {
        console.error('Error selecting new coffee getter:', error);
    }
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="body">
                    <div class="title">
                        <span v-if="coffeeGetter">{{coffeeGetter.name}} has to get the coffee.</span>
                        <span v-else>Please choose a new coffee-getter.</span>
                    </div>
                    <div class="button" @click="selectNewCoffeeGetter">Choose new coffee-getter</div>
                    <div class="user-container">
                        <div class="users" v-for="user in users" :key="user.id">
                            <p :class="{highlight: user.id == me.id}">{{ user.name }} {{user.finished ? '&#10004;' : '&#10006;' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
.body{
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}
.title{
    margin: 20px 0 30px;
}
.user-container{
    width: 100%;
    padding-top: 20px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    flex-direction: row;
}
.users{
    margin: 6px;
    border: 2px solid rgb(78, 75, 240);
    padding: 7px 12px;
    border-radius: 10px;
    min-width: 150px;
    text-align: center;
}
.highlight{
    color:rgb(78, 75, 240);
}
.button{
    cursor: pointer;
    background-color: rgb(78, 75, 240);
    padding: 7px 12px;
    border-radius: 10px;
}
</style>
