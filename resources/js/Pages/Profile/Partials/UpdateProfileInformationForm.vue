<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useTrans } from '@/composables/useTrans'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})

const trans = useTrans()
const user = usePage().props.auth.user

const form = useForm({
    name: user.name,
    email: user.email,
})
</script>

<template>
    <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-t border-b border-gray-900/10 pt-10 pb-12 md:grid-cols-3">
        <div>
            <h2 class="text-base/7 font-semibold text-gray-900">{{ trans('Profile Information') }}</h2>
            <p class="mt-1 text-sm/6 text-gray-600">{{ trans('Update your account\'s profile information and email address.') }}</p>
        </div>

        <form @submit.prevent="form.patch(route('profile.update'))" class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
            <div class="sm:col-span-4">
                <Label for="name">{{ trans('Name') }}</Label>
                <div class="mt-2">
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        :disabled="form.processing"
                        autocomplete="name"
                        autofocus
                    />
                </div>
                <p v-if="form.errors.name" class="mt-3 text-sm/6 text-destructive">{{ form.errors.name }}</p>
            </div>

            <div class="sm:col-span-4">
                <Label for="email">{{ trans('Email') }}</Label>
                <div class="mt-2">
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :disabled="form.processing"
                        autocomplete="username"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-3 text-sm/6 text-destructive">{{ form.errors.email }}</p>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="sm:col-span-4">
                <p class="text-sm text-gray-800">
                    {{ trans('Your email address is unverified.') }}
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        {{ trans('Click here to re-send the verification email.') }}
                    </Link>
                </p>

                <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                    {{ trans('A new verification link has been sent to your email address.') }}
                </div>
            </div>

            <div class="sm:col-span-4 flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? trans('Saving...') : trans('Save') }}
                </Button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">
                        {{ trans('Saved.') }}
                    </p>
                </Transition>
            </div>
        </form>
    </div>
</template>
