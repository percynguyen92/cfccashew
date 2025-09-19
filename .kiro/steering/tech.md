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