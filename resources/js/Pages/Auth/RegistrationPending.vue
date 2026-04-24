<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    registration: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Head title="Registration Submitted" />

    <div class="min-h-screen bg-slate-100 px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="bg-amber-500 px-8 py-6 text-white">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em]">Account Pending</p>
                    <h1 class="mt-2 text-3xl font-bold">Registration received</h1>
                    <p class="mt-3 text-sm text-amber-50">
                        Your store has been created and is waiting for admin approval before you can sign in.
                    </p>
                </div>

                <div class="space-y-6 px-8 py-8 text-slate-700">
                    <div class="rounded-xl bg-slate-50 p-5">
                        <p class="text-sm text-slate-500">Store</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ registration.store_name }}</p>
                        <p class="mt-4 text-sm text-slate-500">Login email</p>
                        <p class="mt-1 font-medium text-slate-900">{{ registration.email }}</p>
                    </div>

                    <div class="space-y-3 text-sm leading-6">
                        <p>We’ve saved your account details successfully.</p>
                        <p>Once an administrator approves the store, you can sign in with the email and password you just created.</p>
                        <p>If approval is taking longer than expected, contact support or your administrator for help.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Go to login
                        </Link>
                        <Link
                            :href="route('home')"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Back to home
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
