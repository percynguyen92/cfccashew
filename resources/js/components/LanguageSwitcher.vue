<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    SUPPORTED_LOCALES,
    setActiveLocale,
    type SupportedLocale,
} from '@/lib/i18n';
import type { AppPageProps } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, withDefaults } from 'vue';
import { useI18n } from 'vue-i18n';
import type { AcceptableValue } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{ fullWidth?: boolean }>(), {
    fullWidth: false,
});

const { locale, t } = useI18n();
const page = usePage<AppPageProps<{ availableLocales?: string[] }>>();

const availableLocales = computed<SupportedLocale[]>(() => {
    const locales = page.props.availableLocales ?? SUPPORTED_LOCALES;

    return locales.filter((value): value is SupportedLocale =>
        (SUPPORTED_LOCALES as readonly string[]).includes(value),
    );
});

const localeOptions = computed(() =>
    availableLocales.value.map((value) => ({
        value,
        label:
            value === 'en' ? t('language.english') : t('language.vietnamese'),
    })),
);

const currentLocale = computed(() => locale.value as SupportedLocale);

const triggerClass = computed(() =>
    cn(
        'h-8 min-h-0 min-w-0 rounded-md px-2 py-1 text-xs font-medium',
        props.fullWidth ? 'w-full' : 'w-[120px] min-w-[120px]',
    ),
);

const handleChange = (value: AcceptableValue): void => {
    if (value === null || value === undefined) {
        return;
    }

    if (typeof value === 'object') {
        return;
    }

    const nextLocale = String(value);

    if (!(SUPPORTED_LOCALES as readonly string[]).includes(nextLocale)) {
        return;
    }

    const newLocale = nextLocale as SupportedLocale;

    if (currentLocale.value === newLocale) {
        return;
    }

    locale.value = newLocale;
    setActiveLocale(newLocale);

    router.visit(page.url, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Select :model-value="currentLocale" @update:model-value="handleChange">
        <SelectTrigger :class="triggerClass">
            <SelectValue
                class="text-xs leading-tight"
                :placeholder="t('language.label')"
            />
        </SelectTrigger>
        <SelectContent class="min-w-[auto]">
            <SelectItem
                v-for="option in localeOptions"
                :key="option.value"
                :value="option.value"
                class="gap-1 py-1 pl-2 pr-6 text-xs"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
