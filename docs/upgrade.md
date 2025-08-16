# Upgrade Notes

- Laravel 8+ removed the global factory() helper. If you copy examples from legacy tests, update to class-based factories or install laravel/legacy-factories for dev.
- When changing config('acquaintances.tables.*'), ensure published migrations and runtime config match.
- Events listed in docs/events.md are part of expected behavior; keep dispatching them when modifying core flows.
