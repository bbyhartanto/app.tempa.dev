<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    storeLinks: {
        type: Array,
        required: true,
    },
});

const links = ref(props.storeLinks || []);
const newLink = ref({ label: '', url: '' });
const isSaving = ref(false);

function addLink() {
    if (newLink.value.label && newLink.value.url) {
        // Prepend https:// if not already present
        let url = newLink.value.url;
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'https://' + url;
        }
        
        links.value.push({
            label: newLink.value.label,
            url: url,
            order: links.value.length,
        });
        newLink.value = { label: '', url: '' };
    }
}

function removeLink(index) {
    links.value.splice(index, 1);
}

function moveLink(index, direction) {
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    if (newIndex >= 0 && newIndex < links.value.length) {
        [links.value[index], links.value[newIndex]] = 
        [links.value[newIndex], links.value[index]];
    }
}

function saveLinks() {
    isSaving.value = true;
    
    // Ensure all URLs have https:// prefix before saving
    const linksWithUrls = links.value.map(link => {
        let url = link.url;
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'https://' + url;
        }
        return { ...link, url };
    });
    
    router.put(route('dashboard.links.update'), {
        store_links: linksWithUrls,
    }, {
        onSuccess: () => {
            isSaving.value = false;
        },
        onError: () => {
            isSaving.value = false;
        },
    });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Links" />

        <!-- Content -->
        <main class="p-4 max-w-2xl mx-auto">
            <!-- Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <strong>Store Links</strong> appear on your home page as buttons. 
                    Add links to GrabFood, ShopeeFood, Tokopedia, and other platforms.
                </p>
            </div>

            <!-- Add New Link -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <h2 class="font-bold text-gray-900 mb-3">Add New Link</h2>
                <div class="space-y-2">
                    <input
                        v-model="newLink.label"
                        type="text"
                        placeholder="Label (e.g., GrabFood)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            https://
                        </span>
                        <input
                            v-model="newLink.url"
                            type="url"
                            placeholder="food.grab.com/..."
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <button
                        @click="addLink"
                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
                    >
                        Add Link
                    </button>
                </div>
            </div>

            <!-- Links List -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-bold text-gray-900">Your Links ({{ links.length }})</h2>
                    <button
                        @click="saveLinks"
                        :disabled="isSaving"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ isSaving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>

                <div v-if="links.length === 0" class="text-center text-gray-500 py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <p>No links added yet</p>
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="(link, index) in links"
                        :key="index"
                        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ link.label }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ link.url.replace(/^https?:\/\//, '') }}</p>
                        </div>
                        <div class="flex items-center space-x-1 ml-2">
                            <button
                                type="button"
                                @click="moveLink(index, 'up')"
                                :disabled="index === 0"
                                class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                                title="Move up"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="moveLink(index, 'down')"
                                :disabled="index === links.length - 1"
                                class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30"
                                title="Move down"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="removeLink(index)"
                                class="p-1 text-red-400 hover:text-red-600"
                                title="Remove"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div v-if="links.length > 0" class="bg-white rounded-lg shadow p-4 mt-4">
                <h2 class="font-bold text-gray-900 mb-3">Preview</h2>
                <div class="space-y-2">
                    <a
                        v-for="(link, index) in links"
                        :key="index"
                        href="#"
                        class="block w-full py-3 px-4 bg-white border border-gray-200 rounded-full text-center hover:shadow-sm"
                    >
                        <span class="font-medium text-gray-900">{{ link.label }}</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
</template>
