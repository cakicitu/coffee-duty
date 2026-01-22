<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3'

interface Bean {
    id: number;
    name: string;
    finished: boolean;
    lasted: number;      
    count: number;     
    total: number;    
    likes: Like[];
    dislikes: Dislike[];
    created_at: EpochTimeStamp;
    finished_at: EpochTimeStamp;
}

interface Like {
    id: number;
    user_id: number;      
    bean_id: number;      
}

interface Dislike {
    id: number;
    user_id: number;      
    bean_id: number;      
}

const page = usePage()
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Beans',
        href: dashboard().url,
    },
];

let beans = ref<Bean[]>(page.props.beans as Bean[])

let hasEval = page.props.hasEval

let currentBeans = ref<Bean>(page.props.currentBeans as Bean)

console.log("BEAN", beans)
// let likes = ref<Like[]>(page.props.beans as Bean[])


let averageDuration = computed(() => {
    if (beans){
        const finishedBeans = beans.value.filter(bean => bean.finished);
        
        if (!beans || finishedBeans.length === 0) return 0;
        
        const totalLasted = finishedBeans.reduce((sum, bean) => sum + bean.lasted, 0);
        
        return totalLasted / finishedBeans.length;
    }else{
        return 0
    }

});

let dueDate = computed(() => {
    return (averageDuration.value - currentBeans.value.lasted).toFixed(2)
});

const createNewBeanRotation = async () => {
    try {
        const response = await fetch('/api/bean', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify({beanId: currentBeans.value.id})
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data) {
            // Update users data with the response
            console.log("GOT DATA: ", data)
            beans.value = data.beans;
            currentBeans.value = data.currentBeans;
            console.log('New bean rotation selected successfully');
        } else {
            console.error('Error selecting new coffee getter:', data);
        }
    } catch (error) {
        console.error('Error creating new bean rotation:', error);
    }
}

const likeCurrentBeans = async () => {
    try {
        const response = await fetch('/api/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify({beanId: currentBeans.value.id})
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data) {
            location.reload();
        } else {
            console.error('Error selecting new coffee getter:', data);
        }
    } catch (error) {
        console.error('Error creating new bean rotation:', error);
    }
}

const dislikeCurrentBeans = async () => {
    try {
        const response = await fetch('/api/dislike', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify({beanId: currentBeans.value.id})
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data) {
            location.reload();
        } else {
            console.error('Error selecting new coffee getter:', data);
        }
    } catch (error) {
        console.error('Error creating new bean rotation:', error);
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
                        <h1 style="font-size: 20px;" v-if="currentBeans">The <b style="color: rgb(78, 75, 240);">current</b> Beans are in for: {{ currentBeans.lasted }} days.</h1>
                        <span v-else>There arent beans in the machine.</span>
                        <h1 style="font-size: 20px;" v-if="beans && beans.length > 1"><b style="color: rgb(21, 187, 21);">New</b> Beans are due in: {{ dueDate }} days.</h1>
                        <span v-else>The forecast is available as soon as there is one full rotation.</span>
                    </div>
                    <div class="button" @click="createNewBeanRotation">I put in new beans</div>

                    <div class="eval_area" v-if="!hasEval">
                        <h1 style="font-size: 20px;" >Do you like the current beans?</h1>
                        <div class="eval_button_area">
                            <div class="eval_like_button" @click="likeCurrentBeans()">Like</div>
                            <div class="eval_dislike_button" @click="dislikeCurrentBeans()">Dislike</div>           
                        </div>
                    </div>



                    <table>
                        <thead>
                            <tr>
                                <td>name</td>
                                <td>count</td>
                                <td>lasted</td>
                                <td>finished</td>
                                <td>likes</td>
                                <td>dislikes</td>
                                <td>created at</td>
                                <td>finished at</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="bean in beans" :class="{ active: !bean.finished }">
                                <td>{{ bean.name }}</td>
                                <td>{{ bean.count }} cups</td>
                                <td>{{ bean.lasted }} days</td>
                                <td>{{ bean.finished }}</td>
                                <td>{{ bean && bean.likes.length }} </td>
                                <td>{{ bean && bean.dislikes.length }} </td>
                                <td>{{ bean.created_at }}</td>
                                <td>{{ bean.finished_at }}</td>
                            </tr>
                        </tbody>
                    </table>
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
.button{
    cursor: pointer;
    background-color: rgb(21, 187, 21);
    padding: 7px 12px;
    border-radius: 10px;
}

table{
    margin-top: 20px;
    width: 100%;
    padding: 10px;

    tr{
        border-bottom: 1px solid white;

        &.active{
            background-color: rgb(78, 75, 240, 0.5);
        }
    }
    td{
        padding: 5px 10px;
    }
}

.eval_area{
    margin: 20px;
    
    .eval_button_area{
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: row;

        .eval_like_button{
            margin: 5px;
            cursor: pointer;
            background-color: rgb(21, 187, 21);
            padding: 7px 12px;
            border-radius: 10px;
        }

        .eval_dislike_button{
            margin: 5px;
            cursor: pointer;
            background-color: rgb(255, 0, 0);
            padding: 7px 12px;
            border-radius: 10px;
        }
    }
}
</style>
