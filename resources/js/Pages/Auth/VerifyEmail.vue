<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useTrans } from '@/composables/useTrans'

interface Props {
    status?: string
}

const props = defineProps<Props>()
const trans = useTrans()

const form = useForm({})

const submit = (): void => {
    form.post(route('verification.send'))
}

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
)
</script>

<template>
    <GuestLayout
        :title="trans('auth.verify_email.title')"
        :description="trans('auth.verify_email.description')"
    >
        <Head :title="trans('auth.verify_email.page_title')" />

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200"
        >
            {{ trans('auth.verify_email.link_sent') }}
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">{{ trans('auth.verify_email.sending') }}</span>
                    <span v-else>{{ trans('auth.verify_email.resend') }}</span>
                </Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-center text-sm underline hover:opacity-80"
                >
                    {{ trans('auth.verify_email.logout') }}
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
