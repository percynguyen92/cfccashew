<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert/index';
import { Calculator, AlertTriangle, CheckCircle, Clock } from 'lucide-vue-next';
import type { CalculatedWeights, WeightCalculationStatus, WeightDiscrepancy } from '@/composables/useWeightCalculations';
import { useI18n } from 'vue-i18n';

interface Props {
    calculatedWeights: CalculatedWeights;
    calculationStatus: WeightCalculationStatus;
    discrepancies: WeightDiscrepancy[];
    isValid: boolean;
    hasDiscrepancies: boolean;
}

const props = defineProps<Props>();

const { t } = useI18n();

const formatWeight = (weight: number | null): string => {
    if (weight === null) {
        return t('common.placeholders.notAvailable');
    }
    return weight.toLocaleString();
};

const getStatusIcon = (status: 'calculated' | 'pending' | 'manual') => {
    switch (status) {
        case 'calculated':
            return CheckCircle;
        case 'pending':
            return Clock;
        case 'manual':
            return Calculator;
        default:
            return Calculator;
    }
};

const getStatusColor = (status: 'calculated' | 'pending' | 'manual') => {
    switch (status) {
        case 'calculated':
            return 'text-green-600';
        case 'pending':
            return 'text-gray-400';
        case 'manual':
            return 'text-blue-600';
        default:
            return 'text-gray-400';
    }
};

const getBorderColor = (status: 'calculated' | 'pending' | 'manual') => {
    switch (status) {
        case 'calculated':
            return 'border-green-300 bg-green-50/50';
        case 'pending':
            return 'border-gray-300 bg-gray-50/50';
        case 'manual':
            return 'border-blue-300 bg-blue-50/50';
        default:
            return 'border-gray-300 bg-gray-50/50';
    }
};

const errorDiscrepancies = computed(() =>
    props.discrepancies.filter(d => d.type === 'error')
);

const warningDiscrepancies = computed(() =>
    props.discrepancies.filter(d => d.type === 'warning')
);
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-medium flex items-center gap-2">
            <Calculator class="h-5 w-5" />
            {{ t('containers.form.calculated.heading') }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Gross Weight -->
            <div class="space-y-2">
                <Label class="flex items-center gap-2">
                    {{ t('containers.form.calculated.gross.label') }}
                    <component :is="getStatusIcon(calculationStatus.gross)"
                        :class="['h-4 w-4', getStatusColor(calculationStatus.gross)]" />
                </Label>
                <div class="relative">
                    <Input :value="formatWeight(calculatedWeights.w_gross)" readonly :class="[
                        'cursor-not-allowed transition-all duration-200',
                        getBorderColor(calculationStatus.gross)
                    ]" />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <Calculator class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ t('containers.form.calculated.gross.formula') }}
                </p>
                <div class="text-xs" :class="getStatusColor(calculationStatus.gross)">
                    <span v-if="calculationStatus.gross === 'calculated'">
                        ✓ {{ t('containers.form.calculated.status.calculated') }}
                    </span>
                    <span v-else-if="calculationStatus.gross === 'pending'">
                        ⏳ {{ t('containers.form.calculated.status.pending') }}
                    </span>
                </div>
            </div>

            <!-- Tare Weight -->
            <div class="space-y-2">
                <Label class="flex items-center gap-2">
                    {{ t('containers.form.calculated.tare.label') }}
                    <component :is="getStatusIcon(calculationStatus.tare)"
                        :class="['h-4 w-4', getStatusColor(calculationStatus.tare)]" />
                </Label>
                <div class="relative">
                    <Input :value="formatWeight(calculatedWeights.w_tare)" readonly :class="[
                        'cursor-not-allowed transition-all duration-200',
                        getBorderColor(calculationStatus.tare)
                    ]" />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <Calculator class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ t('containers.form.calculated.tare.formula') }}
                </p>
                <div class="text-xs" :class="getStatusColor(calculationStatus.tare)">
                    <span v-if="calculationStatus.tare === 'calculated'">
                        ✓ {{ t('containers.form.calculated.status.calculated') }}
                    </span>
                    <span v-else-if="calculationStatus.tare === 'pending'">
                        ⏳ {{ t('containers.form.calculated.status.pending') }}
                    </span>
                </div>
            </div>

            <!-- Net Weight -->
            <div class="space-y-2">
                <Label class="flex items-center gap-2">
                    {{ t('containers.form.calculated.net.label') }}
                    <component :is="getStatusIcon(calculationStatus.net)"
                        :class="['h-4 w-4', getStatusColor(calculationStatus.net)]" />
                </Label>
                <div class="relative">
                    <Input :value="formatWeight(calculatedWeights.w_net)" readonly :class="[
                        'cursor-not-allowed transition-all duration-200',
                        getBorderColor(calculationStatus.net)
                    ]" />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <Calculator class="h-4 w-4 text-muted-foreground" />
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ t('containers.form.calculated.net.formula') }}
                </p>
                <div class="text-xs" :class="getStatusColor(calculationStatus.net)">
                    <span v-if="calculationStatus.net === 'calculated'">
                        ✓ {{ t('containers.form.calculated.status.calculated') }}
                    </span>
                    <span v-else-if="calculationStatus.net === 'pending'">
                        ⏳ {{ t('containers.form.calculated.status.pending') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Calculation Status Messages -->
        <div v-if="!isValid || (calculationStatus.gross === 'pending' || calculationStatus.tare === 'pending' || calculationStatus.net === 'pending')"
            class="space-y-2">
            <!-- Pending calculations info -->
            <div v-if="calculationStatus.gross === 'pending' || calculationStatus.tare === 'pending' || calculationStatus.net === 'pending'"
                class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-700 flex items-center gap-2">
                    <Clock class="h-4 w-4" />
                    {{ t('containers.form.calculated.fillAll') }}
                </p>
            </div>
        </div>

        <!-- Error Discrepancies -->
        <div v-if="errorDiscrepancies.length > 0" class="space-y-2">
            <Alert variant="destructive">
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <p class="font-medium mb-2">
                        {{ t('containers.form.calculated.errors') }}
                    </p>
                    <ul class="space-y-1">
                        <li v-for="error in errorDiscrepancies" :key="error.field + error.message"
                            class="flex items-start gap-1 text-sm">
                            <span class="font-bold">•</span>
                            <span>{{ error.message }}</span>
                        </li>
                    </ul>
                </AlertDescription>
            </Alert>
        </div>

        <!-- Warning Discrepancies -->
        <div v-if="warningDiscrepancies.length > 0" class="space-y-2">
            <Alert>
                <AlertTriangle class="h-4 w-4" />
                <AlertDescription>
                    <p class="font-medium mb-2">
                        {{ t('containers.form.calculated.warnings') }}
                    </p>
                    <ul class="space-y-1">
                        <li v-for="warning in warningDiscrepancies" :key="warning.field + warning.message"
                            class="flex items-start gap-1 text-sm">
                            <span class="font-bold">•</span>
                            <span>{{ warning.message }}</span>
                        </li>
                    </ul>
                </AlertDescription>
            </Alert>
        </div>
    </div>
</template>