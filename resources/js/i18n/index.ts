import { createI18n } from 'vue-i18n';
import pl from './locales/pl';
import en from './locales/en';

export const SUPPORTED_LOCALES = ['pl', 'en'] as const;
export type Locale = (typeof SUPPORTED_LOCALES)[number];

const STORAGE_KEY = 'qrme.locale';
const DEFAULT_LOCALE: Locale = 'pl';

function detectInitialLocale(): Locale {
    if (typeof window === 'undefined') {
        return DEFAULT_LOCALE;
    }

    const stored = window.localStorage.getItem(STORAGE_KEY);
    if (stored && (SUPPORTED_LOCALES as readonly string[]).includes(stored)) {
        return stored as Locale;
    }

    const browser = (window.navigator.language || '').toLowerCase();
    if (browser.startsWith('pl')) return 'pl';
    if (browser.startsWith('en')) return 'en';
    return DEFAULT_LOCALE;
}

export const i18n = createI18n({
    legacy: false,
    locale: detectInitialLocale(),
    fallbackLocale: DEFAULT_LOCALE,
    messages: {
        pl,
        en,
    },
});

export function setLocale(locale: Locale): void {
    i18n.global.locale.value = locale;
    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEY, locale);
        document.documentElement.setAttribute('lang', locale);
    }
}

if (typeof window !== 'undefined') {
    document.documentElement.setAttribute('lang', i18n.global.locale.value);
}
