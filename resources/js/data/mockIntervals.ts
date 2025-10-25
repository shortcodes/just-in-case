import type { IntervalOption } from '@/types/models'

export const mockIntervals: IntervalOption[] = [
    { value: 'P30D', label: '1 month (30 days)', days: 30 },
    { value: 'P60D', label: '2 months (60 days)', days: 60 },
    { value: 'P90D', label: '3 months (90 days)', days: 90 },
    { value: 'P180D', label: '6 months (180 days)', days: 180 },
    { value: 'P365D', label: '1 year (365 days)', days: 365 },
]
