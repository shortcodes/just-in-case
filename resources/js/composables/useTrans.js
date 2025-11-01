import { usePage } from '@inertiajs/vue3';

export function useTrans() {
    const page = usePage();

    return (key) => {
        const translations = page.props.translations || {};
        return translations[key] || key;
    };
}

export function useLocale() {
    const page = usePage();
    return page.props.locale || 'en';
}
