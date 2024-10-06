<template>
    <div
        id="map"
        class="!z-0"
        :class="customClass"
    ></div>
</template>

<script setup>
import { onMounted } from 'vue';
import L from 'leaflet';

const props = defineProps({
    latitude: {
        type: Float32Array,
        required: true,
        default: () => 0.00
    },
    longitude: {
        type: Float32Array,
        required: true,
        default: () => 0.00
    },
    popupText: {
        type: String,
        required: false,
        default: ''
    },
    zoom: {
        type: Number,
        required: false,
        default: 8
    },
    customClass: {
        type: String,
        required: false,
        default: ''
    }
});

const mapInit = () => {

    const map = L.map('map').setView([props.latitude, props.longitude], props.zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://saptarshi.de">Develop by Saptarshi Dey</a>'
    }).addTo(map);

    L.marker([props.latitude, props.longitude]).addTo(map)
    .bindPopup(props.popupText)
    .openPopup();

}

onMounted(() => {
    mapInit();
});
</script>

<style>
@import "leaflet/dist/leaflet.css";
.leaflet-popup-close-button {
    display: none;
}
</style>
