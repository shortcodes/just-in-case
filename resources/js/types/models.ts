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

export interface RecipientViewModel {
    id: number
    email: string
    createdAt: string
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
    deliveryStatus: DeliveryStatus
    interval: string
    intervalDays: number
    lastResetAt: string | null
    nextTriggerAt: string | null
    activatedAt: string | null
    recipients: RecipientViewModel[]
    messageContent: string | null
    attachments: AttachmentViewModel[]
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
    stats: DashboardStatsViewModel
}

/**
 * Types for Create Custodianship View
 */

export interface IntervalOption {
    value: string // ISO 8601 duration
    label: string
    days: number
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
    interval: string // ISO 8601
    recipients: string[] // emails
    attachments: string[] // temp attachment IDs
}

export interface CreateCustodianshipPageProps {
    user: UserViewModel
    intervals: IntervalOption[]
}
