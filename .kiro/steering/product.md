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