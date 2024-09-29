<template>
    <div id="map" class="!z-0" :class="customClass"></div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import L from 'leaflet';

const props = defineProps({
    markers: {
        type: Array,
        required: true,
        default: () => [],
    },
    zoom: {
        type: Number,
        required: false,
        default: 8
    },
    customClass: {
        type: String,
        default: ''
    }
});

L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
});

const map = ref(null);

const initMap = () => {

    map.value = L.map('map').setView([
        Math.min(...props.markers.map(marker => marker.lat)),
        Math.max(...props.markers.map(marker => marker.lng))
    ], props.zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://saptarshi.de">Develop by Saptarshi Dey</a>'
    }).addTo(map.value);

    props.markers.forEach(marker => {
        const leafletMarker = L.marker([marker.lat, marker.lng]).addTo(map.value);
        leafletMarker.bindPopup(marker.popupText);
    });

}

onMounted(() => {
    initMap();
});
</script>

<style>
@import "leaflet/dist/leaflet.css";
</style>
