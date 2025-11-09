<script setup lang="ts">
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { useTrans } from '@/composables/useTrans'

defineProps<{
    canLogin?: boolean
    canRegister?: boolean
}>()

const trans = useTrans()
const page = usePage()

const user = computed(() => page.props.auth?.user)
const isAuthenticated = computed(() => !!user.value)
const locale = computed(() => (page.props.locale as string) || 'en')

const userNavigation = computed(() => [
    { name: trans('Custodianships'), href: route('custodianships.index') },
    { name: trans('Profile'), href: route('profile.edit') },
])

const logout = () => {
    router.post(route('logout'))
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white flex flex-col">
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
                        <Menu as="div" class="relative ml-3">
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
                            <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
                            <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
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
                            <div class="text-base font-medium text-gray-800">{{ user?.name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ user?.email }}</div>
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
                    <div class="flex items-center space-x-3">
                        <Link :href="route('login')">
                            <ApplicationLogo class="h-10 w-10" />
                        </Link>
                        <span class="hidden min-[400px]:inline-block text-xl font-bold text-slate-900">{{ trans('Just In Case') }}</span>
                    </div>

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

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

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
