# Testing

This package is intended to be tested with Orchestra Testbench. The current legacy test suite in tests/ assumes a full Laravel app and legacy factories; see .junie/guidelines.md for details.

Quick smoke test:

```bash
vendor/bin/phpunit --filter SanityTest
```

To migrate to Testbench (recommended):
- Make your TestCase extend Orchestra\Testbench\TestCase.
- Register the service provider in getPackageProviders().
- Set up in-memory SQLite and acquaintances.migrations = true in getEnvironmentSetUp().
- Provide a users table and a factory mechanism compatible with your Laravel version.

See .junie/guidelines.md for an in-depth guide.
