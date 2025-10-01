# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> **Context guide for AI-assisted development with Claude Code CLI**
> Last updated: 2025-09-30

---

## Project Identity

**Product**: CFCCashew Agents Module
**Purpose**: Internal inspection tool for managing Bills of Lading, Containers, and Cutting Tests
**Users**: Inspectors and auditors
**Database**: `cfccashew` (MySQL production, SQLite dev)

---

## Tech Stack at a Glance

### Backend
- **PHP 8.2+** with **Laravel 12.0**
- **Authentication**: Laravel Fortify
- **Testing**: Pest PHP + PHPUnit
- **Code style**: Laravel Pint (PSR-12)

### Frontend
- **Vue 3.5+** (Composition API) + **TypeScript**
- **Inertia.js 2.0+** (SPA-like navigation with SSR support)
- **Tailwind CSS 4.1+**
- **UI**: shadcn-vue, Reka UI, Lucide Vue Next icons
- **Build**: Vite 7.0+ with Laravel Vite Plugin
- **Linting**: ESLint + Prettier (Tailwind plugin)

---

## Key File Locations

```
app/
├── Http/Controllers/  # Thin route handlers
├── Services/          # Business logic layer
├── Repositories/      # DB write operations
├── Queries/           # Read/filter/report logic
├── Models/            # Eloquent models
├── Enums/             # Domain constants
└── Http/
    ├── Requests/      # Form validation
    └── Resources/     # API transformers

resources/js/
├── pages/             # Inertia page components
├── components/        # Reusable Vue components
├── composables/       # Vue composition functions
└── types/             # Shared TypeScript types

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Feature/
└── Unit/

.kiro/
├── specs/             # Feature specifications
│   └── */tasks.md     # Must follow during dev
└── steering/          # Product backbone docs
```

---

## Architecture Principles

### Layered Responsibilities

**Controllers** → Route handling only. Inject services, use Form Requests, return Inertia/JSON.
**Services** → Business rules, validation, transactions. Coordinate repos + queries.
**Repositories** → Encapsulate writes and simple ID lookups.
**Queries** → Complex reads, filtering, pagination, aggregations.
**Resources** → Transform models for API responses.
**Form Requests** → Validation rules, authorization, error messages.
**Enums** → Type-safe domain constants.

### Code Standards

- ✅ `declare(strict_types=1)` in all PHP files
- ✅ Constructor injection (no service locators)
- ✅ Small, testable classes
- ✅ PSR-12 formatting via Pint
- ✅ Explicit types in TypeScript (never `as unknown as`)

### Type Casting Rules (TypeScript)

❌ **Avoid**:
```typescript
const props = data as unknown as SomeType;
```

✅ **Prefer**:
```typescript
type PageProps = { page?: { props?: { locale?: string } } };
const props = data as PageProps;

// Or use type guards
if ('page' in data && data.page) { ... }
```

### SSR Considerations

- Register browser-only plugins (e.g., Inertia router hooks) only in client entrypoint
- Guard shared utilities against SSR execution to prevent listener leaks on Node server

---

## Domain Model Summary

### Relationships
- **Bill** belongsToMany **Container** (pivot: bill_container with note)
- **Bill** hasMany **CuttingTest**
- **Container** belongsToMany **Bill** (pivot: bill_container with note)
- **Container** hasMany **CuttingTest**

### bills
| Field | Type | Notes |
|-------|------|-------|
| id | BIGINT | PK |
| bill_number | VARCHAR(20) | Nullable, non-unique |
| seller, buyer | VARCHAR(255) | Nullable |
| note | TEXT | Nullable |
| timestamps | — | created_at, updated_at, deleted_at |

### containers
| Field | Type | Notes |
|-------|------|-------|
| id | BIGINT | PK |
| truck, container_number | VARCHAR | ISO format: 4 letters + 7 digits |
| quantity_of_bags | INT UNSIGNED | — |
| w_jute_bag | DECIMAL(4,2) | kg per bag, default 1.00 |
| w_total, w_truck, w_container | INT UNSIGNED | kg, whole numbers |
| w_gross | INT UNSIGNED | Calculated: total - truck - container |
| w_dunnage_dribag | INT UNSIGNED | kg |
| w_tare | DECIMAL(10,2) | bags × jute_bag |
| w_net | DECIMAL(10,2) | gross - dunnage - tare |
| note | TEXT | Nullable |
| timestamps | — | created_at, updated_at, deleted_at |

**All container weights in kg. Only jute_bag, w_tare, w_net allow decimals.**

