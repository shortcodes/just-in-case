<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { InboxIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import EmailVerificationBanner from '@/Components/EmailVerificationBanner.vue'
import ExpiringCustodianshipsBanner from '@/Components/ExpiringCustodianshipsBanner.vue'
import CustodianshipCard from '@/Components/CustodianshipCard.vue'
import EmptyState from '@/Components/EmptyState.vue'
import type { CustodianshipsIndexPageProps } from '@/types/models'
import { useTrans } from '@/composables/useTrans'
import dayjs from '@/plugins/dayjs'

const props = defineProps<CustodianshipsIndexPageProps>()
const trans = useTrans()

// Local state
const isResendingVerification = ref(false)

// Computed properties
const showEmailBanner = computed(() => {
    return !props.user.emailVerified
})

const showExpiringBanner = computed(() => {
    return props.stats?.expiringCount && props.stats.expiringCount > 0
})

const isEmpty = computed(() => {
    return props.custodianships.length === 0
})

const canCreateNew = computed(() => {
    return props.custodianships.length < 3
})

const expiringCustodianships = computed(() => {
    return props.custodianships.filter(c => {
        if (!c.nextTriggerAt || c.status !== 'active') return false
        const daysRemaining = dayjs(c.nextTriggerAt).diff(dayjs(), 'day', true)
        return daysRemaining < 7 && daysRemaining > 0
    })
})

// Methods
const handleCreateNew = () => {
    if (!canCreateNew.value) {
        // Show limit modal
        alert(trans('You have reached the limit of 3 custodianships in the free plan. Delete an existing one to create a new one.'))
        return
    }

    router.visit(route('custodianships.create'))
}

const handleResendVerification = () => {
    isResendingVerification.value = true

    router.post(route('verification.send'), {}, {
        onSuccess: () => {
            isResendingVerification.value = false
        },
        onError: () => {
            isResendingVerification.value = false
        }
    })

    setTimeout(() => {
        isResendingVerification.value = false
    }, 1000)
}
</script>

<template>
    <Head :title="trans('My Custodianships')" />

    <AuthenticatedLayout>
        <!-- Breadcrumbs -->
        <Breadcrumbs
            :items="[
                { label: trans('Custodianships') }
            ]"
        />

        <EmailVerificationBanner
            v-if="showEmailBanner"
            :user-email="props.user.email"
            @resend="handleResendVerification"
        />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                        {{ trans('My Custodianships') }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ trans('Manage your secure messages and time-based deliveries') }}
                    </p>
                </div>
                <Button
                    @click="handleCreateNew"
                    :disabled="!canCreateNew"
                    size="lg"
                    class="w-full sm:w-auto shrink-0"
                >
                    {{ trans('Create New') }}
                </Button>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="isEmpty"
            :title="trans('No custodianships yet')"
            :description="trans('Create your first custodianship to secure important information for your loved ones.')"
        >
            <template #icon>
                <InboxIcon class="h-12 w-12 text-gray-400" />
            </template>
            <template #action>
                <Button
                    size="lg"
                    @click="handleCreateNew"
                >
                    {{ trans('Create Your First Custodianship') }}
                </Button>
            </template>
        </EmptyState>

        <!-- Custodianship List -->
        <div
            v-else
            class="space-y-4"
        >
            <CustodianshipCard
                v-for="custodianship in props.custodianships"
                :key="custodianship.id"
                :custodianship="custodianship"
                :email-verified="props.user.emailVerified"
            />
        </div>
    </AuthenticatedLayout>
</template>
