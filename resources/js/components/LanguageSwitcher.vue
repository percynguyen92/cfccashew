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
import { router, usePage } from '@inertiajs/vue3';
import { computed, withDefaults } from 'vue';
import { useI18n } from 'vue-i18n';

const props = withDefaults(defineProps<{ fullWidth?: boolean }>(), {
    fullWidth: false,
});

const { locale, t } = useI18n();
const page = usePage<{ availableLocales?: string[] }>();

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
            value === 'en'
                ? t('language.english')
                : t('language.vietnamese'),
    })),
);

const currentLocale = computed(() => locale.value as SupportedLocale);

const triggerClass = computed(() =>
    props.fullWidth ? 'w-full' : 'w-[140px]',
);

const handleChange = (nextLocale: string) => {
    if (!(SUPPORTED_LOCALES as readonly string[]).includes(nextLocale)) {
        return;
    }

    const newLocale = nextLocale as SupportedLocale;

    if (currentLocale.value === newLocale) {
        return;
    }

    locale.value = newLocale;
    setActiveLocale(newLocale);

    router.reload({
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Select :model-value="currentLocale" @update:model-value="handleChange">
        <SelectTrigger :class="triggerClass">
            <SelectValue :placeholder="t('language.label')" />
        </SelectTrigger>
        <SelectContent>
            <SelectItem
                v-for="option in localeOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
