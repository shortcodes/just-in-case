<script setup lang="ts">
import { ref } from 'vue'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { EnvelopeIcon } from '@heroicons/vue/24/outline'
import type { EmailVerificationBannerProps } from '@/types/components'
import { useTrans } from '@/composables/useTrans'

defineProps<EmailVerificationBannerProps>()

const emit = defineEmits<{
    resend: []
}>()

const trans = useTrans()
const emailSent = ref(false)

const handleResend = () => {
    emit('resend')
    emailSent.value = true
}
</script>

<template>
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-lg p-4 mb-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
            <div class="flex items-start gap-x-3 sm:gap-x-4 flex-1">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-amber-100">
                        <EnvelopeIcon class="h-5 w-5 text-amber-700" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-amber-900 mb-1">
                        {{ trans('common.email_verification_banner.title') }}
                    </h3>
                    <p class="text-sm text-amber-800" v-html="trans('common.email_verification_banner.message').replace(':email', userEmail)">
                    </p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:self-center">
                <p v-if="emailSent" class="text-sm text-green-700 font-medium">
                    {{ trans('common.email_verification_banner.verification_sent') }}
                </p>
                <Button
                    variant="outline"
                    size="sm"
                    class="w-full sm:w-auto border-amber-300 bg-white text-amber-700 hover:bg-amber-50 hover:border-amber-400"
                    @click="handleResend"
                >
                    {{ emailSent ? trans('common.email_verification_banner.resend') : trans('common.email_verification_banner.send_email') }}
                </Button>
            </div>
        </div>
    </div>
</template>
