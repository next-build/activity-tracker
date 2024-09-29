<template>
    <div>
        <div class="bg-white">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a 
                        v-for="tab in props.tabs" :key="tab.key"
                        :class="[
                            (tab.key === current) ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                            'whitespace-nowrap border-b-2 px-6 py-4 text-base font-medium'
                        ]"
                        @click="current = tab.key"
                    >
                        {{ tab.label }}
                    </a>
                </nav>
            </div>
        </div>
        <div>
            <slot :name="current"></slot>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    tabs: Array,
    current: String,
});

const current = ref(props.current);
</script>