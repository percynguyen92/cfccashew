import { router } from '@inertiajs/vue3';
import { createI18n } from 'vue-i18n';
import en from '@/locales/en.json';
import vi from '@/locales/vi.json';

export const SUPPORTED_LOCALES = ['en', 'vi'] as const;
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number];

const STORAGE_KEY = 'cfccashew.locale';
const COOKIE_KEY = 'app_locale';
const DEFAULT_LOCALE: SupportedLocale = 'en';
const FALLBACK_LOCALE: SupportedLocale = 'vi';

type MaybeLocale = SupportedLocale | string | undefined | null;

const isSupportedLocale = (locale: MaybeLocale): locale is SupportedLocale =>
    typeof locale === 'string' && (SUPPORTED_LOCALES as readonly string[]).includes(locale);

const writeCookie = (locale: SupportedLocale) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = 60 * 60 * 24 * 365; // 1 year
    document.cookie = `${COOKIE_KEY}=${locale};path=/;max-age=${maxAge};samesite=lax`;
};

const readStoredLocale = (): SupportedLocale | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    const stored = window.localStorage.getItem(STORAGE_KEY);

    return isSupportedLocale(stored) ? stored : null;
};

const detectBrowserLocale = (): SupportedLocale | null => {
    if (typeof navigator === 'undefined') {
        return null;
    }

    const [language] = navigator.language?.toLowerCase().split('-') ?? [];

    return isSupportedLocale(language) ? language : null;
};

const persistLocale = (locale: SupportedLocale) => {
    if (typeof window !== 'undefined') {
        window.localStorage.setItem(STORAGE_KEY, locale);
    }

    writeCookie(locale);
};

const resolveInitialLocale = (initialLocale?: MaybeLocale): SupportedLocale => {
    if (isSupportedLocale(initialLocale)) {
        persistLocale(initialLocale);
        return initialLocale;
    }

    const storedLocale = readStoredLocale();
    if (storedLocale) {
        persistLocale(storedLocale);
        return storedLocale;
    }

    const browserLocale = detectBrowserLocale();
    if (browserLocale) {
        persistLocale(browserLocale);
        return browserLocale;
    }

    persistLocale(DEFAULT_LOCALE);
    return DEFAULT_LOCALE;
};

export const createI18nInstance = (initialLocale?: MaybeLocale) => {
    const locale = resolveInitialLocale(initialLocale);
    const i18n = createI18n({
        legacy: false,
        locale,
        fallbackLocale: FALLBACK_LOCALE,
        messages: {
            en,
            vi,
        },
    });

    router.on('success', (event) => {
        const pageLocale = (event.detail.page.props as { locale?: MaybeLocale }).locale;

        if (isSupportedLocale(pageLocale) && i18n.global.locale.value !== pageLocale) {
            i18n.global.locale.value = pageLocale;
            persistLocale(pageLocale);
        }
    });

    return i18n;
};

export const setActiveLocale = (locale: SupportedLocale) => {
    if (!isSupportedLocale(locale)) {
        return;
    }

    persistLocale(locale);
};

export const getActiveLocale = (): SupportedLocale => {
    const stored = readStoredLocale();

    return stored ?? DEFAULT_LOCALE;
};
