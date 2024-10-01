<template>
    <div class="flex flex-col gap-8" v-if="entry">
        <div class="flex flex-row gap-8">
            <MarkerMap
                customClass="flex-1 !h-auto !rounded-md !shadow"
                :latitude="entry?.content?.latitude" :longitude="entry?.content?.longitude"
                :popupText="entry?.ip_address" zoom="8"
            />

            <!-- <div class="flex-1 rounded-md shadow">
                <div class="border ring-opacity-5">
                    <div class="sm:flex sm:items-center px-6 py-4 bg-white">
                        <div class="sm:flex-auto">
                            <h1 class="text-xl leading-6 text-gray-900">
                                Request Header
                            </h1>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 px-6 py-4">
                        <div class="flex flex-row">
                            <p class="flex-1 text-base">IP Address</p>
                            <p class="flex-1 text-base">{{ entry?.ip_address }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Timezone</p>
                            <p class="flex-1 text-base">{{ entry?.timezone }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">City</p>
                            <p class="flex-1 text-base">{{ entry?.content?.city }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Zip Code</p>
                            <p class="flex-1 text-base">{{ entry?.content?.zip }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Region</p>
                            <p class="flex-1 text-base">{{ entry?.content?.regionName }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Country</p>
                            <p class="flex-1 text-base">{{ entry?.content?.country }}</p>
                        </div>
                    </div>
                </div>
            </div> -->

            <Tab
                class="flex-1 rounded-md shadow"
                :tabs="[
                    { key: 'ip', label: 'IP Information' },
                    { key: 'device', label: 'Device Details' },
                ]"
                current="ip"
            >

                <template #ip>
                    <div class="flex flex-col gap-4 px-6 py-4">
                        <!-- <div class="flex flex-row">
                            <p class="flex-1 text-base">IP Address</p>
                            <p class="flex-1 text-base">{{ entry?.ip_address }}</p>
                        </div> -->
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Timezone</p>
                            <p class="flex-1 text-base">{{ entry?.timezone }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">City</p>
                            <p class="flex-1 text-base">{{ entry?.content?.city }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Zip Code</p>
                            <p class="flex-1 text-base">{{ entry?.content?.zip }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Region</p>
                            <p class="flex-1 text-base">{{ entry?.content?.regionName }}</p>
                        </div>
                        <div class="flex flex-row flex-1">
                            <p class="flex-1 text-base">Country</p>
                            <p class="flex-1 text-base">{{ entry?.content?.country }}</p>
                        </div>
                    </div>
                </template>

                <template #device>

                    <div class="flex flex-col gap-4 px-6 py-4">
                        <div class="flex flex-row">
                            <p class="flex-1 text-base">Device</p>
                            <p class="flex-1 text-base">{{ entry?.content?.device }}</p>
                        </div>
                        <div class="flex flex-row">
                            <p class="flex-1 text-base">Platform</p>
                            <p class="flex-1 text-base">{{ entry?.content?.platform }}</p>
                        </div>
                        <div class="flex flex-row">
                            <p class="flex-1 text-base">Platform Version</p>
                            <p class="flex-1 text-base">{{ entry?.content?.platformVersion }}</p>
                        </div>
                        <div class="flex flex-row">
                            <p class="flex-1 text-base">Browser</p>
                            <p class="flex-1 text-base">{{ entry?.content?.browser }}</p>
                        </div>
                        <div class="flex flex-row">
                            <p class="flex-1 text-base">Browser Version</p>
                            <p class="flex-1 text-base">{{ entry?.content?.browserVersion }}</p>
                        </div>
                    </div>

                    <!-- <div class="px-6">
                        <table class="w-full table-auto border-separate border-spacing-y-4">
                            <tbody>
                                <tr>
                                    <td>Device</td>
                                    <td>{{ entry?.content?.device }}</td>
                                </tr>
                                <tr>
                                    <td>Platform</td>
                                    <td>{{ entry?.content?.platform }}</td>
                                </tr>
                                <tr>
                                    <td>Platform Version</td>
                                    <td>{{ entry?.content?.platformVersion }}</td>
                                </tr>
                                <tr>
                                    <td>Browser</td>
                                    <td>{{ entry?.content?.browser }}</td>
                                </tr>
                                <tr>
                                    <td>Browser Version</td>
                                    <td>{{ entry?.content?.browserVersion }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div> -->

                </template>

            </Tab>

        </div>

        <Table title="Visitor Requests" :fields="fields" :items="entries" @paginate="loadEntries">
            <template #method="data">
                {{ data.item.content?.method }}
            </template>
            <template #type="data">
                {{ data.item.type }}
            </template>
            <template #url="data">
                <p class="max-w-96 truncate">
                    {{ data.item.content?.full_url }}
                </p>
            </template>
            <template #response_status="data">
                {{ data.item.content?.response_status }}
            </template>
            <template #created_at="data">
                {{ timeAgo(data.item.created_at) }}
            </template>
            <template #action="data">
                <router-link :to="`/visitors/${entry.uuid}/requests/${data.item.uuid}`">
                    <ArrowRightCircleIcon class="w-6 h-6" />
                </router-link>
            </template>
            <template #content="data">
                {{ data.item.content }}
            </template>
        </Table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Table from '@/components/Table.vue';
import MarkerMap from '@/components/MarkerMap.vue';
import Tab from '@/components/Tab.vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import dateFormat, { masks } from "dateformat";
import { timeAgo } from '@/base.js';

import {
    ArrowRightCircleIcon,
} from '@heroicons/vue/24/outline';

const route = useRoute();

const entry = ref(null);
const entries = ref([]);

const filters = ref({
    tag: null,
    lastEntryIndex: null,
    entriesPerRequest: null,
    familyHash: null,
});

const fields = [
    { key: 'method', label: 'Verb' },
    { key: 'url', label: 'URL' },
    { key: 'response_status', label: 'Status' },
    { key: 'created_at', label: 'Happened' },
    // { key: 'content', label: 'content' },
    { key: 'action', label: '' },
];

const loadEntry = () => {
    axios.get(ActivityTracker.basePath + '/activity-tracker-api/requests/' + route.params.id
    ).then(response => {
        console.log(response.data);
        entry.value = response.data.entry;
    }).catch(error => { })
}

const loadEntries = (page = 1) => {
    axios.get(
        ActivityTracker.basePath + '/activity-tracker-api/visitor-ip/' + route.params.id + '/requests' + '?page=' + page
    ).then(response => {
        console.log(response.data);
        entries.value = response.data.entries;
    }).catch(error => { })
}

onMounted(() => {
    loadEntry();
    loadEntries();
});
</script>
