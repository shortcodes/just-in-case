<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

interface Props {
    email: string
    token: string
}

const props = defineProps<Props>()

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = (): void => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout
        title="Reset your password"
        description="Enter your email and new password to reset your password"
    >
        <Head title="Reset Password" />

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
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        placeholder="Enter your new password"
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
                        placeholder="Confirm your new password"
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
                    <span v-if="form.processing">Resetting...</span>
                    <span v-else>Reset Password</span>
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
