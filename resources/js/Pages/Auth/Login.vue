<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import type { LoginFormData, LoginPageProps } from '@/types/auth'

const props = defineProps<LoginPageProps>()

const form = useForm<LoginFormData>({
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
    <Head title="Log in" />

    <GuestLayout
        title="Sign in"
        description="Enter your email below to sign in to your account"
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
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="Enter your email"
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
                        <Label for="password">Password</Label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="ml-auto inline-block text-sm underline"
                        >
                            Forgot your password?
                        </Link>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        placeholder="Enter your password"
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
                        Remember me
                    </Label>
                </div>

                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Signing in...</span>
                    <span v-else>Sign in</span>
                </Button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm">
            Don't have an account?
            <Link
                :href="route('register')"
                class="underline"
            >
                Sign up
            </Link>
        </div>
    </GuestLayout>
</template>
