<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

interface Props {
    status?: string
}

const props = defineProps<Props>()

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
        title="Verify your email"
        description="Thanks for signing up! Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email Verification" />

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 border border-green-200"
        >
            A new verification link has been sent to your email address.
        </div>

        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">Sending...</span>
                    <span v-else>Resend Verification Email</span>
                </Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-center text-sm underline hover:opacity-80"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
