<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3'

interface CleaningDuty {
    id: number;
    user_id: number;
    week_start: string;
    done: boolean;
    done_at: string | null;
    user: { id: number; name: string } | null;
}

interface CleaningStat {
    id: number;
    name: string;
    selected: number;
    done: number;
    missed: number;
}

const page = usePage()
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cleaning',
        href: dashboard().url,
    },
];

// The logged-in user, needed to decide whether the confirm button is shown
const me = computed(() => page.props.auth.user)

let currentDuty = ref<CleaningDuty | null>(page.props.currentDuty as CleaningDuty | null)
let stats = ref<CleaningStat[]>(page.props.stats as CleaningStat[])
let showHelpModal = ref(false);

// Whether the logged-in user is this week's cleaner and still has to do the job
const canConfirm = computed(() => {
    return currentDuty.value && !currentDuty.value.done && currentDuty.value.user_id === me.value.id;
});

// Confirms this week's cleaning job and refreshes duty and stats from the server
const confirmCleaningDone = async () => {
    try {
        const response = await fetch('/api/cleaning/done', {
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
            currentDuty.value = data.current_duty;
            stats.value = data.stats;
        } else {
            console.error('Error confirming cleaning duty:', data);
        }
    } catch (error) {
        console.error('Error confirming cleaning duty:', error);
    }
}
</script>

<template>
    <Head title="Cleaning" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="cleaning-help-button" @click="showHelpModal = true" title="How to clean the machine">?</div>
                <div class="body">
                    <div class="title">
                        <h1 style="font-size: 20px;" v-if="currentDuty && currentDuty.user">
                            <b>{{ currentDuty.user.name }}</b> has to clean the machine this week.
                        </h1>
                        <span v-else>No users available for cleaning duty.</span>
                        <p class="cleaning-subtitle">The cleaning duty rotates every Sunday, in order of user id.</p>
                        <p class="cleaning-done-note" v-if="currentDuty && currentDuty.done">
                            Done for this week ✓ <span v-if="currentDuty.done_at">({{ currentDuty.done_at }})</span>
                        </p>
                    </div>

                    <div class="button" v-if="canConfirm" @click="confirmCleaningDone">I did my job!</div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <td>user</td>
                                    <td>selected</td>
                                    <td>did the job</td>
                                    <td>missed</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="stat in stats" :key="stat.id" :class="{ active: currentDuty && stat.id === currentDuty.user_id }">
                                    <td data-label="user">{{ stat.name }}</td>
                                    <td data-label="selected">{{ stat.selected }} times</td>
                                    <td data-label="did the job">{{ stat.done }} times</td>
                                    <td data-label="missed">{{ stat.missed }} times</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="cleaning-modal-overlay" v-if="showHelpModal" @click.self="showHelpModal = false">
            <div class="cleaning-modal">
                <div class="cleaning-modal-header">
                    <h2>How to clean the DeLonghi coffee machine</h2>
                    <div class="cleaning-modal-close" @click="showHelpModal = false" title="Close">✕</div>
                </div>
                <div class="cleaning-modal-content">
                    <h3>Daily</h3>
                    <ul>
                        <li>Empty the drip tray and the coffee grounds container, rinse both with warm water and dry them.</li>
                        <li>Rinse the water tank and refill it with fresh, cold water. Never top up old water.</li>
                        <li>Wipe the coffee spouts with a clean, damp cloth so no oily residue builds up.</li>
                        <li>Wipe the housing with a soft, damp cloth. Never use solvents, alcohol or abrasive cleaners.</li>
                    </ul>

                    <h3>Weekly</h3>
                    <ul>
                        <li>Switch the machine off and unplug it.</li>
                        <li>Open the service door on the side and remove the brew group (infuser) by pressing the two colored release buttons and pulling it out.</li>
                        <li>Rinse the brew group under lukewarm running water. <b>Never use dishwashing soap</b> — it damages the unit and leaves residue in your coffee.</li>
                        <li>Let the brew group air-dry completely before putting it back.</li>
                        <li>Remove coffee residue inside the machine with a small brush or a vacuum cleaner.</li>
                        <li>Slide the brew group back in until it clicks and close the service door.</li>
                        <li>Wipe the inside of the bean hopper with a dry cloth only.</li>
                    </ul>

                    <h3>Descaling (every 2–3 months or when the descale light comes on)</h3>
                    <ul>
                        <li>Use only DeLonghi EcoDecalk descaler — other products can damage the machine.</li>
                        <li>Empty the drip tray. Pour descaler into the empty water tank up to mark A, then add water up to mark B.</li>
                        <li>Place a container with at least 2 liters capacity under the coffee spouts.</li>
                        <li>Start the descaling program (on most models: hold the OK / descale button for about 5 seconds — check the manual for your exact model).</li>
                        <li>The program runs for roughly 25 minutes and dispenses the solution in intervals. Do not interrupt it.</li>
                        <li>When finished, rinse the water tank thoroughly, fill it with fresh water and run the rinse cycle to flush out all descaler.</li>
                    </ul>

                    <h3>Milk system (models with milk carafe or frother)</h3>
                    <ul>
                        <li>Run the CLEAN function (or flush the steam wand) directly after every use of milk.</li>
                        <li>Disassemble the milk carafe once a week and wash all parts in warm water.</li>
                    </ul>

                    <h3>Important</h3>
                    <ul>
                        <li>Never immerse the machine in water.</li>
                        <li>Do not put parts in the dishwasher unless the manual explicitly allows it.</li>
                        <li>No soap on the brew group — water only.</li>
                    </ul>
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
.cleaning-subtitle{
    margin-top: 6px;
    opacity: 0.7;
    font-size: 14px;
}
.cleaning-done-note{
    margin-top: 10px;
    color: rgb(21, 187, 21);
    font-weight: 600;
}
.button{
    cursor: pointer;
    background-color: rgb(21, 187, 21);
    padding: 7px 12px;
    border-radius: 10px;
}

.cleaning-help-button{
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 10;
    width: 34px;
    height: 34px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    background-color: rgb(78, 75, 240);
    color: white;
    border-radius: 50%;
    font-weight: 700;
    font-size: 16px;
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

.cleaning-modal-overlay{
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 16px;
}

.cleaning-modal{
    background: white;
    color: #1b1b1f;
    border-radius: 12px;
    max-width: 640px;
    width: 100%;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.dark .cleaning-modal{
    background: #1b1b1f;
    color: #eee;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.cleaning-modal-header{
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(128, 128, 128, 0.3);
}

.cleaning-modal-header h2{
    font-size: 18px;
    font-weight: 700;
}

.cleaning-modal-close{
    cursor: pointer;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 16px;
}

.cleaning-modal-close:hover{
    background: rgba(128, 128, 128, 0.2);
}

.cleaning-modal-content{
    padding: 16px 20px 24px;
    overflow-y: auto;
}

.cleaning-modal-content h3{
    font-size: 16px;
    font-weight: 700;
    margin: 16px 0 6px;
}

.cleaning-modal-content h3:first-child{
    margin-top: 0;
}

.cleaning-modal-content ul{
    list-style: disc;
    padding-left: 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* Mobile: table collapses into stacked cards */
@media (max-width: 768px) {
    .title h1{
        font-size: 17px !important;
    }
    .button{
        padding: 12px 16px;
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
