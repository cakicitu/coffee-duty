<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Beans',
        href: dashboard().url,
    },
];

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <div class="body">
                    <div class="title">
                        <h1 style="font-size: 20px;" ">Hilfe</h1>
                    </div>
                  
                    <div class="help-text">
                        <h1 style="font-size: 20px;" ">Wie bediene ich wann was ?????</h1>
                        <br>
                        <b>Ich habe Kaffe besorgt. Was mache ich jetzt ?</b> => ich gehe auf der Homepage auf Dashboard und drücke auf den Button "I got the Beans". <b>Achtung!</b> Dieser Button erscheint erst wenn man dran ist.
                        <br>
                        <br>
                        <b>Die Packung Bohnen ist leer. Was mache ich jetzt ?</b> => Zunächst die neue Packung aufmachen und die Maschine befüllen. Wir wollen ja nicht das Stau entsteht ;) DIREKT danach auf dem Display den <b>BLAUEN</b> Button betätigen mit der aufschrift "I put in new beans". 
                        <br>
                        <br>
                        <b>Ich habe mir ein Kaffee geholt. Was mache ich nun?</b>Auf dem Display auf den eigenen Namen clicken <b>ODER</b> auf der Homepage auf "add Drank" clicken <b>ODER</b> in der Siedle App auf "Name +1" clicken.(Falls man kein QR-Code für die Siedle App braucht, bei Türker melden)               
                        <br>
                        <br>
                        <b>Ich will die Kaffeebohnen bewerten. Wie mache ich das ?</b>Auf dem Homepage unter Beans ist folgede Frage zu sehen "Do you like the current beans?" sofern man noch keine Bewertung für diese Bohnen abgegeben hat. Durch betätigen des "Like" oder "Dislike" Buttons gibt man seine Bewertung ab. Diese ist nicht änderbar und auf 1 pro User pro Packung Bohnen begrenzt. Sobald die Bewertung abgegeben ist erscheint der Eintrag in der Tabelle under likes und dislikes.
                        <br>
                        <br>
                        <h1 style="font-size: 20px;" ">Weitere Infos</h1>
                        <b>Welche Infos stehen mir zu Verfügung ???</b>
                        <br>
                        <br>
                        <b>Wer ist dran mit Bohnen holen?</b> => Steht auf dem Display und auf dem Dashboard der Homepage.
                        <br>
                        <br>
                        <b>Wie lange halten die die Bohnen noch ?</b> => Steht der Homepage unter dem Reiter "Bohnen" oder auf dem Display
                        <br>
                        <br>
                        <b>Statistiken</b> => Steht auf der Homepage unter dem Reiter "Bohnen"
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

.help-text{
    width: 100%;
    padding: 20px;
    text-align: left;
}
</style>
