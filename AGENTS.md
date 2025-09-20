# Repository Guidelines

## Project Structure & Module Organization
Backend domain logic lives in pp/ and is split into Models, Queries, Repositories, and Services for the bill workflow. Additions should follow that layering so controllers in pp/Http/Controllers stay thin. HTTP routes are grouped in outes/web.php, outes/api.php, and outes/settings.php, each pointing to Inertia screens under esources/js/pages. Shared Vue composition utilities sit in esources/js/composables, with reusable atoms in esources/js/components. Styling is centralized in esources/css/app.css, while static uploads go in public/. Database migrations, factories, and seeders live in database/.

## Build, Test, and Development Commands
Install dependencies with composer install and 
pm install. Use composer run dev to boot the Laravel server, queue listener, and Vite watcher together; 
pm run dev is available when you only need the front-end. 
pm run build produces the production bundle, and 
pm run build:ssr adds the server renderer required by Inertia SSR. Run database updates with php artisan migrate (append --seed when new seeders ship). Clear caches via php artisan config:clear before debugging environment issues.

## Coding Style & Naming Conventions
Editor settings default to LF and four-space indentation via .editorconfig. Keep PHP formatted with ./vendor/bin/pint, and run 
pm run lint plus 
pm run format:check before pushing. TypeScript and Vue components should use PascalCase filenames under pages/ and components/, camelCase composables, and keep props typed via the shared esources/js/types module. Favor descriptive enums (see pp/Enums/CuttingTestType.php) and consistent bill identifiers like BL-YYYY-###.

## Testing Guidelines
Feature tests rely on Pest wrapping PHPUnit; place request-driven specs in 	ests/Feature and pure units in 	ests/Unit. Follow the existing 	est_* naming so Laravel auto-discovers the methods. Run the full suite with php artisan test; use ./vendor/bin/pest --filter=Bill to focus on a module. Keep factories up to date when altering repositories so inertia assertions continue to match.

## Commit & Pull Request Guidelines
Recent history mixes English and Vietnamese narratives; move toward concise imperative subjects such as eat: add bill average outturn widget. Reference related routes or services when helpful (BillService), and note migrations or front-end contracts changed. Pull requests should summarize scope, list manual or automated test output, and attach screenshots or Vite recordings for UI changes. Mention any .env keys or queue listeners contributors must toggle.
