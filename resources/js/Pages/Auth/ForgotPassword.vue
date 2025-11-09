<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useTrans } from '@/composables/useTrans'

interface Props {
    status?: string
}

const props = defineProps<Props>()
const trans = useTrans()

const form = useForm({
    email: '',
})

const submit = (): void => {
    form.post(route('password.email'))
}
</script>

<template>
    <GuestLayout
        :title="trans('auth.forgot_password.title')"
        :description="trans('auth.forgot_password.description')"
    >
        <Head :title="trans('auth.forgot_password.page_title')" />

        <div
            v-if="status"
            class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="email">{{ trans('auth.forgot_password.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        :placeholder="trans('auth.forgot_password.email_placeholder')"
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
                    <span v-if="form.processing">{{ trans('auth.forgot_password.sending') }}</span>
                    <span v-else>{{ trans('auth.forgot_password.send_link') }}</span>
                </Button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm">
            {{ trans('auth.forgot_password.remember_password') }}
            <Link
                :href="route('login')"
                class="underline"
            >
                {{ trans('auth.forgot_password.sign_in') }}
            </Link>
        </div>
    </GuestLayout>
</template>
