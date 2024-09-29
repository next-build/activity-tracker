<template>
    <div class="border ring-opacity-5 rounded-md bg-white shadow">
        <div class="sm:flex sm:items-center px-6 py-4">
            <div class="sm:flex-auto">
                <h1 class="text-xl leading-6 text-gray-900">
                    {{ title }}
                </h1>
                <!-- <p class="mt-2 text-sm text-gray-700">
                    A list of all the users in your account including their name, title, email and role.
                </p> -->
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <!-- <button type="button"
                    class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add
                    user</button> -->
                <div class="w-full max-w-sm min-w-[200px]">
                    <input class="pl-4 w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded-full px-3 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-300 shadow-sm focus:shadow" placeholder="Type here...">
                </div>
            </div>
        </div>
        <div class="flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50 border-y">
                                <tr class="">

                                    <th v-for="(field, index) in fields" :key="index"
                                        scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                                    >
                                        {{ field.label }}
                                    </th>

                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr 
                                    v-for="(item, itemsIndex) in (props.items.data ?? props.items)"
                                    :key="itemsIndex" class=""
                                >

                                    <td 
                                        v-for="(field, fieldIndex) in fields" :key="fieldIndex"
                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                                    >
                                        <slot :name="field.key" :item="item">
                                            {{ item[field.key] ?? defaultItemValue }}
                                        </slot>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="props.items?.data">
            <nav class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-4 sm:px-6" aria-label="Pagination">
                <div class="hidden sm:block">
                    <p class="text-sm text-gray-700">
                        Showing
                        {{ ' ' }}
                        <span class="font-medium">{{ props.items.current_page }}</span>
                        {{ ' ' }}
                        to
                        {{ ' ' }}
                        <span class="font-medium">{{ props.items.last_page }}</span>
                        {{ ' ' }}
                        of
                        {{ ' ' }}
                        <span class="font-medium">{{ props.items.total }}</span>
                        {{ ' ' }}
                        results
                    </p>
                </div>
                <div class="flex flex-1 justify-between sm:justify-end">
                    <button 
                        @click="() => {
                            if (props.items.prev_page_url) emit('paginate', getURLParameter(props.items.prev_page_url, 'page'))
                        }"
                        :disabled="!props.items.prev_page_url"
                        class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 disabled:text-gray-300 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button 
                        @click="() => {
                            if (props.items.next_page_url) emit('paginate', getURLParameter(props.items.next_page_url, 'page'))
                        }"
                        :disabled="!props.items.next_page_url"
                        class="relative ml-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 disabled:text-gray-300 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </nav>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: String,
    fields: Array,
    items: Array,
    pagination: Object,
    defaultItemValue: {
        type: String,
        default: 'N/A'
    },
    emptyListMessage: {
        default: 'No Data Found',
        type: String,
    },
});

const emit = defineEmits(['paginate']);

function getURLParameter(url, param) {
    const urlParams = new URLSearchParams(new URL(url).search);
    return urlParams.get(param);
}
</script>