# Copilot Instructions for CFCCashew Agents Module

## Project Overview

- **Domain:** Internal inspection tool for managing Bills, Containers, and Cutting Tests.
- **Stack:** Laravel 12 (PHP 8.2+), Vue 3.5+ (TypeScript, Inertia.js), Tailwind CSS 4.1+, MySQL.
- **Architecture:** Strict layering—Controllers (thin), Services (business logic), Repositories (DB access), Queries (reporting), Enums (constants).

## Key Patterns & Structure

- **Backend:**
    - `app/Models/`: Eloquent models (Bill, Container, CuttingTest, User).
    - `app/Repositories/`: One per model, encapsulates DB logic (e.g., BillRepository).
    - `app/Services/`: Domain logic, always use constructor DI for repositories.
    - `app/Queries/`: Complex selects, reporting, filtering.
    - `app/Enums/`: PHP enums for domain constants.
    - `app/Http/Controllers/`: Route endpoints, keep thin.
- **Frontend:**
    - `resources/js/pages/`: Inertia screens (route targets).
    - `resources/js/components/`: Vue components (PascalCase).
    - `resources/js/composables/`: Reusable Vue logic (camelCase).
    - `resources/js/types/`: Shared TypeScript types.
- **Testing:**
    - Pest + PHPUnit. Feature tests in `tests/Feature`, unit tests in `tests/Unit`. Factories in `database/factories/`.

## Developer Workflows

- **Install:** `composer install` & `npm install`
- **Dev:** `composer run dev` (Laravel + Vite + Queue), `npm run dev` (frontend only)
- **Build:** `npm run build`, `npm run build:ssr`
- **Test:** `php artisan test`, `./vendor/bin/pest --filter=Bill`
- **Lint/Format:** `npm run lint`, `npm run format`, `./vendor/bin/pint`
- **Migrate DB:** `php artisan migrate`, `php artisan migrate:fresh --seed`

## Conventions & Best Practices

- **PSR-12** style, `declare(strict_types=1)` everywhere.
- **Constructor injection** for all dependencies.
- **Enums** for all fixed domain values.
- **DTOs** encouraged for data passing.
- **Naming:** PascalCase for components, camelCase for composables, descriptive enums
- **Validation:** Use custom Request classes for input validation.
- **Derived fields:** Calculate container/cutting test weights as per schema in `AGENTS.md`.

## Integration & Communication

- **Inertia.js** for SPA navigation between Laravel and Vue.
- **SSR:** Use `npm run build:ssr` for server-side rendering.
- **Icons:** Use Lucide Vue Next.
- **UI:** Use shadcn-vue with Tailwindcss.

## Examples

- **Repository pattern:** See `app/Repositories/BillRepository.php`.
- **Service pattern:** See `app/Services/BillService.php`.
- **Query pattern:** See `app/Queries/BillQuery.php`.
- **Vue page:** See `resources/js/pages/`.

## Commit & PR Guidelines

- Use imperative, concise commit subjects (e.g., `feat: add bill average outturn widget`).
- PRs must summarize scope, include test results, and attach UI screenshots for front-end changes.

---

For more details, see `AGENTS.md` and `ARCHITECTURE.md`.
