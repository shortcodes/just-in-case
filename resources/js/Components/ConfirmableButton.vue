<script setup lang="ts">
import { ref, onUnmounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import type { ConfirmableButtonProps } from '@/types/components'

const props = withDefaults(defineProps<ConfirmableButtonProps>(), {
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    disabled: false,
    tooltipDisabled: '',
    size: 'default',
    buttonClass: '',
    confirmButtonClass: '',
    cancelButtonClass: '',
})

const emit = defineEmits<{
    confirm: []
}>()

const isExpanded = ref(false)
let collapseTimeout: number | null = null
let clickOutsideListener: ((e: MouseEvent) => void) | null = null

const handleClick = () => {
    if (props.disabled) return

    isExpanded.value = true

    collapseTimeout = window.setTimeout(() => {
        isExpanded.value = false
    }, 5000)

    clickOutsideListener = (e: MouseEvent) => {
        const target = e.target as HTMLElement
        if (!target.closest('.confirmable-button-wrapper')) {
            collapse()
        }
    }

    // Delay adding the listener to avoid immediate collapse
    setTimeout(() => {
        document.addEventListener('click', clickOutsideListener)
    }, 100)
}

const handleConfirm = () => {
    emit('confirm')
    collapse()
}

const handleCancel = () => {
    collapse()
}

const collapse = () => {
    isExpanded.value = false

    if (collapseTimeout !== null) {
        clearTimeout(collapseTimeout)
        collapseTimeout = null
    }

    if (clickOutsideListener !== null) {
        document.removeEventListener('click', clickOutsideListener)
        clickOutsideListener = null
    }
}

onUnmounted(() => {
    collapse()
})
</script>

<template>
    <div class="confirmable-button-wrapper inline-flex gap-2">
        <TooltipProvider v-if="disabled && tooltipDisabled">
            <Tooltip>
                <TooltipTrigger as-child>
                    <div>
                        <Button
                            v-if="!isExpanded"
                            :disabled="disabled"
                            :size="size"
                            :class="buttonClass"
                            @click="handleClick"
                        >
                            <slot name="icon" />
                            {{ label }}
                        </Button>
                    </div>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{{ tooltipDisabled }}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>

        <Button
            v-else-if="!isExpanded"
            :disabled="disabled"
            :size="size"
            :class="buttonClass"
            @click="handleClick"
        >
            <slot name="icon" />
            {{ label }}
        </Button>

        <template v-if="isExpanded">
            <Button
                variant="default"
                :size="size"
                :class="['bg-green-600 hover:bg-green-700 text-white', confirmButtonClass]"
                @click="handleConfirm"
            >
                {{ confirmLabel }}
            </Button>
            <Button
                variant="outline"
                :size="size"
                :class="cancelButtonClass"
                @click="handleCancel"
            >
                {{ cancelLabel }}
            </Button>
        </template>
    </div>
</template>
