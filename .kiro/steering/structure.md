---
inclusion: always
---

# Architecture & Code Structure

## Internationalization (i18n) Requirement
**CRITICAL**: Never hardcode text strings in code. Always use the existing i18n system for all user-facing text.

## Required Architecture Pattern

**Strict Layered Architecture** - Follow this pattern for ALL new code:

1. **Controllers** (`app/Http/Controllers/`) - HTTP only, no business logic
2. **Services** (`app/Services/`) - Business logic and orchestration
3. **Repositories** (`app/Repositories/`) - Database access via Eloquent
4. **Requests** (`app/Http/Requests/`) - Input validation
5. **Enums** (`app/Enums/`) - Constants and domain values
6. **Queries** (`app/Queries/`) - Complex read operations

## File Organization Rules

### Backend Structure
```
app/
├── Http/Controllers/    # Thin controllers, HTTP coordination only
├── Http/Requests/       # Form validation classes
├── Models/             # Eloquent models with relationships
├── Services/           # Business logic (BillService, ContainerService)
├── Repositories/       # Database access (BillRepository, ContainerRepository)
├── Enums/             # Domain constants (CuttingTestType)
├── Queries/           # Complex queries (BillQuery, ContainerQuery)
└── Providers/         # Service providers
```

### Frontend Structure
```
resources/js/
├── components/        # Vue components (PascalCase)
├── composables/       # Reusable Vue logic
├── lib/              # Utility functions
└── components/ui/    # shadcn-vue UI components
```

## Naming Conventions

- **PHP Classes**: PascalCase (`BillService`, `ContainerRepository`)
- **PHP Files**: snake_case (`bill_service.php`)
- **Vue Components**: PascalCase (`BillForm.vue`, `ContainerList.vue`)
- **Variables**: camelCase (JS/TS), snake_case (PHP)
- **Database**: snake_case tables/columns

## Import Aliases
- `@/components` → `resources/js/components`
- `@/composables` → `resources/js/composables`
- `@/lib` → `resources/js/lib`
- `@/components/ui` → `resources/js/components/ui`

## Code Quality Requirements

### PHP Standards
- Use `declare(strict_types=1)` in all PHP files
- Apply PSR-12 coding standards
- Constructor injection for dependencies
- Type hints for all parameters and return values

### Domain-Specific Rules
- **Bill/Container/CuttingTest** models require corresponding Repository and Service
- **Bill-Container** relationship is many-to-many via pivot table `bill_container`
- Container model provides `bill()` method for accessing first associated bill
- Container model provides `bill_id` attribute for backward compatibility
- Use `CuttingTestType` enum for test types (1-4)
- All weight calculations must be in Service classes
- Validation alerts for weight discrepancies in Services

### Vue/TypeScript
- Composition API only
- Full TypeScript typing
- Use composables for shared logic
- shadcn-vue components for UI

## Required Dependencies Flow
```
Controller → Service → Repository → Model
         ↘ Request (validation)
```

Never skip layers - Controllers must not call Repositories directly.
