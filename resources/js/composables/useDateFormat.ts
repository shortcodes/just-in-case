import { useLocale } from './useTrans'
import dayjs from '@/plugins/dayjs'

export function useDateFormat() {
    const locale = useLocale()

    const formatDate = (date: string | Date, includeTime: boolean = false): string => {
        if (!date) return ''

        if (locale === 'pl') {
            return dayjs(date).format(includeTime ? 'D MMMM YYYY HH:mm' : 'D MMMM YYYY')
        }

        // Default to English format
        return dayjs(date).format(includeTime ? 'MMMM D, YYYY h:mm A' : 'MMMM D, YYYY')
    }

    return {
        formatDate
    }
}
