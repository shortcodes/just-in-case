import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/pl'
import 'dayjs/locale/en'

dayjs.extend(relativeTime)

export function setDayjsLocale(locale: string) {
    dayjs.locale(locale)
}

export default dayjs
