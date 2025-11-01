<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { useTrans } from '@/composables/useTrans'

const trans = useTrans()
const passwordInput = ref<HTMLInputElement | null>(null)
const currentPasswordInput = ref<HTMLInputElement | null>(null)

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
})

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation')
                passwordInput.value?.focus()
            }
            if (form.errors.current_password) {
                form.reset('current_password')
                currentPasswordInput.value?.focus()
            }
        },
    })
}
</script>

<template>
    <div class="grid grid-cols-1 gap-x-8 gap-y-10 mt-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
        <div>
            <h2 class="text-base/7 font-semibold text-gray-900">{{ trans('Update Password') }}</h2>
            <p class="mt-1 text-sm/6 text-gray-600">{{ trans('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>

        <form @submit.prevent="updatePassword" class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
            <div class="sm:col-span-4">
                <Label for="current_password">{{ trans('Current Password') }}</Label>
                <div class="mt-2">
                    <Input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        :disabled="form.processing"
                        autocomplete="current-password"
                    />
                </div>
                <p v-if="form.errors.current_password" class="mt-3 text-sm/6 text-destructive">{{ form.errors.current_password }}</p>
            </div>

            <div class="sm:col-span-4">
                <Label for="password">{{ trans('New Password') }}</Label>
                <div class="mt-2">
                    <Input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        :disabled="form.processing"
                        autocomplete="new-password"
                    />
                </div>
                <p v-if="form.errors.password" class="mt-3 text-sm/6 text-destructive">{{ form.errors.password }}</p>
            </div>

            <div class="sm:col-span-4">
                <Label for="password_confirmation">{{ trans('Confirm Password') }}</Label>
                <div class="mt-2">
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        :disabled="form.processing"
                        autocomplete="new-password"
                    />
                </div>
                <p v-if="form.errors.password_confirmation" class="mt-3 text-sm/6 text-destructive">{{ form.errors.password_confirmation }}</p>
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
