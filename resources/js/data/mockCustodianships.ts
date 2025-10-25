/**
 * Mock Data for Custodianships Index Development
 */

import type {
    UserViewModel,
    CustodianshipViewModel,
    DashboardStatsViewModel,
    CustodianshipsIndexPageProps,
    CustodianshipDetailViewModel,
    ResetLogViewModel,
    ShowCustodianshipPageProps
} from '@/types/models'
import dayjs from 'dayjs'

export const mockUser: UserViewModel = {
    id: 1,
    name: 'John Doe',
    email: 'john.doe@example.com',
    emailVerified: false,
    emailVerifiedAt: null,
    createdAt: dayjs().subtract(7, 'days').toISOString(),
}

export const mockCustodianships: CustodianshipViewModel[] = [
    // EXPIRED - will trigger delivery soon
    {
        id: 1,
        uuid: 'a1b2c3d4-e5f6-4a5b-9c8d-7e6f5a4b3c2d',
        name: 'Bank Account Passwords',
        status: 'active',
        deliveryStatus: null,
        interval: 'PT15M', // 15 minutes for testing
        intervalDays: 15 / (24 * 60), // 15 minutes = 0.010416... days
        lastResetAt: dayjs().subtract(20, 'minutes').toISOString(),
        nextTriggerAt: dayjs().subtract(5, 'minutes').toISOString(), // Expired 5 minutes ago
        activatedAt: dayjs().subtract(20, 'minutes').toISOString(),
        recipients: [
            {
                id: 1,
                email: 'spouse@example.com',
                createdAt: dayjs().subtract(20, 'minutes').toISOString(),
            },
            {
                id: 2,
                email: 'sibling@example.com',
                createdAt: dayjs().subtract(20, 'minutes').toISOString(),
            },
        ],
        messageContent: 'Important bank account information and passwords are attached.',
        attachments: [
            {
                id: 1,
                name: 'Bank Accounts.pdf',
                fileName: 'bank-accounts.pdf',
                size: 245760,
                mimeType: 'application/pdf',
                createdAt: dayjs().subtract(20, 'minutes').toISOString(),
            },
        ],
        createdAt: dayjs().subtract(20, 'minutes').toISOString(),
        updatedAt: dayjs().subtract(20, 'minutes').toISOString(),
    },
    // EXPIRING SOON - 5 minutes remaining (watch it count down!)
    {
        id: 2,
        uuid: 'b2c3d4e5-f6a7-4b5c-9d8e-7f6a5b4c3d2e',
        name: 'Crypto Wallet Access',
        status: 'active',
        deliveryStatus: null,
        interval: 'PT10M', // 10 minutes interval
        intervalDays: 10 / (24 * 60), // 10 minutes = 0.006944... days
        lastResetAt: dayjs().subtract(5, 'minutes').toISOString(),
        nextTriggerAt: dayjs().add(1, 'minutes').toISOString(), // 5 minutes left - WATCH IT COUNT DOWN!
        activatedAt: dayjs().subtract(5, 'minutes').toISOString(),
        recipients: [
            {
                id: 3,
                email: 'trusted.friend@example.com',
                createdAt: dayjs().subtract(5, 'minutes').toISOString(),
            },
        ],
        messageContent: 'Cryptocurrency wallet seed phrases and access instructions.',
        attachments: [
            {
                id: 2,
                name: 'Wallet Seeds.txt',
                fileName: 'wallet-seeds.txt',
                size: 1024,
                mimeType: 'text/plain',
                createdAt: dayjs().subtract(5, 'minutes').toISOString(),
            },
        ],
        createdAt: dayjs().subtract(5, 'minutes').toISOString(),
        updatedAt: dayjs().subtract(5, 'minutes').toISOString(),
    },
    // DRAFT - inactive
    {
        id: 3,
        uuid: 'c3d4e5f6-a7b8-4c5d-9e8f-7a6b5c4d3e2f',
        name: 'Social Media Accounts',
        status: 'draft',
        deliveryStatus: null,
        interval: 'P180D',
        intervalDays: 180,
        lastResetAt: null,
        nextTriggerAt: null,
        activatedAt: null,
        recipients: [
            {
                id: 4,
                email: 'family@example.com',
                createdAt: dayjs().subtract(2, 'days').toISOString(),
            },
        ],
        messageContent: 'Access to all my social media accounts and what to do with them.',
        attachments: [],
        createdAt: dayjs().subtract(2, 'days').toISOString(),
        updatedAt: dayjs().subtract(2, 'days').toISOString(),
    },
]

export const mockStats: DashboardStatsViewModel = {
    totalCount: 3,
    draftCount: 1,
    activeCount: 2,
    completedCount: 0,
    failedCount: 0,
    expiringCount: 2, // Both expired and expiring soon
}

export const mockCustodianshipsIndexPageProps: CustodianshipsIndexPageProps = {
    user: mockUser,
    custodianships: mockCustodianships,
    stats: mockStats,
}

/**
 * Mock Data for Show Custodianship View
 */

export const mockCustodianshipDetail: CustodianshipDetailViewModel = {
    ...mockCustodianships[0], // Active custodianship with expired timer
    user: {
        id: 1,
        name: 'John Doe',
    },
    resetCount: 15,
}

export const mockResetHistory: ResetLogViewModel[] = [
    {
        id: 1,
        resetMethod: 'manual_button',
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        createdAt: dayjs().subtract(2, 'days').toISOString(),
    },
    {
        id: 2,
        resetMethod: 'post_edit_modal',
        ipAddress: '192.168.1.1',
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        createdAt: dayjs().subtract(10, 'days').toISOString(),
    },
    {
        id: 3,
        resetMethod: 'manual_button',
        ipAddress: '192.168.1.100',
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X)',
        createdAt: dayjs().subtract(25, 'days').toISOString(),
    },
]

export const mockShowPageProps: ShowCustodianshipPageProps = {
    user: mockUser,
    custodianship: mockCustodianshipDetail,
    resetHistory: mockResetHistory,
}
