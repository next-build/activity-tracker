<template>
    <div class="flex flex-col gap-8">
        <div class="border ring-opacity-5 rounded-md shadow">
        <div class="sm:flex sm:items-center px-6 py-4 bg-white border-b border-gray-200">
            <div class="sm:flex-auto">
                <h1 class="text-xl leading-6 text-gray-900">
                    Request Details
                </h1>
            </div>
        </div>
        <div>

            <div class="px-6">
                <table class="w-full table-auto border-separate border-spacing-y-4">
                    <tbody>
                        <tr>
                            <td>Date Time</td>
                            <td>{{ dateFormat(entry?.created_at, "mmmm dS yyyy, hh:MM:ss TT") }}</td>
                        </tr>
                        <tr>
                            <td>URL</td>
                            <td>{{ entry?.content?.full_url }}</td>
                        </tr>
                        <tr>
                            <td>Method</td>
                            <td>{{ entry?.content?.method }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>{{ entry?.content?.response_status }}</td>
                        </tr>
                        <tr>
                            <td>Referer URL</td>
                            <td>{{ entry?.content?.headers?.referer ?? 'NOT AVAILABLE' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- <div class="flex flex-col gap-4 px-6 py-4">
                <div class="flex flex-row gap-x-16">
                    <p class="text-base">URL</p>
                    <p class="text-base">{{ entry?.content?.full_url }}</p>
                </div>
                <div class="flex flex-row gap-x-16">
                    <p class="text-base">Method</p>
                    <p class="text-base">{{ entry?.content?.method }}</p>
                </div>
                <div class="flex flex-row gap-x-16">
                    <p class="text-base">Status</p>
                    <p class="text-base">{{ entry?.content?.response_status }}</p>
                </div>
                <div class="flex flex-row" v-if="entry?.content?.headers?.referer">
                    <p class="flex-1  text-base">Referer URL</p>
                    <p class="flex-1 text-base">{{ entry?.content?.headers?.referer }}</p>
                </div>
                <div class="flex flex-row gap-x-16">
                    <p class="text-base">Date Time</p>
                    <p class="text-base">{{ dateFormat(entry?.created_at, "hh:MM:ss TT, mm-dd-yyyy") }}</p>
                </div>
            </div> -->

        </div>
    </div>

    <div>
        <Tab
            class="flex-1 rounded-md shadow"
            :tabs="[
                { key: 'header', label: 'Header' },
                { key: 'payload', label: 'Payload' },
                { key: 'response', label: 'Response' },
            ]"
            current="header"
        >
            <template #header>
                <div class="p-4 bg-[#2e1e2e] text-white">
                    <!-- <JsonViewer theme="jv-dark" :value="entry?.content?.headers" copyable /> -->
                    <VueJsonView class="overflow-hidden" theme="paraiso" :src="entry?.content?.headers" />
                </div>
            </template>
            <template #payload>
                <div class="p-4 bg-[#2e1e2e] text-white">
                    <!-- <VueJsonPretty v-model="entry" /> -->
                    <!-- <JsonViewer theme="jv-dark" :value="entry?.content?.payload" copyable /> -->
                    <VueJsonView theme="paraiso" :src="entry?.content?.payload" />
                </div>
            </template>
            <template #response>
                <div class="p-4 bg-[#2e1e2e] text-white">
                    <!-- <JsonViewer theme="jv-dark" :value="entry?.content?.response" copyable /> -->
                    <VueJsonView theme="paraiso" :src="entry?.content?.response" />
                </div>
            </template>
        </Tab>
    </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Tab from '@/components/Tab.vue';
import VueJsonView from '@matpool/vue-json-view'
import dateFormat from "dateformat";

const route = useRoute();
const entry = ref(null);

const loadEntry = () => {
    axios.get(ActivityTracker.basePath + '/activity-tracker-api/visitor-ip/' + route.params.visitor_id + '/requests/' + route.params.request_id
    ).then(response => {
        console.log(response.data.entry);
        entry.value = response.data.entry;
    }).catch(error => { })
}

onMounted(() => {
    loadEntry();
});
</script>
