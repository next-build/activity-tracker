<template>
    <MultiMarkerMap
        v-if="markers.length > 0"
        customClass="!h-[500px] !rounded-md !shadow"
        :markers="markers"
        zoom="4"
    />
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from 'axios';
import MultiMarkerMap from "@/components/MultiMarkerMap.vue";

const markers = ref([]);

const loadEntries = () => {
    axios.get(ActivityTracker.basePath + '/activity-tracker-api/requests')
    .then(response => {
        response.data.entries.forEach(entry => {
            if (entry.content.latitude && entry.content.latitude) {
                markers.value.push({
                    lat: entry.content.latitude,
                    lng: entry.content.longitude,
                    popupText: entry.ip_address,
                });
            }
        });
    }).catch(error => {})
}

onMounted(() => {
    loadEntries();
});
</script>
