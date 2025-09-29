import { router } from '@inertiajs/vue3';
import { createI18n } from 'vue-i18n';
import type { WritableComputedRef } from 'vue';
import en from '@/locales/en.json';
import vi from '@/locales/vi.json';

export const SUPPORTED_LOCALES = ['en', 'vi'] as const;
export type SupportedLocale = (typeof SUPPORTED_LOCALES)[number];

const STORAGE_KEY = 'cfccashew.locale';
const COOKIE_KEY = 'app_locale';
const DEFAULT_LOCALE: SupportedLocale = 'en';
const FALLBACK_LOCALE: SupportedLocale = 'vi';

let activeLocaleRef: WritableComputedRef<string> | null = null;

export type MaybeLocale = SupportedLocale | string | undefined | null;

type LocaleCarrier = {
    props: {
        locale?: MaybeLocale;
    } & Record<string, unknown>;
};

type InertiaLocaleSource = {
    initialPage: LocaleCarrier;
    page?: LocaleCarrier;
} & Record<string, unknown>;

export const extractLocaleFromInertiaProps = (
    source: InertiaLocaleSource,
): MaybeLocale => {
    const initialLocale = source.initialPage.props.locale;
    if (isSupportedLocale(initialLocale)) {
        return initialLocale;
    }

    const pageLocale = source.page?.props.locale;

    if (isSupportedLocale(pageLocale)) {
        return pageLocale;
    }

    return initialLocale ?? pageLocale ?? undefined;
};

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
    const storedLocale = readStoredLocale();
    if (storedLocale) {
        persistLocale(storedLocale);
        return storedLocale;
    }

    if (isSupportedLocale(initialLocale)) {
        persistLocale(initialLocale);
        return initialLocale;
    }

    const browserLocale = detectBrowserLocale();
    if (browserLocale) {
        persistLocale(browserLocale);
        return browserLocale;
    }

    persistLocale(DEFAULT_LOCALE);
    return DEFAULT_LOCALE;
};

type CreateI18nInstanceOptions = {
    attachRouterListener?: boolean;
};

export const createI18nInstance = (
    initialLocale?: MaybeLocale,
    options: CreateI18nInstanceOptions = {},
) => {
    const locale = resolveInitialLocale(initialLocale);
    const { attachRouterListener = true } = options;
    const i18n = createI18n({
        legacy: false,
        locale,
        fallbackLocale: FALLBACK_LOCALE,
        messages: {
            en,
            vi,
        },
    });

    activeLocaleRef = i18n.global.locale;

    if (attachRouterListener && typeof window !== 'undefined') {
        router.on('success', (event) => {
            const pageLocale = (event.detail.page.props as { locale?: MaybeLocale }).locale;

            const storedLocale = readStoredLocale();

            if (storedLocale && storedLocale !== pageLocale) {
                if (i18n.global.locale.value !== storedLocale) {
                    i18n.global.locale.value = storedLocale;
                }

                persistLocale(storedLocale);
                return;
            }

            if (
                isSupportedLocale(pageLocale) &&
                i18n.global.locale.value !== pageLocale
            ) {
                i18n.global.locale.value = pageLocale;
                persistLocale(pageLocale);
                return;
            }

            if (storedLocale && i18n.global.locale.value !== storedLocale) {
                i18n.global.locale.value = storedLocale;
                persistLocale(storedLocale);
            }
        });
    }

    return i18n;
};

export const setActiveLocale = (locale: SupportedLocale) => {
    if (!isSupportedLocale(locale)) {
        return;
    }

    persistLocale(locale);
    if (activeLocaleRef) {
        activeLocaleRef.value = locale;
    }
};

export const getActiveLocale = (): SupportedLocale => {
    const stored = readStoredLocale();

    return stored ?? DEFAULT_LOCALE;
};
