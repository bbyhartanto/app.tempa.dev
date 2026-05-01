<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

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
    if (confirm('Are you sure you want to remove this link?')) {
        links.value.splice(index, 1);
    }
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

function handleBack() {
    router.visit('/dashboard');
}
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 pb-12">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="handleBack" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h1 class="text-xl font-extrabold tracking-tight">Daftar Links</h1>
            </div>
            <button 
                @click="saveLinks"
                :disabled="isSaving"
                class="bg-black text-white px-6 py-2.5 rounded-xl text-sm font-bold active:scale-95 transition-all shadow-lg shadow-gray-100 disabled:opacity-50"
            >
                {{ isSaving ? 'Saving...' : 'Save' }}
            </button>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-8">
            <!-- Info Banner -->
            <div class="bg-blue-50 border-2 border-blue-100/50 rounded-[24px] p-5">
                <div class="flex items-start space-x-3">
                    <span class="text-xl mt-0.5">💡</span>
                    <p class="text-[14px] font-bold text-blue-700/80 leading-relaxed">
                        Add links to your GrabFood, ShopeeFood, or Tokopedia. These will appear as buttons on your store.
                    </p>
                </div>
            </div>

            <!-- Add New Link Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-5">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl border border-blue-100/50">
                        🔗
                    </div>
                    <h2 class="text-lg font-black tracking-tight text-gray-900">Add New Link</h2>
                </div>

                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Button Label</label>
                        <input
                            v-model="newLink.label"
                            type="text"
                            placeholder="e.g., Order on GrabFood"
                            class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                        />
                    </div>

                    <div class="space-y-2">
                        <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Destination URL</label>
                        <div class="flex items-center bg-gray-50 rounded-[10px] border-2 border-transparent focus-within:border-gray-100 focus-within:bg-white transition-all overflow-hidden">
                            <span class="pl-5 text-gray-300 text-[15px] font-bold select-none">https://</span>
                            <input
                                v-model="newLink.url"
                                type="text"
                                placeholder="food.grab.com/..."
                                class="flex-1 bg-transparent px-1 py-4 text-[15px] font-bold outline-none placeholder:text-gray-300"
                            />
                        </div>
                    </div>

                    <button
                        @click="addLink"
                        class="w-full py-4 bg-blue-600 text-white rounded-[20px] font-bold text-[15px] hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-100"
                    >
                        Add to List
                    </button>
                </div>
            </div>

            <!-- Your Links Card -->
            <div v-if="links.length > 0" class="bg-white border-2 border-gray-50 rounded-[12px] overflow-hidden shadow-[0_12px_40px_rgb(0,0,0,0.03)]">
                <div class="p-6 border-b-2 border-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-black tracking-tight">Your Links ({{ links.length }})</h2>
                    <span class="text-[11px] font-black text-gray-300 uppercase tracking-[0.2em]">Drag to sort</span>
                </div>

                <div class="divide-y-2 divide-gray-50">
                    <div
                        v-for="(link, index) in links"
                        :key="index"
                        class="p-5 flex items-center justify-between hover:bg-gray-50/50 transition-colors group"
                    >
                        <div class="flex-1 min-w-0 mr-4">
                            <p class="font-bold text-[16px] text-gray-900 truncate">{{ link.label }}</p>
                            <p class="text-[13px] font-bold text-gray-400 truncate opacity-60">{{ link.url.replace(/^https?:\/\//, '') }}</p>
                        </div>

                        <div class="flex items-center space-x-1.5">
                            <button
                                @click="moveLink(index, 'up')"
                                :disabled="index === 0"
                                class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-black disabled:opacity-10 transition-all active:scale-90"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>
                            </button>
                            <button
                                @click="moveLink(index, 'down')"
                                :disabled="index === links.length - 1"
                                class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 hover:text-black disabled:opacity-10 transition-all active:scale-90"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <button
                                @click="removeLink(index)"
                                class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-400 hover:text-red-600 transition-all active:scale-90"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div v-if="links.length > 0" class="space-y-5 pt-4">
                <div class="flex items-center space-x-3 px-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center text-sm border border-purple-100/50">
                        ✨
                    </div>
                    <h2 class="text-[13px] font-black uppercase tracking-[0.2em] text-gray-400">Storefront Preview</h2>
                </div>
                
                <div class="bg-gray-50/30 border-2 border-dashed border-gray-100 rounded-[40px] p-10 space-y-3.5">
                    <div
                        v-for="(link, index) in links"
                        :key="index"
                        class="w-full py-4.5 px-6 bg-white border-2 border-gray-50 rounded-full text-center shadow-sm active:scale-[0.98] transition-all"
                    >
                        <span class="font-bold text-[15px] text-gray-800">{{ link.label }}</span>
                    </div>
                    <div v-if="links.length === 0" class="text-center py-4 text-gray-300 font-bold text-sm italic">
                        Your links will appear here
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="links.length === 0" class="text-center py-20 space-y-6">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto grayscale opacity-40">
                    <span class="text-5xl">🔗</span>
                </div>
                <div class="space-y-2">
                    <p class="text-xl font-black text-gray-900">No links added</p>
                    <p class="text-gray-400 font-bold text-sm">Start by adding your first platform<br/>link in the form above.</p>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.transition-all {
    transition-duration: 250ms;
}
</style>
