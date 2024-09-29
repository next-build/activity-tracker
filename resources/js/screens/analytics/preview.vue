<template>
    <div id="map" style="height: 500px; border-radius: 8px;"></div>
</template>

<script>
import L from 'leaflet';
import axios from 'axios';

L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon-2x.png',
    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
    shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
});

export default {
    name: 'MapComponent',
    data() {
        return {
            map: null,
            markers: [],
            resource: 'requests',
            entries: [],
            ready: false,
        };
    },
    mounted() {
        this.loadEntries((entries) => {
            this.entries = entries;
            this.ready = true;
            // this.calculatedMarker();
            this.initMap();
        });
    },
    methods: {
        initMap() {
            this.calculatedMarker();

            // Initialize the map and set its view to a chosen geographical coordinates and zoom level
            this.map = L.map('map').setView([
                Math.min(...this.markers.map(marker => marker.lat)),
                Math.max(...this.markers.map(marker => marker.lng))
            ], 4);

            // Set up the OpenStreetMap layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(this.map);

            // Loop through the markers array and add markers to the map
            this.markers.forEach(marker => {
                const leafletMarker = L.marker([marker.lat, marker.lng]).addTo(this.map);
                leafletMarker.bindPopup(marker.popupText);
            });
        },
        loadEntries(after) {
            axios.post(ActivityTracker.basePath + '/activity-tracker-api/' + this.resource
            ).then(response => {
                // this.lastEntryIndex = response.data.entries.length ? _.last(response.data.entries).sequence : this.lastEntryIndex;
                // this.hasMoreEntries = response.data.entries.length >= this.entriesPerRequest;
                // this.recordingStatus = response.data.status;

                if (_.isFunction(after)) {
                    after(
                        this.familyHash || this.showAllFamily ? response.data.entries : _.uniqBy(response.data.entries, entry => entry.family_hash || _.uniqueId())
                    );
                }
            })
        },
        calculatedMarker() {
            this.entries.forEach((value) => {
                this.markers.push({
                    lat: value.content.latitude,
                    lng: value.content.longitude,
                    popupText: value.ip_address,
                });
                // { lat: 26.7084, lng: 88.4318, popupText: 'Marker 1' },
            });
            // [
            //     { lat: 26.7084, lng: 88.4318, popupText: 'Marker 1' },
            //     { lat: 30.7084, lng: 60.4318, popupText: 'Marker 2' },
            //     { lat: 32.7084, lng: 87.4318, popupText: 'Marker 3' },
            //     { lat: 35.7084, lng: 89.4318, popupText: 'Marker 4' },
            //     { lat: 24.7084, lng: 78.4318, popupText: 'Marker 5' },
            //     { lat: 78.7084, lng: 87.4318, popupText: 'Marker 6' },
            // ]
        }
    }
};
</script>

<style scoped>
#map {
    width: 100%;
    height: 100%;
}
</style>

