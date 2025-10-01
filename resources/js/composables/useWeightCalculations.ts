import { computed, ref, type Ref } from 'vue';

export interface WeightInputs {
    w_total: number | null;
    w_truck: number | null;
    w_container: number | null;
    w_dunnage_dribag: number | null;
    quantity_of_bags: number | null;
    w_jute_bag: number | null;
}

export interface CalculatedWeights {
    w_gross: number | null;
    w_tare: number | null;
    w_net: number | null;
}

export interface WeightCalculationStatus {
    gross: 'calculated' | 'pending' | 'manual';
    tare: 'calculated' | 'pending' | 'manual';
    net: 'calculated' | 'pending' | 'manual';
}

export interface WeightDiscrepancy {
    type: 'warning' | 'error';
    field: string;
    message: string;
}

export interface WeightCalculationResult {
    calculatedWeights: Ref<CalculatedWeights>;
    calculationStatus: Ref<WeightCalculationStatus>;
    discrepancies: Ref<WeightDiscrepancy[]>;
    isValid: Ref<boolean>;
    hasDiscrepancies: Ref<boolean>;
    updateInputs: (inputs: Partial<WeightInputs>) => void;
    resetCalculations: () => void;
}

const asNumber = (value: number | null | undefined): number | null =>
    typeof value === 'number' && Number.isFinite(value) ? value : null;

export function useWeightCalculations(
    initialInputs: WeightInputs = {
        w_total: null,
        w_truck: null,
        w_container: null,
        w_dunnage_dribag: null,
        quantity_of_bags: null,
        w_jute_bag: null,
    }
): WeightCalculationResult {
    const inputs = ref<WeightInputs>({ ...initialInputs });
    
    // Calculated weights
    const calculatedWeights = computed<CalculatedWeights>(() => {
        const total = asNumber(inputs.value.w_total);
        const truck = asNumber(inputs.value.w_truck);
        const container = asNumber(inputs.value.w_container);
        const dunnage = asNumber(inputs.value.w_dunnage_dribag);
        const quantity = asNumber(inputs.value.quantity_of_bags);
        const juteBag = asNumber(inputs.value.w_jute_bag);

        // Calculate gross weight: w_gross = w_total - w_truck - w_container
        const w_gross = (total !== null && truck !== null && container !== null)
            ? Math.max(0, total - truck - container)
            : null;

        // Calculate tare weight: w_tare = quantity_of_bags * w_jute_bag
        const w_tare = (quantity !== null && juteBag !== null)
            ? quantity * juteBag
            : null;

        // Calculate net weight: w_net = w_gross - w_dunnage_dribag - w_tare
        const w_net = (w_gross !== null && dunnage !== null && w_tare !== null)
            ? Math.max(0, w_gross - dunnage - w_tare)
            : null;

        return { w_gross, w_tare, w_net };
    });

    // Calculation status
    const calculationStatus = computed<WeightCalculationStatus>(() => ({
        gross: calculatedWeights.value.w_gross !== null ? 'calculated' : 'pending',
        tare: calculatedWeights.value.w_tare !== null ? 'calculated' : 'pending',
        net: calculatedWeights.value.w_net !== null ? 'calculated' : 'pending',
    }));

    // Weight discrepancies and validation
    const discrepancies = computed<WeightDiscrepancy[]>(() => {
        const issues: WeightDiscrepancy[] = [];
        
        const total = asNumber(inputs.value.w_total);
        const truck = asNumber(inputs.value.w_truck);
        const container = asNumber(inputs.value.w_container);
        const dunnage = asNumber(inputs.value.w_dunnage_dribag);
        const gross = calculatedWeights.value.w_gross;
        const tare = calculatedWeights.value.w_tare;
        const net = calculatedWeights.value.w_net;

        // Check if total weight is reasonable compared to truck and container
        if (total !== null && truck !== null && container !== null) {
            if (total <= truck + container) {
                issues.push({
                    type: 'error',
                    field: 'w_total',
                    message: 'Total weight must be greater than truck + container weight'
                });
            }
            
            // Warning if gross weight is very small
            if (gross !== null && gross < 100) {
                issues.push({
                    type: 'warning',
                    field: 'w_gross',
                    message: 'Gross weight seems unusually low (< 100 kg)'
                });
            }
        }

        // Check if net weight calculation results in reasonable values
        if (gross !== null && tare !== null && dunnage !== null) {
            if (gross <= dunnage + tare) {
                issues.push({
                    type: 'error',
                    field: 'w_net',
                    message: 'Insufficient gross weight for dunnage and tare deductions'
                });
            }
        }

        // Check for negative net weight
        if (net !== null && net <= 0) {
            issues.push({
                type: 'error',
                field: 'w_net',
                message: 'Net weight cannot be zero or negative'
            });
        }

        // Check for unreasonable tare weight (too high compared to gross)
        if (gross !== null && tare !== null && tare > gross * 0.3) {
            issues.push({
                type: 'warning',
                field: 'w_tare',
                message: 'Tare weight seems high (> 30% of gross weight)'
            });
        }

        // Check for unreasonable dunnage weight
        if (gross !== null && dunnage !== null && dunnage > gross * 0.2) {
            issues.push({
                type: 'warning',
                field: 'w_dunnage_dribag',
                message: 'Dunnage weight seems high (> 20% of gross weight)'
            });
        }

        return issues;
    });

    const isValid = computed(() => 
        discrepancies.value.filter(d => d.type === 'error').length === 0
    );

    const hasDiscrepancies = computed(() => discrepancies.value.length > 0);

    const updateInputs = (newInputs: Partial<WeightInputs>) => {
        inputs.value = { ...inputs.value, ...newInputs };
    };

    const resetCalculations = () => {
        inputs.value = {
            w_total: null,
            w_truck: null,
            w_container: null,
            w_dunnage_dribag: null,
            quantity_of_bags: null,
            w_jute_bag: null,
        };
    };

    return {
        calculatedWeights,
        calculationStatus,
        discrepancies,
        isValid,
        hasDiscrepancies,
        updateInputs,
        resetCalculations,
    };
}