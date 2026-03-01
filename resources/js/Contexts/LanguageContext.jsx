import { usePage, router } from '@inertiajs/react';

import { translations } from '../lang';

/**
 * Custom hook to access language/locale functionality
 * Must be used within Inertia page components
 */
export function useLanguage() {
    const { locale, availableLocales, isRTL } = usePage().props;

    const switchLanguage = (newLocale) => {
        if (availableLocales.includes(newLocale) && newLocale !== locale) {
            router.post(`/locale/${newLocale}`, {}, {
                preserveState: false,
                preserveScroll: false,
                onSuccess: () => {
                    window.location.reload();
                }
            });
        }
    };

    const t = (key, replacements = {}) => {
        const keys = key.split('.');
        let value = translations[locale] || translations['en'];
        
        for (const k of keys) {
            value = value?.[k];
        }

        if (!value) {
            // Fallback to English
            let fallback = translations['en'];
            for (const k of keys) {
                fallback = fallback?.[k];
            }
            value = fallback || key;
        }

        if (typeof value === 'string') {
            Object.keys(replacements).forEach(r => {
                value = value.replace(`{${r}}`, replacements[r]);
                // Handle pluralization for "s" if needed, but simple replacement is fine for now
                // For "term{s}", we might need a simpler hack or just use separate keys if it gets complex.
                // The current strings have "{count} term{s}". 
                // Let's handle the specific case of {s} based on count if present.
                if (r === 'count' && value.includes('{s}')) {
                     const count = parseInt(replacements[r]);
                     value = value.replace('{s}', count !== 1 ? 's' : '');
                }
            });
            // Clean up any remaining {s} if count wasn't passed but {s} is there (shouldn't happen with my usage)
            value = value.replace('{s}', '');
        }

        return value;
    };

    return {
        locale,
        availableLocales,
        isRTL,
        switchLanguage,
        t,
    };
}
