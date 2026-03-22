# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run tests
./vendor/bin/phpunit

# Run a single test file
./vendor/bin/phpunit tests/Registry/ConfigurableServiceRegistryTest.php

# Static analysis (level 9)
./vendor/bin/phpstan

# Automated refactoring check
./vendor/bin/rector --dry-run

# Run all quality checks (via GrumPHP)
vendor/bin/grumphp run

# Run specific GrumPHP tasks
vendor/bin/grumphp run --tasks=phpunit
vendor/bin/grumphp run --tasks=phpstan
vendor/bin/grumphp run --tasks=infection
vendor/bin/grumphp run --tasks=rector
```

Do not run any of the quality tests for single files, always complete suite.

## Architecture

This is a Symfony bundle (`jwderoos/configurable`) that provides a **trait-based configuration registry pattern** — a way to create services that can be dynamically matched to compatible configuration objects.

### Core Pattern

The system connects **configurable services** to **configuration objects** through a registry. A service declares what configuration class it supports; the registry finds which services support a given configuration at runtime.

### Key Abstractions

**Services** implement `ConfigurableServiceInterface` and use `ConfigurableServiceTrait`. They declare:
- A supported configuration class via `getConfigurationClass(): string`
- Allowed options via `CONFIG_*` class constants
- Option validation via OptionsResolver (in `getOptionsResolver()`)

**Configurations** implement `ConfigurableServiceConfigurationInterface` and use `ConfigurationPropertiesTrait`. They hold named properties (key/value pairs) and validate themselves against a given service's OptionsResolver.

**Properties** implement `ConfigurableServiceConfigurationPropertyInterface` and use `ConfigurationPropertyTrait`. Values are stored as strings; arrays are JSON-serialized.

**Registry** (`ConfigurableServiceRegistry`) is injected with all tagged services and provides `getConfigurableServicesByConfiguration()` — returns all services whose declared configuration class matches the given configuration (handles Doctrine proxy classes and inheritance).

### Inheritance

`InheritedConfigurableServiceConfigurationInterface` extends the base configuration with a parent reference. `InheritedConfigurationPropertiesTrait` resolves properties by walking up the parent chain, and the registry collects applicable services from both child and parent.

### Symfony Integration

- `ConfigurableBundle` registers `ConfigurableExtension`
- `ConfigurableExtension` loads `Resources/config/services.yaml`, which tags all `ConfigurableServiceInterface` implementations and wires them into the registry via `tagged_iterator`
- Services using the bundle get automatic autoconfigure support

### Quality Requirements

- PHPStan level 9
- 100% code coverage on phpunit
- 100% mutation score index (Infection)
- PSR-12 code style
- No magic numbers, no copy-paste blocks ≥10 lines
- GrumPHP enforces all checks as git hooks

After every code change, run the full GrumPHP suite before considering the task done:
vendor/bin/grumphp run 
All checks must pass. Fix any failures before responding to the user.

### Notes
- `declare(strict_types=1)` is required in all files
- PHP 8.3+ with PSR-4 autoloading: `jwderoos\Configurable\` → `src/`, `jwderoos\Configurable\tests\` → `tests/`
