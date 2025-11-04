import { usePage } from '@inertiajs/vue3';

export function useTrans() {
    const page = usePage();

    return (key) => {
        const translations = page.props.translations || {};

        if (translations[key]) {
            return translations[key];
        }

        const parts = key.split('.');
        let value = translations;

        for (const part of parts) {
            if (value && typeof value === 'object' && part in value) {
                value = value[part];
            } else {
                return key;
            }
        }

        return typeof value === 'string' ? value : key;
    };
}

export function useLocale() {
    const page = usePage();
    return page.props.locale || 'en';
}
