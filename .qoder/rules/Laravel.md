---
trigger: always_on
alwaysApply: true
---

# Technology Stack

## Backend
- **PHP**: 8.2+ required
- **Laravel**: 12.0 framework
- **Database**: MySQL, configurable for production
- **Authentication**: Laravel Fortify
- **Testing**: Pest PHP testing framework

## Frontend
- **Vue.js**: 3.5+ with Composition API
- **TypeScript**: Full type safety
- **Inertia.js**: 2.0+ for SPA-like experience
- **Tailwind CSS**: 4.1+ utility-first styling
- **UI Components**: shadcn-vue with Reka UI primitives
- **Icons**: Lucide Vue Next

## Build Tools
- **Vite**: 7.0+ for fast development and building
- **Laravel Vite Plugin**: Asset bundling integration
- **Wayfinder**: Laravel route helpers for frontend

## Code Quality
- **ESLint**: Vue + TypeScript linting
- **Prettier**: Code formatting with Tailwind plugin
- **Laravel Pint**: PHP code style fixer

## Common Commands

### Development
```bash
# Start development server (Laravel + Vite + Queue)
composer dev

# Start with SSR support
composer dev:ssr

# Frontend only
npm run dev
```

### Testing
```bash
# Run PHP tests
composer test
# or
php artisan test

# Run specific test
php artisan test --filter=TestName
```

### Code Quality
```bash
# Format frontend code
npm run format

# Check formatting
npm run format:check

# Lint and fix
npm run lint

# Fix PHP code style
./vendor/bin/pint
```

### Building
```bash
# Build for production
npm run build

# Build with SSR
npm run build:ssr
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create migration
php artisan make:migration create_table_name
```

# Project Structure

## Root Level
- **artisan**: Laravel command-line interface
- **composer.json**: PHP dependencies and scripts
- **package.json**: Node.js dependencies and build scripts
- **vite.config.ts**: Vite build configuration
- **components.json**: shadcn-vue component configuration

## Backend (Laravel)
```
app/
├── Http/           # Controllers, middleware, requests
├── Models/         # Eloquent models
└── Providers/      # Service providers

bootstrap/
├── app.php         # Application bootstrap
├── providers.php   # Provider registration
└── cache/          # Bootstrap cache files

config/             # Configuration files
├── app.php         # Application config
├── database.php    # Database connections
├── auth.php        # Authentication config
└── ...

database/
├── migrations/     # Database schema migrations
├── seeders/        # Database seeders
├── factories/      # Model factories
└── database.sqlite # SQLite database file

routes/             # Route definitions
storage/            # File storage, logs, cache
```

## Frontend (Vue + TypeScript)
```
resources/
├── js/
│   ├── app.ts      # Main application entry
│   ├── ssr.ts      # SSR entry point
│   ├── components/ # Vue components
│   ├── composables/# Vue composables
│   └── lib/        # Utility functions
├── css/
│   └── app.css     # Main stylesheet (Tailwind)
└── views/          # Blade templates (minimal with Inertia)

public/
├── build/          # Compiled assets (generated)
├── index.php       # Application entry point
└── ...             # Static assets
```

## Testing
```
tests/
├── Feature/        # Feature/integration tests
├── Unit/           # Unit tests
├── Pest.php        # Pest configuration
└── TestCase.php    # Base test case
```

## Configuration Files
- **.env**: Environment variables (not committed)
- **.env.example**: Environment template
- **.prettierrc**: Code formatting rules
- **eslint.config.js**: JavaScript/TypeScript linting
- **phpunit.xml**: PHP testing configuration
- **tsconfig.json**: TypeScript configuration

## Conventions

### File Naming
- **PHP**: PascalCase for classes, snake_case for files
- **Vue**: PascalCase for components
- **TypeScript**: camelCase for variables, PascalCase for types/interfaces

### Directory Organization
- Place Vue components in `resources/js/components/`
- Use `resources/js/composables/` for reusable Vue logic
- Store utilities in `resources/js/lib/`
- Follow Laravel conventions for backend structure

### Import Aliases (configured in components.json)
- `@/components` → `resources/js/components`
- `@/composables` → `resources/js/composables`
- `@/lib` → `resources/js/lib`
- `@/components/ui` → `resources/js/components/ui`

## Architecture & Code Conventions

- **Framework Structure**
  - Follow Laravel's MVC pattern strictly.
  - Avoid fat controllers. Controllers must only coordinate requests and responses.
  - Implement business logic in **Service classes** (app/Services).
  - Use **Repository classes** (app/Repositories) for all database access through Eloquent.
  - Keep validation in **Form Request classes** (app/Http/Requests).
  - Use **Enum classes** (app/Enums) for all constant values such as CuttingTest types, statuses, etc.
  - Extract reusable queries into **Query classes** (app/Queries).
  - Use **DTOs (Data Transfer Objects)** when passing structured data between layers (optional but encouraged).

