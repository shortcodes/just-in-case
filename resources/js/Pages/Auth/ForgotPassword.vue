<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

interface Props {
    status?: string
}

const props = defineProps<Props>()

const form = useForm({
    email: '',
})

const submit = (): void => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout
        title="Forgot your password?"
        description="No problem. Just enter your email address and we will send you a password reset link."
    >
        <Head title="Forgot Password" />

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200"
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

                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Sending...</span>
                    <span v-else>Email Password Reset Link</span>
                </Button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm">
            Remember your password?
            <Link
                :href="route('login')"
                class="underline"
            >
                Sign in
            </Link>
        </div>
    </GuestLayout>
</template>
