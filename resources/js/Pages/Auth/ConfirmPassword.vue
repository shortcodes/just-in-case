<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useTrans } from '@/composables/useTrans'

const trans = useTrans()

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
        :title="trans('auth.confirm_password.title')"
        :description="trans('auth.confirm_password.description')"
    >
        <Head :title="trans('auth.confirm_password.page_title')" />

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="password">{{ trans('auth.confirm_password.password') }}</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        :placeholder="trans('auth.confirm_password.password_placeholder')"
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
                    <span v-if="form.processing">{{ trans('auth.confirm_password.confirming') }}</span>
                    <span v-else>{{ trans('auth.confirm_password.confirm') }}</span>
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
