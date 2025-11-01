import { router } from '@inertiajs/vue3'
import { computed, ref, type Ref } from 'vue'
import type { CustodianshipViewModel } from '@/types/models'
import dayjs from '@/plugins/dayjs'

export function useCustodianshipActions(
    custodianship: Ref<CustodianshipViewModel> | CustodianshipViewModel,
    emailVerified?: Ref<boolean> | boolean
) {
    const custodianshipRef = 'value' in custodianship ? custodianship : ref(custodianship)
    const emailVerifiedRef = emailVerified !== undefined
        ? (typeof emailVerified === 'boolean' ? ref(emailVerified) : emailVerified)
        : ref(true)

    const isDraft = computed(() => custodianshipRef.value.status === 'draft')
    const isCompleted = computed(() => custodianshipRef.value.status === 'completed')
    const isActive = computed(() => custodianshipRef.value.status === 'active')

    const isExpired = computed(() => {
        if (!custodianshipRef.value.nextTriggerAt) return false
        return dayjs(custodianshipRef.value.nextTriggerAt).isBefore(dayjs())
    })

    const hasRecipients = computed(() => {
        const count = custodianshipRef.value.recipientsCount
        if (count !== undefined) {
            return count > 0
        }

        const recipients = custodianshipRef.value.recipients
        if (recipients !== undefined) {
            return recipients.length > 0
        }

        return false
    })

    const hasMessage = computed(() => {
        const content = custodianshipRef.value.messageContent
        return content !== undefined && content !== null && content.trim().length > 0
    })

    const canActivateFromDraft = computed(() => {
        return isDraft.value && emailVerifiedRef.value && hasRecipients.value && hasMessage.value
    })

    const canActivate = computed(() => {
        return canActivateFromDraft.value || (isActive.value && isExpired.value)
    })

    const canReset = computed(() => {
        return isActive.value && !isExpired.value
    })

    const missingEmailVerification = computed(() => {
        return isDraft.value && !emailVerifiedRef.value
    })

    const missingRecipientOrMessage = computed(() => {
        return isDraft.value && (!hasRecipients.value || !hasMessage.value)
    })

    const handleActivate = (onSuccess?: () => void, onError?: () => void) => {
        router.post(
            route('custodianships.activate', custodianshipRef.value.uuid),
            {},
            {
                preserveScroll: true,
                preserveState: false,
                onSuccess,
                onError,
            }
        )
    }

    const handleReset = (onSuccess?: () => void, onError?: () => void) => {
        router.post(
            route('custodianships.reset', custodianshipRef.value.uuid),
            {},
            {
                preserveState: false,
                onSuccess,
                onError,
            }
        )
    }

    return {
        canActivate,
        canActivateFromDraft,
        canReset,
        isDraft,
        isActive,
        isCompleted,
        isExpired,
        missingEmailVerification,
        missingRecipientOrMessage,
        handleActivate,
        handleReset,
    }
}

export { useCustodianshipActions as useCustodianshipActivation }
