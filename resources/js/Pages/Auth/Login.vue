<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import type { LoginPageProps } from '@/types/auth'
import { useTrans } from '@/composables/useTrans'

const props = defineProps<LoginPageProps>()
const trans = useTrans()

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = (): void => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head :title="trans('auth.login.page_title')" />

    <GuestLayout
        :title="trans('auth.login.title')"
        :description="trans('auth.login.description')"
    >
        <div
            v-if="status"
            class="rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">{{ trans('auth.login.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        :placeholder="trans('auth.login.email_placeholder')"
                        required
                        autofocus
                        autocomplete="email"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.email" class="text-sm font-medium text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center">
                        <Label for="password">{{ trans('auth.login.password') }}</Label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="ml-auto inline-block text-sm underline"
                        >
                            {{ trans('auth.login.forgot_password') }}
                        </Link>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        :placeholder="trans('auth.login.password_placeholder')"
                        required
                        autocomplete="current-password"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.password" class="text-sm font-medium text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="remember"
                        :checked="form.remember"
                        @update:checked="form.remember = $event"
                    />
                    <Label
                        for="remember"
                        class="text-sm font-normal leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                        {{ trans('auth.login.remember_me') }}
                    </Label>
                </div>

                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">{{ trans('auth.login.signing_in') }}</span>
                    <span v-else>{{ trans('auth.login.sign_in') }}</span>
                </Button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm">
            {{ trans('auth.login.no_account') }}
            <Link
                :href="route('register')"
                class="underline"
            >
                {{ trans('auth.login.sign_up') }}
            </Link>
        </div>
    </GuestLayout>
</template>
