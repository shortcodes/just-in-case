/**
 * ViewModels for Custodianships Index
 */

export type CustodianshipStatus = 'draft' | 'active' | 'completed'
export type DeliveryStatus = 'pending' | 'sent' | 'delivered' | 'failed' | 'bounced' | null

export interface UserViewModel {
    id: number
    name: string
    email: string
    emailVerified: boolean
    emailVerifiedAt: string | null
    createdAt: string
}

export interface DeliveryViewModel {
    id: number
    status: 'pending' | 'delivered' | 'failed'
    mailgunMessageId: string | null
    recipientEmail: string
    deliveredAt: string | null
    createdAt: string
    updatedAt: string
}

export interface RecipientViewModel {
    id: number
    email: string
    createdAt: string
    latestDelivery?: DeliveryViewModel | null
}

export interface AttachmentViewModel {
    id: number
    name: string
    fileName: string
    size: number
    mimeType: string
    createdAt: string
}

export interface CustodianshipViewModel {
    id: number
    uuid: string
    name: string
    status: CustodianshipStatus
    interval: string
    lastResetAt: string | null
    nextTriggerAt: string | null
    activatedAt: string | null
    deliveryStatus?: 'delivered' | 'failed' | 'bounced' | null
    recipientsCount?: number
    attachmentsCount?: number
    recipients?: RecipientViewModel[]
    messageContent?: string | null
    attachments?: AttachmentViewModel[]
    createdAt: string
    updatedAt: string
}

export interface DashboardStatsViewModel {
    totalCount: number
    draftCount: number
    activeCount: number
    completedCount: number
    failedCount: number
    expiringCount: number
}

export interface CustodianshipsIndexPageProps {
    user: UserViewModel
    custodianships: CustodianshipViewModel[]
    stats?: DashboardStatsViewModel
}

/**
 * Types for Create Custodianship View
 */

export interface IntervalUnitOption {
    value: string
    label: string
}

export interface TempAttachment {
    id: string // temporary UUID
    name: string
    size: number
    mimeType: string
    tempPath: string
    uploadProgress: number // 0-100
}

export interface CreateCustodianshipFormData {
    name: string
    messageContent: string | null
    intervalValue: number
    intervalUnit: string
    recipients: string[] // emails
    attachments: string[] // temp attachment IDs
}

export interface CreateCustodianshipPageProps {
    user: UserViewModel
    intervalUnits: IntervalUnitOption[]
}

export interface EditCustodianshipPageProps {
    user: UserViewModel
    custodianship: CustodianshipViewModel
    intervalUnits: IntervalUnitOption[]
}

/**
 * Types for Show Custodianship View
 */

export type ResetMethod = 'manual_button' | 'post_edit_modal'

export interface CustodianshipDetailViewModel extends CustodianshipViewModel {
    user: {
        id: number
        name: string
    }
    resetCount?: number
}

export interface ResetLogViewModel {
    id: number
    resetMethod: ResetMethod
    ipAddress: string
    userAgent: string
    createdAt: string
}

export interface ShowCustodianshipPageProps {
    user: UserViewModel
    custodianship: CustodianshipDetailViewModel
    resetHistory?: ResetLogViewModel[]
}
