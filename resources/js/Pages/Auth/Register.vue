<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    store_name: '',
    whatsapp_number: '',
    phone: '',
});

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Register" />

    <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Create Your Store
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Start selling via WhatsApp in minutes
                </p>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Personal Info -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Your Name *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email *</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Store Info -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Store Information</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Store Name *</label>
                                <input
                                    v-model="form.store_name"
                                    type="text"
                                    required
                                    placeholder="e.g., Kopi Senja"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.store_name }"
                                />
                                <p v-if="form.errors.store_name" class="mt-1 text-sm text-red-600">{{ form.errors.store_name }}</p>
                                <p class="mt-1 text-xs text-gray-500">This will be your store's public name</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">WhatsApp Number *</label>
                                <input
                                    v-model="form.whatsapp_number"
                                    type="tel"
                                    required
                                    placeholder="+6281234567890"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.whatsapp_number }"
                                />
                                <p v-if="form.errors.whatsapp_number" class="mt-1 text-sm text-red-600">{{ form.errors.whatsapp_number }}</p>
                                <p class="mt-1 text-xs text-gray-500">Orders will be sent to this number</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Phone Number (optional)</label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Password</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password *</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.password }"
                                />
                                <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <p class="text-sm text-yellow-800">
                            <strong>Note:</strong> Your store will be pending approval until activated by an administrator.
                            You'll receive an email once approved.
                        </p>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between pt-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-blue-600 hover:text-blue-500"
                        >
                            Already have an account? Sign in
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            Create Store
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