- **Repositories**
  - Each main model (Bill, Container, CuttingTest) must have a repository.
  - Repository exposes CRUD methods and encapsulates query logic.
  - Example: `BillRepository`, `ContainerRepository`, `CuttingTestRepository`.

- **Services**
  - Each domain process (e.g., managing Bills, calculating weights, evaluating cutting tests) must have a Service.
  - Services depend on repositories via dependency injection.
  - Example: `BillService`, `ContainerService`, `CuttingTestService`.

- **Requests**
  - All input validation is handled in custom Request classes.
  - Example: `StoreBillRequest`, `UpdateContainerRequest`, `StoreCuttingTestRequest`.

- **Enums**
  - Use PHP Enums (Laravel 9+ native) for values like CuttingTest type:
    - `FinalFirstCut`, `FinalSecondCut`, `FinalThirdCut`, `ContainerCut`.

- **Queries**
  - Use Query classes for complex read operations (filters, dashboard KPIs, reports).
  - Example: `BillQuery` (recent bills, by number), `ContainerQuery` (pending cutting tests), `CuttingTestQuery` (moisture distribution).

- **Separation of Concerns**
  - Controllers → thin, handle HTTP only.
  - Services → contain business rules.
  - Repositories → encapsulate database/Eloquent logic.
  - Requests → input validation.
  - Enums → domain constants.
  - Queries → read/report logic.

- **Additional Conventions**
  - Apply PSR-12 coding standards.
  - Use strict typing (`declare(strict_types=1)`) for PHP.
  - Always use constructor injection for dependencies.
  - Favor small, testable classes over monolithic ones.

# Product Specification

## Product Overview
- Product Name: CFCCashew Inspection System
- Goal: Manage Bill of Lading (Bill), containers, and inspection results (cutting tests).
- Primary User: Inspector.
- Scope: Internal tool, running on Laragon (local), for personal use.
- A full-stack web application template that combines Laravel (PHP backend) with Vue.js (frontend) using Inertia.js for seamless SPA-like experiences.

---

## Key Features
- Laravel 12 backend with modern PHP 8.3+
- Vue 3 frontend with TypeScript support
- MySQL for database
- Inertia.js for SPA-like navigation without API complexity
- Laravel Fortify for authentication
- Tailwind CSS with shadcn-vue components, reuse the existing Laravel Starter Kit style.
- Server-side rendering (SSR) support

---

## Database Schema

