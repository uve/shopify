# 📘 GitHub Copilot Repository Custom Instructions

These instructions are tailored to guide GitHub Copilot to work effectively within this repository using **Laravel** and **PHP**, following strict, high-quality engineering practices with a strong emphasis on **TDD**.

## 🔧 General Coding Standards

- **Only use PHP and Laravel** for application code.
- Apply DRY, KISS, YAGNI, SOLID, Clean Code, and Clean Architecture principles.
- Prefer clear domain boundaries: keep business logic out of controllers, console commands, and Blade views.
- Use modern PHP: type hints, return types, meaningful exceptions, and small focused classes.
- Favor Laravel conventions first (service container, middleware, policies, form requests, jobs, events).

## 🗂️ Project Structure (Laravel)

- Keep framework concerns in their conventional locations:
  - `app/` application code (Actions/Services/Domain objects as needed)
  - `routes/` HTTP/console routes
  - `config/` configuration (the only place `env()` should be read)
  - `database/` migrations/seeders/factories
  - `resources/` views/assets
  - `tests/` unit + feature tests
- Do not introduce custom folder structures unless there’s a clear need and it stays consistent with Laravel.

## ⚙️ Configuration Management

- **All environment variables MUST be strictly required** with **no default fallbacks** for production behavior.
- Commit a `.env.example` containing all required variables with example (non-secret) values.
- Never read `env()` outside `config/*.php`. Use `config('...')` everywhere else.
- Fail fast on boot when required configuration is missing or still set to placeholder values.

**Example validation pattern (Laravel):**
```php
<?php

$apiKey = config('services.some_service.api_key');

if (!is_string($apiKey) || $apiKey === '') {
    throw new RuntimeException('SOME_SERVICE_API_KEY is required but not set');
}

if ($apiKey === 'your-key-here') {
    throw new RuntimeException('SOME_SERVICE_API_KEY is set to a placeholder value');
}
```

## 🧪 Testing Guidelines (TDD)

- Default workflow is **TDD**: write a failing test → implement → refactor.
- Add tests for every change:
  - Happy path
  - Validation failures / invalid inputs
  - Edge cases
  - Error handling paths
- Use Laravel’s built-in testing stack (PHPUnit via `php artisan test`). Use Pest only if the repo already uses it.
- Prefer:
  - **Feature tests** for HTTP endpoints, queues, events, and integration through Laravel boundaries.
  - **Unit tests** for small pure classes (value objects, domain services, rules).
- Use PHPUnit data providers for multi-scenario coverage.
- Use Laravel test helpers and fakes:
  - `Http::fake()` for external HTTP
  - `Queue::fake()`, `Event::fake()`, `Notification::fake()`
  - `Carbon::setTestNow()` / time travel
- Database tests should use `RefreshDatabase` and factories.

## 📄 Documentation Rules

- Maintain and update only `README.md` for technical documentation (setup, env vars, run/test instructions).
- Do not create additional docs files (e.g., `USAGE.md`, `QUICKSTART.md`).
- Exception: `CHANGELOG.md` is allowed.
- Never hardcode provider versions or model names in docs; reference environment variables instead.

## 🪵 Logging Standards

- Use Laravel logging (`Log` facade or injected logger). Never use `echo`, `print_r`, `var_dump`, `dump()`, or `dd()` in committed code.
- Log with context arrays for discoverability (e.g., `request_id`, `user_id`, `order_id`).
- Keep logs minimal when idle; be detailed when actions/errors occur.
- Prefer existing log channels and rotation (`daily`) and do not invent custom logging unless needed.

## 🌐 External Services & HTTP

- Use Laravel HTTP client (`Http::...`) with explicit timeouts.
- For idempotent operations, use retries (max 3) with exponential backoff and jitter.
- Handle rate-limits (`429`), timeouts, and non-2xx responses explicitly.
- Avoid leaking secrets into logs; redact sensitive fields.

## 🔒 Security Best Practices

- Validate all incoming requests using Form Requests (preferred) or validator rules.
- Avoid mass-assignment issues: use `$fillable`/`$guarded` intentionally.
- Prefer Eloquent query builder bindings; avoid raw SQL unless necessary and well-reviewed.
- Authorize access via policies/gates; do not rely on client-side checks.
- Never hardcode secrets; load from environment via `config()`.

## 🧹 Code Quality Tooling

- Format using Laravel Pint (preferred) if present.
- Use static analysis (PHPStan/Larastan) if configured.
- Keep dependencies tidy via Composer; avoid unnecessary packages.

## 🤖 AI Collaboration & Behavior

- **Think Step-by-Step**: Before writing code, outline the plan. Explain the approach, the files to be modified, and the reasoning behind the changes.
- **Be Proactive**: Don't just fulfill the request. Identify potential issues, suggest improvements, and recommend best practices. If you see anti-patterns, refactor them.
- **Ask Clarifying Questions**: If the user's request is ambiguous or incomplete, ask for more details rather than making assumptions.
- **Adhere Strictly to These Instructions**: Your primary goal is to follow every rule in this document. Announce if a user request conflicts with these instructions and suggest a compliant alternative.

## ✅ Final Checklist for Every PR

- [ ] All code is written in high-quality PHP and follows Laravel conventions.
- [ ] `.env.example` is updated with all required variables.
- [ ] Required config is validated on boot (no silent defaults for required values).
- [ ] No committed debugging calls (`dd`, `dump`, `var_dump`, `print_r`, `die`).
- [ ] Logs are structured with context and are not noisy.
- [ ] Tests are added/updated using TDD and cover success + failure paths.
- [ ] `php artisan test` passes.
- [ ] Code is formatted (Pint if configured).
- [ ] Static analysis passes (PHPStan/Larastan if configured).
- [ ] `README.md` is updated when behavior/config changes.
- [ ] No secrets or provider-specific versions/models are hardcoded.

---

**These instructions are designed to ensure the highest possible development quality and speed, maximizing developer efficiency while maintaining clean, reliable, and production-ready code.**
