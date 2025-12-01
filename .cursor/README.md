# Cursor Rules Organization

All Cursor rules are organized using nested rules as per [Cursor documentation](https://cursor.com/docs/context/rules#nested-rules).

## Structure

```
.cursor/
├── rules/                                 # Project-wide shared rules
│   ├── backend-frontend-integration.mdc  # Always applied
│   ├── fullstack-feature.mdc            # Context-aware
│   └── testing-pyramid.mdc              # Context-aware (test files)
├── api/
│   └── .cursor/
│       └── rules/                         # API-specific rules (auto-attach in api/)
│           ├── design-patterns.mdc
│           ├── laravel-boost.mdc
│           └── testing-pyramid.mdc
└── client/
    └── .cursor/
        └── rules/                         # Client-specific rules (auto-attach in client/)
            ├── api-testing.mdc
            ├── playwright.mdc
            ├── testing-pyramid.mdc
            └── vue.mdc
```

## How Nested Rules Work

According to [Cursor's nested rules documentation](https://cursor.com/docs/context/rules#nested-rules), nested rules automatically attach when files in their directory are referenced:

- **Root rules** (`.cursor/rules/`): Apply project-wide
- **API rules** (`api/.cursor/rules/`): Automatically attach when working in the `api/` directory
- **Client rules** (`client/.cursor/rules/`): Automatically attach when working in the `client/` directory

## Rule Categories

### Shared Rules (Root `.cursor/rules/`)
- **backend-frontend-integration.mdc**: Always applied - Enforces API response format, authentication patterns, type safety
- **fullstack-feature.mdc**: Context-aware - Guides full-stack feature development workflow
- **testing-pyramid.mdc**: Context-aware - Enforces testing pyramid principles for both backend and frontend tests

### API Rules (`api/.cursor/rules/`)
- **design-patterns.mdc**: Laravel design patterns and best practices
- **laravel-boost.mdc**: Laravel Boost framework guidelines
- **testing-pyramid.mdc**: Backend testing pyramid principles (globs: `tests/**/*Test.php`)

### Client Rules (`client/.cursor/rules/`)
- **api-testing.mdc**: Frontend API testing guidelines
- **playwright.mdc**: Playwright E2E testing guidelines
- **testing-pyramid.mdc**: Frontend testing pyramid principles (globs: `tests/**/*.spec.ts`)
- **vue.mdc**: Vue.js development guidelines

## Usage

Rules are automatically applied by Cursor based on:
- `alwaysApply: true` - Always active (backend-frontend-integration.mdc)
- `globs` patterns - Applied when files match the patterns
- Directory context - Nested rules automatically attach when working in their directory

## References

See `AGENTS.md` in the root directory for the development agent workflow.
