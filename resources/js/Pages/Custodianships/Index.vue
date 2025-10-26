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
import { parseIntervalToDays } from '@/composables/useInterval'
import dayjs from 'dayjs'

const props = defineProps<CustodianshipsIndexPageProps>()

// Local state
const isResetting = ref<Record<number, boolean>>({})
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
        alert('You have reached the limit of 3 custodianships in the free plan. Delete an existing one to create a new one.')
        return
    }

    router.visit(route('custodianships.create'))
}

const handleReset = (custodianshipId: number) => {
    isResetting.value[custodianshipId] = true

    router.post(route('custodianships.reset', custodianshipId), {}, {
        preserveState: false,
        onSuccess: () => {
            isResetting.value[custodianshipId] = false
        },
        onError: () => {
            isResetting.value[custodianshipId] = false
        }
    })
}

const handleResendVerification = () => {
    isResendingVerification.value = true

    // In production, this would be a POST request
    // router.post(route('verification.send'), {}, {
    //     onSuccess: () => {
    //         isResendingVerification.value = false
    //     },
    //     onError: () => {
    //         isResendingVerification.value = false
    //     }
    // })

    // For development, simulate success
    setTimeout(() => {
        isResendingVerification.value = false
        alert('Verification email sent!')
    }, 1000)
}

const handleResetAll = () => {
    if (confirm(`Are you sure you want to reset ${expiringCustodianships.value.length} custodianships?`)) {
        expiringCustodianships.value.forEach(c => handleReset(c.id))
    }
}

const handleActivate = (custodianshipId: number) => {
    // In production, this would be a POST request
    // router.post(route('custodianships.activate', custodianshipId), {}, {
    //     onSuccess: () => {
    //         // Custodianship activated
    //     },
    //     onError: () => {
    //         // Handle error
    //     }
    // })

    // For development, simulate optimistic update
    const custodianship = props.custodianships.find(c => c.id === custodianshipId)
    if (custodianship) {
        custodianship.status = 'active'
        custodianship.activatedAt = dayjs().toISOString()
        custodianship.lastResetAt = dayjs().toISOString()
        const intervalDays = parseIntervalToDays(custodianship.interval)
        custodianship.nextTriggerAt = dayjs().add(intervalDays, 'day').toISOString()
    }
}
</script>

<template>
    <Head title="My Custodianships" />

    <AuthenticatedLayout>
        <!-- Breadcrumbs -->
        <Breadcrumbs
            :items="[
                { label: 'Custodianships' }
            ]"
        />

<!--        &lt;!&ndash; Email Verification Banner &ndash;&gt;-->
<!--        <EmailVerificationBanner-->
<!--            v-if="showEmailBanner"-->
<!--            :user-email="props.user.email"-->
<!--            @resend="handleResendVerification"-->
<!--        />-->

        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                        My Custodianships
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage your secure messages and time-based deliveries
                    </p>
                </div>
                <Button
                    @click="handleCreateNew"
                    :disabled="!canCreateNew"
                    size="lg"
                    class="w-full sm:w-auto shrink-0"
                >
                    Create New
                </Button>
            </div>
        </div>

        <!-- Empty State -->
        <EmptyState
            v-if="isEmpty"
            title="No custodianships yet"
            description="Create your first custodianship to secure important information for your loved ones."
        >
            <template #icon>
                <InboxIcon class="h-12 w-12 text-gray-400" />
            </template>
            <template #action>
                <Button
                    size="lg"
                    @click="handleCreateNew"
                >
                    Create Your First Custodianship
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
                :is-resetting="isResetting[custodianship.id] || false"
                :email-verified="props.user.emailVerified"
                @reset="handleReset"
                @activate="handleActivate"
            />
        </div>
    </AuthenticatedLayout>
</template>
