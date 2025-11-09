<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { useTrans } from '@/composables/useTrans';

defineProps<{
    canLogin: boolean;
    canRegister: boolean;
}>();

const trans = useTrans();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);
const locale = computed(() => (page.props.locale as string) || 'en');

const userNavigation = computed(() => [
    { name: trans('Custodianships'), href: route('custodianships.index') },
    { name: trans('Profile'), href: route('profile.edit') },
]);

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <Head :title="trans('Just In Case')" />

    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
        <!-- Header / Navigation - Authenticated -->
        <Disclosure
            v-if="isAuthenticated"
            as="nav"
            class="border-b border-gray-200 bg-white sticky top-0 z-50"
            v-slot="{ open }"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center space-x-3">
                            <Link :href="route('custodianships.index')">
                                <ApplicationLogo class="h-9 w-auto fill-current text-gray-800" />
                            </Link>
                            <span class="hidden min-[400px]:inline-block text-xl font-bold text-slate-900">{{ trans('Just In Case') }}</span>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <Menu
                            as="div"
                            class="relative ml-3"
                        >
                            <MenuButton class="relative flex max-w-xs items-center rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                <span class="absolute -inset-1.5" />
                                <span class="sr-only">Open user menu</span>
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-600">
                                            {{ user?.name?.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ user?.name }}</span>
                                </div>
                            </MenuButton>

                            <transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none">
                                    <MenuItem
                                        v-for="item in userNavigation"
                                        :key="item.name"
                                        v-slot="{ active }"
                                    >
                                        <Link
                                            :href="item.href"
                                            :class="[
                                                active ? 'bg-gray-100' : '',
                                                'block px-4 py-2 text-sm text-gray-700'
                                            ]"
                                        >
                                            {{ item.name }}
                                        </Link>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            @click="logout"
                                            :class="[
                                                active ? 'bg-gray-100' : '',
                                                'block w-full text-left px-4 py-2 text-sm text-gray-700'
                                            ]"
                                        >
                                            {{ trans('Log Out') }}
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">
                        <DisclosureButton class="relative inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <span class="absolute -inset-0.5" />
                            <span class="sr-only">Open main menu</span>
                            <Bars3Icon
                                v-if="!open"
                                class="block h-6 w-6"
                                aria-hidden="true"
                            />
                            <XMarkIcon
                                v-else
                                class="block h-6 w-6"
                                aria-hidden="true"
                            />
                        </DisclosureButton>
                    </div>
                </div>
            </div>

            <DisclosurePanel class="sm:hidden">
                <div class="border-t border-gray-200 pb-3 pt-4">
                    <div class="flex items-center px-4">
                        <div class="shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-base font-medium text-gray-600">
                                    {{ user?.name?.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">
                                {{ user?.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ user?.email }}
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <DisclosureButton
                            v-for="item in userNavigation"
                            :key="item.name"
                            as="a"
                            :href="item.href"
                            class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        >
                            {{ item.name }}
                        </DisclosureButton>
                        <DisclosureButton
                            as="button"
                            @click="logout"
                            class="block w-full text-left px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                        >
                            {{ trans('Log Out') }}
                        </DisclosureButton>
                    </div>
                </div>
            </DisclosurePanel>
        </Disclosure>

        <!-- Header / Navigation - Guest -->
        <header v-else class="border-b border-slate-200 bg-white/80 backdrop-blur-sm sticky top-0 z-50">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <ApplicationLogo class="h-10 w-10" />
                        <span class="hidden min-[400px]:inline-block text-xl font-bold text-slate-900">{{ trans('Just In Case') }}</span>
                    </div>

                    <!-- Auth Links -->
                    <div v-if="canLogin" class="flex items-center space-x-4">
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors"
                        >
                            {{ trans('Log in') }}
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-all hover:shadow-md"
                        >
                            {{ trans('Create Account') }}
                        </Link>
                    </div>
                </div>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="relative isolate overflow-hidden">
            <!-- Background Image -->
            <img
                src="/img_1.jpg"
                alt=""
                class="absolute inset-0 -z-10 size-full object-cover opacity-15"
            />

            <!-- Top Gradient Blob -->
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-indigo-200 to-purple-300 opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>

            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl py-20 sm:py-28 lg:py-32">
                    <!-- Disclaimer Badge -->
                    <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                        <div class="relative rounded-full px-4 py-1.5 text-sm text-slate-600 ring-1 ring-slate-900/10 hover:ring-slate-900/20 bg-white/50 backdrop-blur-sm">
                            <svg class="inline-block h-4 w-4 mr-2 -mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                            </svg>
                            {{ trans('This is not a legal will, just an automatic message delivery system') }}
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="text-center">
                        <h1 class="text-balance text-5xl font-bold tracking-tight text-slate-900 sm:text-7xl" v-html="trans('Secure access to critical information')"></h1>
                        <p class="mt-8 text-pretty text-lg font-medium text-slate-600 sm:text-xl/8">
                            {{ trans('Automatic message delivery system in case of death, accident or disappearance. Transfer passwords, documents and final wishes to selected people – safely and automatically.') }}
                        </p>
                        <div class="mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-4">
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-indigo-600 px-8 py-3 text-base font-semibold text-white shadow-lg hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all hover:shadow-xl"
                            >
                                {{ trans('Create Free Account') }}
                            </Link>
                            <a href="#how-it-works" class="text-base font-semibold text-slate-900 hover:text-indigo-600 transition-colors">
                                {{ trans('How does it work?') }} <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Gradient Blob -->
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-indigo-200 to-purple-300 opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="bg-white py-16 sm:py-20">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base/7 font-semibold text-indigo-600">{{ trans('Simple Process') }}</h2>
                    <p class="mt-2 text-pretty text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl lg:text-balance">
                        {{ trans('How does it work?') }}
                    </p>
                    <p class="mt-6 text-lg/8 text-slate-600">
                        {{ trans('Automated way to pass critical information to your loved ones in case of unforeseen situations') }}
                    </p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                        <!-- Step 1 -->
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-slate-900">
                                <div class="absolute left-0 top-0 flex size-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                </div>
                                {{ trans('Create a trust') }}
                            </dt>
                            <dd class="mt-2 text-base/7 text-slate-600">
                                {{ trans('Write a message, add attachments (up to 10MB) and select recipients. Set a time interval after which the message will be sent (e.g. 90 days).') }}
                            </dd>
                        </div>

                        <!-- Step 2 -->
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-slate-900">
                                <div class="absolute left-0 top-0 flex size-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                {{ trans('Reset timer regularly') }}
                            </dt>
                            <dd class="mt-2 text-base/7 text-slate-600">
                                {{ trans('Confirm with one click that everything is fine. The system will send a reminder before the timer expires, giving you time to react.') }}
                            </dd>
                        </div>

                        <!-- Step 3 -->
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-slate-900">
                                <div class="absolute left-0 top-0 flex size-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                {{ trans('Automatic delivery') }}
                            </dt>
                            <dd class="mt-2 text-base/7 text-slate-600">
                                {{ trans('If you stop resetting the timer, the system will automatically send an encrypted message with attachments to selected recipients.') }}
                            </dd>
                        </div>

                        <!-- CTA Step -->
                        <div class="relative pl-16">
                            <dt class="text-base/7 font-semibold text-slate-900">
                                <div class="absolute left-0 top-0 flex size-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                    </svg>
                                </div>
                                {{ trans('Start now') }}
                            </dt>
                            <dd class="mt-2 text-base/7 text-slate-600">
                                {{ trans('Setup takes less than 5 minutes. Create a free account and secure your information today.') }}
                                <div class="mt-4">
                                    <Link
                                        v-if="canRegister"
                                        :href="route('register')"
                                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                                    >
                                        {{ trans('Create Account') }} <span aria-hidden="true">→</span>
                                    </Link>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <!-- Problem/Solution Section -->
        <section class="bg-white">
            <div class="relative isolate overflow-hidden bg-slate-900 px-6 py-20 lg:px-16 lg:py-28 xl:px-24">
                <div class="mx-auto grid max-w-2xl grid-cols-1 gap-x-12 gap-y-16 lg:mx-0 lg:max-w-none lg:grid-cols-2 lg:gap-x-20">
                    <div class="lg:max-w-xl lg:pr-8">
                        <h2 class="text-balance text-4xl font-bold tracking-tight text-white sm:text-5xl">
                            {{ trans('The problem we solve') }}
                        </h2>
                        <p class="mt-6 text-xl/8 text-slate-300">
                            {{ trans('In case of death, accident or disappearance, loved ones often do not have access to critical information') }}
                        </p>
                    </div>

                    <div>
                        <dl class="max-w-xl space-y-8 text-lg/8 text-slate-300">
                            <div class="relative">
                                <dt class="ml-11 inline-block font-semibold text-white text-lg">
                                    <svg class="absolute left-0 top-1 size-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ trans('Simple') }}
                                </dt>
                                {{ ' ' }}
                                <dd class="inline">{{ trans('No lawyers, no complicated procedures') }}</dd>
                            </div>
                            <div class="relative">
                                <dt class="ml-11 inline-block font-semibold text-white text-lg">
                                    <svg class="absolute left-0 top-1 size-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                                    </svg>
                                    {{ trans('Automatic') }}
                                </dt>
                                {{ ' ' }}
                                <dd class="inline">{{ trans('The system works without your intervention') }}</dd>
                            </div>
                            <div class="relative">
                                <dt class="ml-11 inline-block font-semibold text-white text-lg">
                                    <svg class="absolute left-0 top-1 size-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                    {{ trans('Secure') }}
                                </dt>
                                {{ ' ' }}
                                <dd class="inline">{{ trans('Data and attachment encryption') }}</dd>
                            </div>
                            <div class="relative">
                                <dt class="ml-11 inline-block font-semibold text-white text-lg">
                                    <svg class="absolute left-0 top-1 size-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                    </svg>
                                    {{ trans('Fast') }}
                                </dt>
                                {{ ' ' }}
                                <dd class="inline">{{ trans('Setup under 5 minutes') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Background gradient blob -->
                <div class="pointer-events-none absolute left-12 top-1/2 -z-10 -translate-y-1/2 transform-gpu blur-3xl lg:-bottom-48 lg:top-auto lg:translate-y-0" aria-hidden="true">
                    <div class="aspect-[1155/678] w-[72.1875rem] bg-gradient-to-tr from-indigo-400 to-purple-400 opacity-20" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
                </div>
            </div>
        </section>

        <!-- For Whom Section -->
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-indigo-50 via-purple-50 to-blue-50 py-24 px-6 text-center border-y border-indigo-100">
            <div class="mx-auto max-w-2xl">
                <h2 class="text-base/7 font-semibold text-indigo-600">{{ trans('For everyone') }}</h2>
                <p class="mt-2 text-balance text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">
                    {{ trans('Who is Just In Case for?') }}
                </p>
                <p class="mt-6 max-w-xl mx-auto text-pretty text-lg/8 text-slate-600">
                    {{ trans('Perfect for anyone who wants to secure access to important information for their loved ones – from freelancers with digital assets to parents caring for their family\'s future.') }}
                </p>
            </div>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-4">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all"
                >
                    {{ trans('Create Account Now') }}
                </Link>
                <a href="#how-it-works" class="text-sm/6 font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                    {{ trans('How does it work?') }}
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <!-- Background gradient circle -->
            <svg viewBox="0 0 1024 1024" class="absolute left-1/2 top-1/2 -z-10 size-[64rem] -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)]" aria-hidden="true">
                <circle cx="512" cy="512" r="512" fill="url(#gradient-for-whom)" fill-opacity="0.2" />
                <defs>
                    <radialGradient id="gradient-for-whom">
                        <stop stop-color="#6366f1" />
                        <stop offset="1" stop-color="#a855f7" />
                    </radialGradient>
                </defs>
            </svg>
        </section>

        <!-- Pricing Section -->
        <section class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center mb-16">
                    <h2 class="text-base/7 font-semibold text-indigo-600">{{ trans('No commitment') }}</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        {{ trans('Start for free') }}
                    </p>
                    <p class="mt-4 text-lg text-slate-600">
                        {{ trans('Free plan with all key features') }}
                    </p>
                </div>

                <div class="mx-auto max-w-md">
                    <div class="relative rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-900/10">
                        <!-- Badge -->
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-slate-900">{{ trans('Free Plan') }}</h3>
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 ring-1 ring-inset ring-indigo-600/20">
                                {{ trans('Get started free') }}
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="mb-8">
                            <div class="flex items-baseline">
                                <span class="text-5xl font-bold tracking-tight text-slate-900">{{ trans('$0') }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ trans('Forever free') }}</p>
                        </div>

                        <!-- CTA Button -->
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="block w-full text-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all mb-8"
                        >
                            {{ trans('Create Account Now') }}
                        </Link>

                        <!-- Features List -->
                        <ul role="list" class="space-y-3 text-sm text-slate-600">
                            <li class="flex items-start gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ trans('Up to 3 trusts') }}</span>
                            </li>
                            <li class="flex items-start gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ trans('Up to 10MB attachments per trust') }}</span>
                            </li>
                            <li class="flex items-start gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ trans('Up to 2 recipients per trust') }}</span>
                            </li>
                            <li class="flex items-start gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ trans('Encryption of data at-rest') }}</span>
                            </li>
                            <li class="flex items-start gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ trans('Automatic delivery of messages') }}</span>
                            </li>
                            <li class="flex items-start gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ trans('Email reminders before expiration') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-indigo-600 via-blue-600 to-purple-600 py-24 px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    {{ trans('Ready to secure your information?') }}
                </h2>
                <p class="mt-6 text-lg leading-8 text-indigo-100">
                    {{ trans('Join people who have taken care of their loved ones\' future. Setup takes less than 5 minutes.') }}
                </p>
                <div class="mt-10">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-lg bg-white px-8 py-3 text-base font-semibold text-indigo-600 shadow-lg hover:bg-indigo-50 transition-all hover:shadow-xl inline-block"
                    >
                        {{ trans('Create Free Account') }}
                    </Link>
                </div>
            </div>

            <!-- Background decorative elements -->
            <svg viewBox="0 0 1024 1024" class="absolute left-1/2 top-1/2 -z-10 size-[64rem] -translate-x-1/2 -translate-y-1/2 [mask-image:radial-gradient(closest-side,white,transparent)]" aria-hidden="true">
                <circle cx="512" cy="512" r="512" fill="url(#gradient-final-cta)" fill-opacity="0.3" />
                <defs>
                    <radialGradient id="gradient-final-cta">
                        <stop stop-color="#93c5fd" />
                        <stop offset="1" stop-color="#c084fc" />
                    </radialGradient>
                </defs>
            </svg>

            <!-- Additional decorative blobs -->
            <div class="absolute -top-24 right-0 -z-10 transform-gpu blur-3xl" aria-hidden="true">
                <div class="aspect-[1404/767] w-[87.75rem] bg-gradient-to-r from-purple-400 to-blue-400 opacity-25" style="clip-path: polygon(73.6% 51.7%, 91.7% 11.8%, 100% 46.4%, 97.4% 82.2%, 92.5% 84.9%, 75.7% 64%, 55.3% 47.5%, 46.5% 49.4%, 45% 62.9%, 50.3% 87.2%, 21.3% 64.1%, 0.1% 100%, 5.4% 51.1%, 21.4% 63.9%, 58.9% 0.2%, 73.6% 51.7%)" />
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between space-y-4 md:flex-row md:space-y-0">
                    <div class="flex items-center space-x-3">
                        <ApplicationLogo class="h-8 w-8 brightness-0 invert" />
                        <span class="text-lg font-semibold text-white">{{ trans('Just In Case') }}</span>
                    </div>

                    <p class="text-sm text-slate-400">
                        {{ trans('© 2025 Just In Case. All rights reserved.') }}
                    </p>

                    <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 text-sm text-slate-400">
                        <Link :href="route(`legal.privacy.${locale}`)" class="hover:text-white transition-colors">{{ trans('Privacy Policy') }}</Link>
                        <span class="text-slate-600">|</span>
                        <Link :href="route(`legal.terms.${locale}`)" class="hover:text-white transition-colors">{{ trans('Terms of Service') }}</Link>
                        <span class="text-slate-600">|</span>
                        <Link :href="route(`legal.disclaimer.${locale}`)" class="hover:text-white transition-colors">{{ trans('Legal Disclaimer') }}</Link>
                    </div>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-800 text-center">
                    <p class="text-sm text-slate-500">
                        {{ trans('Just In Case is an automatic message delivery system. It is not a will or a legal service.') }}
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
