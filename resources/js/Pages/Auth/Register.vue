<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Head, Link, useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import type { RegisterPageProps } from '@/types/auth'

const props = defineProps<RegisterPageProps>()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const submit = (): void => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Create your account" />

    <GuestLayout
        title="Create your account"
        description="Enter your details below to create your account"
    >
        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        v-model="form.name"
                        placeholder="Enter your full name"
                        required
                        autofocus
                        autocomplete="name"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.name" class="text-sm font-medium text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="Enter your email"
                        required
                        autocomplete="email"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.email" class="text-sm font-medium text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        placeholder="Enter your password"
                        required
                        autocomplete="new-password"
                        :disabled="form.processing"
                        minlength="8"
                    />
                    <p v-if="form.errors.password" class="text-sm font-medium text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        placeholder="Confirm your password"
                        required
                        autocomplete="new-password"
                        :disabled="form.processing"
                        minlength="8"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-sm font-medium text-destructive">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Creating account...</span>
                    <span v-else>Create Account</span>
                </Button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm">
            Already have an account?
            <Link
                :href="route('login')"
                class="underline"
            >
                Sign in
            </Link>
        </div>
    </GuestLayout>
</template>
