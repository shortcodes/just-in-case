# Repository Guidelines

## Project Structure & Module Organization
Application logic lives in `app/`, with HTTP controllers and requests under `app/Http`. Domain services, jobs, and policies follow the same namespace. Front-end code sits in `resources/js`, organized by `Components`, `Layouts`, and Inertia `Pages`; shared utilities land in `resources/js/composables`, `data`, and `lib`. Tailwind styles are in `resources/css`. Blade fallbacks remain in `resources/views`. Routing is split between `routes/web.php` for the UI surface and `routes/api.php` for JSON endpoints. Database migrations, factories, and seeders are in `database/`, while feature and unit tests reside in `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands
Run `composer run setup` to provision dependencies, environment files, and a bundled build. Use `composer run dev` for the full local stack (Laravel server, queue listener, log stream, and Vite dev server). For front-end-only work, `npm run dev` launches Vite hot reload. `npm run build` compiles assets for production, and `composer run test` (or `php artisan test`) executes the automated test suite after clearing cached config.

## Coding Style & Naming Conventions
Adhere to Laravel’s PSR-12 defaults with strict typing where possible. Format PHP changes via `./vendor/bin/pint`. Vue and TypeScript modules use single-file components with script setup, four-space indentation, and PascalCase filenames (`ExampleModal.vue`). Shared composables follow the `useThing.ts` pattern, and utility modules use camelCase exports. Tailwind classes should prefer design tokens already defined in `tailwind.config.js`.

## Testing Guidelines
Feature tests cover routed workflows and Inertia responses; unit tests target services and helpers. Create new tests alongside the code under test (`tests/Feature/...ControllerTest.php`, `tests/Unit/...ServiceTest.php`). Run `php artisan test --parallel` for faster feedback, and apply dataset factories for model setup. Aim to back new endpoints or Vue flows with at least one happy-path feature test and relevant edge-case assertions.

## Commit & Pull Request Guidelines
Follow conventional commits (`feat:`, `fix:`, `chore:`, etc.) reflecting the scope you touch, mirroring the existing history. Keep commits focused and reference work items with `#issue-id` when applicable. Pull requests should summarize the change, list affected routes or components, include screenshots for UI adjustments, and mention any config migrations or artisan tasks required. Confirm that `composer run test` and `npm run build` succeed locally before requesting review.
