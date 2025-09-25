# AGENTS.md

## Technology Stack

### Backend

- **PHP**: 8.2+
- **Laravel**: 12.0 framework
- **Database**: MySQL (production), SQLite (local/dev optional)
- **Authentication**: Laravel Fortify
- **Testing**: Pest PHP + PHPUnit

### Frontend

- **Vue.js**: 3.5+ with Composition API
- **TypeScript**: Enabled
- **Inertia.js**: 2.0+ for SPA-like navigation
- **Tailwind CSS**: 4.1+ utility-first
- **UI Components**: shadcn-vue, Reka UI primitives
- **Icons**: Lucide Vue Next

### Build Tools

- **Vite**: 7.0+
- **Laravel Vite Plugin**: Bundling integration
- **Wayfinder**: Laravel route helpers

### Code Quality

- **ESLint**: Vue + TypeScript linting
- **Prettier**: Code formatting (Tailwind plugin)
- **Laravel Pint**: PHP code style fixer

---

## Project Structure

### Root Level

- **artisan** – Laravel CLI
- **composer.json** – PHP dependencies
- **package.json** – Node.js dependencies
- **vite.config.ts** – Vite configuration
- **components.json** – shadcn-vue config

### Backend (Laravel)

```
app/
├── Http/           # Controllers, Middleware, Requests
├── Models/         # Eloquent models
├── Repositories/   # DB access encapsulation
├── Services/       # Domain business logic
├── Queries/        # Query classes (filters, reports)
├── Enums/          # Domain constants
└── Providers/      # Service providers

bootstrap/
├── app.php
├── providers.php
└── cache/

config/
├── app.php
├── database.php
├── auth.php
└── ...

database/
├── migrations/
├── seeders/
├── factories/
└── database.sqlite

routes/
├── web.php
├── api.php
└── settings.php
```

### Frontend (Vue + TS)

```
resources/
├── js/
│   ├── app.ts
│   ├── ssr.ts
│   ├── pages/        # Inertia screens
│   ├── components/   # Vue components
│   ├── composables/  # Reusable Vue logic
│   └── types/        # Shared TS types
├── css/app.css
└── views/            # Blade entrypoints

public/
├── build/
├── index.php
└── uploads/
```

### Testing

```
tests/
├── Feature/
├── Unit/
├── Pest.php
└── TestCase.php
```

### Config Files

- **.env** and **.env.example**
- **eslint.config.js**
- **.prettierrc**
- **phpunit.xml**
- **tsconfig.json**

---

## Architecture & Code Conventions

- **Framework Structure**
    - Controllers must remain thin.
    - Services hold business logic.
    - Repositories encapsulate DB queries.
    - Requests manage validation.
    - Enums define constants.
    - Queries handle read/report logic.
    - DTOs encouraged for data passing.

- **Repositories**
    - One per domain model (BillRepository, ContainerRepository, CuttingTestRepository).
    - Expose CRUD and encapsulate query logic.

- **Services**
    - Contain domain processes (BillService, ContainerService, CuttingTestService).
    - Depend on repositories via DI.

- **Requests**
    - Custom Request classes validate input.

- **Enums**
    - Use PHP Enums for fixed domain values.

- **Queries**
    - Extract complex selects into dedicated Query classes.

- **Conventions**
    - PSR-12 coding style.
    - `declare(strict_types=1)` everywhere.
    - Constructor injection mandatory.
    - Small, testable classes.

---

## Build, Test, and Development Commands

### Dependencies

```bash
composer install
npm install
```

### Development

```bash
composer dev         # Laravel + Queue + Vite
composer dev:ssr     # With SSR support
npm run dev          # Frontend only
```

### Building

```bash
npm run build
npm run build:ssr
```

### Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan make:migration create_table_name
```

### Testing

```bash
composer test
php artisan test
php artisan test --filter=TestName
./vendor/bin/pest --filter=Bill
```

### Code Quality

```bash
npm run lint
npm run format
npm run format:check
./vendor/bin/pint
```

---

## Testing Guidelines

- Feature tests → `tests/Feature`
- Unit tests → `tests/Unit`
- Naming convention: `test_*`
- Run: `php artisan test` or Pest filters
- Keep factories aligned with repositories

---

## Commit & Pull Request Guidelines

- Commit subjects must be imperative and concise.  
  Example: `feat: add bill average outturn widget`
- Reference related services, migrations, or contracts.
- PR requirements:
    - Scope summary
    - Screenshots/recordings for UI changes
    - Test results included
    - `.env` keys or queue listeners documented

---

## Product Specification

### Product Overview

- **Product Name**: CFCCashew Agents Module
- **Goal**: Manage Bills, Containers, and Cutting Tests with agent-like structure.
- **Scope**: Internal inspection tool.
- **Target Users**: Inspectors and auditors.

### Key Features

- Structured repository + service + query layers.
- SSR support for Inertia.
- Bill, Container, and CuttingTest workflow automation.

### Development Plan

- Stick to task in .kiro folder

```
├── specs/                    #features plan
│   └── navigation-system/
│       ├── design.md
│       ├── requirements.md
│       └── tasks.md
└── steering/                 #backbone plan
    ├── product.md
    ├── structure.md
    └── tech.md