### cutting_tests
| Field | Type | Notes |
|-------|------|-------|
| id | BIGINT | PK |
| bill_id | BIGINT | FK → bills.id |
| container_id | BIGINT | FK → containers.id (nullable for final samples) |
| type | SMALLINT | 1=final 1st, 2=final 2nd, 3=final 3rd, 4=container |
| moisture | DECIMAL(4,2) | 0–100% |
| sample_weight | SMALLINT UNSIGNED | Grams, default 1000 |
| nut_count | SMALLINT UNSIGNED | — |
| w_reject_nut, w_defective_nut | SMALLINT UNSIGNED | Grams |
| w_defective_kernel, w_good_kernel | SMALLINT UNSIGNED | Grams |
| w_sample_after_cut | SMALLINT UNSIGNED | Grams |
| outturn_rate | DECIMAL(5,2) | 0–60 lbs/80kg |
| timestamps | — | created_at, updated_at, deleted_at |

**All cutting test weights in grams. Moisture in %. Outturn in lbs/80kg (0–60 range).**

#### Calculations & Alerts
```
outturn_rate = (w_defective_kernel/2 + w_good_kernel) × 80 / 453.6

Container calculations:
- w_gross = max(0, w_total - w_truck - w_container)
- w_tare = quantity_of_bags * w_jute_bag
- w_net = max(0, w_gross - w_dunnage_dribag - w_tare)

Bill average outturn = average of final samples' outturn_rate (types 1-3, null container_id)

⚠️ Alert if:
- (sample_weight - w_sample_after_cut) > 5
- (w_defective_nut/3.3 - w_defective_kernel) > 5
- ((sample_weight - w_reject_nut - w_defective_nut)/3.3 - w_good_kernel) > 10
```

#### Accessors
- **Bill**: averageOutturn (avg final samples outturn), slug (id-bill_number)
- **Container**: averageMoisture (avg from cutting tests), outturnRate (from first cutting test), weight calculations

### Enums
- **CuttingTestType**: FinalFirstCut=1, FinalSecondCut=2, FinalThirdCut=3, ContainerCut=4

---

## Common Commands

### Setup
```bash
composer install && npm install
```

### Development
```bash
composer dev         # Laravel + Queue + Vite
composer dev:ssr     # With SSR enabled
npm run dev          # Frontend only
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan make:migration create_xyz_table
```

### Testing
```bash
composer test
php artisan test --filter=TestName
./vendor/bin/pest --filter=Bill
```

### Code Quality
```bash
npm run lint && npm run format
./vendor/bin/pint
```

### Building
```bash
npm run build
npm run build:ssr
```

---

## Development Workflow

1. **Check task list** in `.kiro/specs/*/tasks.md`
2. **Follow architecture layers**: Controller → Service → Repository/Query
3. **Write tests** in `tests/Feature` or `tests/Unit`
4. **Format code** before committing (Pint + Prettier)
5. **Commit with imperative mood**: `feat: add container weight validation`
6. **PR checklist**:
   - Scope summary
   - Screenshots for UI changes
   - Test results
   - Document any new `.env` keys or queue listeners

---

## Testing Guidelines

- **Feature tests**: End-to-end workflows in `tests/Feature`
- **Unit tests**: Isolated logic in `tests/Unit`
- **Naming**: `test_*` methods
- **Factories**: Keep aligned with repository structure
- **Run**: `php artisan test` or Pest filters

---

## UI/UX Notes

- **Desktop-first** responsive design
- **Font**: Alexandria via Bunny Fonts
- **SSR-compatible** Inertia components
- **Expand rows** to show related cutting tests
- **Auto-calculate** derived weights on input

---

## What to Build Next

**Always consult** `.kiro/specs/*/tasks.md` for current feature tasks.

**Common patterns**:
- Add new resource → Model + Migration + Factory + Repository + Service + Query + Controller + Request + Resource + Routes + Tests
- Add UI feature → Page component + composables + types + backend endpoint
- Add validation → Form Request rules + frontend form validation

---

## Future Enhancements

- OCR integration for weight slip scanning
- Automated inspection agent workflows
- Dashboard widgets with JSON API endpoints

---

## Notes for Claude Code CLI

- **Prefer explicit types** over `any` or `unknown` casts
- **Check SSR compatibility** when adding browser APIs
- **Follow layer separation** strictly (no business logic in controllers)
- **Run tests** after significant changes
- **Format before commit**: `npm run format && ./vendor/bin/pint`
- **Reference `.kiro/specs/` tasks** when implementing features
- **Note:** Recent changes include many-to-many relationship between Bill and Container via pivot table with timestamps and unique constraint. Pivot includes 'note' field (ensure migration updated if missing).

---

**Questions? Check**:
- Laravel docs: https://laravel.com/docs/12.x
- Inertia docs: https://inertiajs.com
- Vue 3 docs: https://vuejs.org
- Tailwind docs: https://tailwindcss.com
