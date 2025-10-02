---
inclusion: always
---

# CFCCashew Inspection System - Product Domain

## Internationalization (i18n) Requirement
**CRITICAL**: Never hardcode text strings in code. Always use the existing i18n system for all user-facing text.

## Domain Overview
Cashew inspection system managing Bills of Lading, containers, and cutting test results for quality control.

## Core Domain Models

### Bill (Bill of Lading)
- Root entity containing: `bill_number`, `seller`, `buyer`, `note`
- Has many-to-many relationship with Containers (a bill can have multiple containers, a container can belong to multiple bills)
- Has many CuttingTests
- Can have up to 3 final samples (cutting tests with `container_id = NULL`, `type = 1-3`)

### Container
- Many-to-many relationship with Bills (can belong to multiple bills)
- Contains weight calculations and container details
- Key fields: `truck`, `container_number` (ISO format: 4 letters + 7 digits), weight fields
- Has many CuttingTests (`type = 4`)
- Weight calculations use data from associated bills (w_jute_bag, w_dunnage_dribag)

### CuttingTest
- Inspection results linked to Bill (required) and Container (optional for final samples)
- Types: 1-3 (final samples), 4 (container-specific)
- Contains moisture, weights, and calculated outturn rate

## Critical Business Rules

### Weight Calculations (Container)
```
w_gross = w_total - w_truck - w_container
w_tare = quantity_of_bags * w_jute_bag
w_net = w_gross - w_dunnage_dribag - w_tare
```

### Outturn Rate Calculation (CuttingTest)
```
outturn_rate = (w_defective_kernel/2 + w_good_kernel) * 80 / 453.6
```

### Validation Alerts (CuttingTest)
- Alert if `(sample_weight - w_sample_after_cut) > 5`
- Alert if `(w_defective_nut/3.3 - w_defective_kernel) > 5`
- Alert if `((sample_weight - w_reject_nut - w_defective_nut)/3.3 - w_good_kernel) > 10`

### Data Constraints
- Container number: ISO format validation (4 letters + 7 digits)
- Moisture: 0-100% range
- Outturn rate: 0-60 lbs/80kg range
- CuttingTest types: 1 (final first), 2 (final second), 3 (final third), 4 (container)

## Display Formatting Rules
- Moisture: Display as `xx.x` (one decimal place)
- Defective nut/kernel: Display as `xxx/yyy.y` where `yyy.y = xxx/2`
- Use `CuttingTestType` enum for test types (1-4)

## Dashboard Requirements
- KPI Cards: Recent Bills, Bills pending final cutting tests
- Alerts: Bills missing final samples, Containers with moisture > 11%
- Query via lightweight JSON endpoints
- Reuse existing Laravel Starter Kit dashboard layout

## UI/UX Guidelines
- Desktop-first responsive design
- Font: Alexandria via Bunny Fonts
- Expandable container rows showing related cutting tests
- Inline cutting test creation from container view
- Weight discrepancy alerts in Service layer