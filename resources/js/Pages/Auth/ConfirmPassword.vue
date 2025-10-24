<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
    password: '',
})

const submit = (): void => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    })
}
</script>

<template>
    <GuestLayout
        title="Confirm your password"
        description="This is a secure area. Please confirm your password before continuing."
    >
        <Head title="Confirm Password" />

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        placeholder="Enter your password"
                        required
                        autofocus
                        autocomplete="current-password"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.password" class="text-sm font-medium text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>

                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Confirming...</span>
                    <span v-else>Confirm</span>
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
