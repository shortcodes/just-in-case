import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import localizedFormat from 'dayjs/plugin/localizedFormat'
import 'dayjs/locale/pl'
import 'dayjs/locale/en'

dayjs.extend(relativeTime)
dayjs.extend(localizedFormat)

export function setDayjsLocale(locale: string) {
    dayjs.locale(locale)
}

export default dayjs