### General Conventions
- Database: `cfccashew`
- Engine: InnoDB, Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci`
- All tables include `created_at`, `updated_at`, `deleted_at` for timestamps and soft deletes
- Relationships:
  - A Bill has many Containers
  - A Bill has many CuttingTests
  - A Container has many CuttingTests

---

### bills
Represents a Bill of Lading.

**Columns**
- `id` BIGINT UNSIGNED PK AUTO_INCREMENT
- `bill_number` VARCHAR(20) NULL
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
Containers linked to a Bill.

**Columns**
- `id` BIGINT UNSIGNED PK AUTO_INCREMENT
- `bill_id` BIGINT UNSIGNED NOT NULL (FK → bills.id)
- `truck` VARCHAR(20) NULL
- `container_number` VARCHAR(11) NULL  
- `quantity_of_bags` INT UNSIGNED NULL
- `w_jute_bag` DECIMAL(4,2) DEFAULT 1.00
- `w_total` INT UNSIGNED NULL
- `w_truck` INT UNSIGNED NULL
- `w_container` INT UNSIGNED NULL
- `w_gross` INT UNSIGNED NULL
- `w_dunnage_dribag` INT UNSIGNED NULL
- `w_tare` DECIMAL(10,2) NULL
- `w_net` DECIMAL(10,2) NULL
- `note` TEXT NULL
- `created_at` TIMESTAMP NULL
- `updated_at` TIMESTAMP NULL
- `deleted_at` TIMESTAMP NULL
**Validate**
w_gross = w_total - w_truck - w_container
w_tare = quantity_of_bags * w_jute_bag
w_net = w_gross - w_dunnage_dribag - w_tare

**Constraints**
- FK `bill_id` → `bills.id`
- CHECK `container_number` matches ISO format (4 letters + 7 digits) if not null

**Indexes**
- `idx_containers_created_at` on (`created_at`)
- `idx_containers_container_number` on (`container_number`)

**Relations**
- `Container` belongsTo `Bill`
- `Container` hasMany `CuttingTest`

---

### cutting_tests
Inspection/cutting test results.

**Columns**
- `id` BIGINT UNSIGNED PK AUTO_INCREMENT
- `bill_id` BIGINT UNSIGNED NOT NULL (FK → bills.id)
- `container_id` BIGINT UNSIGNED NULL (FK → containers.id, NULL for final samples)
- `type` SMALLINT NOT NULL  
  - 1 = final sample first cut  
  - 2 = final sample second cut  
  - 3 = final sample third cut  
  - 4 = container cut
- `moisture` DECIMAL(4,2) NULL (percentage, 0–100)
- `sample_weight` SMALLINT UNSIGNED NOT NULL DEFAULT 1000 (grams)
- `nut_count` SMALLINT UNSIGNED NULL
- `w_reject_nut` SMALLINT UNSIGNED NULL
- `w_defective_nut` SMALLINT UNSIGNED NULL
- `w_defective_kernel` SMALLINT UNSIGNED NULL
- `w_good_kernel` SMALLINT UNSIGNED NULL
- `w_sample_after_cut` SMALLINT UNSIGNED NULL
- `outturn_rate` DECIMAL(5,2) NULL (valid 0–60, unit: lbs/80kg)
- `note` TEXT NULL
- `created_at` TIMESTAMP NULL
- `updated_at` TIMESTAMP NULL
- `deleted_at` TIMESTAMP NULL
**Validate**
outturn_rate = (w_defective_kernel/2 + w_good_kernel) * 80 / 453.6
alert if (sample_weight - w_sample_after_cut) > 5
alert if (w_defective_nut/3.3 - w_defective_kernel) > 5
alert if ((sample_weight - w_reject_nut - w_defective_nut)/3.3 - w_good_kernel) > 10

**Constraints**
- FK `bill_id` → `bills.id`
- FK `container_id` → `containers.id`
- CHECK moisture range: `0 ≤ moisture ≤ 100`
- CHECK outturn_rate range: `0 ≤ outturn_rate ≤ 60`

**Indexes**
- `idx_cutting_tests_created_at` on (`created_at`)

**Relations**
- `CuttingTest` belongsTo `Bill`
- `CuttingTest` belongsTo `Container` (nullable)

---

## Core Features (General)
1. **Bill of Lading (Bill)**
   - Each Bill contains details such as: billNumber, Seller, Buyer, ...
   - Each Bill is linked to multiple containers.
   - A Bill can have multiple containers
   - A Bill can also have up to 3 final samples (`type = 1–3`) stored in cutting_tests with `container_id = NULL`

2. **Containers**
   - Linked to a Bill.
   - Stores weights: quantity of bags, gross, tare, net, etc...
   - Container-level cutting tests use `type = 4` with a `container_id`
   - Displays related cutting_tests in an expandable row.
   - Allows adding new cutting_tests.

3. **Cutting Tests**
   - Linked to containers or final samples of a Bill.
   - Records moisture and other indicators (to be added).
   - Moisture should be displayed as `xx.x` (one decimal)
   - Defective nut and kernel should display as xxx/yyy.y (yyy.y = xxx/2)

4. **Dashboard**
   - Reuse the existing dashboard layout from the Laravel Starter Kit.
   - Add widgets:
     - **KPI Cards**:
       - Recent Bills (billNumber, Seller/Buyer, containers count, updatedAt, quick link)
       - Bills pending final cutting_test
     - **Alerts**:
       - Bills missing any final sample
       - Containers with moisture > 11
   - All widgets query via lightweight JSON endpoints.

---

## User Stories

### Bill of Lading
- As an inspector, I want to create a Bill with basic information (billNumber, Seller, Buyer, …) to serve as the root for containers and samples.
- As an inspector, I want to view a list of Bills I have entered so I can quickly access containers and test results.
- As an inspector, I want to store final sample results (1–3 cutting tests) for each Bill to summarize quality.

### Containers
- As an inspector, I want to input the gross, tare, and net weight of a container so the system can automatically calculate the actual cargo weight.
- As an inspector, I want to view cutting tests result directly in the container row for quick reference.
- As an inspector, I want to add a cutting test to a container right on the spot.

### Cutting Tests
- As an inspector, I want cutting tests to be correctly linked to their container or corresponding final sample.

## Non-Functional Requirements
- Responsive UI, desktop first.
- Font: Alexandria via Bunny Fonts.

---

## Future Considerations
- Integrate OCR tool to automatically extract weights from weigh-slips.
- Public to internet by Clouflare Tunel and Traefil