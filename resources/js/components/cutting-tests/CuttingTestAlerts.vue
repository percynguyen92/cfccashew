<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, AlertCircle, Info } from 'lucide-vue-next';
import { computed } from 'vue';
import type { CuttingTest } from '@/types';
import { useCuttingTestValidation } from '@/composables/useCuttingTestValidation';

interface Props {
    cuttingTest: CuttingTest | null;
    showInline?: boolean;
    maxAlerts?: number;
    categories?: ('moisture' | 'weight' | 'ratio' | 'calculation')[];
}

const props = withDefaults(defineProps<Props>(), {
    showInline: false,
    maxAlerts: 5,
    categories: () => ['moisture', 'weight', 'ratio', 'calculation'],
});

const validation = useCuttingTestValidation(computed(() => props.cuttingTest));

const filteredAlerts = computed(() => {
    return validation.alerts.value
        .filter(alert => props.categories.includes(alert.category))
        .slice(0, props.maxAlerts);
});

const remainingCount = computed(() => {
    const totalFiltered = validation.alerts.value.filter(alert =>
        props.categories.includes(alert.category)
    ).length;
    return Math.max(0, totalFiltered - props.maxAlerts);
});

// Get alert icon based on type
const getAlertIcon = (type: 'error' | 'warning' | 'info') => {
    switch (type) {
        case 'error':
            return AlertCircle;
        case 'warning':
            return AlertTriangle;
        case 'info':
            return Info;
        default:
            return AlertTriangle;
    }
};

// Get alert color classes based on type
const getAlertClasses = (type: 'error' | 'warning' | 'info') => {
    if (props.showInline) {
        switch (type) {
            case 'error':
                return 'text-red-600';
            case 'warning':
                return 'text-amber-600';
            case 'info':
                return 'text-blue-600';
            default:
                return 'text-amber-600';
        }
    } else {
        switch (type) {
            case 'error':
                return 'border-red-200 bg-red-50 text-red-700';
            case 'warning':
                return 'border-amber-200 bg-amber-50 text-amber-700';
            case 'info':
                return 'border-blue-200 bg-blue-50 text-blue-700';
            default:
                return 'border-amber-200 bg-amber-50 text-amber-700';
        }
    }
};

// Get badge variant based on alert type
const getBadgeVariant = (type: 'error' | 'warning' | 'info') => {
    switch (type) {
        case 'error':
            return 'destructive';
        case 'warning':
            return 'outline';
        case 'info':
            return 'secondary';
        default:
            return 'outline';
    }
};
</script>

<template>
    <div v-if="validation.hasAlerts.value">
        <!-- Inline style alerts -->
        <div v-if="showInline" class="space-y-1">
            <div v-for="alert in filteredAlerts" :key="alert.message"
                :class="['flex items-center gap-1 text-xs', getAlertClasses(alert.type)]">
                <component :is="getAlertIcon(alert.type)" class="h-3 w-3 flex-shrink-0" />
                <span>{{ alert.message }}</span>
            </div>
            <div v-if="remainingCount > 0" class="text-xs text-muted-foreground">
                +{{ remainingCount }} more alerts
            </div>
        </div>

        <!-- Card style alerts -->
        <div v-else class="space-y-2">
            <div v-for="alert in filteredAlerts" :key="alert.message"
                :class="['flex items-start gap-2 rounded-md border p-3 text-sm', getAlertClasses(alert.type)]">
                <component :is="getAlertIcon(alert.type)" class="mt-0.5 h-4 w-4 flex-shrink-0" />
                <div class="flex-1">
                    <span>{{ alert.message }}</span>
                    <Badge v-if="alert.category" :variant="getBadgeVariant(alert.type)" class="ml-2 text-xs">
                        {{ alert.category }}
                    </Badge>
                </div>
            </div>
            <div v-if="remainingCount > 0" class="text-xs text-muted-foreground text-center">
                +{{ remainingCount }} more alerts
            </div>
        </div>
    </div>

    <!-- No alerts message -->
    <div v-else-if="!showInline" class="text-xs text-muted-foreground">
        No validation alerts
    </div>
</template>