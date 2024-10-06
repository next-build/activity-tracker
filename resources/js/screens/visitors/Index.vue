<template>
    <div>
        <Table
            title="Visitors"
            :fields="fields"
            :items="entries"
        >
            <template #country="data">
                {{ data.item.content?.country ?? 'N/A' }}
            </template>
            <template #device="data">
                {{ data.item.content?.device }} ({{ data.item.content?.platform }})
            </template>
            <template #browser="data">
                {{ data.item.content?.browser }} ({{ data.item.content?.browserVersion }})
            </template>
            <template #action="data">
                <router-link :to="`/visitors/${data.item.uuid}`">
                    <ArrowRightCircleIcon class="w-6 h-6" />
                </router-link>
            </template>
        </Table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Table from '@/components/Table.vue';
import axios from 'axios';

import {
    ArrowRightCircleIcon,
} from '@heroicons/vue/24/outline';

const entries = ref([]);

const filters = ref({
    tag: null,
    lastEntryIndex: null,
    entriesPerRequest: null,
    familyHash: null,
});

const fields = [
    { key: 'ip_address', label: 'IP Address' },
    { key: 'device', label: 'Device' },
    { key: 'browser', label: 'Browser' },
    { key: 'timezone', label: 'Timezone' },
    { key: 'country', label: 'Country' },
    { key: 'action', label: '' },
];

const loadEntries = () => {
    axios.get(ActivityTracker.basePath + '/activity-tracker-api/requests' +
        '?tag=' + filters.value.tag +
        '&before=' + filters.value.lastEntryIndex +
        '&take=' + filters.value.entriesPerRequest +
        '&family_hash=' + filters.value.familyHash
    ).then(response => {
        entries.value = response.data.entries;
    }).catch(error => {})
}

onMounted(() => {
    loadEntries();
});
</script>
