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

// The logged-in user, needed so the backend knows who brought the beans
const me = computed(() => page.props.auth.user)

let showLikeButtons = ref(true);

let beanName = ref('');

let beans = ref<Bean[]>(page.props.beans as Bean[])

let editingId = ref<number | null>(null);
let editName = ref('');

let hasEval = page.props.hasEval

let currentBeans = ref<Bean>(page.props.currentBeans as Bean)

// let likes = ref<Like[]>(page.props.beans as Bean[])


// Average number of days a finished bean pack lasted
let averageDuration = computed(() => {
    const finishedBeans = beans.value.filter(bean => bean.finished);

    if (finishedBeans.length === 0) return 0;

    const totalLasted = finishedBeans.reduce((sum, bean) => sum + bean.lasted, 0);

    return totalLasted / finishedBeans.length;
});

// Days until new beans are due, based on the average pack duration
let dueDate = computed(() => {
    if (!currentBeans.value) return null;

    return (averageDuration.value - currentBeans.value.lasted).toFixed(2)
});

// Swaps in a new bean pack; the backend advances the rotation if I was the selected user
const createNewBeanRotation = async () => {
    try {
        const body: { user: number; name?: string } = { user: me.value.id };
        if (beanName.value) {
            body.name = beanName.value;
        }

        const response = await fetch('/api/bean', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            credentials: 'include',
            body: JSON.stringify(body)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data) {
            // Update users data with the response
            beans.value = data.beans;
            currentBeans.value = data.currentBeans;
            location.reload();
        } else {
            console.error('Error selecting new coffee getter:', data);
        }
    } catch (error) {
        console.error('Error creating new bean rotation:', error);
    }
}

const startEdit = (bean) => {
    editingId.value = bean.id;
    editName.value = bean.name ?? '';
};

const cancelEdit = () => {
    editingId.value = null;
    editName.value = '';
};

const saveName = async (bean) => {
    if (!editName.value.trim()) { cancelEdit(); return; }
    try {
        const res = await fetch(`/api/bean/${bean.id}/rename`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
            credentials: 'include',
            body: JSON.stringify({ name: editName.value.trim() })
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        if (data.success) {
            const idx = beans.value.findIndex(b => b.id === bean.id);
            if (idx !== -1) beans.value[idx] = data.bean;
        }
    } catch (e) { console.error('Error renaming bean:', e); }
    editingId.value = null;
};

const likeCurrentBeans = async () => {
    try {
        showLikeButtons.value = false;
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
        showLikeButtons.value = false;
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
                        <h1 style="font-size: 20px;" v-if="currentBeans && beans && beans.length > 1"><b style="color: rgb(21, 187, 21);">New</b> Beans are due in: {{ dueDate }} days.</h1>
                        <span v-else>The forecast is available as soon as there is one full rotation.</span>
                    </div>
                    <div class="new-beans-area">
                        <input v-model="beanName" type="text" placeholder="Bean name (e.g. Paco's Finest)" class="bean-name-input" @keyup.enter="createNewBeanRotation" />
                        <div class="button" @click="createNewBeanRotation">I put in new beans</div>
                    </div>

                    <div class="eval_area" v-if="!hasEval && showLikeButtons">
                        <h1 style="font-size: 20px;" >Do you like the current beans?</h1>
                        <div class="eval_button_area">
                            <div class="eval_like_button" @click="likeCurrentBeans()">Like</div>
                            <div class="eval_dislike_button" @click="dislikeCurrentBeans()">Dislike</div>           
                        </div>
                    </div>



                    <div class="table-wrap">
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
                                    <td data-label="name">
                                        <template v-if="editingId === bean.id" style="display:inline-flex; align-items:center; gap:4px;">
                                            <input v-model="editName" class="inline-edit-input" @keyup.enter="saveName(bean)" @blur="saveName(bean)" @keyup.escape="cancelEdit" autofocus />
                                        </template>
                                        <template v-else>
                                            <span class="bean-name-cell">
                                                {{ bean.name }}
                                                <span class="edit-icon" @click.stop="startEdit(bean)" title="Rename">✏️</span>
                                            </span>
                                        </template>
                                    </td>
                                    <td data-label="count">{{ bean.count }} cups</td>
                                    <td data-label="lasted">{{ bean.lasted }} days</td>
                                    <td data-label="finished">{{ bean.finished }}</td>
                                    <td data-label="likes">{{ bean && bean.likes && bean.likes.length }} </td>
                                    <td data-label="dislikes">{{ bean && bean.dislikes && bean.dislikes.length }} </td>
                                    <td data-label="created at">{{ bean.created_at }}</td>
                                    <td data-label="finished at">{{ bean.finished_at }}</td>
                                </tr>
                            </tbody>
                        </table>
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
    padding: 0 12px;
    text-align: center;
}
.new-beans-area{
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    margin: 16px 0;
}

.bean-name-input{
    padding: 7px 12px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: transparent;
    color: inherit;
    font-size: 15px;
    width: 260px;
    max-width: 90%;
    text-align: center;
    outline: none;
}

.bean-name-input:focus{
    border-color: rgb(78, 75, 240);
}

.inline-edit-input{
    background: transparent;
    border: 1px solid rgb(78, 75, 240);
    border-radius: 4px;
    color: inherit;
    font-size: inherit;
    padding: 2px 6px;
    width: 80px;
    max-width: 80px;
    min-width: 60px;
    outline: none;
}

.bean-name-cell{
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.edit-icon{
    cursor: pointer;
    opacity: 0.4;
    font-size: 14px;
    transition: opacity 0.15s;
    line-height: 1;
}

.edit-icon:hover{
    opacity: 1;
}

.button{
    cursor: pointer;
    background-color: rgb(21, 187, 21);
    padding: 7px 12px;
    border-radius: 10px;
}

@media (max-width: 768px) {
    .inline-edit-input{
        width: 80px;
    }
    .new-beans-area{
        width: 100%;
        padding: 0 12px;
    }
    .bean-name-input{
        width: 100%;
    }
}

.table-wrap{
    width: 100%;
    overflow-x: auto;
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

/* Mobile: table collapses into stacked cards */
@media (max-width: 768px) {
    .title h1{
        font-size: 17px !important;
    }
    .button{
        padding: 12px 16px;
    }
    .eval_area .eval_button_area .eval_like_button,
    .eval_area .eval_button_area .eval_dislike_button{
        padding: 12px 20px;
    }

    table{
        padding: 0 8px;
    }
    table thead{
        display: none;
    }
    table tr{
        display: block;
        margin-bottom: 12px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        padding: 8px 4px;
    }
    table tr.active{
        border: 1px solid rgb(78, 75, 240);
    }
    table td{
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 4px 10px;
        text-align: right;
        word-break: break-word;
    }
    table td::before{
        content: attr(data-label);
        font-weight: 600;
        text-align: left;
        opacity: 0.7;
    }
}
</style>
