/**
 * Component Props Interfaces
 */

import type { CustodianshipStatus, DeliveryStatus, RecipientViewModel, CustodianshipViewModel } from './models'

export interface StatusBadgeProps {
    status: CustodianshipStatus | 'pending'
    deliveryStatus?: DeliveryStatus | null
}

export interface TimerProgressBarProps {
    nextTriggerAt: string | null
    interval: string
    status: CustodianshipStatus
}

export interface ConfirmableButtonProps {
    label: string
    confirmLabel?: string
    cancelLabel?: string
    disabled?: boolean
    tooltipDisabled?: string
    size?: 'default' | 'sm' | 'lg' | 'icon'
    buttonClass?: string
    confirmButtonClass?: string
    cancelButtonClass?: string
}

export interface RecipientListProps {
    recipients: RecipientViewModel[]
    readonly?: boolean
    maxVisible?: number
}

export interface EmptyStateProps {
    title: string
    description: string
}

export interface EmailVerificationBannerProps {
    userEmail: string
}

export interface ExpiringCustodianshipsBannerProps {
    expiringCount: number
    expiringCustodianships: CustodianshipViewModel[]
}

export interface CustodianshipCardProps {
    custodianship: CustodianshipViewModel
    isResetting?: boolean
    emailVerified?: boolean
}