```

---

## Database Schema

### General Conventions

- **Database**: `cfccashew`
- **Engine**: InnoDB
- **Charset/Collation**: `utf8mb4` / `utf8mb4_unicode_ci`
- **Timestamps & Soft Deletes**: All tables include `created_at`, `updated_at`, `deleted_at`
- **Relationships**
    - A **Bill** has many **Containers**
    - A **Bill** has many **CuttingTests**
    - A **Container** has many **CuttingTests**

---

### bills

Represents a Bill of Lading.

**Columns**

- `id` BIGINT UNSIGNED PK AUTO_INCREMENT
- `bill_number` VARCHAR(20) NULL _(not unique; a bill number may repeat)_
- `seller` VARCHAR(255) NULL
- `buyer` VARCHAR(255) NULL
- `note` TEXT NULL
- `created_at` TIMESTAMP NULL
- `updated_at` TIMESTAMP NULL
- `deleted_at` TIMESTAMP NULL

**Indexes**

- `idx_bills_created_at` on (`created_at`)

**Relations**

- `Bill` hasMany `Container`
- `Bill` hasMany `CuttingTest`

---

### containers

Containers linked to a Bill. All container weights use **kg**. Only `w_jute_bag`, `w_tare`, `w_net` allow decimals.

**Columns**

- `id` BIGINT UNSIGNED PK AUTO_INCREMENT
- `bill_id` BIGINT UNSIGNED NOT NULL (FK → `bills.id`)
- `truck` VARCHAR(20) NULL
- `container_number` VARCHAR(11) NULL _(ISO: 4 letters + 7 digits)_
- `quantity_of_bags` INT UNSIGNED NULL
- `w_jute_bag` DECIMAL(4,2) DEFAULT 1.00 _(kg per bag, decimal allowed)_
- `w_total` INT UNSIGNED NULL _(gross weighbridge, kg)_
- `w_truck` INT UNSIGNED NULL _(truck tare, kg)_
- `w_container` INT UNSIGNED NULL _(container tare, kg)_
- `w_gross` INT UNSIGNED NULL _(calculated gross cargo, kg)_
- `w_dunnage_dribag` INT UNSIGNED NULL _(dunnage + dribag, kg)_
- `w_tare` DECIMAL(10,2) NULL _(quantity_of_bags \* w_jute_bag)_
- `w_net` DECIMAL(10,2) NULL _(net cargo weight)_
- `note` TEXT NULL
- `created_at` TIMESTAMP NULL
- `updated_at` TIMESTAMP NULL
- `deleted_at` TIMESTAMP NULL

**Validations / Derived fields**

- `w_gross = w_total - w_truck - w_container`
- `w_tare = quantity_of_bags * w_jute_bag`
- `w_net = w_gross - w_dunnage_dribag - w_tare`

**Constraints**

- FK `bill_id` → `bills.id`
- CHECK `container_number` matches ISO format `^[A-Z]{4}\d{7}$` when not null

**Indexes**

- `idx_containers_created_at` on (`created_at`)
- `idx_containers_container_number` on (`container_number`)

**Relations**

- `Container` belongsTo `Bill`
- `Container` hasMany `CuttingTest`

---

### cutting_tests

Inspection / cutting test results. `sample_weight` and all `w_*` fields use **grams**. `moisture` uses **percent**. `outturn_rate` uses **lbs/80kg** with valid range 0–60.

**Columns**

- `id` BIGINT UNSIGNED PK AUTO_INCREMENT
- `bill_id` BIGINT UNSIGNED NOT NULL (FK → `bills.id`)
- `container_id` BIGINT UNSIGNED NULL (FK → `containers.id`, NULL for final samples)
- `type` SMALLINT NOT NULL
    - 1 = final sample first cut
    - 2 = final sample second cut
    - 3 = final sample third cut
    - 4 = container cut
- `moisture` DECIMAL(4,2) NULL _(0–100, %)_
- `sample_weight` SMALLINT UNSIGNED NOT NULL DEFAULT 1000 _(grams)_
- `nut_count` SMALLINT UNSIGNED NULL
- `w_reject_nut` SMALLINT UNSIGNED NULL _(grams)_
- `w_defective_nut` SMALLINT UNSIGNED NULL _(grams)_
- `w_defective_kernel` SMALLINT UNSIGNED NULL _(grams)_
- `w_good_kernel` SMALLINT UNSIGNED NULL _(grams)_
- `w_sample_after_cut` SMALLINT UNSIGNED NULL _(grams)_
- `outturn_rate` DECIMAL(5,2) NULL _(0–60, lbs/80kg)_
- `note` TEXT NULL
- `created_at` TIMESTAMP NULL
- `updated_at` TIMESTAMP NULL
- `deleted_at` TIMESTAMP NULL

**Validations / Alerts**

- `outturn_rate = (w_defective_kernel/2 + w_good_kernel) * 80 / 453.6`
- Alert if `(sample_weight - w_sample_after_cut) > 5`
- Alert if `(w_defective_nut/3.3 - w_defective_kernel) > 5`
- Alert if `((sample_weight - w_reject_nut - w_defective_nut)/3.3 - w_good_kernel) > 10`

**Constraints**

- FK `bill_id` → `bills.id`
- FK `container_id` → `containers.id`
- CHECK moisture range: `0 ≤ moisture ≤ 100`
- CHECK outturn range: `0 ≤ outturn_rate ≤ 60`

**Indexes**

- `idx_cutting_tests_created_at` on (`created_at`)

**Relations**

- `CuttingTest` belongsTo `Bill`
- `CuttingTest` belongsTo `Container` (nullable)

## User Stories

### Bill of Lading

- Create and manage Bills with seller, buyer, number.
- View Bills with related containers and tests.

### Containers

- Input weights, auto calculate tare/gross/net.
- Expand rows to show cutting tests.
- Add new cutting tests.

### Cutting Tests

- Record moisture and outturn.
- Link to containers or final samples.

---

## Non-Functional Requirements

- Responsive, desktop-first UI.
- Alexandria font via Bunny Fonts.
- SSR-compatible.

---

## Future Considerations

- OCR integration for weight slips.
- Extend to automated inspection agents.
- Dashboard widgets with JSON endpoints.
