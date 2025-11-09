<script setup lang="ts">
import { ref, toRef } from 'vue'
import ConfirmableButton from '@/Components/ConfirmableButton.vue'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { ArrowPathIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import { useCustodianshipActivation } from '@/composables/useCustodianshipActivation'
import { useTrans } from '@/composables/useTrans'
import type { CustodianshipViewModel } from '@/types/models'

interface Props {
    custodianship: CustodianshipViewModel
    emailVerified?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    emailVerified: false,
})

const trans = useTrans()
const emailTooltipOpen = ref(false)
const recipientMessageTooltipOpen = ref(false)
const isResetting = ref(false)

const {
    canActivate,
    canActivateFromDraft,
    canReset,
    isDraft,
    missingEmailVerification,
    missingRecipientOrMessage,
    handleActivate: activateCustodianship,
    handleReset: resetCustodianship,
} = useCustodianshipActivation(toRef(props, 'custodianship'), toRef(props, 'emailVerified'))

const handleActivate = () => {
    activateCustodianship()
}

const handleReset = () => {
    isResetting.value = true
    resetCustodianship(
        () => {
            isResetting.value = false
        },
        () => {
            isResetting.value = false
        }
    )
}

const toggleEmailTooltip = () => {
    emailTooltipOpen.value = !emailTooltipOpen.value
}

const toggleRecipientMessageTooltip = () => {
    recipientMessageTooltipOpen.value = !recipientMessageTooltipOpen.value
}
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Activate button for drafts only -->
        <ConfirmableButton
            v-if="isDraft"
            data-testid="activate-button"
            :label="trans('Activate')"
            :confirm-label="trans('Confirm Activation')"
            size="default"
            :disabled="!canActivateFromDraft"
            :button-class="canActivateFromDraft ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-400 text-gray-200 cursor-not-allowed'"
            confirm-button-class="bg-green-600 hover:bg-green-700 text-white"
            @confirm="handleActivate"
        />

        <!-- Info icon for email verification -->
        <TooltipProvider v-if="missingEmailVerification" :delayDuration="0">
            <Tooltip :open="emailTooltipOpen" @update:open="emailTooltipOpen = $event">
                <TooltipTrigger as-child>
                    <InformationCircleIcon
                        class="h-5 w-5 text-red-500 cursor-pointer outline-none focus:outline-none select-none hover:text-red-600"
                        @click="toggleEmailTooltip"
                        tabindex="0"
                    />
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ trans('Please confirm your email address to activate') }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <!-- Info icon for recipient/message requirement -->
        <TooltipProvider v-if="missingRecipientOrMessage" :delayDuration="0">
            <Tooltip :open="recipientMessageTooltipOpen" @update:open="recipientMessageTooltipOpen = $event">
                <TooltipTrigger as-child>
                    <InformationCircleIcon
                        class="h-5 w-5 text-red-500 cursor-pointer outline-none focus:outline-none select-none hover:text-red-600"
                        @click="toggleRecipientMessageTooltip"
                        tabindex="0"
                    />
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ trans('You have to have recipient and message to activate this custodianship') }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <!-- Reset button for active custodianships -->
        <ConfirmableButton
            v-if="canReset"
            data-testid="reset-timer-button"
            :label="trans('Reset Timer')"
            size="default"
            :disabled="!canReset || isResetting"
            @confirm="handleReset"
        >
            <template #icon>
                <ArrowPathIcon class="h-4 w-4 mr-1.5" />
            </template>
        </ConfirmableButton>
    </div>
</template>
