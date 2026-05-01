<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import SubscriptionRequestModal from '@/Components/Tenant/SubscriptionRequestModal.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    subscription: {
        type: Object,
        required: true,
    },
    availablePlans: {
        type: Array,
        required: true,
    },
});

const showRequestModal = ref(false);

// Dummy stats data for Orderan
const stats = ref({
    today: { approved: 100, pending: 20 },
    week: { approved: 540, pending: 85 }
});

const activeTab = ref('hari_ini');

const currentStats = computed(() => {
    return activeTab.value === 'hari_ini' ? stats.value.today : stats.value.week;
});

const copied = ref(false);

const storeUrl = computed(() => {
    const slug = props.tenant.store_link || props.tenant.slug || props.tenant.name?.toLowerCase().replace(/\s+/g, '-');
    return `...mpa.dev/${slug}`;
});

function copyStoreLink() {
    const slug = props.tenant.store_link || props.tenant.slug;
    const fullUrl = `${window.location.origin}/${slug}`;
    navigator.clipboard.writeText(fullUrl).then(() => {
        copied.value = true;
        setTimeout(() => copied.value = false, 2000);
    });
}

function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        router.post(route('logout'));
    }
}



function handleViewStore() {
    const link = props.tenant.store_link || props.tenant.slug;
    window.open(route('storefront.home', { store_link: link }), '_blank');
}
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 pb-12">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Dashboard</h1>
            <button class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-7">
            <!-- Profile Section -->
            <div class="flex items-center space-x-5">
                <div class="relative group">
                    <div class="w-24 h-24 rounded-full bg-gray-100 overflow-hidden border-2 border-gray-50 shadow-sm flex-shrink-0">
                        <img 
                            v-if="tenant.logo_url" 
                            :src="tenant.logo_url" 
                            :alt="tenant.name" 
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 space-y-1.5 min-w-0">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 leading-none truncate">{{ tenant.name }}</h2>
                    <div class="flex items-center text-gray-500 text-sm font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5 opacity-60"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        <span class="truncate">{{ storeUrl }}</span>
                        <button 
                            @click="copyStoreLink" 
                            class="ml-2 p-1 hover:bg-gray-100 rounded-lg transition-colors flex items-center space-x-1"
                            :class="copied ? 'text-green-600' : 'text-gray-400'"
                        >
                            <svg v-if="!copied" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <span v-if="copied" class="text-[10px] font-black uppercase tracking-widest">Copied</span>
                        </button>
                    </div>
                    <div class="inline-flex items-center px-4 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-green-50 text-green-600 border border-green-100">
                        {{ tenant.status || 'Active' }}
                    </div>
                </div>
            </div>

            <!-- Main Actions -->
            <div>
                <button 
                    @click="handleViewStore"
                    class="w-full py-4 px-6 border-2 border-gray-100 rounded-[10px] text-[16px] font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-200 transition-all active:scale-95 shadow-sm"
                >
                    View store
                </button>
            </div>

            <!-- Usage Info Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[24px] p-5 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex justify-between items-center">
                <div class="space-y-1">
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Product Usage:</p>
                    <p class="text-[15px] font-bold text-gray-800">
                        {{ subscription.remaining_slots }}/{{ tenant.item_limit || 25 }} Slots remaining
                    </p>
                </div>
                <div class="text-right space-y-1">
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider">Subscription status</p>
                    <span :class="subscription.badge_class || 'bg-red-50 text-red-500 border-red-100'" class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase border">
                        {{ subscription.status_label || 'Expired' }}
                    </span>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] overflow-hidden shadow-[0_12px_40px_rgb(0,0,0,0.03)]">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-xl font-black tracking-tight text-gray-900">Orderan</h3>
                        <div class="flex bg-gray-50 p-1.5 rounded-[10px] border border-gray-100">
                            <button 
                                @click="activeTab = 'hari_ini'"
                                :class="activeTab === 'hari_ini' ? 'bg-white shadow-sm text-gray-900 border border-gray-100' : 'text-gray-400'"
                                class="px-5 py-2 rounded-xl text-[13px] font-bold transition-all duration-200"
                            >
                                Hari ini
                            </button>
                            <button 
                                @click="activeTab = 'minggu_ini'"
                                :class="activeTab === 'minggu_ini' ? 'bg-white shadow-sm text-gray-900 border border-gray-100' : 'text-gray-400'"
                                class="px-5 py-2 rounded-xl text-[13px] font-bold transition-all duration-200"
                            >
                                Minggu ini
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 divide-x-2 divide-gray-50">
                        <div class="text-center space-y-1">
                            <p class="text-4xl font-black tracking-tighter text-gray-900">{{ currentStats.approved }}</p>
                            <p class="text-[15px] font-bold text-gray-400">Approved</p>
                        </div>
                        <div class="text-center space-y-1">
                            <p class="text-4xl font-black tracking-tighter text-gray-900">{{ currentStats.pending }}</p>
                            <p class="text-[15px] font-bold text-gray-400">Pending</p>
                        </div>
                    </div>
                </div>
                
                <Link 
                    href="/dashboard/orders"
                    class="group w-full flex items-center justify-between p-6 bg-gray-50/30 border-t-2 border-gray-50 hover:bg-gray-50 transition-colors"
                >
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-xl">
                            🧾
                        </div>
                        <span class="font-bold text-[16px] text-gray-700">Semua pesanan</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                </Link>
            </div>

            <!-- Navigation Menu -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] overflow-hidden shadow-[0_12px_40px_rgb(0,0,0,0.03)] divide-y-2 divide-gray-50">
                <Link 
                    v-if="tenant.enabled_modules?.includes('catalog')"
                    href="/dashboard/products"
                    class="group flex items-center justify-between p-6 hover:bg-gray-50 transition-all"
                >
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-xl border border-orange-100/50">
                            📦
                        </div>
                        <span class="font-bold text-[16px] text-gray-700">Daftar produk</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                </Link>

                <Link 
                    v-if="tenant.enabled_modules?.includes('booking')"
                    href="/dashboard/services"
                    class="group flex items-center justify-between p-6 hover:bg-gray-50 transition-all"
                >
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-xl border border-green-100/50">
                            📅
                        </div>
                        <span class="font-bold text-[16px] text-gray-700">Daftar Layanan</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                </Link>

                <Link 
                    href="/dashboard/links"
                    class="group flex items-center justify-between p-6 hover:bg-gray-50 transition-all"
                >
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl border border-blue-100/50">
                            🔗
                        </div>
                        <span class="font-bold text-[16px] text-gray-700">Daftar Links</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                </Link>

                <Link 
                    href="/dashboard/template"
                    class="group flex items-center justify-between p-6 hover:bg-gray-50 transition-all"
                >
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl border border-purple-100/50">
                            🎨
                        </div>
                        <span class="font-bold text-[16px] text-gray-700">Custom Theme</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                </Link>

                <Link 
                    href="/dashboard/settings"
                    class="group flex items-center justify-between p-6 hover:bg-gray-50 transition-all"
                >
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-xl border border-gray-100/50">
                            ⚙️
                        </div>
                        <span class="font-bold text-[16px] text-gray-700">Settings</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all"><path d="m9 18 6-6-6-6"/></svg>
                </Link>
            </div>

            <!-- Upgrade/Subscription Action -->
            <div v-if="tenant.subscription_status !== 'subscribed'" class="mt-4">
                <button 
                    @click="showRequestModal = true"
                    class="w-full py-4 bg-black text-white rounded-[24px] font-bold text-[15px] hover:bg-gray-800 transition-all active:scale-95 shadow-lg shadow-gray-200"
                >
                    🚀 Upgrade Subscription
                </button>
            </div>

            <!-- Footer / Logout -->
            <div class="pt-8 flex flex-col items-center">
                <button 
                    @click="handleLogout"
                    class="px-8 py-3 text-red-500 font-black uppercase tracking-widest text-[13px] hover:bg-red-50 rounded-[10px] transition-all active:scale-95"
                >
                    Logout
                </button>
                <p class="mt-8 text-[11px] font-bold text-gray-300 uppercase tracking-widest">Tempa.dev v1.0</p>
            </div>
        </main>

        <!-- Subscription Request Modal -->
        <SubscriptionRequestModal 
            :show="showRequestModal" 
            :tenant-id="tenant.id"
            :available-plans="availablePlans"
            @close="showRequestModal = false" 
        />
    </div>
</template>

<style scoped>
.transition-all {
    transition-duration: 250ms;
}
</style>
