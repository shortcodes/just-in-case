<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useTrans } from '@/composables/useTrans'

interface Props {
    email: string
    token: string
}

const props = defineProps<Props>()
const trans = useTrans()

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
        :title="trans('auth.reset_password.title')"
        :description="trans('auth.reset_password.description')"
    >
        <Head :title="trans('auth.reset_password.page_title')" />

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">{{ trans('auth.reset_password.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        :placeholder="trans('auth.reset_password.email_placeholder')"
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
                    <Label for="password">{{ trans('auth.reset_password.password') }}</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        :placeholder="trans('auth.reset_password.password_placeholder')"
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
                    <Label for="password_confirmation">{{ trans('auth.reset_password.confirm_password') }}</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        :placeholder="trans('auth.reset_password.confirm_password_placeholder')"
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
                    <span v-if="form.processing">{{ trans('auth.reset_password.resetting') }}</span>
                    <span v-else>{{ trans('auth.reset_password.reset_password') }}</span>
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
