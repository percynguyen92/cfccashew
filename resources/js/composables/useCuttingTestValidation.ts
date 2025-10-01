import { computed, type Ref } from 'vue';
import type { CuttingTest } from '@/types';

export interface ValidationAlert {
    type: 'error' | 'warning' | 'info';
    category: 'moisture' | 'weight' | 'ratio' | 'calculation';
    message: string;
    field?: string;
    value?: number;
    threshold?: number;
}

export interface CuttingTestValidationResult {
    alerts: Ref<ValidationAlert[]>;
    hasAlerts: Ref<boolean>;
    hasErrors: Ref<boolean>;
    hasWarnings: Ref<boolean>;
    moistureAlerts: Ref<ValidationAlert[]>;
    weightAlerts: Ref<ValidationAlert[]>;
    ratioAlerts: Ref<ValidationAlert[]>;
}

/**
 * Composable for cutting test validation alerts
 * Implements all business rules for cutting test validation
 */
export function useCuttingTestValidation(
    cuttingTest: Ref<CuttingTest | null> | CuttingTest | null
): CuttingTestValidationResult {
    const test = computed(() => {
        if (!cuttingTest) return null;
        return typeof cuttingTest === 'object' && 'value' in cuttingTest 
            ? cuttingTest.value 
            : cuttingTest;
    });

    const alerts = computed<ValidationAlert[]>(() => {
        if (!test.value) return [];

        const validationAlerts: ValidationAlert[] = [];
        const t = test.value;

        // 1. Moisture validation alerts
        if (t.moisture !== null && t.moisture !== undefined) {
            // High moisture alert (>11%)
            if (t.moisture > 11) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'moisture',
                    message: `High moisture content: ${t.moisture.toFixed(1)}% (threshold: 11%)`,
                    field: 'moisture',
                    value: t.moisture,
                    threshold: 11
                });
            }

            // Very high moisture alert (>15%)
            if (t.moisture > 15) {
                validationAlerts.push({
                    type: 'error',
                    category: 'moisture',
                    message: `Critical moisture level: ${t.moisture.toFixed(1)}% (critical threshold: 15%)`,
                    field: 'moisture',
                    value: t.moisture,
                    threshold: 15
                });
            }

            // Unusually low moisture (< 5%)
            if (t.moisture < 5) {
                validationAlerts.push({
                    type: 'info',
                    category: 'moisture',
                    message: `Low moisture content: ${t.moisture.toFixed(1)}% (may indicate over-drying)`,
                    field: 'moisture',
                    value: t.moisture,
                    threshold: 5
                });
            }
        }

        // 2. Sample weight discrepancy validation
        if (t.sample_weight && t.w_sample_after_cut) {
            const weightDifference = t.sample_weight - t.w_sample_after_cut;
            
            if (weightDifference > 5) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'weight',
                    message: `Sample weight discrepancy: ${weightDifference.toFixed(1)}g loss after cutting (threshold: 5g)`,
                    field: 'sample_weight',
                    value: weightDifference,
                    threshold: 5
                });
            }

            // Critical weight loss
            if (weightDifference > 50) {
                validationAlerts.push({
                    type: 'error',
                    category: 'weight',
                    message: `Excessive sample weight loss: ${weightDifference.toFixed(1)}g (critical threshold: 50g)`,
                    field: 'sample_weight',
                    value: weightDifference,
                    threshold: 50
                });
            }

            // Negative weight difference (weight increased)
            if (weightDifference < 0) {
                validationAlerts.push({
                    type: 'error',
                    category: 'weight',
                    message: `Invalid weight change: sample weight increased by ${Math.abs(weightDifference).toFixed(1)}g after cutting`,
                    field: 'sample_weight',
                    value: weightDifference
                });
            }
        }

        // 3. Defective nut/kernel ratio validation
        if (t.w_defective_nut && t.w_defective_kernel) {
            const expectedKernel = t.w_defective_nut / 3.3;
            const kernelDifference = Math.abs(expectedKernel - t.w_defective_kernel);
            
            if (kernelDifference > 5) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'ratio',
                    message: `Defective nut/kernel ratio discrepancy: ${kernelDifference.toFixed(1)}g difference (threshold: 5g)`,
                    field: 'w_defective_kernel',
                    value: kernelDifference,
                    threshold: 5
                });
            }

            // Critical ratio discrepancy
            if (kernelDifference > 20) {
                validationAlerts.push({
                    type: 'error',
                    category: 'ratio',
                    message: `Critical defective ratio discrepancy: ${kernelDifference.toFixed(1)}g difference (critical threshold: 20g)`,
                    field: 'w_defective_kernel',
                    value: kernelDifference,
                    threshold: 20
                });
            }
        }

        // 4. Good kernel calculation validation
        if (t.sample_weight && t.w_reject_nut && t.w_defective_nut && t.w_good_kernel) {
            const expectedGoodKernel = (t.sample_weight - t.w_reject_nut - t.w_defective_nut) / 3.3;
            const goodKernelDifference = Math.abs(expectedGoodKernel - t.w_good_kernel);
            
            if (goodKernelDifference > 10) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'calculation',
                    message: `Good kernel calculation discrepancy: ${goodKernelDifference.toFixed(1)}g difference (threshold: 10g)`,
                    field: 'w_good_kernel',
                    value: goodKernelDifference,
                    threshold: 10
                });
            }

            // Critical calculation discrepancy
            if (goodKernelDifference > 30) {
                validationAlerts.push({
                    type: 'error',
                    category: 'calculation',
                    message: `Critical good kernel calculation error: ${goodKernelDifference.toFixed(1)}g difference (critical threshold: 30g)`,
                    field: 'w_good_kernel',
                    value: goodKernelDifference,
                    threshold: 30
                });
            }
        }

        // 5. Outturn rate validation
        if (t.outturn_rate !== null && t.outturn_rate !== undefined) {
            // Unusually high outturn rate
            if (t.outturn_rate > 55) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'calculation',
                    message: `High outturn rate: ${t.outturn_rate.toFixed(2)} lbs/80kg (typical range: 35-55)`,
                    field: 'outturn_rate',
                    value: t.outturn_rate,
                    threshold: 55
                });
            }

            // Unusually low outturn rate
            if (t.outturn_rate < 30) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'calculation',
                    message: `Low outturn rate: ${t.outturn_rate.toFixed(2)} lbs/80kg (typical range: 35-55)`,
                    field: 'outturn_rate',
                    value: t.outturn_rate,
                    threshold: 30
                });
            }

            // Extremely low outturn rate
            if (t.outturn_rate < 20) {
                validationAlerts.push({
                    type: 'error',
                    category: 'calculation',
                    message: `Critical low outturn rate: ${t.outturn_rate.toFixed(2)} lbs/80kg (minimum expected: 20)`,
                    field: 'outturn_rate',
                    value: t.outturn_rate,
                    threshold: 20
                });
            }
        }

        // 6. Weight consistency validation
        if (t.w_reject_nut && t.w_defective_nut && t.w_good_kernel && t.sample_weight) {
            const totalProcessedWeight = (t.w_reject_nut + t.w_defective_nut) + (t.w_good_kernel * 3.3);
            const weightAccountedFor = totalProcessedWeight / t.sample_weight;
            
            // Check if weights add up reasonably
            if (weightAccountedFor < 0.8) {
                validationAlerts.push({
                    type: 'warning',
                    category: 'weight',
                    message: `Weight accounting issue: only ${(weightAccountedFor * 100).toFixed(1)}% of sample weight accounted for`,
                    field: 'sample_weight',
                    value: weightAccountedFor * 100,
                    threshold: 80
                });
            }

            if (weightAccountedFor > 1.2) {
                validationAlerts.push({
                    type: 'error',
                    category: 'weight',
                    message: `Weight accounting error: ${(weightAccountedFor * 100).toFixed(1)}% of sample weight accounted for (exceeds 120%)`,
                    field: 'sample_weight',
                    value: weightAccountedFor * 100,
                    threshold: 120
                });
            }
        }

        return validationAlerts;
    });

    const hasAlerts = computed(() => alerts.value.length > 0);
    const hasErrors = computed(() => alerts.value.some(alert => alert.type === 'error'));
    const hasWarnings = computed(() => alerts.value.some(alert => alert.type === 'warning'));

    const moistureAlerts = computed(() => 
        alerts.value.filter(alert => alert.category === 'moisture')
    );

    const weightAlerts = computed(() => 
        alerts.value.filter(alert => alert.category === 'weight')
    );

    const ratioAlerts = computed(() => 
        alerts.value.filter(alert => alert.category === 'ratio')
    );

    return {
        alerts,
        hasAlerts,
        hasErrors,
        hasWarnings,
        moistureAlerts,
        weightAlerts,
        ratioAlerts,
    };
}