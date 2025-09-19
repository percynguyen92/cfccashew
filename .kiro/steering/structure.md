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
