<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useTrans } from '@/composables/useTrans'

interface DeleteAccountModalProps {
    open: boolean
}

const props = defineProps<DeleteAccountModalProps>()

const emit = defineEmits<{
    'update:open': [value: boolean]
}>()

const trans = useTrans()
const isChecked = ref(false)
const passwordInput = ref<HTMLInputElement | null>(null)

const form = useForm({
    password: '',
})

const isDeleteEnabled = computed(() => {
    return isChecked.value && form.password.length > 0
})

watch(() => props.open, (newVal) => {
    if (!newVal) {
        isChecked.value = false
        form.reset()
        form.clearErrors()
    } else {
        setTimeout(() => {
            passwordInput.value?.focus()
        }, 100)
    }
})

const handleConfirm = () => {
    if (isDeleteEnabled.value) {
        form.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => handleOpenChange(false),
            onError: () => passwordInput.value?.focus(),
        })
    }
}

const handleOpenChange = (value: boolean) => {
    emit('update:open', value)
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex items-center space-x-2 text-red-600 mb-2">
                    <ExclamationTriangleIcon class="h-6 w-6" />
                    <DialogTitle class="text-red-600">{{ trans('Delete Account') }}</DialogTitle>
                </div>
                <DialogDescription class="text-gray-600">
                    {{ trans('Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.') }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="flex items-start space-x-2">
                    <Checkbox
                        id="confirm-checkbox"
                        v-model="isChecked"
                    />
                    <Label
                        for="confirm-checkbox"
                        class="text-sm font-normal cursor-pointer leading-tight"
                    >
                        {{ trans('I understand this action is permanent') }}
                    </Label>
                </div>

                <div class="space-y-2">
                    <Label for="password" class="text-sm">
                        {{ trans('Enter your password to confirm:') }}
                    </Label>
                    <Input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        :placeholder="trans('Password')"
                        :class="[
                            'transition-colors',
                            form.password && isDeleteEnabled ? 'border-green-500 focus:border-green-500' : '',
                            form.errors.password ? 'border-red-500 focus:border-red-500' : ''
                        ]"
                        @keydown.enter="handleConfirm"
                    />
                    <p v-if="form.errors.password" class="text-sm text-red-600">
                        {{ form.errors.password }}
                    </p>
                </div>
            </div>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="handleOpenChange(false)"
                >
                    {{ trans('Cancel') }}
                </Button>
                <Button
                    variant="destructive"
                    :disabled="!isDeleteEnabled || form.processing"
                    @click="handleConfirm"
                    class="bg-red-600 hover:bg-red-700"
                >
                    {{ trans('Delete Permanently') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
