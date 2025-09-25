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
console.log("USERS", users)
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

const toggleFinished = async (user) => {
    try {
        const response = await fetch('/api/user/'+user.id+'/toggle/finished', {
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

const toggleSelected = async (user) => {
    try {
        const response = await fetch('/api/user/'+user.id+'/toggle/selected', {
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

const addDrank = async (user) => {
    try {
        const response = await fetch('/api/user/'+user.id+'/add/drank', {
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
                        <h1 style="font-size: 20px;" v-if="coffeeGetter"><b>{{coffeeGetter.name}}</b> has to get the coffee.</h1>
                        <span v-else>Please choose a new coffee-getter.</span>
                    </div>
                    <div class="button" @click="selectNewCoffeeGetter" v-if="coffeeGetter && coffeeGetter.id == me.id">I got the coffee</div>
                    <div class="user-container">
                        <div class="users" :class="{green: user.finished}" v-for="user in users" :key="user.id">
                            <p :class="{highlight: user.id == me.id}">{{ user.id }}. {{ user.name }}</p>
                            <p>got {{ user.count }} beans</p> 
                            <p>drank {{ user.drank }} cups</p>
                            <div class="toogle-selected"  @click="toggleSelected(user)" v-if="me.isAdmin">
                                toggle selected
                            </div>
                             <div class="toogle-finished"  @click="toggleFinished(user)" v-if="me.isAdmin">
                                toggle finished
                            </div>
                            <div class="add-drank"  @click="addDrank(user)" v-if="me.isAdmin">
                                add drank
                            </div>
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
    border: 2px solid rgb(253, 49, 49);
    padding: 7px 12px;
    border-radius: 10px;
    min-width: 150px;
    text-align: center;
}
.green{
    border: 2px solid rgb(21, 187, 21);
}
.highlight{
    color:rgb(78, 75, 240);
}
.button{
    cursor: pointer;
    background-color: rgb(21, 187, 21);
    padding: 7px 12px;
    border-radius: 10px;
}
.toogle-selected{
    margin: 5px 0;
    cursor: pointer;
    background-color: rgb(78, 75, 240);
    padding: 7px 12px;
    border-radius: 10px;
}
.toogle-finished{
    margin: 5px 0;
    cursor: pointer;
    background-color: rgb(21, 187, 21);
    padding: 7px 12px;
    border-radius: 10px;
}
.add-drank{
    margin: 5px 0;
    cursor: pointer;
    background-color: rgb(253, 49, 49);
    padding: 7px 12px;
    border-radius: 10px;
}
</style>
