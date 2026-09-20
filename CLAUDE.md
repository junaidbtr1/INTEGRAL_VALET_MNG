# BTR Valet - White Label Valet/Parking SaaS

## Project Overview
A white-label multi-tenant valet and parking management SaaS system. One core system serving multiple clients (buildings, malls, societies) with separate branding, data, and configurations.

---

## Project Structure

```
btr_project/
├── backend/                → Laravel 11 (Pure REST API)
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/V1/          → API v1 controllers
│   │   │   │   ├── Admin/           → Super admin controllers
│   │   │   │   └── Tenant/          → Tenant-scoped controllers
│   │   │   ├── Middleware/
│   │   │   ├── Requests/            → Form request validation
│   │   │   └── Resources/           → API resources (transformers)
│   │   ├── Models/
│   │   │   ├── Concerns/            → Model traits (HasTenant, HasUuid, etc.)
│   │   │   └── Scopes/              → Global & local query scopes
│   │   ├── Services/                → Business logic layer
│   │   ├── Repositories/            → Database query layer
│   │   │   └── Contracts/           → Repository interfaces
│   │   ├── Enums/                   → PHP enums
│   │   ├── Events/
│   │   ├── Listeners/
│   │   ├── Notifications/
│   │   ├── Observers/               → Model observers
│   │   ├── Policies/                → Authorization policies
│   │   ├── Exceptions/              → Custom exception classes
│   │   ├── DTOs/                    → Data Transfer Objects
│   │   ├── Actions/                 → Single-purpose action classes
│   │   ├── Traits/
│   │   └── Helpers/                 → Global helper functions
│   ├── config/
│   ├── database/
│   │   ├── migrations/
│   │   ├── seeders/
│   │   └── factories/
│   ├── routes/
│   │   ├── api.php                  → All API routes
│   │   ├── api_v1.php               → V1 route definitions
│   │   ├── api_admin.php            → Super admin route definitions
│   │   └── channels.php             → Broadcasting channels
│   ├── stubs/                       → Custom artisan stub templates
│   └── tests/
│       ├── Feature/
│       │   ├── Api/
│       │   ├── Admin/
│       │   └── Tenant/
│       ├── Unit/
│       │   ├── Services/
│       │   ├── Repositories/
│       │   └── Actions/
│       └── Traits/                  → Test helper traits
│
├── frontend/               → Vue 3 SPA (Standalone)
│   ├── src/
│   │   ├── assets/
│   │   │   ├── styles/              → Global styles, variables, theme
│   │   │   ├── images/
│   │   │   └── fonts/
│   │   ├── components/
│   │   │   ├── common/              → Shared/reusable components
│   │   │   ├── layout/              → Layout components (Sidebar, Header, etc.)
│   │   │   ├── ui/                  → UI primitives (Button, Input, Modal, etc.)
│   │   │   └── forms/               → Form-specific components
│   │   ├── composables/             → Vue composables (hooks)
│   │   ├── directives/              → Custom Vue directives
│   │   ├── guards/                  → Route guards
│   │   ├── layouts/                 → Page layouts (AuthLayout, AdminLayout, etc.)
│   │   ├── pages/                   → Route-based pages
│   │   │   ├── auth/
│   │   │   ├── super-admin/
│   │   │   ├── admin/               → Tenant admin pages
│   │   │   └── valet/               → Valet staff pages
│   │   ├── router/                  → Vue Router config
│   │   │   ├── index.ts
│   │   │   ├── routes.ts
│   │   │   └── middleware.ts
│   │   ├── stores/                  → Pinia stores
│   │   ├── services/                → API service layer (axios)
│   │   ├── types/                   → TypeScript interfaces & types
│   │   │   ├── models/              → Backend model types
│   │   │   ├── api/                 → API request/response types
│   │   │   └── enums/               → Enum types (mirror backend)
│   │   ├── utils/                   → Helper functions
│   │   ├── constants/               → App-wide constants
│   │   ├── plugins/                 → Vue plugins
│   │   ├── i18n/                    → Internationalization files
│   │   │   ├── en.json
│   │   │   └── ar.json
│   │   ├── App.vue
│   │   └── main.ts
│   ├── public/
│   ├── .env.example
│   └── index.html
│
├── docs/                   → Architecture Decision Records (ADRs)
├── docker/                 → Docker configs (dev & prod)
│   ├── nginx/
│   ├── php/
│   └── docker-compose.yml
├── CLAUDE.md               → This file (coding rules)
└── .gitignore
```

---

## Tech Stack

### Backend
- **Framework:** Laravel 11 (PHP 8.4)
- **Database:** MySQL (primary), Redis (cache/queues/sessions)
- **Auth:** Laravel Sanctum (token-based API auth)
- **Roles & Permissions:** Spatie Laravel Permission v6 (spatie/laravel-permission)
- **Multi-tenancy:** stancl/tenancy v3
- **API Format:** RESTful JSON API
- **Queue:** Laravel Queue with Redis driver
- **Storage:** Laravel Storage (S3-compatible for production)
- **Search:** Laravel Scout (optional, for ticket/user search)
- **PDF/Receipts:** DomPDF or Snappy
- **Testing:** Pest PHP (primary), PHPUnit compatible
- **Code Quality:** Laravel Pint (formatter), PHPStan level 6+ (static analysis)
- **API Docs:** Scramble (auto-generated OpenAPI/Swagger)

### Frontend
- **Framework:** Vue 3 (Composition API ONLY, no Options API)
- **Language:** TypeScript (strict mode)
- **Build Tool:** Vite
- **State Management:** Pinia (with pinia-plugin-persistedstate)
- **Router:** Vue Router 4
- **HTTP Client:** Axios (via service layer)
- **UI Library:** PrimeVue + Tailwind CSS
- **Form Validation:** VeeValidate + Yup (NOT Zod)
- **Debounce:** lodash-es/debounce (for search, API calls)
- **i18n:** vue-i18n (multi-language support)
- **Charts:** Chart.js + vue-chartjs (dashboard analytics)
- **Date Handling:** dayjs (NOT moment.js)
- **Testing:** Vitest + Vue Test Utils
- **Code Quality:** ESLint + Prettier (auto-format on save)
- **Icons:** PrimeIcons + Lucide Vue

---

## Coding Rules

### General Rules
- Use English for all code, comments, variable names, and documentation
- No hardcoded values — use constants, enums, or config files
- No `console.log`, `dd()`, `dump()`, `var_dump()`, or `print_r()` in committed code
- No commented-out code in commits
- No `@ts-ignore`, `@ts-nocheck`, `// @phpstan-ignore` — fix the type error instead
- Keep functions/methods under 30 lines — extract if longer
- One class/component per file
- Files should not exceed 300 lines — split if larger
- DRY (Don't Repeat Yourself) — if you write the same logic 3 times, extract it
- KISS (Keep It Simple, Stupid) — simplest solution that works
- YAGNI (You Aren't Gonna Need It) — don't build what isn't needed yet
- Favor composition over inheritance
- Every public method/function must have a clear single responsibility
- No God classes or God components — split by responsibility

### Git Rules
- Branch naming: `feature/BTR-{id}-short-desc`, `fix/BTR-{id}-short-desc`, `hotfix/`, `refactor/`
- Commit messages: conventional commits format
  - `feat(tickets): add ticket creation endpoint`
  - `fix(auth): resolve tenant token isolation bug`
  - `refactor(billing): extract billing service from controller`
  - `chore(deps): update laravel to 11.x`
  - `test(tickets): add unit tests for TicketService`
  - `docs(api): update swagger annotations`
  - `perf(queries): optimize ticket listing query with eager loading`
- Scope in parentheses is REQUIRED — identifies affected module
- Never commit `.env`, credentials, secrets, or API keys
- One logical change per commit
- Squash WIP commits before merging to main
- PR must have description, test evidence, and migration notes if applicable
- No force push to `main` or `develop` branches
- All PRs require at least 1 approval before merge

### Code Review Checklist
Before any PR is merged, verify:
- [ ] No N+1 query problems (use eager loading)
- [ ] Tenant isolation is maintained (no cross-tenant data leaks)
- [ ] All new endpoints have Form Request validation
- [ ] All new endpoints have proper authorization (policies)
- [ ] API responses use Resource classes (no raw model dumps)
- [ ] New migrations are reversible (`down()` method works)
- [ ] No sensitive data in logs or responses
- [ ] Frontend types match backend response structure
- [ ] Loading and error states handled in UI
- [ ] Mobile responsive design verified

---

## Backend Rules (Laravel/PHP)

### Naming Conventions
| Item | Convention | Example |
|------|-----------|---------|
| Controller | PascalCase, singular, suffix `Controller` | `TicketController` |
| Model | PascalCase, singular | `Ticket`, `ParkingSlot` |
| Migration | snake_case, descriptive, timestamped | `2024_01_15_create_tickets_table` |
| Table | snake_case, plural | `tickets`, `parking_slots` |
| Column | snake_case | `tenant_id`, `created_at` |
| Foreign Key | `{model}_id` | `tenant_id`, `user_id` |
| Boolean Column | `is_` or `has_` prefix | `is_active`, `has_subscription` |
| Date Column | `_at` suffix | `verified_at`, `expired_at` |
| Service | PascalCase, suffix `Service` | `TicketService` |
| Repository | PascalCase, suffix `Repository` | `TicketRepository` |
| Repository Interface | PascalCase, prefix `I`, suffix `Repository` | `ITicketRepository` |
| Request | PascalCase, action prefix, suffix `Request` | `StoreTicketRequest`, `UpdateTicketRequest` |
| Resource | PascalCase, suffix `Resource` | `TicketResource` |
| Collection Resource | PascalCase, suffix `Collection` | `TicketCollection` |
| Enum | PascalCase | `TicketStatus`, `UserRole` |
| DTO | PascalCase, suffix `DTO` | `CreateTicketDTO` |
| Action | PascalCase, verb prefix, suffix `Action` | `CreateTicketAction` |
| Event | PascalCase, past tense | `TicketCreated`, `PaymentProcessed` |
| Listener | PascalCase, descriptive | `SendTicketNotification` |
| Observer | PascalCase, suffix `Observer` | `TicketObserver` |
| Policy | PascalCase, suffix `Policy` | `TicketPolicy` |
| Middleware | PascalCase | `EnsureTenantAccess`, `RateLimitApi` |
| Exception | PascalCase, suffix `Exception` | `TenantNotFoundException` |
| Trait | PascalCase, adjective or `Has` prefix | `HasTenant`, `Filterable`, `Sortable` |
| Route | kebab-case, plural nouns | `/api/v1/tickets`, `/api/v1/parking-slots` |
| Route Name | dot notation | `api.v1.tickets.index` |
| Config Key | snake_case | `tenant.default_features` |
| Method (controller) | `index`, `show`, `store`, `update`, `destroy` | Standard REST only |
| Method (service) | verb-first camelCase | `createTicket()`, `calculateRevenue()` |
| Scope | camelCase, descriptive | `scopeActive()`, `scopeForTenant()` |

### Architecture Pattern
```
Request
  → Middleware (auth, tenant, rate limit)
    → Controller (thin, delegates to service)
      → FormRequest (validation)
      → Service (business logic, orchestration)
        → Action (single-purpose operations)
        → Repository (database queries via interface)
          → Model (Eloquent, relationships, scopes)
      → Resource/Collection (response transformation)
        → Response
```

- **Controllers:** Thin — only handle request/response. No business logic. Max 5 public methods (REST). If you need extra endpoints, create a separate controller (e.g., `TicketStatusController` for PATCH status).
- **Services:** Business logic lives here. Inject repository interfaces via constructor. Services can call other services. Throw custom exceptions for business rule violations.
- **Actions:** Single-purpose operations (e.g., `CreateTicketAction`, `ProcessRefundAction`). Use when logic is complex enough to warrant its own class but doesn't need a full service.
- **Repositories:** Database queries only. Return Eloquent models/collections. Always code to the interface (`ITicketRepository`), not the concrete class. Bind in `AppServiceProvider`.
- **DTOs:** Use for passing structured data between layers. Immutable (readonly properties). Create from request or array.
- **Requests:** All validation in Form Request classes, never in controllers. Include `authorize()` method for simple checks. Use custom rule objects for complex validation.
- **Resources:** All API response transformation in Resource classes. Never return raw models from controllers. Use `TicketCollection` for paginated lists.
- **Policies:** All authorization logic in Policy classes. Register in `AuthServiceProvider`. Use `$this->authorize()` in controllers.
- **Observers:** Use for model lifecycle hooks (creating, created, updating, etc.). Register in `AppServiceProvider`. Keep light — heavy logic goes in listeners.

### API Response Format

All API responses MUST follow this consistent envelope:

**Success (single resource):**
```json
{
  "success": true,
  "message": "Ticket created successfully",
  "data": {
    "id": "uuid-here",
    "ticket_number": "TKT-00001",
    "status": "active"
  }
}
```

**Success (collection/paginated):**
```json
{
  "success": true,
  "message": "Tickets retrieved successfully",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 150,
    "last_page": 10
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

**Error (validation):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "vehicle_number": ["The vehicle number field is required."],
    "vehicle_type": ["The selected vehicle type is invalid."]
  }
}
```

**Error (business logic / not found / unauthorized):**
```json
{
  "success": false,
  "message": "Ticket not found",
  "error_code": "TICKET_NOT_FOUND"
}
```

Use a shared `ApiResponse` trait or helper class to enforce this format across all controllers:
```php
trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Success', int $code = 200);
    protected function created(mixed $data = null, string $message = 'Created successfully');
    protected function noContent(string $message = 'Deleted successfully');
    protected function error(string $message, int $code = 400, ?string $errorCode = null);
    protected function validationError(array $errors, string $message = 'Validation failed');
}
```

### HTTP Status Codes
| Action | Code | When |
|--------|------|------|
| GET success | `200` | Resource(s) retrieved |
| POST success | `201` | Resource created |
| PUT/PATCH success | `200` | Resource updated |
| DELETE success | `204` | Resource deleted |
| Validation fail | `422` | Input validation errors |
| Auth fail | `401` | Not authenticated |
| Forbidden | `403` | Authenticated but not authorized |
| Not found | `404` | Resource doesn't exist |
| Conflict | `409` | Duplicate or state conflict |
| Rate limited | `429` | Too many requests |
| Server error | `500` | Unexpected error |

### API Versioning
- All routes prefixed with `/api/v1/`
- Group by resource: `/api/v1/tickets`, `/api/v1/users`
- Tenant-scoped routes: `/api/v1/tenant/tickets`
- Super admin routes: `/api/v1/admin/tenants`
- When v2 is needed, create new controllers — don't modify v1

### API Pagination, Filtering & Sorting
Every list endpoint MUST support:
```
GET /api/v1/tickets?page=1&per_page=15&sort=-created_at&filter[status]=active&search=ABC-123
```
- `page` — page number (default: 1)
- `per_page` — items per page (default: 15, max: 100)
- `sort` — column name, prefix `-` for descending
- `filter[field]` — exact match filter
- `search` — full-text search across relevant columns
- Use `Filterable` and `Sortable` traits on models/repositories

### Multi-Tenancy Rules
- Every tenant-scoped model MUST use `HasTenant` trait (auto-sets `tenant_id`)
- Every tenant-scoped model MUST have a `TenantScope` global scope
- Always use tenant middleware for tenant routes — NEVER manually filter by `tenant_id` in controllers
- Tenant identification order: subdomain → `X-Tenant-ID` header → API token's tenant
- Super admin routes MUST NOT have tenant middleware
- Tenant switching is ONLY allowed for super admins via `X-Tenant-ID` header
- Cross-tenant queries are FORBIDDEN except in super admin context
- All queued jobs MUST include `tenant_id` and restore tenant context before execution
- All notifications MUST be tenant-scoped
- File uploads MUST be stored in tenant-specific directories (`tenants/{tenant_id}/...`)

### Database Rules
- Always use migrations — never modify DB manually
- Always add foreign key constraints with `onDelete` behavior specified
- Always add indexes on: foreign keys, frequently filtered columns, unique constraints
- Use `uuid` for primary keys on all models (`HasUuid` trait)
- Soft deletes (`SoftDeletes`) on all major models
- Always set `nullable()` explicitly when a column can be null
- Always set sensible defaults with `default()`
- Use database-level constraints in addition to application validation
- Seed data using Seeders + Factories — factories must produce realistic data
- Every migration MUST have a working `down()` method
- Use database transactions for multi-step operations (`DB::transaction()`)
- Never use raw queries — use Eloquent or Query Builder
- Use `$casts` on models for date, enum, boolean, and JSON columns
- Pivot tables: alphabetical order of model names (`role_user`, not `user_role`)

### Eloquent Rules
- Always define `$fillable` — never use `$guarded = []`
- Always define `$casts` for non-string columns
- Always define relationships with return types
- Always eager load relationships to prevent N+1 (`with()`, `load()`)
- Use `whenLoaded()` in Resources to conditionally include relationships
- Use local scopes for reusable query constraints
- Use accessors/mutators with `Attribute` cast for computed fields
- Never query inside loops — batch queries and map results

### Error Handling Rules
- Create custom exceptions for each business domain error
- All exceptions extend a base `AppException` class
- Map exceptions to HTTP status codes in `Handler.php`
- Log all 500 errors with full context (tenant, user, request)
- Never expose stack traces or internal details in API responses
- Use error codes (strings) in addition to messages for frontend mapping
- Example custom exceptions:
  ```
  TenantNotFoundException → 404
  TicketAlreadyClosedException → 409
  InsufficientPermissionException → 403
  SubscriptionExpiredException → 402
  RateLimitExceededException → 429
  ```

### Logging Rules
- Use structured logging with context: `Log::info('Ticket created', ['ticket_id' => $id, 'tenant_id' => $tenantId])`
- Log levels:
  - `emergency` / `critical` — system is unusable, data corruption
  - `error` — runtime errors that need attention
  - `warning` — unusual events that might need attention
  - `info` — significant business events (ticket created, payment processed)
  - `debug` — detailed debug info (only in dev)
- Always include `tenant_id` and `user_id` in log context
- Use separate log channels per concern (e.g., `payment`, `auth`, `tenant`)
- Sensitive data (passwords, tokens, card numbers) MUST NEVER appear in logs
- Log all authentication events (login, logout, failed attempts)
- Log all payment events with transaction IDs

### PHP Code Style
- Follow PSR-12 coding standard
- Use strict types: `declare(strict_types=1);` in every PHP file
- Use PHP 8.4 features: enums, readonly properties, named arguments, match expressions, first-class callables
- Use typed properties and return types on ALL methods — no exceptions
- Use constructor promotion where applicable
- Use `readonly` on properties that should not change after construction
- Use `match` instead of `switch` statements
- Use `enum` instead of class constants for fixed value sets
- Use null-safe operator `?->` instead of null checks
- Use named arguments for methods with 3+ parameters or boolean flags
- Max 4 parameters per method — use DTO if more are needed
- Avoid static methods except for factory methods and pure utility functions

### Caching Rules
- Cache tenant settings and features (invalidate on update)
- Cache configuration that rarely changes (plans, global settings)
- Use cache tags for tenant-scoped cache: `Cache::tags(['tenant:{id}'])`
- Set appropriate TTLs — no indefinite caching
- Always invalidate cache on related data changes
- Use Redis for all caching — never file-based cache in production
- Cache keys format: `tenant:{id}:resource:{identifier}` (e.g., `tenant:1:settings`)

---

## Frontend Rules (Vue/TypeScript)

### Naming Conventions
| Item | Convention | Example |
|------|-----------|---------|
| Component file | PascalCase `.vue` | `TicketCard.vue` |
| Component (index) | `index.vue` inside PascalCase folder | `TicketCard/index.vue` |
| Composable file | camelCase, prefix `use` | `useTickets.ts` |
| Store file | camelCase, suffix `Store` | `ticketStore.ts` |
| Service file | camelCase, suffix `Service` | `ticketService.ts` |
| Type file | camelCase | `ticket.ts` in `types/models/` |
| Interface | PascalCase, prefix `I` | `ITicket`, `ICreateTicketPayload` |
| Type alias | PascalCase | `TicketStatus`, `ApiResponse<T>` |
| Enum | PascalCase | `TicketStatusEnum`, `UserRoleEnum` |
| Variable | camelCase | `ticketList`, `isLoading` |
| Constant | UPPER_SNAKE_CASE | `MAX_RETRY_COUNT`, `API_TIMEOUT` |
| CSS class | kebab-case (Tailwind utility-first) | `ticket-card` |
| Event emit | kebab-case | `@ticket-created` |
| Prop | camelCase in script, kebab-case in template | `:ticket-data` / `ticketData` |
| Route name | kebab-case, dot-separated by hierarchy | `admin.tickets.index` |
| Route path | kebab-case | `/admin/parking-slots` |
| Page folder | kebab-case | `super-admin/`, `parking-slots/` |
| Test file | same name + `.spec.ts` | `TicketCard.spec.ts` |
| Guard file | camelCase, suffix `Guard` | `authGuard.ts` |

### Vue Component Rules
- **Composition API ONLY** — no Options API, no mixins, no `this`
- Use `<script setup lang="ts">` in every component
- Component structure order MUST be:
  1. `<script setup lang="ts">` — in this exact order inside:
     - Imports (external → internal → types)
     - Props definition (`defineProps`)
     - Emits definition (`defineEmits`)
     - Composables / Store access
     - Reactive state (`ref`, `reactive`, `computed`)
     - Methods / functions
     - Watchers (`watch`, `watchEffect`)
     - Lifecycle hooks (`onMounted`, etc.)
     - `defineExpose` (if needed, at the very end)
  2. `<template>` — single root element preferred
  3. `<style scoped>` — only if Tailwind isn't sufficient
- Props must be typed with `defineProps<{ ... }>()`
- Emits must be typed with `defineEmits<{ ... }>()`
- Use `withDefaults()` when props have default values
- Extract reusable logic into composables (`src/composables/`)
- Keep components under 200 lines — split into smaller components if larger
- No business logic in templates — use computed properties
- Avoid `v-if` + `v-for` on the same element
- Always use `:key` with `v-for` (use unique ID, never index)
- Use `<Suspense>` for async components
- Use `<Teleport>` for modals and dropdowns that need portal rendering

### Component Categories
Components are organized into clear categories:
- **Pages** (`pages/`) — route-level components, handle data fetching and orchestration
- **Layout** (`components/layout/`) — structural components (Sidebar, Header, Footer, Breadcrumb)
- **Common** (`components/common/`) — shared business components (TicketCard, TenantBadge)
- **UI** (`components/ui/`) — generic primitives (AppButton, AppInput, AppModal, AppToast)
- **Forms** (`components/forms/`) — form-related components (TicketForm, LoginForm)

UI components MUST be prefixed with `App` to avoid conflicts with HTML elements and PrimeVue.

### State Management (Pinia)
- One store per domain: `useAuthStore`, `useTicketStore`, `useTenantStore`
- Use setup store syntax (composition API style)
- Store structure:
  ```typescript
  export const useTicketStore = defineStore('ticket', () => {
    // State
    const tickets = ref<ITicket[]>([])
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // Getters (computed)
    const activeTickets = computed(() => tickets.value.filter(t => t.status === 'active'))

    // Actions
    async function fetchTickets() { ... }

    return { tickets, isLoading, error, activeTickets, fetchTickets }
  })
  ```
- Persist auth state using `pinia-plugin-persistedstate` (localStorage)
- Reset store state on logout
- Never directly mutate store state from components — use actions
- Handle loading and error states in EVERY store action

### API Service Layer & Axios Interceptors (CRITICAL)

All API calls go through service files (`src/services/`).
Never call axios directly from components or stores.

#### Axios Instance Setup (`src/services/api.ts`)
```typescript
import axios, { AxiosError, InternalAxiosRequestConfig, AxiosResponse } from 'axios'
import { useAuthStore } from '@/stores/authStore'
import { useTenantStore } from '@/stores/tenantStore'
import { useToast } from '@/composables/useToast'
import router from '@/router'

// ─── Create Instance ───
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// ─── Request Interceptor ───
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const authStore = useAuthStore()
    const tenantStore = useTenantStore()

    // Inject auth token
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`
    }

    // Inject tenant ID
    if (tenantStore.tenantId) {
      config.headers['X-Tenant-ID'] = tenantStore.tenantId
    }

    // Inject idempotency key for POST/PUT/PATCH
    if (['post', 'put', 'patch'].includes(config.method || '')) {
      config.headers['X-Idempotency-Key'] = crypto.randomUUID()
    }

    // Dev logging
    if (import.meta.env.DEV) {
      console.debug(`[API] ${config.method?.toUpperCase()} ${config.url}`)
    }

    return config
  },
  (error) => Promise.reject(error)
)

// ─── Response Interceptor ───
api.interceptors.response.use(
  (response: AxiosResponse) => {
    // Unwrap API envelope → return response.data.data directly
    return response.data
  },
  (error: AxiosError<IApiErrorResponse>) => {
    const { toast } = useToast()
    const authStore = useAuthStore()
    const status = error.response?.status
    const errorData = error.response?.data

    switch (status) {
      case 401:
        // Token expired or invalid → clear auth, redirect to login
        authStore.clearAuth()
        router.push({ name: 'auth.login', query: { expired: '1' } })
        toast.error('Session expired. Please login again.')
        break

      case 403:
        // Forbidden → show permission denied
        toast.error(errorData?.message || 'You do not have permission to perform this action.')
        break

      case 404:
        // Resource not found
        toast.error(errorData?.message || 'Requested resource not found.')
        break

      case 409:
        // Conflict (duplicate, state conflict)
        toast.warning(errorData?.message || 'Conflict detected. Please refresh and try again.')
        break

      case 422:
        // Validation errors → DO NOT toast, return errors to form
        // Errors are handled by VeeValidate's setErrors()
        // Return the error so the calling code can extract field errors
        break

      case 429:
        // Rate limited
        toast.warning('Too many requests. Please wait a moment and try again.')
        break

      case 500:
      case 502:
      case 503:
        // Server error
        toast.error('Something went wrong. Please try again later.')
        break

      default:
        // Network error (no response)
        if (!error.response) {
          toast.error('Network error. Please check your connection.')
        }
    }

    return Promise.reject(error)
  }
)

export default api
```

#### Axios Interceptor Rules
- Request interceptor MUST inject: auth token, tenant ID, idempotency key
- Response interceptor MUST unwrap the API envelope (`response.data.data`)
- 401 MUST clear auth state AND redirect to login — no partial state
- 422 MUST NOT show toast — validation errors go to form fields (VeeValidate `setErrors`)
- 403/404/409/429/500 MUST show toast notification
- Network errors (no `error.response`) MUST show offline message
- Dev mode logs all requests (method + URL) to console
- All interceptors are typed — no `any`

#### Service Layer Pattern
```typescript
// src/services/ticketService.ts
import api from '@/services/api'
import type { ITicket, ICreateTicketPayload, IPaginatedResponse } from '@/types'

export const ticketService = {
  getAll: (params?: Record<string, unknown>) =>
    api.get<IPaginatedResponse<ITicket>>('/tickets', { params }),
  getById: (id: string) =>
    api.get<ITicket>(`/tickets/${id}`),
  create: (data: ICreateTicketPayload) =>
    api.post<ITicket>('/tickets', data),
  update: (id: string, data: Partial<ICreateTicketPayload>) =>
    api.patch<ITicket>(`/tickets/${id}`, data),
  delete: (id: string) =>
    api.delete(`/tickets/${id}`),
  updateStatus: (id: string, status: string) =>
    api.patch<ITicket>(`/tickets/${id}/status`, { status }),
}
```

---

### Debounce & Throttle Rules

#### When to Debounce (wait for user to STOP typing)
| Use Case | Delay | Example |
|----------|-------|---------|
| Search input | 300ms | Ticket search, vehicle lookup |
| Filter change | 300ms | Status filter, date range |
| Auto-save draft | 1000ms | Long form auto-save |
| Username/email availability check | 500ms | Registration form |
| Resize handler | 200ms | Window resize recalculations |

#### When to Throttle (execute at most once per interval)
| Use Case | Interval | Example |
|----------|----------|---------|
| Scroll handler | 100ms | Infinite scroll loading |
| Button click (prevent double-submit) | 2000ms | Payment submit |
| Live GPS updates | 5000ms | Valet location tracking |
| Real-time counter refresh | 3000ms | Dashboard counters |

#### Debounce Implementation Pattern
```typescript
// src/composables/useDebounce.ts
import { ref, watch } from 'vue'
import { debounce } from 'lodash-es'

export function useDebouncedRef<T>(initialValue: T, delay = 300) {
  const value = ref<T>(initialValue)
  const debouncedValue = ref<T>(initialValue)

  const updateDebounced = debounce((newVal: T) => {
    debouncedValue.value = newVal as any
  }, delay)

  watch(value, (newVal) => {
    updateDebounced(newVal)
  })

  return { value, debouncedValue }
}
```

#### Debounced Search Example
```typescript
// In component
const { value: searchQuery, debouncedValue: debouncedSearch } = useDebouncedRef('', 300)

watch(debouncedSearch, (query) => {
  // This fires 300ms AFTER user stops typing
  ticketStore.fetchTickets({ search: query })
})
```

#### Debounced API Calls in Service Layer
```typescript
// src/composables/useSearch.ts
import { ref, watch } from 'vue'
import { debounce } from 'lodash-es'
import { ticketService } from '@/services/ticketService'

export function useTicketSearch() {
  const query = ref('')
  const results = ref<ITicket[]>([])
  const isSearching = ref(false)

  const search = debounce(async (searchTerm: string) => {
    if (!searchTerm || searchTerm.length < 2) {
      results.value = []
      return
    }
    isSearching.value = true
    try {
      const response = await ticketService.getAll({ search: searchTerm })
      results.value = response.data
    } catch {
      results.value = []
    } finally {
      isSearching.value = false
    }
  }, 300)

  watch(query, (val) => search(val))

  return { query, results, isSearching }
}
```

#### Request Cancellation with Debounce
```typescript
// Cancel previous request when new one fires (search-as-you-type)
import axios, { CancelTokenSource } from 'axios'

let cancelToken: CancelTokenSource | null = null

const searchApi = debounce(async (query: string) => {
  // Cancel previous request
  if (cancelToken) {
    cancelToken.cancel('New search initiated')
  }
  cancelToken = axios.CancelToken.source()

  try {
    const response = await api.get('/tickets', {
      params: { search: query },
      cancelToken: cancelToken.token,
    })
    return response.data
  } catch (error) {
    if (!axios.isCancel(error)) throw error
  }
}, 300)
```

#### Debounce Rules
- ALWAYS debounce search/filter inputs — never fire API on every keystroke
- Minimum search query length: 2 characters (don't search for "a")
- Cancel previous API request when new debounced call fires
- Show loading indicator during debounce wait (subtle spinner on input)
- Clear results when search input is emptied
- Use `lodash-es` (tree-shakeable) NOT full `lodash` — saves 70KB bundle
- Debounce is frontend-only — backend still rate-limits regardless

### TypeScript Rules
- `strict: true` in `tsconfig.json` — no exceptions
- No `any` type — use `unknown` if type is truly unknown, then narrow
- No `@ts-ignore` or `@ts-nocheck` — fix the type error
- Define interfaces for ALL API request and response payloads
- Define interfaces for ALL component props
- Mirror backend enums in `src/types/enums/` as TypeScript enums
- Use discriminated unions for state management (loading | success | error)
- Derive form types from Yup schemas: `type FormData = yup.InferType<typeof schema>`
- Use generics for reusable types:
  ```typescript
  interface IApiResponse<T> {
    success: boolean
    message: string
    data: T
  }
  interface IApiErrorResponse {
    success: false
    message: string
    errors?: Record<string, string[]>
    error_code?: string
  }
  interface IPaginatedResponse<T> extends IApiResponse<T[]> {
    meta: IPaginationMeta
    links: IPaginationLinks
  }
  ```
- Export types from barrel files (`types/index.ts`)
- Use `satisfies` operator for type checking objects

### Error Handling (Frontend)
- Every API call must handle errors — no unhandled promise rejections
- Use a global error handler in `main.ts` (`app.config.errorHandler`)
- Show user-friendly toast notifications for errors
- Show inline validation errors on form fields
- Implement retry logic for network failures (max 3 retries with backoff)
- Use error boundaries for component-level error catching
- Log errors to console in development, to a service in production
- Never show raw error messages or stack traces to users

### Loading & Empty States
Every page/component that fetches data MUST handle:
- **Loading state** — skeleton loaders or spinners (never blank screen)
- **Empty state** — descriptive message + action button (e.g., "No tickets yet. Create your first ticket.")
- **Error state** — error message + retry button
- **Partial loading** — skeleton for individual sections that load independently

### Styling Rules
- Tailwind CSS utility-first approach — minimize custom CSS
- PrimeVue components for complex UI (DataTable, Dialog, Dropdown, Calendar, etc.)
- Tenant theming via CSS custom properties:
  ```css
  :root {
    --btr-primary: var(--tenant-primary, #3B82F6);
    --btr-secondary: var(--tenant-secondary, #10B981);
    --btr-accent: var(--tenant-accent, #F59E0B);
    --btr-logo: var(--tenant-logo-url, '');
  }
  ```
- Load tenant theme from API on app initialization
- Responsive design: mobile-first approach
- Breakpoints: `sm` (640px), `md` (768px), `lg` (1024px), `xl` (1280px)
- Consistent spacing scale — use Tailwind's default spacing (4px base)
- Dark mode support via Tailwind's `dark:` prefix (optional per tenant)
- No inline styles — use Tailwind classes or scoped styles
- Z-index scale: `10` (dropdown), `20` (sticky), `30` (overlay), `40` (modal), `50` (toast)

### Router Rules
- Lazy-load ALL page components: `() => import('@/pages/admin/Tickets.vue')`
- Route guards in `src/guards/` — separate file per guard
- Guard hierarchy:
  1. `authGuard` — is user logged in?
  2. `roleGuard` — does user have the required role?
  3. `tenantGuard` — is tenant context set?
  4. `featureGuard` — is the feature enabled for this tenant?
- Group routes by role: `/super-admin/*`, `/admin/*`, `/valet/*`
- Use `meta` fields for permissions, page titles, breadcrumbs:
  ```typescript
  {
    path: '/admin/tickets',
    meta: {
      requiresAuth: true,
      roles: ['admin', 'supervisor'],
      title: 'Tickets',
      breadcrumb: ['Dashboard', 'Tickets'],
      feature: 'ticket_management'
    }
  }
  ```
- `404` catch-all route at the bottom of route list
- `403` forbidden page for unauthorized access attempts

### Form Handling & Validation Rules (VeeValidate + Yup)

#### Architecture: Dual-Layer Validation
```
Layer 1: Frontend (Yup + VeeValidate) → instant UX feedback
Layer 2: Backend (Laravel Form Request) → source of truth

Both layers show errors UNDER the field — same UI, same position.
```

#### Yup Schema Definition Pattern
```typescript
// src/validations/ticketSchema.ts
import * as yup from 'yup'

export const createTicketSchema = yup.object({
  vehicle_number: yup
    .string()
    .required('Vehicle number is required')
    .matches(/^[A-Z]{2,3}-?\d{1,4}$/, 'Invalid plate format (e.g., ABC-1234)')
    .uppercase(),

  vehicle_type: yup
    .string()
    .required('Vehicle type is required')
    .oneOf(['motorcycle', 'car', 'suv', 'van', 'truck'], 'Invalid vehicle type'),

  slot_id: yup
    .string()
    .nullable()
    .uuid('Invalid slot ID'),

  notes: yup
    .string()
    .nullable()
    .max(500, 'Notes cannot exceed 500 characters'),
})

export type CreateTicketFormData = yup.InferType<typeof createTicketSchema>
```

#### VeeValidate Form Component Pattern
```vue
<!-- src/components/forms/TicketForm.vue -->
<script setup lang="ts">
import { useForm, useField } from 'vee-validate'
import { createTicketSchema } from '@/validations/ticketSchema'
import { ticketService } from '@/services/ticketService'
import { useToast } from '@/composables/useToast'
import { isAxiosError } from 'axios'

const { toast } = useToast()

const { handleSubmit, setErrors, isSubmitting, resetForm } = useForm({
  validationSchema: createTicketSchema,
  initialValues: {
    vehicle_number: '',
    vehicle_type: '',
    slot_id: null,
    notes: '',
  },
})

// Each field gets its own error state
const { value: vehicleNumber, errorMessage: vehicleNumberError } = useField('vehicle_number')
const { value: vehicleType, errorMessage: vehicleTypeError } = useField('vehicle_type')
const { value: slotId, errorMessage: slotIdError } = useField('slot_id')
const { value: notes, errorMessage: notesError } = useField('notes')

const onSubmit = handleSubmit(async (values) => {
  try {
    await ticketService.create(values)
    toast.success('Ticket created successfully')
    resetForm()
  } catch (error) {
    if (isAxiosError(error) && error.response?.status === 422) {
      // ✅ Map backend validation errors to form fields
      const backendErrors = error.response.data.errors
      setErrors(backendErrors)
      // backendErrors = { vehicle_number: ['Already has active ticket'] }
      // → VeeValidate shows "Already has active ticket" under the field
    }
  }
})
</script>

<template>
  <form @submit.prevent="onSubmit" novalidate>
    <!-- Vehicle Number Field -->
    <div class="mb-4">
      <label for="vehicle_number" class="block text-sm font-medium mb-1">
        Vehicle Number <span class="text-red-500">*</span>
      </label>
      <input
        id="vehicle_number"
        v-model="vehicleNumber"
        type="text"
        class="w-full border rounded-lg px-3 py-2"
        :class="{ 'border-red-500': vehicleNumberError }"
        placeholder="ABC-1234"
      />
      <!-- ✅ Error shown directly under field (frontend OR backend) -->
      <p v-if="vehicleNumberError" class="mt-1 text-sm text-red-500">
        {{ vehicleNumberError }}
      </p>
    </div>

    <!-- Vehicle Type Field -->
    <div class="mb-4">
      <label for="vehicle_type" class="block text-sm font-medium mb-1">
        Vehicle Type <span class="text-red-500">*</span>
      </label>
      <select
        id="vehicle_type"
        v-model="vehicleType"
        class="w-full border rounded-lg px-3 py-2"
        :class="{ 'border-red-500': vehicleTypeError }"
      >
        <option value="">Select type</option>
        <option value="motorcycle">Motorcycle</option>
        <option value="car">Car</option>
        <option value="suv">SUV</option>
      </select>
      <p v-if="vehicleTypeError" class="mt-1 text-sm text-red-500">
        {{ vehicleTypeError }}
      </p>
    </div>

    <!-- Submit Button -->
    <button
      type="submit"
      :disabled="isSubmitting"
      class="w-full bg-primary text-white py-2 rounded-lg disabled:opacity-50"
    >
      <span v-if="isSubmitting" class="flex items-center justify-center gap-2">
        <i class="pi pi-spinner pi-spin" />
        Creating...
      </span>
      <span v-else>Create Ticket</span>
    </button>
  </form>
</template>
```

#### Reusable Form Field Component (`AppFormField.vue`)
```vue
<!-- src/components/ui/AppFormField.vue -->
<script setup lang="ts">
interface Props {
  label: string
  name: string
  error?: string
  required?: boolean
  helpText?: string
}
defineProps<Props>()
</script>

<template>
  <div class="mb-4">
    <label :for="name" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <slot />
    <p v-if="error" class="mt-1 text-sm text-red-500 flex items-center gap-1">
      <i class="pi pi-exclamation-circle text-xs" />
      {{ error }}
    </p>
    <p v-else-if="helpText" class="mt-1 text-sm text-gray-400">
      {{ helpText }}
    </p>
  </div>
</template>
```

Usage:
```vue
<AppFormField label="Vehicle Number" name="vehicle_number" :error="vehicleNumberError" required>
  <input v-model="vehicleNumber" type="text" class="input" />
</AppFormField>
```

#### Backend Error → Frontend Field Mapping (KEY PATTERN)
```
Backend returns 422:
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "vehicle_number": ["The vehicle number has already been taken."],
    "vehicle_type": ["The selected vehicle type is invalid."]
  }
}

↓ Axios interceptor lets 422 through (no toast)

↓ Component catch block extracts errors:
catch (error) {
  if (isAxiosError(error) && error.response?.status === 422) {
    setErrors(error.response.data.errors)
  }
}

↓ VeeValidate maps errors to fields:
vehicleNumberError → "The vehicle number has already been taken."
vehicleTypeError   → "The selected vehicle type is invalid."

↓ Displayed under each field in red text
```

#### Composable for Form Submission with Backend Errors
```typescript
// src/composables/useFormSubmit.ts
import { isAxiosError } from 'axios'
import { useToast } from '@/composables/useToast'

export function useFormSubmit() {
  const { toast } = useToast()

  async function submitForm<T>(
    apiCall: () => Promise<T>,
    setErrors: (errors: Record<string, string | string[]>) => void,
    options?: {
      successMessage?: string
      onSuccess?: (data: T) => void
    }
  ): Promise<T | null> {
    try {
      const result = await apiCall()
      if (options?.successMessage) {
        toast.success(options.successMessage)
      }
      options?.onSuccess?.(result)
      return result
    } catch (error) {
      if (isAxiosError(error) && error.response?.status === 422) {
        // Map backend errors to form fields — NO toast for validation
        const backendErrors = error.response.data.errors
        setErrors(backendErrors)
      }
      // Other errors already handled by axios interceptor (toast)
      return null
    }
  }

  return { submitForm }
}
```

Usage in component:
```typescript
const { submitForm } = useFormSubmit()

const onSubmit = handleSubmit(async (values) => {
  await submitForm(
    () => ticketService.create(values),
    setErrors,
    {
      successMessage: 'Ticket created successfully',
      onSuccess: () => router.push({ name: 'admin.tickets.index' }),
    }
  )
})
```

#### Yup Schema Rules
- One schema file per form: `src/validations/{resource}Schema.ts`
- Infer TypeScript types from Yup schemas: `yup.InferType<typeof schema>`
- Schema field names MUST match backend field names exactly (snake_case)
- Custom error messages on every rule — no default "field is required" messages
- Common validations extracted to shared rules:
  ```typescript
  // src/validations/rules.ts
  import * as yup from 'yup'

  export const phoneRule = yup
    .string()
    .matches(/^\+?[1-9]\d{9,14}$/, 'Invalid phone number (E.164 format)')

  export const uuidRule = yup
    .string()
    .uuid('Invalid ID format')

  export const plateNumberRule = yup
    .string()
    .required('Vehicle number is required')
    .matches(/^[A-Z]{2,3}-?\d{1,4}$/, 'Invalid plate format')
    .uppercase()

  export const emailRule = yup
    .string()
    .required('Email is required')
    .email('Invalid email address')
    .lowercase()

  export const passwordRule = yup
    .string()
    .required('Password is required')
    .min(8, 'Password must be at least 8 characters')
    .matches(/[A-Z]/, 'Must contain at least 1 uppercase letter')
    .matches(/[a-z]/, 'Must contain at least 1 lowercase letter')
    .matches(/[0-9]/, 'Must contain at least 1 number')
    .matches(/[^A-Za-z0-9]/, 'Must contain at least 1 special character')
  ```

#### Validation File Structure
```
src/validations/
  ├── rules.ts              → Shared reusable rules
  ├── authSchema.ts         → Login, register, forgot password, reset password
  ├── ticketSchema.ts       → Create ticket, update ticket, close ticket
  ├── paymentSchema.ts      → Process payment, refund
  ├── userSchema.ts         → Create user, update user, change password
  ├── vehicleSchema.ts      → Create vehicle, update vehicle
  ├── slotSchema.ts         → Create slot, update slot
  ├── couponSchema.ts       → Create coupon, update coupon
  ├── tenantSchema.ts       → Create tenant, update tenant, branding settings
  └── settingsSchema.ts     → Tenant settings, notification preferences
```

#### Form Handling Rules (Summary)
- Use VeeValidate `useForm` + `useField` for ALL forms — no manual validation
- Yup schemas for validation rules — shared with TypeScript types via `InferType`
- Show validation errors inline UNDER each field (red text with icon)
- Highlight invalid fields with red border (`:class="{ 'border-red-500': error }"`)
- Frontend validation = instant UX feedback (Yup validates on blur/change)
- Backend validation = source of truth (422 errors mapped to same fields via `setErrors`)
- User sees ONE consistent error UI — whether error comes from Yup or Laravel
- Disable submit button while submitting (`isSubmitting` from VeeValidate)
- Show loading spinner on submit button during submission
- Clear form after successful submission (or redirect)
- Confirm before destructive actions (delete, cancel, etc.) via `AppConfirmDialog`
- Auto-save drafts for long forms (debounced, 1000ms)
- Keyboard accessible — Tab order through fields, Enter to submit
- Validate on blur (field-level) + on submit (form-level)
- Never validate on every keystroke — use `validateOnChange: false, validateOnBlur: true`

#### Backend Validation Rules (Laravel Side)
```php
// Ensure backend returns errors in VeeValidate-compatible format
// Laravel's default 422 response already matches:
// { "message": "...", "errors": { "field": ["msg1", "msg2"] } }

// In Form Request, wrap with API envelope:
protected function failedValidation(Validator $validator): void
{
    throw new HttpResponseException(
        response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()->toArray(),
        ], 422)
    );
}
```

#### Field Error Display Priority
```
1. Frontend Yup error (shown immediately on blur)
2. Backend 422 error (shown after submit, replaces Yup error)
3. On next user input → clear error, re-validate with Yup
```

### Accessibility (a11y) Rules
- All images must have `alt` attributes
- All form inputs must have associated `<label>` elements
- Use semantic HTML (`<nav>`, `<main>`, `<article>`, `<aside>`)
- Color contrast must meet WCAG AA standard
- Interactive elements must be keyboard accessible
- Use `aria-label` for icon-only buttons
- Focus management on modal open/close
- Screen reader announcements for dynamic content changes

---

## Multi-Tenant Design

### Tenant Identification
- Primary: Subdomain (`client1.app.com`, `client2.app.com`)
- Fallback: Header-based (`X-Tenant-ID`) for API calls from SPA
- Super admin: Main domain (`app.com/super-admin`)
- Tenant resolution is cached for performance

### Data Isolation
- Single database with `tenant_id` on all tenant-scoped tables
- **Global tables** (no `tenant_id`): `tenants`, `plans`, `plan_features`, `super_admins`, `system_settings`
- **Tenant tables** (with `tenant_id`): `users`, `tickets`, `payments`, `vehicles`, `parking_slots`, `coupons`, `settings`, `audit_logs`
- Always apply tenant scope via global scope + middleware — never manually filter
- Database indexes: every `tenant_id` column MUST have a composite index with the primary query column

### Tenant Settings Schema
```json
{
  "branding": {
    "name": "DHA Parking",
    "logo_url": "/tenants/uuid/logo.png",
    "primary_color": "#2563EB",
    "secondary_color": "#10B981",
    "accent_color": "#F59E0B"
  },
  "features": {
    "coupon_system": true,
    "chargeback": false,
    "subscription_billing": true,
    "ticket_printing": true,
    "qr_code": true,
    "barcode": false,
    "sms_notifications": true,
    "email_notifications": true,
    "valet_tracking": true,
    "customer_feedback": false
  },
  "parking": {
    "max_slots": 500,
    "default_rate_per_hour": 50,
    "currency": "PKR",
    "timezone": "Asia/Karachi",
    "operating_hours": { "start": "06:00", "end": "23:00" }
  },
  "ticket": {
    "prefix": "DHA",
    "format": "{prefix}-{YYMMDD}-{sequence}",
    "auto_close_after_hours": 24
  }
}
```

### Tenant Features (Configurable)
Each tenant can toggle:
- Coupon system
- Chargeback
- Subscription billing
- Ticket printing
- QR vs Barcode
- SMS notifications
- Email notifications
- Valet GPS tracking
- Customer feedback
- Vehicle photo capture
- Multi-floor parking
- Reserved parking slots

---

## Security Rules

### Authentication & Authorization
- **Auth:** Laravel Sanctum (token-based API auth for SPA + mobile)
- **Roles & Permissions:** Spatie Laravel Permission v6 (database-backed, tenant-scoped via teams)
- Tokens have configurable expiry (default: 7 days)
- Refresh token mechanism for seamless re-auth
- Roles: `tenant_admin`, `supervisor`, `valet_staff`, `cashier`, `viewer` (Spatie roles, tenant-scoped)
- Super admin: separate `super_admins` table with `is_super_admin` flag (NOT a Spatie role)
- 50+ granular permissions assigned to roles via Spatie (`tickets.view`, `payments.create`, etc.)
- Policies + Spatie for authorization — Policy is primary, Spatie powers the `hasPermissionTo()` checks
- Spatie `permission:` middleware for route-level, Policy for resource-level authorization
- Every endpoint MUST have authorization check (policy or middleware) — no unprotected endpoints
- Failed auth attempts are logged with IP and user agent
- Account lockout after 5 failed login attempts (15-minute cooldown)

### Input Validation & Sanitization
- All inputs validated on backend (Form Requests) — frontend validation is UX only
- Sanitize HTML inputs to prevent XSS
- Use parameterized queries (Eloquent handles this) — never concatenate SQL
- Validate file uploads: type, size, dimensions
- Validate UUIDs format before database queries
- Reject requests with unexpected fields (strict validation)

### API Security
- CORS configured for allowed frontend domains only
- Rate limiting per endpoint:
  - Auth endpoints: 5 requests/minute
  - Standard API: 60 requests/minute
  - Heavy endpoints (reports): 10 requests/minute
- Request size limits (default: 10MB, file uploads: 50MB)
- API versioning prevents breaking changes
- Disable debug mode and stack traces in production
- Remove `X-Powered-By` and server version headers

### Data Security
- Passwords hashed with bcrypt (cost factor 12)
- Sensitive data encrypted at rest (card details, personal info)
- No sensitive data in URL parameters (use POST body or headers)
- No sensitive data in logs (mask card numbers, passwords, tokens)
- No sensitive data in API responses (exclude password hashes, internal IDs where unnecessary)
- PII (Personally Identifiable Information) access is logged
- HTTPS enforced in production (redirect HTTP → HTTPS)
- Secure cookie flags: `HttpOnly`, `Secure`, `SameSite=Strict`

### Tenant Security
- Tenant data isolation enforced at database, middleware, and application levels (defense in depth)
- Cross-tenant data access is impossible without super admin credentials
- Tenant admin cannot access other tenants' data even by manipulating IDs
- All API endpoints are tenant-scoped by default — super admin endpoints are explicit opt-out
- Tenant deletion is soft-delete with 30-day recovery window

---

## Performance Rules

### Backend Performance
- Eager load relationships to prevent N+1 queries — use `with()` on EVERY query that accesses relationships
- Use database indexes on all foreign keys and commonly filtered/sorted columns
- Paginate all list endpoints — never return unbounded collections
- Use Redis caching for:
  - Tenant settings (TTL: 1 hour)
  - Dashboard aggregations (TTL: 5 minutes)
  - User permissions (TTL: 15 minutes)
- Use database transactions for multi-step writes
- Use queued jobs for heavy operations:
  - Email/SMS sending
  - Report generation
  - PDF generation
  - Bulk operations
  - Audit log writing
- Use `chunk()` or `cursor()` for processing large datasets
- Use database-level aggregations (`COUNT`, `SUM`, `AVG`) instead of collection methods
- Profile queries in development — flag any query over 100ms

### Frontend Performance
- Lazy-load all route pages
- Lazy-load heavy components (charts, data tables) with `defineAsyncComponent`
- Use `v-once` for static content that never changes
- Use `v-memo` for expensive list renderings
- Debounce search inputs (300ms)
- Throttle scroll and resize event handlers
- Use virtual scrolling for lists over 100 items
- Optimize images: use WebP, lazy-load below-fold images
- Bundle size budget: initial load under 200KB gzipped
- Use `shallowRef` for large objects that don't need deep reactivity
- Avoid watchers on large arrays — use computed properties instead
- Preload critical fonts
- Cache API responses where appropriate (tenant settings, static lists)

---

## Testing Rules

### Backend Testing (Pest PHP)
- Test coverage target: 80% minimum
- Every service method must have unit tests
- Every API endpoint must have feature tests
- Test happy path + at least 2 edge cases per method
- Test tenant isolation explicitly (verify tenant A can't access tenant B's data)
- Test authorization (verify each role's access)
- Test validation (verify invalid inputs are rejected)
- Use factories for test data — never hardcode IDs or values
- Use `RefreshDatabase` trait for feature tests
- Test file structure mirrors `app/` structure
- Name tests descriptively: `it('creates a ticket with valid data')`, `it('rejects ticket creation without vehicle number')`

### Frontend Testing (Vitest)
- Test all composables
- Test all store actions and getters
- Test complex component interactions
- Test form validation logic
- Test API service error handling
- Use `@vue/test-utils` for component testing
- Mock API calls — never hit real backend in unit tests
- Test loading, error, and empty states

---

## Audit & Compliance

### Audit Trail
- Log ALL data modifications (create, update, delete) in `audit_logs` table
- Audit log fields: `tenant_id`, `user_id`, `action`, `model_type`, `model_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `timestamp`
- Audit logs are append-only — never delete or modify
- Use model observer or Laravel Auditing package
- Audit logs are tenant-scoped but accessible to super admin

### Data Retention
- Active data: retained indefinitely
- Soft-deleted data: retained 90 days, then hard-deleted
- Audit logs: retained 1 year
- Session data: retained 30 days
- Temporary files: cleaned daily

---

## Environment & Configuration

- Backend `.env` for Laravel config (DB, Redis, Mail, Storage)
- Frontend `.env` for Vue config (API URL, app settings)
- Never commit `.env` files — `.env.example` as template
- Use `.env.testing` for test environment
- Environment-specific configs: `local`, `staging`, `production`

### Required ENV (Backend)
```
APP_NAME="BTR Valet"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=btr_valet
DB_USERNAME=
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

SANCTUM_TOKEN_EXPIRY=10080
```

### Required ENV (Frontend)
```
VITE_API_URL=http://localhost:8000/api/v1
VITE_APP_NAME="BTR Valet"
VITE_APP_ENV=local
VITE_PUSHER_APP_KEY=
VITE_PUSHER_APP_CLUSTER=
```

---

## Deployment Checklist

### Before Every Deployment
- [ ] All tests pass (`php artisan test`, `npm run test`)
- [ ] No TypeScript errors (`npm run type-check`)
- [ ] No linting errors (`npm run lint`, `./vendor/bin/pint --test`)
- [ ] PHPStan passes (`./vendor/bin/phpstan analyse`)
- [ ] Migrations are reversible
- [ ] Environment variables are set
- [ ] Cache is cleared and rebuilt
- [ ] Queue workers are restarted

### Production Hardening
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] HTTPS enforced
- [ ] CORS origins restricted
- [ ] Rate limiting enabled
- [ ] Error reporting to external service (Sentry/Bugsnag)
- [ ] Database backups configured (daily)
- [ ] Redis persistence configured
- [ ] Queue monitoring set up (Horizon)
- [ ] Log rotation configured

---

## Commands Reference

### Backend
```bash
cd backend
php8.4 artisan serve                    # Start dev server (port 8000)
php8.4 artisan migrate                  # Run migrations
php8.4 artisan migrate:fresh --seed     # Reset DB and seed (dev only!)
php8.4 artisan db:seed                  # Seed database
php8.4 artisan make:model Name -mfsr    # Model + migration + seeder + factory + resource
php8.4 artisan test                     # Run all tests
php8.4 artisan test --filter=TicketTest # Run specific tests
php8.4 artisan queue:work               # Process queue jobs
php8.4 artisan horizon                  # Start Horizon dashboard
php8.4 artisan route:list               # List all routes
php8.4 artisan tinker                   # Interactive REPL
./vendor/bin/pint                       # Format code (Laravel Pint)
./vendor/bin/phpstan analyse            # Static analysis
```

### Frontend
```bash
cd frontend
npm run dev                             # Start dev server (port 5173)
npm run build                           # Production build
npm run preview                         # Preview production build
npm run lint                            # Lint code (ESLint)
npm run lint:fix                        # Auto-fix lint errors
npm run format                          # Format code (Prettier)
npm run type-check                      # TypeScript check
npm run test                            # Run tests (Vitest)
npm run test:coverage                   # Run tests with coverage
```

---

## API Design Patterns (Advanced)

### Idempotency
- All POST endpoints MUST support idempotency via `X-Idempotency-Key` header
- Client generates a UUID as idempotency key for each mutation request
- Backend stores idempotency keys in Redis (TTL: 24 hours)
- If duplicate key received, return the original response — don't re-execute
- Critical for payment and ticket creation endpoints to prevent double-charges

### ETags & Conditional Requests
- All GET endpoints returning single resources MUST include `ETag` header
- Support `If-None-Match` header — return `304 Not Modified` if unchanged
- Support `If-Match` header on PUT/PATCH — return `412 Precondition Failed` if stale (optimistic locking)
- Prevents lost updates when two users edit the same record simultaneously

### Rate Limiting Headers
Every response MUST include rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1704067200
```

### API Health Check
- `GET /api/health` — public, no auth required
- Returns: database connectivity, Redis connectivity, queue status, disk space
- Response time MUST be under 200ms
- Used by load balancers and monitoring
```json
{
  "status": "healthy",
  "version": "1.2.0",
  "timestamp": "2024-01-15T10:30:00Z",
  "services": {
    "database": "up",
    "redis": "up",
    "queue": "up",
    "storage": "up"
  }
}
```

### Bulk Operations
- Support bulk create/update/delete: `POST /api/v1/tickets/bulk`
- Maximum 100 items per bulk request
- Return individual success/failure per item:
```json
{
  "success": true,
  "message": "Bulk operation completed",
  "data": {
    "total": 10,
    "succeeded": 8,
    "failed": 2,
    "results": [
      { "index": 0, "status": "created", "id": "uuid" },
      { "index": 3, "status": "failed", "error": "Validation failed" }
    ]
  }
}
```

### API Export Endpoints
- `GET /api/v1/tickets/export?format=csv|xlsx|pdf`
- Large exports are processed via queue — return `202 Accepted` with job ID
- `GET /api/v1/exports/{job_id}` — check export status and download
- Exports are tenant-scoped and stored temporarily (24-hour TTL)

---

## State Machines & Business Logic

### Ticket Lifecycle State Machine
```
                  ┌─────────────┐
                  │   CREATED   │
                  └──────┬──────┘
                         │ vehicle parked
                         ▼
                  ┌─────────────┐
            ┌─────│   ACTIVE    │─────┐
            │     └──────┬──────┘     │
            │            │            │
     lost ticket    customer      overstay
            │       returns          │
            ▼            │            ▼
    ┌──────────────┐     │   ┌──────────────┐
    │  LOST_TICKET │     │   │   OVERSTAY   │
    └──────┬───────┘     │   └──────┬───────┘
            │            │            │
            └─────┬──────┘────────────┘
                  │ payment processed
                  ▼
           ┌─────────────┐
           │   COMPLETED │
           └──────┬──────┘
                  │ vehicle exits
                  ▼
           ┌─────────────┐
           │   CLOSED    │
           └─────────────┘

    Any State ──── cancel ───→ CANCELLED
    Any State ──── dispute ──→ DISPUTED
```

- State transitions MUST be validated — no skipping states
- Use a `TicketStateMachine` service to enforce transitions
- Each transition fires an event (e.g., `TicketActivated`, `TicketCompleted`)
- Invalid transitions throw `InvalidStateTransitionException`
- All transitions are logged in audit trail

### Ticket Number Generation
- Format: `{TENANT_PREFIX}-{YYMMDD}-{SEQUENCE}` (e.g., `DHA-250115-00042`)
- Sequence resets daily per tenant
- Use database sequence or atomic Redis counter — NO race conditions
- Tenant prefix is configurable (max 5 chars, uppercase)

### Payment Flow
```
Payment Initiated
  → Validate amount matches ticket
  → Apply coupon/discount if any
  → Process payment (cash / card / digital)
  → Generate receipt
  → Update ticket status to COMPLETED
  → Fire PaymentProcessed event
  → Queue receipt notification (SMS/email)
```
- All payment operations MUST be wrapped in database transaction
- Payment amounts calculated server-side — NEVER trust client-sent amounts
- Store all payment attempts (success and failure) for audit
- Support partial payments and split payments
- Refund creates a new negative payment record — never modify original

---

## Notification System

### Notification Channels
- **Database** — in-app notifications (always enabled)
- **Email** — via configured SMTP/SES (configurable per tenant)
- **SMS** — via Twilio/local provider (configurable per tenant)
- **Push** — via Firebase Cloud Messaging for mobile (future)
- **Real-time** — via Laravel Echo + Pusher/Soketi for live updates

### Notification Types
| Event | Database | Email | SMS | Real-time |
|-------|----------|-------|-----|-----------|
| Ticket created | Yes | No | Optional | Yes |
| Vehicle ready for pickup | Yes | No | Yes | Yes |
| Payment received | Yes | Yes | Optional | Yes |
| Daily report | No | Yes | No | No |
| Account locked | Yes | Yes | No | No |
| Subscription expiring | Yes | Yes | Yes | No |
| New staff member added | Yes | Yes | No | No |
| System maintenance | Yes | Yes | No | Yes |

### Notification Rules
- All notifications MUST be queued — never send synchronously
- All notifications MUST be tenant-branded (logo, colors, name)
- Respect tenant notification preferences (on/off per channel)
- Respect user notification preferences (on/off per type)
- Failed notifications are retried 3 times with exponential backoff
- Notification templates are stored in database for tenant customization
- All sent notifications are logged with delivery status
- Unsubscribe links in every email

---

## Real-Time & WebSocket Rules

### Broadcasting Events
- Use Laravel Echo with Pusher/Soketi for real-time
- Channel naming: `tenant.{tenant_id}.{resource}` (e.g., `tenant.uuid.tickets`)
- Private channels for tenant-scoped data — auth required
- Presence channels for valet staff tracking (who's online)
- Events:
  - `TicketCreated` — new ticket appears on dashboard
  - `TicketStatusChanged` — live ticket board updates
  - `PaymentReceived` — cashier dashboard updates
  - `ValetLocationUpdated` — GPS tracking (if enabled)
  - `DashboardStatsUpdated` — live counter updates

### Frontend WebSocket Rules
- Connect on app mount, disconnect on unmount
- Auto-reconnect with exponential backoff (1s, 2s, 4s, 8s, max 30s)
- Show connection status indicator in UI (green/yellow/red)
- Graceful degradation — app works without WebSocket (polling fallback)
- Deduplicate events that arrive both via WebSocket and API response

---

## File Upload Rules

### General Upload Rules
- Validate file type on both frontend and backend
- Validate file size: images ≤ 5MB, documents ≤ 20MB, bulk imports ≤ 50MB
- Generate unique filenames: `{tenant_id}/{resource}/{uuid}.{ext}`
- Never use original filename for storage (security + collision risk)
- Scan for malware in production (ClamAV or cloud scanning)
- Generate thumbnails for images (150x150, 300x300)
- Store metadata in database: filename, mime_type, size, path, uploaded_by

### Allowed File Types
| Context | Allowed Types |
|---------|--------------|
| Tenant logo | jpg, png, svg, webp |
| Vehicle photo | jpg, png, webp |
| Receipt/invoice | pdf |
| Bulk import | csv, xlsx |
| User avatar | jpg, png, webp |

### Storage Structure
```
tenants/
  {tenant_id}/
    logos/
    vehicles/
    receipts/
    imports/
    exports/
    users/
```

---

## Queue & Job Design Rules

### Job Categories
| Priority | Queue | Timeout | Retries | Examples |
|----------|-------|---------|---------|---------|
| Critical | `high` | 30s | 5 | Payment processing, ticket creation |
| Normal | `default` | 60s | 3 | Email, SMS, PDF generation |
| Low | `low` | 300s | 2 | Report generation, bulk exports |
| Scheduled | `scheduled` | 600s | 1 | Daily reports, cleanup tasks |

### Job Rules
- Every job MUST implement `ShouldQueue`
- Every job MUST set `$tries`, `$timeout`, `$backoff`
- Every job MUST have `failed()` method for error handling
- Every job MUST log start and completion
- Jobs MUST be idempotent — safe to retry
- Jobs MUST restore tenant context: `$this->tenantId`
- Large jobs MUST report progress via `batch()` or custom events
- Use `RateLimited` middleware on jobs that call external APIs
- Use `WithoutOverlapping` for jobs that shouldn't run concurrently
- Monitor failed jobs — alert on failure count threshold

### Scheduled Tasks (Cron)
```
Daily:
  - 00:00 → Generate daily revenue reports per tenant
  - 01:00 → Clean up expired temporary files
  - 02:00 → Hard-delete records past retention period
  - 03:00 → Database backup
  - 06:00 → Send daily summary email to tenant admins

Hourly:
  - Auto-close tickets past overstay threshold
  - Refresh cached dashboard aggregations
  - Check subscription expirations

Every 5 minutes:
  - Process pending notification queue
  - Update real-time dashboard counters
```

---

## Internationalization (i18n) Rules

### Backend i18n
- All user-facing strings (error messages, notification text) MUST use Laravel's `__()` or `trans()` helper
- Language files in `lang/{locale}/` directory
- Default locale: `en`
- Supported locales configurable per tenant
- Validation messages must be translated
- Email/SMS templates must be translated
- Date/time formatting respects locale
- Currency formatting respects tenant settings

### Frontend i18n
- Use `vue-i18n` with lazy-loaded locale files
- All UI text MUST use `$t('key')` — no hardcoded strings in templates
- Translation file structure:
  ```
  i18n/
    en/
      common.json    → shared strings (buttons, labels)
      auth.json      → login/register strings
      tickets.json   → ticket module strings
      dashboard.json → dashboard strings
    ur/
      ...
    ar/
      ...
  ```
- Support RTL (Right-to-Left) for Arabic/Urdu via Tailwind's `rtl:` variant
- Pluralization rules for all translatable counts
- Date format per locale (US: MM/DD/YYYY, others: DD/MM/YYYY)
- Number format per locale (comma vs decimal separator)
- Locale stored in user preferences and localStorage

---

## Dependency Management Rules

### Backend (Composer)
- Lock `composer.lock` in git — ensures identical installs
- Run `composer audit` before deploying — no known vulnerabilities
- Pin major versions in `composer.json` (e.g., `"^11.0"` not `"*"`)
- Review changelogs before updating major versions
- No abandoned packages — check Packagist maintenance status
- Prefer Laravel first-party packages (Sanctum, Horizon, Scout, Cashier)
- Maximum dependency tree depth: avoid packages with excessive transitive deps

### Frontend (npm)
- Lock `package-lock.json` in git
- Run `npm audit` before deploying
- Pin exact versions for critical packages: `"vue": "3.4.15"` not `"^3.4.15"`
- Use `npm ci` in CI/CD — not `npm install`
- No `devDependencies` in production bundle (tree-shaking via Vite)
- Bundle analysis: run `npx vite-bundle-visualizer` quarterly
- No packages over 100KB unless absolutely necessary — find lighter alternatives
- Prefer packages with TypeScript support (built-in types)

### Dependency Review Checklist
Before adding ANY new package:
- [ ] Is this already solvable with existing dependencies?
- [ ] How many weekly downloads? (minimum 10K)
- [ ] When was the last commit? (must be within 6 months)
- [ ] How many open issues? (red flag if >100 unresponded)
- [ ] Does it have TypeScript types? (required for frontend)
- [ ] What's the license? (must be MIT, Apache 2.0, or BSD)
- [ ] What's the bundle size impact? (check bundlephobia.com)

---

## CI/CD Pipeline Rules

### Pipeline Stages
```
1. Lint & Format Check
   → Backend: Laravel Pint (--test mode)
   → Frontend: ESLint + Prettier (--check mode)

2. Static Analysis
   → Backend: PHPStan level 6+
   → Frontend: TypeScript strict compilation

3. Unit Tests
   → Backend: Pest (unit tests)
   → Frontend: Vitest (unit tests)

4. Feature/Integration Tests
   → Backend: Pest (feature tests with test DB)
   → Frontend: Vitest (component tests)

5. Security Audit
   → composer audit
   → npm audit

6. Build
   → Frontend: Vite production build
   → Check bundle size against budget

7. Deploy (staging → production)
   → Run migrations
   → Clear & rebuild caches
   → Restart queue workers
   → Smoke test critical endpoints
```

### Pipeline Rules
- Pipeline MUST pass before merge — no exceptions
- Pipeline failures block deployment
- Pipeline should complete in under 10 minutes
- Cache dependencies between pipeline runs (vendor/, node_modules/)
- Use parallel stages where possible (lint + test simultaneously)
- Notify on failure via Slack/email

---

## Monitoring & Observability

### Application Monitoring
- **Error tracking:** Sentry (or Bugsnag) — all unhandled exceptions
- **Performance:** Laravel Telescope (dev), Sentry Performance (prod)
- **Queue monitoring:** Laravel Horizon dashboard
- **Uptime:** External uptime monitor (UptimeRobot/Pingdom)
- **Log aggregation:** Centralized logging (ELK stack or CloudWatch)

### Key Metrics to Track
| Metric | Threshold | Alert |
|--------|-----------|-------|
| API response time (p95) | < 500ms | > 1s |
| API error rate (5xx) | < 0.1% | > 1% |
| Database query time (p95) | < 100ms | > 500ms |
| Queue job wait time | < 30s | > 2min |
| Failed job count (hourly) | 0 | > 5 |
| Disk usage | < 80% | > 90% |
| Memory usage | < 80% | > 90% |
| Active WebSocket connections | n/a | > 10K (scale alert) |

### Health Dashboard
- Real-time system status page for super admin
- Per-tenant usage statistics (tickets/day, active users, storage used)
- Revenue dashboard with daily/weekly/monthly trends
- Anomaly detection: alert on unusual patterns (e.g., 10x normal ticket volume)

---

## Database Schema Design Rules

### Naming Rules (Extended)
| Pattern | Convention | Example |
|---------|-----------|---------|
| Junction/pivot table | alphabetical, singular models | `permission_role` |
| Polymorphic type column | `{relation}_type` | `commentable_type` |
| Polymorphic ID column | `{relation}_id` | `commentable_id` |
| Money columns | `_amount` suffix, integer (cents) | `total_amount` (stored as 5000 = Rs 50.00) |
| Counter cache | `{relation}_count` | `tickets_count` |
| JSON column | descriptive noun | `settings`, `metadata`, `preferences` |
| Status column | `status` (enum) | `status` → `TicketStatus` enum |
| Ordering column | `sort_order` | `sort_order` integer |
| Slug column | `slug` (unique per scope) | `slug` for tenant URL |

### Money & Currency Rules
- **NEVER** store money as float/decimal — use integer (smallest currency unit)
- Rs 150.50 → store as `15050` (paisa)
- $29.99 → store as `2999` (cents)
- All calculations in integers — convert to human-readable only in API Resource
- Currency is tenant-level setting — stored in tenant settings
- All financial reports use database-level SUM/AVG on integer columns
- Display format: `{currency_symbol} {amount / 100}` with 2 decimal places

### Soft Delete Rules
- Soft-deleted records MUST be excluded from unique constraints (use partial unique index)
- Soft-deleted records MUST be excluded from relationship counts
- Cascade soft-delete where appropriate (e.g., deleting tenant soft-deletes its users)
- Provide restore endpoint for admin: `POST /api/v1/admin/tickets/{id}/restore`
- Hard-delete via scheduled cleanup job (after retention period)

### Migration Best Practices
- One concern per migration — don't mix table creation with data migration
- Data migrations in separate files from schema migrations
- Always specify `->after('column')` for adding columns (maintain logical order)
- Use `DB::transaction()` for data migrations
- Test rollback: `php artisan migrate:rollback --step=1` after every new migration
- Zero-downtime migrations: add column → deploy code → backfill → add constraint

---

## Backup & Disaster Recovery

### Backup Strategy
- **Database:** Daily full backup + hourly WAL (Write-Ahead Log) archiving
- **File storage:** Daily incremental backup to separate storage
- **Redis:** RDB snapshot every 6 hours + AOF for persistence
- **Configuration:** All configs in git (except secrets)

### Backup Rules
- Backups stored in different region/provider than primary
- Backup encryption at rest (AES-256)
- Weekly backup restore test (automated)
- Backup retention: daily for 30 days, weekly for 3 months, monthly for 1 year
- Point-in-time recovery capability (MySQL PITR)

### Disaster Recovery Plan
- **RTO** (Recovery Time Objective): < 1 hour
- **RPO** (Recovery Point Objective): < 15 minutes
- Documented step-by-step recovery procedure
- Runbook for each failure scenario:
  - Database crash → restore from latest backup + WAL replay
  - Redis crash → restart + warm cache from DB
  - Application crash → auto-restart via process manager
  - Full server loss → provision new server, restore from backup

---

## Feature Flags System

### Implementation
- Use database-driven feature flags (not config files)
- Feature flags scoped at 3 levels:
  1. **Global** — affects all tenants (system-wide rollout)
  2. **Tenant** — affects specific tenant (per-client features)
  3. **User** — affects specific user (beta testing)

### Feature Flag Rules
- Every new feature behind a flag until verified in production
- Flag naming: `snake_case`, descriptive: `enable_vehicle_photo_capture`
- Flags are checked via middleware or helper: `Feature::isEnabled('flag_name')`
- Frontend checks feature flags from tenant settings API
- Remove feature flags after feature is stable (max 30 days after full rollout)
- Track flag state changes in audit log
- Default state for new flags: `disabled`

---

## Report & Analytics Design

### Report Types
| Report | Frequency | Format | Audience |
|--------|-----------|--------|----------|
| Daily summary | Daily | Email + Dashboard | Tenant Admin |
| Revenue report | Daily/Weekly/Monthly | Dashboard + CSV/PDF | Tenant Admin |
| Occupancy report | Real-time + Daily | Dashboard | Tenant Admin |
| Staff performance | Weekly | Dashboard + PDF | Tenant Admin, Supervisor |
| Ticket analytics | Real-time | Dashboard | All roles |
| Tenant usage | Monthly | Dashboard + PDF | Super Admin |
| System health | Real-time | Dashboard | Super Admin |
| Financial reconciliation | Monthly | PDF + CSV | Tenant Admin |

### Report Rules
- Heavy reports generated via queue jobs — never block API requests
- Reports cached with appropriate TTL (real-time: 1min, daily: 1hour)
- All reports are tenant-scoped (except super admin reports)
- Support date range filtering on all reports
- Support comparison periods (this month vs last month)
- Export in multiple formats: view on dashboard, CSV, PDF, XLSX
- Pre-aggregate data for dashboards — don't query raw data for counters
- Use materialized views or summary tables for complex reports

### Dashboard Widgets
- Total tickets today (real-time counter)
- Active vehicles in parking (real-time)
- Revenue today/this week/this month (with trend)
- Occupancy percentage (with capacity)
- Average parking duration
- Peak hours heatmap
- Staff activity log
- Recent tickets (live feed)

---

## Mobile API Considerations

### Offline-First Design
- Valet staff app MUST work with intermittent connectivity
- Queue actions locally when offline, sync when online
- Conflict resolution strategy: server wins for financial data, last-write-wins for non-critical
- Store pending actions in local SQLite/AsyncStorage
- Show sync status indicator (synced/pending/error)

### Mobile-Specific API Rules
- Minimize payload size — use sparse fieldsets: `?fields=id,number,status`
- Support delta sync: `?updated_since=2024-01-15T10:00:00Z`
- Compress responses (gzip)
- Support image upload with resize (don't send full-res from phone)
- Push notification tokens registered per device, not per user
- API version header: `Accept: application/vnd.btr.v1+json`

---

## Documentation Standards

### Code Documentation
- Every service class: docblock with purpose and usage
- Every complex algorithm: inline comment explaining WHY (not what)
- Every non-obvious business rule: comment with ticket/requirement reference
- Every public API endpoint: PHPDoc with `@param`, `@return`, `@throws`
- No documentation for self-explanatory code (getters, setters, simple CRUD)

### API Documentation
- Auto-generated OpenAPI/Swagger via Scramble package
- Every endpoint documented with: description, parameters, request body, response examples
- Error response examples for each possible error code
- Authentication requirements clearly stated
- Rate limit information per endpoint
- Postman/Insomnia collection exported and kept in `docs/` folder

### Architecture Decision Records (ADRs)
Store in `docs/adr/` folder:
- `001-multi-tenancy-approach.md` — why single DB with tenant_id
- `002-authentication-strategy.md` — why Sanctum over Passport
- `003-state-machine-for-tickets.md` — why explicit state machine
- `004-money-as-integers.md` — why integer storage for currency
- `005-uuid-primary-keys.md` — why UUID over auto-increment

Format:
```markdown
# ADR-{number}: {Title}
**Status:** Accepted | Deprecated | Superseded
**Date:** YYYY-MM-DD
**Context:** What is the problem?
**Decision:** What did we decide?
**Consequences:** What are the trade-offs?
```

---

## Anti-Patterns to AVOID

### Backend Anti-Patterns
- **Fat Controllers** — business logic in controllers instead of services
- **God Models** — models with 50+ methods; split into concerns/traits
- **N+1 Queries** — querying in loops; always eager load
- **Raw Model Returns** — returning Eloquent models directly from API; use Resources
- **Catch-All Exception** — `catch (\Exception $e)` everywhere; catch specific exceptions
- **Config in Code** — hardcoded URLs, keys, limits; use `.env` and `config()`
- **Direct DB in Controller** — `DB::table()` or `Model::where()` in controllers; use repositories
- **Mixed Concerns** — sending email inside a payment service; use events/listeners
- **Tenant Leak** — forgetting tenant scope on a query; use global scopes
- **Synchronous Heavy Ops** — generating PDF in request cycle; use queues

### Frontend Anti-Patterns
- **Prop Drilling** — passing props 4+ levels deep; use provide/inject or stores
- **Component Soup** — 500-line components; split by responsibility
- **Direct API Calls** — `axios.get()` in components; use service layer
- **Untyped Props** — `defineProps({ data: Object })`; use typed interfaces
- **Watch Everything** — watchers for derived state; use computed
- **State Duplication** — same data in store AND component; single source of truth
- **Template Logic** — complex expressions in templates; use computed properties
- **Missing Error States** — only handling happy path; handle all states
- **Index as Key** — `:key="index"` in v-for; use unique IDs
- **Memory Leaks** — not cleaning up intervals/event listeners in `onUnmounted`

---

## Design Patterns to USE

### Backend Patterns
| Pattern | Where | Example |
|---------|-------|---------|
| Repository | Data access | `TicketRepository` behind `ITicketRepository` interface |
| Service | Business logic | `TicketService` orchestrating multiple repositories |
| DTO | Data transfer | `CreateTicketDTO` between controller and service |
| Action | Single operation | `ProcessPaymentAction` for payment flow |
| Observer | Model events | `TicketObserver` for audit logging |
| Strategy | Swappable algorithms | `PricingStrategy` (hourly, flat, tiered) |
| Factory | Object creation | Model factories for testing + `TicketNumberFactory` |
| Builder | Complex queries | `TicketReportBuilder` for report generation |
| Specification | Business rules | `CanCloseTicketSpecification` for state validation |
| Event-Driven | Loose coupling | `TicketCreated` event → multiple listeners |

### Frontend Patterns
| Pattern | Where | Example |
|---------|-------|---------|
| Composable | Reusable logic | `useTickets()`, `usePagination()`, `useDebounce()` |
| Service Layer | API abstraction | `ticketService.getAll()` |
| Store | Global state | `useTicketStore` with Pinia |
| Guard | Route protection | `authGuard`, `roleGuard` |
| Layout | Page structure | `AdminLayout`, `AuthLayout` |
| Render Function | Dynamic rendering | Complex table cell renderers |
| Plugin | Global features | Toast notification plugin |
| Directive | DOM manipulation | `v-permission`, `v-click-outside` |

---

## Scalability Considerations

### When to Scale (Thresholds)
| Metric | Action |
|--------|--------|
| 50+ tenants | Add Redis cluster, consider read replicas |
| 100K+ tickets/day | Add database read replica, optimize indexes |
| 500+ concurrent users | Add load balancer, horizontal scaling |
| 1M+ total records | Implement table partitioning by tenant |
| 10+ GB database | Archive old data, implement data lifecycle |

### Database Scaling Path
1. **Single server** (0-50 tenants) — MySQL with proper indexes
2. **Read replicas** (50-200 tenants) — writes to primary, reads from replica
3. **Connection pooling** (100+ concurrent) — PgBouncer
4. **Table partitioning** (1M+ records) — partition by tenant_id
5. **Sharding** (500+ tenants) — separate databases per tenant group

### Application Scaling Path
1. **Single server** — Laravel + Nginx
2. **Queue workers** — separate servers for queue processing
3. **Load balancer** — multiple app servers behind Nginx/HAProxy
4. **CDN** — static assets and tenant logos via CloudFront/Cloudflare
5. **Microservices** (future) — extract payment, notification, reporting

---

## Git Workflow (Detailed)

### Branch Strategy (Git Flow Simplified)
```
main (production)
  └── develop (staging)
       ├── feature/BTR-101-ticket-creation
       ├── feature/BTR-102-payment-flow
       ├── fix/BTR-103-tenant-isolation-bug
       └── hotfix/BTR-104-payment-crash (branches from main)
```

### Branch Rules
- `main` — always deployable, protected, no direct commits
- `develop` — integration branch, auto-deploys to staging
- `feature/*` — branches from develop, merges back to develop
- `fix/*` — branches from develop for non-urgent bugs
- `hotfix/*` — branches from main for critical production bugs, merges to both main AND develop
- Branch lifetime: max 5 days — if longer, split the feature

### PR Rules
- PR title follows conventional commit format
- PR description MUST include:
  - Summary of changes (what and why)
  - Screenshots/recordings for UI changes
  - Migration notes if schema changes
  - API changes (new/modified endpoints)
  - Testing evidence (test results or manual test steps)
  - Checklist of code review items
- Max PR size: 400 lines changed (excluding auto-generated files)
- Larger features split into multiple PRs with clear dependency order

### Release Process
1. Create release branch from develop: `release/v1.2.0`
2. Version bump in config
3. Final testing on release branch
4. Merge to main + tag: `v1.2.0`
5. Merge back to develop
6. Deploy main to production
7. Create GitHub release with changelog

### Semantic Versioning
- `MAJOR.MINOR.PATCH` (e.g., `1.2.3`)
- **MAJOR** — breaking API changes, major feature overhauls
- **MINOR** — new features, new endpoints, backward compatible
- **PATCH** — bug fixes, performance improvements, no API changes
- Pre-release: `1.2.0-beta.1`, `1.2.0-rc.1`

---

## Development Environment Setup

### Required Tools
- PHP 8.4 with extensions: pdo_mysql, redis, gd, zip, bcmath, intl, mbstring
- Composer 2.x
- Node.js 18+ (LTS)
- npm 9+
- MySQL 15+
- Redis 7+
- Git 2.x

### Local Development
- Backend: `php artisan serve` on port 8000
- Frontend: `npm run dev` on port 5173
- Database: local MySQL on port 5432
- Redis: local Redis on port 6379
- Mail: Mailpit (local email testing) on port 8025

### Docker Development (Optional)
- `docker-compose up` starts all services
- Services: app (PHP-FPM), nginx, postgres, redis, mailpit, horizon
- Volumes for code hot-reloading
- Separate `docker-compose.prod.yml` for production

### IDE Configuration
- PHP: PhpStorm or VS Code with Intelephense + PHP CS Fixer
- Vue: VS Code with Volar (NOT Vetur) + TypeScript Vue Plugin
- EditorConfig for consistent formatting across IDEs
- Shared `.editorconfig`, `.prettierrc`, `.eslintrc`, `phpstan.neon`

---

## Glossary

| Term | Definition |
|------|-----------|
| **Tenant** | A client organization using the system (e.g., Mall, Society, Hotel) |
| **Super Admin** | System owner/operator who manages all tenants |
| **Tenant Admin** | Client's administrator who manages their parking operation |
| **Supervisor** | On-site manager who oversees valet staff |
| **Valet Staff** | Person who parks/retrieves vehicles |
| **Cashier** | Person who handles payments |
| **Ticket** | A parking session record from vehicle entry to exit |
| **Slot** | A physical parking space |
| **Coupon** | A discount code applicable to parking fees |
| **Chargeback** | A disputed payment reversal |
| **Overstay** | When a vehicle exceeds maximum parking duration |
| **White Label** | Product rebrandable by each client |
| **Multi-Tenant** | Single system serving multiple isolated clients |
| **SaaS** | Software as a Service — subscription-based delivery model |
| **RBAC** | Role-Based Access Control — permissions assigned per role |
| **DTO** | Data Transfer Object — structured data container between layers |
| **ADR** | Architecture Decision Record — documented design decisions |
| **PITR** | Point-In-Time Recovery — database restore to exact moment |
| **WAL** | Write-Ahead Log — MySQL transaction log for recovery |
| **RTO** | Recovery Time Objective — max acceptable downtime |
| **RPO** | Recovery Point Objective — max acceptable data loss window |
| **ETL** | Extract, Transform, Load — data processing pipeline |
| **Circuit Breaker** | Pattern to prevent cascading failures in distributed systems |
| **Idempotent** | Operation that produces same result regardless of how many times executed |

---

## RBAC with Spatie Laravel Permission (Detailed)

### Package: `spatie/laravel-permission` v6
- Provides Role and Permission models with database-backed assignment
- Supports multiple guards (we use `sanctum` guard for API)
- Supports direct permissions on users AND role-based permissions
- Tenant-scoped via `team_id` feature (maps to `tenant_id`)

### Spatie Configuration (`config/permission.php`)
```php
return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role' => Spatie\Permission\Models\Role::class,
    ],
    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],
    'teams' => true,  // ← CRITICAL: enables tenant-scoped roles/permissions
    'column_names' => [
        'role_pivot_key' => 'role_id',
        'permission_pivot_key' => 'permission_id',
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'tenant_id',  // ← maps team to tenant
    ],
    'register_permission_check_method' => true,
    'register_octane_reset_listener' => true,
    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'redis',
    ],
];
```

### User Model Setup
```php
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, HasTenant, HasUuid, SoftDeletes;

    // Guard for Spatie (must match Sanctum guard)
    protected string $guard_name = 'sanctum';
}
```

### Role Hierarchy
```
Super Admin (GOD mode — all system access, NOT a Spatie role — checked via is_super_admin flag)
  └── Tenant Admin (full access within their tenant)
       ├── Supervisor (operational oversight)
       │    ├── Valet Staff (ticket operations)
       │    └── Cashier (payment operations)
       └── Viewer (read-only dashboard access)
```

### Role Definitions (Seeder)
```php
// database/seeders/RolePermissionSeeder.php

// Roles are created per tenant (team-scoped)
$roles = ['tenant_admin', 'supervisor', 'valet_staff', 'cashier', 'viewer'];

// Super Admin is NOT a Spatie role — it's a flag on the super_admins table
// This prevents super admin from being tenant-scoped
```

### Permission Definitions
Permissions follow the pattern: `{resource}.{action}`

**Spatie stores these in the `permissions` table. Created once, shared across tenants.**

```php
// All permissions (50+ granular permissions)
$permissions = [
    // Tickets
    'tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete',
    'tickets.close', 'tickets.cancel', 'tickets.export',
    'tickets.bulk_create', 'tickets.reassign',

    // Payments
    'payments.view', 'payments.create', 'payments.refund',
    'payments.export', 'payments.reconcile', 'payments.void',

    // Users
    'users.view', 'users.create', 'users.update', 'users.delete',
    'users.activate', 'users.deactivate', 'users.reset_password',
    'users.assign_role',

    // Vehicles
    'vehicles.view', 'vehicles.create', 'vehicles.update',
    'vehicles.delete', 'vehicles.search',

    // Parking Slots
    'slots.view', 'slots.create', 'slots.update', 'slots.delete',
    'slots.reserve', 'slots.release',

    // Coupons
    'coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete',
    'coupons.activate', 'coupons.deactivate',

    // Reports
    'reports.view', 'reports.export', 'reports.schedule',
    'reports.revenue', 'reports.occupancy', 'reports.staff',

    // Settings
    'settings.view', 'settings.update', 'settings.branding',
    'settings.features', 'settings.billing', 'settings.notifications',

    // Audit Logs
    'audit_logs.view', 'audit_logs.export',

    // Webhooks
    'webhooks.view', 'webhooks.create', 'webhooks.update', 'webhooks.delete',
];
```

### Role-Permission Assignment (Seeder)
```php
// Tenant Admin → gets ALL permissions
$tenantAdmin->givePermissionTo(Permission::all());

// Supervisor
$supervisor->givePermissionTo([
    'tickets.view', 'tickets.create', 'tickets.update', 'tickets.close',
    'tickets.cancel', 'tickets.export', 'tickets.reassign',
    'payments.view',
    'users.view',
    'vehicles.view', 'vehicles.create', 'vehicles.update', 'vehicles.search',
    'slots.view', 'slots.update', 'slots.reserve', 'slots.release',
    'coupons.view',
    'reports.view', 'reports.revenue', 'reports.occupancy', 'reports.staff',
]);

// Valet Staff
$valetStaff->givePermissionTo([
    'tickets.view', 'tickets.create', 'tickets.update', 'tickets.close',
    'vehicles.view', 'vehicles.create', 'vehicles.search',
    'slots.view',
]);

// Cashier
$cashier->givePermissionTo([
    'tickets.view', 'tickets.close',
    'payments.view', 'payments.create',
    'vehicles.view',
    'slots.view',
    'coupons.view',
]);

// Viewer
$viewer->givePermissionTo([
    'tickets.view',
    'payments.view',
    'slots.view',
    'reports.view',
]);
```

### Role-Permission Matrix (Visual)
| Permission | Super Admin | Tenant Admin | Supervisor | Valet Staff | Cashier | Viewer |
|-----------|:-----------:|:------------:|:----------:|:-----------:|:-------:|:------:|
| tickets.view | Y | Y | Y | Y (own) | Y | Y |
| tickets.create | Y | Y | Y | Y | N | N |
| tickets.update | Y | Y | Y | Y (own) | N | N |
| tickets.delete | Y | Y | N | N | N | N |
| tickets.close | Y | Y | Y | Y (own) | Y | N |
| tickets.cancel | Y | Y | Y | N | N | N |
| tickets.export | Y | Y | Y | N | N | N |
| payments.view | Y | Y | Y | N | Y | Y |
| payments.create | Y | Y | N | N | Y | N |
| payments.refund | Y | Y | N | N | N | N |
| payments.export | Y | Y | N | N | N | N |
| users.view | Y | Y | Y | N | N | N |
| users.create | Y | Y | N | N | N | N |
| users.update | Y | Y | N | N | N | N |
| users.delete | Y | Y | N | N | N | N |
| slots.view | Y | Y | Y | Y | Y | Y |
| slots.create | Y | Y | N | N | N | N |
| slots.update | Y | Y | Y | N | N | N |
| coupons.view | Y | Y | Y | N | Y | N |
| coupons.create | Y | Y | N | N | N | N |
| reports.view | Y | Y | Y | N | N | Y |
| reports.export | Y | Y | N | N | N | N |
| settings.view | Y | Y | N | N | N | N |
| settings.update | Y | Y | N | N | N | N |
| audit_logs.view | Y | Y | N | N | N | N |

### How to Use in Code

#### Checking Permissions in Controllers
```php
class TicketController extends Controller
{
    public function index(Request $request)
    {
        // Spatie middleware checks permission automatically
        // OR check manually:
        $this->authorize('viewAny', Ticket::class);
    }

    public function store(StoreTicketRequest $request)
    {
        // Permission checked via Policy + Spatie
    }
}
```

#### Route Middleware (Spatie)
```php
// routes/api_v1.php

// Using Spatie middleware
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    // Role-based middleware
    Route::middleware(['role:tenant_admin|supervisor'])->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Permission-based middleware (PREFERRED over role-based)
    Route::middleware(['permission:tickets.create'])->group(function () {
        Route::post('/tickets', [TicketController::class, 'store']);
    });

    // Or use can() in Policy (BEST approach)
    Route::apiResource('tickets', TicketController::class);
});
```

#### Policy Integration with Spatie
```php
// app/Policies/TicketPolicy.php
class TicketPolicy
{
    // Super admin bypasses all checks
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null; // fall through to specific checks
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('tickets.view');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        // Valet staff can only view their own tickets
        if ($user->hasRole('valet_staff')) {
            return $ticket->created_by === $user->id;
        }
        return $user->hasPermissionTo('tickets.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('tickets.create');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('valet_staff')) {
            return $ticket->created_by === $user->id
                && $user->hasPermissionTo('tickets.update');
        }
        return $user->hasPermissionTo('tickets.update');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasPermissionTo('tickets.delete');
    }

    public function close(User $user, Ticket $ticket): bool
    {
        if ($user->hasRole('valet_staff')) {
            return $ticket->created_by === $user->id;
        }
        return $user->hasPermissionTo('tickets.close');
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo('tickets.export');
    }
}
```

#### Assigning Roles to Users
```php
// When creating a new user (in UserService)
$user = User::create($data);

// Set tenant context for Spatie team feature
setPermissionsTeamId($tenant->id);

// Assign role within tenant scope
$user->assignRole('valet_staff');

// Direct permission override (rare — for special cases)
$user->givePermissionTo('reports.view');

// Remove role
$user->removeRole('valet_staff');
$user->assignRole('supervisor'); // promote
```

#### Checking in Blade/API Response
```php
// In API Resource — include user's roles & permissions
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->getRoleNames(),           // ['supervisor']
            'permissions' => $this->getAllPermissions()   // ['tickets.view', ...]
                ->pluck('name')
                ->toArray(),
        ];
    }
}
```

#### Frontend Permission Check (Vue)
```typescript
// src/composables/usePermission.ts
import { useAuthStore } from '@/stores/authStore'

export function usePermission() {
  const authStore = useAuthStore()

  function can(permission: string): boolean {
    return authStore.permissions.includes(permission)
  }

  function hasRole(role: string): boolean {
    return authStore.roles.includes(role)
  }

  function hasAnyRole(roles: string[]): boolean {
    return roles.some(role => authStore.roles.includes(role))
  }

  return { can, hasRole, hasAnyRole }
}
```

```vue
<!-- Usage in template -->
<script setup lang="ts">
const { can, hasRole } = usePermission()
</script>

<template>
  <button v-if="can('tickets.create')" @click="createTicket">
    Create Ticket
  </button>

  <router-link v-if="can('reports.view')" :to="{ name: 'admin.reports' }">
    Reports
  </router-link>

  <div v-if="hasRole('tenant_admin')">
    <!-- Admin-only settings section -->
  </div>
</template>
```

#### Vue Directive for Permissions
```typescript
// src/directives/vPermission.ts
import { useAuthStore } from '@/stores/authStore'
import type { Directive } from 'vue'

export const vPermission: Directive<HTMLElement, string> = {
  mounted(el, binding) {
    const authStore = useAuthStore()
    if (!authStore.permissions.includes(binding.value)) {
      el.style.display = 'none'
    }
  },
}

// Usage: <button v-permission="'tickets.create'">Create</button>
```

### Sanctum Authentication Flow
```
1. Login: POST /api/v1/auth/login { email, password }
   → Validate credentials
   → Check account not locked
   → Check 2FA if enabled
   → Generate Sanctum token: $user->createToken('api', ['*'])
   → Return: { token, user: { id, name, roles, permissions }, tenant }

2. Authenticated Requests: Authorization: Bearer {token}
   → Sanctum middleware validates token
   → Tenant middleware resolves tenant
   → Spatie middleware checks role/permission
   → Controller processes request

3. Logout: POST /api/v1/auth/logout
   → Revoke current token: $request->user()->currentAccessToken()->delete()

4. Logout All Devices: POST /api/v1/auth/logout-all
   → Revoke all tokens: $request->user()->tokens()->delete()

5. Token Refresh: POST /api/v1/auth/refresh
   → Revoke old token, issue new one
   → Return new token + updated user data
```

### Auth API Endpoints
```
POST   /api/v1/auth/login              → Login (email + password)
POST   /api/v1/auth/register           → Register (tenant admin only, or invite flow)
POST   /api/v1/auth/logout             → Logout current device
POST   /api/v1/auth/logout-all         → Logout all devices
POST   /api/v1/auth/refresh            → Refresh token
POST   /api/v1/auth/forgot-password    → Send reset email
POST   /api/v1/auth/reset-password     → Reset with token
POST   /api/v1/auth/verify-2fa         → Verify 2FA code
GET    /api/v1/auth/me                 → Get current user + roles + permissions
PUT    /api/v1/auth/profile            → Update own profile
PUT    /api/v1/auth/change-password    → Change own password
GET    /api/v1/auth/sessions           → List active sessions
DELETE /api/v1/auth/sessions/{id}      → Revoke specific session
```

### Spatie + Multi-Tenancy Rules
- Enable `teams` feature in Spatie config (`'teams' => true`)
- Map `team_foreign_key` to `tenant_id`
- Always call `setPermissionsTeamId($tenantId)` before assigning or checking roles
- Tenant middleware MUST set `setPermissionsTeamId()` on every request
- Roles are tenant-scoped: user can be `supervisor` in Tenant A and `viewer` in Tenant B
- Permissions are global (shared across tenants) — only role assignments are tenant-scoped
- Super admin check is via `super_admins` table flag — NOT a Spatie role
- Super admin bypasses Spatie via `before()` in policies — never assign Spatie role to super admin
- Cache Spatie permissions in Redis (configured in `config/permission.php`)
- Clear permission cache when roles/permissions change: `app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions()`

### Permission Rules
- PREFER permission-based checks (`can('tickets.create')`) over role-based (`hasRole('supervisor')`)
- Policies are the PRIMARY authorization layer — Spatie powers them
- Middleware `permission:` for route-level checks, Policy for resource-level checks
- Valet staff see ONLY their own tickets (enforced in Policy, not middleware)
- Cashier sees all tickets but can ONLY process payments
- Viewer is read-only — no mutations at all
- Custom direct permissions can be assigned per-user (override role defaults)
- Permission changes take effect after cache clear (max 24h, or force clear)
- Frontend stores permissions array from `/auth/me` → uses `usePermission()` composable
- Frontend hides UI elements based on permissions (but backend ALWAYS re-validates)

---

## Pricing Engine Rules

### Pricing Strategies (Per Tenant)
Each tenant configures their pricing model:

```
1. FLAT RATE
   → Fixed price regardless of duration
   → Example: Rs 100 per entry

2. HOURLY RATE
   → Price per hour with minimum charge
   → Example: Rs 50/hour, minimum Rs 50
   → Grace period: first 15 minutes free

3. TIERED RATE
   → Different rates for different durations
   → Example:
     0-1 hour:  Rs 50
     1-3 hours: Rs 100
     3-6 hours: Rs 200
     6-12 hours: Rs 350
     12-24 hours: Rs 500

4. TIME-OF-DAY RATE
   → Different rates for peak/off-peak
   → Example:
     6AM-10AM (peak):   Rs 80/hour
     10AM-5PM (normal): Rs 50/hour
     5PM-9PM (peak):    Rs 80/hour
     9PM-6AM (night):   Rs 30/hour

5. VEHICLE-BASED RATE
   → Different rates by vehicle type
   → Example:
     Motorcycle: Rs 30/hour
     Car:        Rs 50/hour
     SUV:        Rs 70/hour
     Bus/Truck:  Rs 150/hour

6. SUBSCRIPTION/PASS
   → Pre-paid passes for regular users
   → Example:
     Daily pass:   Rs 300
     Weekly pass:  Rs 1,500
     Monthly pass: Rs 5,000
```

### Pricing Engine Implementation
```
PricingEngine (interface)
  ├── FlatRatePricing
  ├── HourlyRatePricing
  ├── TieredRatePricing
  ├── TimeOfDayPricing
  ├── VehicleBasedPricing
  └── SubscriptionPricing
```
- Use **Strategy Pattern** — swap pricing algorithm per tenant config
- Pricing calculation is ALWAYS server-side — never trust client
- All pricing calculations logged for audit
- Discounts/coupons applied AFTER base price calculation
- Tax calculation separate from base price (configurable tax rate per tenant)
- Lost ticket penalty configurable per tenant (e.g., Rs 500 flat fee)
- Overstay surcharge configurable (e.g., 2x rate after threshold)

### Pricing Rules
- Price is calculated at checkout — not at ticket creation
- Display estimated price on active ticket (recalculated in real-time)
- Grace period applies ONLY to first entry (not re-entries)
- Minimum charge always applies even if below grace period
- Free parking for VIP/reserved vehicles (configurable)
- Validate price never goes negative (floor at 0)
- Price breakdown shown on receipt: base + surcharge + tax - discount = total

---

## Coupon & Discount Engine

### Coupon Types
| Type | Behavior | Example |
|------|----------|---------|
| Percentage | Discount by % of total | `20OFF` → 20% off |
| Fixed Amount | Flat discount | `RS100OFF` → Rs 100 off |
| Free Hours | Deduct hours from duration | `2HRFREE` → First 2 hours free |
| Full Waiver | 100% discount | `VIPFREE` → Completely free |

### Coupon Rules
- Coupon code: uppercase alphanumeric, 4-20 characters
- Each coupon has: `code`, `type`, `value`, `min_amount`, `max_discount`, `usage_limit`, `per_user_limit`, `valid_from`, `valid_until`, `is_active`
- Validation order: exists → active → within date range → usage limit → min amount → applicable vehicle type
- One coupon per ticket (no stacking)
- Coupon usage tracked: `coupon_id`, `ticket_id`, `user_id`, `discount_amount`, `used_at`
- Expired/depleted coupons return clear error message
- Coupons are tenant-scoped — Tenant A's coupons don't work for Tenant B
- Super admin can create system-wide promotional coupons

---

## Tenant Lifecycle Management

### Tenant States
```
TRIAL → ACTIVE → SUSPENDED → CANCELLED → DELETED
                    ↑            ↑
                    └── ACTIVE ──┘ (reactivation)
```

### State Definitions
| State | Access | Billing | Data |
|-------|--------|---------|------|
| **TRIAL** | Full access | No charge | Retained |
| **ACTIVE** | Full access | Active billing | Retained |
| **SUSPENDED** | Read-only (no new tickets) | Billing paused | Retained |
| **CANCELLED** | No access | Billing stopped | Retained 30 days |
| **DELETED** | No access | N/A | Hard-deleted |

### Tenant Onboarding Flow
```
1. Super Admin creates tenant
   → Generate tenant UUID
   → Create tenant record with TRIAL status
   → Set trial expiry (14 days default)

2. Configure tenant
   → Set branding (name, logo, colors)
   → Configure features (toggle on/off)
   → Set pricing model
   → Configure parking slots

3. Create tenant admin user
   → Generate temporary password
   → Send welcome email with login link
   → Force password change on first login

4. Tenant admin completes setup
   → Add staff members
   → Configure notification preferences
   → Test ticket flow

5. Go live
   → Super admin activates subscription
   → Status changes to ACTIVE
   → Billing starts
```

### Tenant Offboarding Flow
```
1. Cancellation request
   → Record reason for cancellation
   → Notify super admin

2. Grace period (7 days)
   → Tenant can still access in read-only
   → Data export available

3. Cancellation effective
   → Status → CANCELLED
   → All API access revoked
   → Staff accounts deactivated
   → Data retained for 30 days

4. Data deletion (after 30 days)
   → All tenant data hard-deleted
   → File storage purged
   → Audit logs archived separately
   → Status → DELETED
```

---

## Subscription & Billing Rules (Tenant Billing)

### Plan Structure
```json
{
  "name": "Professional",
  "slug": "professional",
  "price_monthly": 999900,
  "price_yearly": 9999000,
  "currency": "PKR",
  "features": {
    "max_slots": 500,
    "max_staff_users": 25,
    "max_tickets_per_day": 1000,
    "sms_notifications": true,
    "email_notifications": true,
    "custom_branding": true,
    "api_access": true,
    "priority_support": true,
    "data_export": true,
    "advanced_reports": true,
    "multi_floor": false,
    "valet_tracking": false
  },
  "overage": {
    "extra_ticket_cost": 200,
    "extra_sms_cost": 100
  }
}
```

### Billing Rules
- Billing cycle: monthly or yearly (tenant choice)
- Proration on plan upgrades (charge difference immediately)
- No proration on downgrades (effective next cycle)
- Failed payment → retry 3 times over 7 days → suspend tenant
- Payment receipt auto-generated and emailed
- Usage tracking for overage billing (tickets beyond plan limit)
- Invoice generated on cycle start, due within 7 days
- Tax calculated based on tenant's jurisdiction
- All billing amounts stored in smallest currency unit (paisa/cents)

### Plan Tiers
| Feature | Starter | Professional | Enterprise |
|---------|:-------:|:------------:|:----------:|
| Parking slots | 50 | 500 | Unlimited |
| Staff users | 5 | 25 | Unlimited |
| Tickets/day | 200 | 1,000 | Unlimited |
| SMS notifications | N | Y | Y |
| Custom branding | N | Y | Y |
| Advanced reports | N | Y | Y |
| API access | N | N | Y |
| Multi-floor | N | N | Y |
| Priority support | N | N | Y |
| SLA guarantee | N | N | 99.9% |

---

## Webhook System (Tenant Integrations)

### Purpose
Allow tenants to receive real-time event notifications in their own systems (CRM, ERP, accounting software).

### Webhook Events
```
ticket.created       ticket.closed        ticket.cancelled
payment.received     payment.refunded     payment.failed
vehicle.entered      vehicle.exited
slot.reserved        slot.released
coupon.redeemed      staff.clock_in       staff.clock_out
daily_report.ready   subscription.renewed subscription.expiring
```

### Webhook Payload Format
```json
{
  "id": "wh_uuid",
  "event": "ticket.created",
  "tenant_id": "tenant_uuid",
  "timestamp": "2024-01-15T10:30:00Z",
  "data": {
    "ticket_id": "uuid",
    "ticket_number": "DHA-250115-00042",
    "vehicle_number": "ABC-123",
    "status": "active"
  },
  "metadata": {
    "attempt": 1,
    "webhook_url": "https://client.example.com/webhooks/btr"
  }
}
```

### Webhook Rules
- Each tenant can register up to 10 webhook URLs
- Each webhook URL subscribes to specific events
- Webhook secret generated per URL for signature verification
- Payload signed with HMAC-SHA256: `X-BTR-Signature: sha256=...`
- Delivery attempts: 1st immediately, retry at 1min, 5min, 30min, 2hr, 12hr (5 retries)
- Timeout: 10 seconds per delivery attempt
- Webhook delivery log: URL, event, status_code, response_time, attempt_count
- Failed webhooks (all retries exhausted) → alert tenant admin
- Disable webhook URL after 50 consecutive failures
- Test endpoint: `POST /api/v1/tenant/webhooks/{id}/test` — sends a test ping
- Webhook URLs MUST be HTTPS in production

---

## QR Code & Barcode Rules

### QR Code
- Library: `simplesoftwareio/simple-qrcode` (Laravel) or `qrcode.vue` (frontend)
- Content: JSON-encoded `{ "ticket_id": "uuid", "tenant_id": "uuid", "ticket_number": "DHA-250115-00042" }`
- Size: 200x200px for screen display, 300x300px for print
- Error correction level: M (15% recovery)
- Include tenant logo in QR center (optional, configurable)
- QR is generated server-side and cached (Redis, TTL: ticket lifetime)
- QR validation: decode → verify tenant_id matches → verify ticket exists → verify ticket status

### Barcode
- Format: Code128 (supports alphanumeric)
- Content: ticket_number string only (e.g., `DHA-250115-00042`)
- Height: 50px, width: auto-scaled
- Include human-readable text below barcode
- Library: `milon/barcode` (Laravel)

### Scanning
- Frontend camera scanner: `vue-qrcode-reader` for QR, `quagga2` for barcode
- Scan result → API call to `POST /api/v1/tickets/scan` with decoded content
- Handle scan errors gracefully: invalid QR, expired ticket, wrong tenant
- Audible feedback: beep on successful scan
- Offline scan queue: store scans locally, sync when online

---

## Thermal Printer Integration

### Supported Printers
- ESC/POS compatible (Epson, Star, Bixolon, etc.)
- Connection: USB, Bluetooth, Wi-Fi, Network (IP)
- Library: `mike42/escpos-php` (backend) or Web Bluetooth API (frontend)

### Ticket Print Format
```
╔════════════════════════════╗
║    {TENANT_NAME}           ║
║    {TENANT_ADDRESS}        ║
╠════════════════════════════╣
║  PARKING TICKET            ║
║                            ║
║  Ticket: DHA-250115-00042  ║
║  Date: 15-Jan-2025 10:30   ║
║  Vehicle: ABC-1234         ║
║  Type: Car                 ║
║  Slot: A-15                ║
║                            ║
║  [QR CODE / BARCODE]       ║
║                            ║
║  Rate: Rs 50/hour          ║
║  Min Charge: Rs 50         ║
║                            ║
║  * Keep this ticket safe   ║
║  * Lost ticket fee: Rs 500 ║
╚════════════════════════════╝
```

### Receipt Print Format
```
╔════════════════════════════╗
║    {TENANT_NAME}           ║
║    PAYMENT RECEIPT         ║
╠════════════════════════════╣
║  Receipt: RCP-250115-0018  ║
║  Date: 15-Jan-2025 14:45   ║
║                            ║
║  Ticket: DHA-250115-00042  ║
║  Vehicle: ABC-1234         ║
║  Entry: 10:30  Exit: 14:45 ║
║  Duration: 4h 15m          ║
║                            ║
║  Base:        Rs 250.00    ║
║  Tax (5%):    Rs  12.50    ║
║  Discount:   -Rs  50.00    ║
║  ─────────────────────     ║
║  TOTAL:       Rs 212.50    ║
║  Paid: Cash                ║
║                            ║
║  Thank you for parking!    ║
╚════════════════════════════╝
```

### Print Rules
- Print is triggered via API: `POST /api/v1/tickets/{id}/print`
- Backend generates print data (formatted text + QR) and returns to frontend
- Frontend sends to printer via Web USB/Bluetooth or network print
- Fallback: browser print dialog with CSS print stylesheet
- Print templates are tenant-configurable (header, footer, messages)
- Auto-print on ticket creation (configurable per tenant)
- Duplicate print protection: confirm before reprinting

---

## Timezone & Date/Time Handling

### Backend Rules
- ALL dates stored in UTC in database (`TIMESTAMP WITH TIME ZONE`)
- Application timezone set to UTC: `config('app.timezone') = 'UTC'`
- Convert to tenant timezone ONLY in API Resources (response layer)
- Tenant timezone stored in tenant settings (e.g., `Asia/Karachi`)
- Use Carbon for all date operations — never raw `date()` or `strtotime()`
- Duration calculations use UTC timestamps — timezone irrelevant
- Scheduled tasks account for tenant timezone (daily report at 6 AM tenant time)
- Date range filters: receive in tenant timezone, convert to UTC for query

### Frontend Rules
- API always sends UTC dates
- Display in tenant timezone using `dayjs` with `timezone` plugin
- Format configuration per locale:
  ```
  en: "MMM DD, YYYY hh:mm A"    → Jan 15, 2025 10:30 AM
  ur: "DD/MM/YYYY HH:mm"        → 15/01/2025 10:30
  ```
- Relative time for recent events: "2 minutes ago", "1 hour ago"
- Absolute time for historical data: full date/time string
- Date pickers send dates in ISO 8601 format: `2025-01-15T10:30:00Z`

### Edge Cases
- Daylight Saving Time: handled by using timezone-aware timestamps
- Midnight ticket (crosses day boundary): belongs to the START day
- 24-hour+ tickets: correctly calculate multi-day duration
- Timezone change by tenant: does NOT retroactively change existing tickets

---

## Password & Authentication Policy

### Password Requirements
- Minimum 8 characters
- Must contain: 1 uppercase, 1 lowercase, 1 number, 1 special character
- Cannot match any of last 5 passwords (password history)
- Cannot contain username or email
- Checked against common password dictionary (top 10K leaked passwords)
- Password strength meter on frontend (zxcvbn library)

### Authentication Rules
- Login: email + password → Sanctum token
- Token expiry: 7 days (configurable in env)
- Refresh: token refreshed on each API call if within last 24 hours of expiry
- Logout: revoke current token (single device) or all tokens (all devices)
- Session management: users can view and revoke active sessions
- Force logout: admin can force-logout any user in their tenant

### Multi-Device Session Management
- Each login creates a new token (device-specific)
- Session record: `token_id`, `device_name`, `ip_address`, `user_agent`, `last_active_at`, `location`
- Dashboard shows: "Active Sessions" with device, location, last activity
- "Logout all other devices" button
- Automatic session cleanup: tokens unused for 30 days are revoked
- Max concurrent sessions: 5 per user (configurable)

### Two-Factor Authentication (2FA)
- Optional per tenant (can be enforced by tenant admin)
- Methods: TOTP (Google Authenticator), SMS OTP
- Setup: QR code for TOTP app, phone number verification for SMS
- Recovery codes: 10 one-time-use codes generated at setup
- 2FA required for: tenant admin, super admin (mandatory)
- 2FA optional for: supervisor, valet staff, cashier
- Remember device option: skip 2FA for 30 days on trusted device

### Account Security
- Account lockout: 5 failed attempts → 15-minute lockout
- Progressive lockout: 3rd lockout → 1 hour, 5th → 24 hours
- Lockout notification sent to user email
- Suspicious login detection: alert on login from new location/device
- Password reset: email link with 1-hour expiry, single use
- Email change: requires current password + confirmation email to both old and new

---

## SMS Provider Abstraction

### Architecture
```
SmsService (interface)
  ├── TwilioSmsProvider
  ├── NexmoSmsProvider
  ├── LocalSmsProvider (e.g., Zong, Jazz in Pakistan)
  └── LogSmsProvider (dev/testing — logs to file)
```

### SMS Rules
- Provider configured per tenant (some prefer local providers)
- Fallback chain: primary provider fails → try secondary → log failure
- SMS templates stored in database (tenant-customizable)
- Template variables: `{ticket_number}`, `{vehicle_number}`, `{amount}`, `{tenant_name}`
- Character limit: 160 chars (single SMS) — warn if exceeded
- Phone number validation: E.164 format (`+923001234567`)
- Opt-out handling: honor unsubscribe requests
- Rate limit: max 100 SMS/hour per tenant (prevent abuse)
- Cost tracking: log SMS cost per message, show in billing
- Delivery status tracking via provider webhooks

---

## Email Template System

### Template Architecture
- Base templates stored as Blade views
- Tenant-customizable via database overrides
- Variables injected at send time
- Preview endpoint: `GET /api/v1/admin/email-templates/{slug}/preview`

### Required Email Templates
| Template | Trigger | Variables |
|----------|---------|-----------|
| `welcome` | New staff account created | name, email, temp_password, login_url |
| `password-reset` | Password reset request | name, reset_url, expiry |
| `ticket-receipt` | Payment completed | ticket_number, vehicle, duration, amount, receipt_url |
| `vehicle-ready` | Vehicle ready for pickup | ticket_number, vehicle, location |
| `daily-summary` | Daily scheduled report | date, total_tickets, revenue, occupancy |
| `subscription-expiring` | 7 days before expiry | tenant_name, expiry_date, renew_url |
| `subscription-renewed` | Payment successful | tenant_name, plan, amount, next_billing |
| `account-locked` | Too many failed logins | name, unlock_time, support_email |
| `suspicious-login` | New device/location | name, device, location, time, block_url |

### Email Rules
- All emails MUST be tenant-branded (logo, colors, name in header/footer)
- All emails MUST have plain-text fallback
- All emails MUST have unsubscribe link (CAN-SPAM compliance)
- Email sender: `{tenant_name} <noreply@{tenant_domain}>` or system default
- Queue all emails — never send synchronously
- Track: sent, delivered, opened, bounced, complained (via provider webhooks)
- Bounce handling: 3 hard bounces → mark email as invalid, stop sending
- HTML email tested across: Gmail, Outlook, Apple Mail, Yahoo

---

## Vehicle Management Rules

### Vehicle Data Model
```
Vehicle {
  id: uuid
  tenant_id: uuid
  plate_number: string (normalized: uppercase, no spaces)
  vehicle_type: enum (motorcycle, car, suv, van, truck, bus)
  color: string (optional)
  make: string (optional, e.g., "Toyota")
  model: string (optional, e.g., "Corolla")
  owner_name: string (optional)
  owner_phone: string (optional)
  is_vip: boolean
  is_blacklisted: boolean
  blacklist_reason: string (optional)
  photo_url: string (optional)
  notes: string (optional)
  visit_count: integer (counter cache)
  last_visit_at: timestamp
}
```

### Vehicle Rules
- Plate number normalization: uppercase, strip spaces/hyphens → `ABC1234`
- Plate format validation per country/region (configurable regex per tenant)
- Returning vehicle auto-identified: populate owner info from history
- VIP vehicles: auto-apply free parking or discount
- Blacklisted vehicles: show alert to valet staff, block ticket creation (configurable)
- Vehicle type determines applicable pricing strategy
- Photo captured at entry (optional, stored in tenant storage)
- Visit history: show all previous tickets for the vehicle
- Frequent visitor detection: auto-suggest subscription after N visits

---

## Parking Slot Management

### Slot Data Model
```
ParkingSlot {
  id: uuid
  tenant_id: uuid
  floor: string (e.g., "B1", "G", "1F")
  zone: string (e.g., "A", "B", "VIP", "Disabled")
  slot_number: string (e.g., "A-015")
  slot_type: enum (standard, compact, large, disabled, ev_charging, vip, reserved)
  status: enum (available, occupied, reserved, maintenance, out_of_service)
  vehicle_type_allowed: enum[] (which vehicles can park here)
  is_covered: boolean
  gps_coordinates: json (optional, for large lots)
  current_ticket_id: uuid (nullable, FK to active ticket)
  sort_order: integer
}
```

### Slot Status Machine
```
AVAILABLE → OCCUPIED (ticket created)
AVAILABLE → RESERVED (reservation made)
AVAILABLE → MAINTENANCE (admin action)
OCCUPIED → AVAILABLE (ticket closed)
RESERVED → OCCUPIED (reservation checked in)
RESERVED → AVAILABLE (reservation expired/cancelled)
MAINTENANCE → AVAILABLE (admin action)
Any → OUT_OF_SERVICE (admin action)
OUT_OF_SERVICE → AVAILABLE (admin action)
```

### Slot Rules
- Slot assignment can be: automatic (next available) or manual (staff picks)
- Auto-assignment algorithm: nearest to entrance → by zone priority → by slot number
- Reserved slots cannot be assigned to walk-in vehicles
- Disabled parking slots: only assign to vehicles with disabled permit
- EV charging slots: only assign to electric vehicles (if tracked)
- Floor/zone/slot hierarchy for navigation: "Floor B1 → Zone A → Slot A-015"
- Real-time occupancy map: visual grid showing available/occupied slots
- Capacity alerts: notify when 80%, 90%, 100% full
- Over-capacity prevention: reject new tickets when 100% full

---

## Network Resilience & Offline Handling

### Backend Resilience
- **Circuit Breaker** on all external service calls (SMS, email, payment gateway):
  ```
  CLOSED (normal) → 5 failures → OPEN (reject all, 30s)
  OPEN → 30s timeout → HALF-OPEN (allow 1 request)
  HALF-OPEN → success → CLOSED
  HALF-OPEN → failure → OPEN
  ```
- Retry with exponential backoff: 1s, 2s, 4s, 8s, 16s (max 5 retries)
- Timeout on all external HTTP calls: 10s connect, 30s read
- Fallback responses for degraded services:
  - SMS down → queue for later, show warning in UI
  - Payment gateway down → allow cash payment, queue card retry
  - Email down → queue for later, no user impact
- Database connection pooling (PgBouncer) to handle connection spikes
- Redis connection retry with sentinel/cluster for high availability

### Frontend Resilience
- **Service Worker** for offline capability (valet staff app)
- Offline action queue:
  ```typescript
  interface IOfflineAction {
    id: string
    action: 'create_ticket' | 'close_ticket' | 'process_payment'
    payload: Record<string, unknown>
    created_at: string
    retry_count: number
    status: 'pending' | 'syncing' | 'synced' | 'failed'
  }
  ```
- Sync strategy: FIFO (First In, First Out) when connectivity restored
- Conflict resolution: server response wins, notify user of conflicts
- Network status indicator: green (online), yellow (slow), red (offline)
- Automatic retry on network restoration
- Cache critical data locally: tenant settings, slot map, active tickets
- Stale-while-revalidate: show cached data immediately, refresh in background

---

## Browser & Device Compatibility

### Browser Support
| Browser | Minimum Version | Priority |
|---------|----------------|----------|
| Chrome | 90+ | Primary |
| Firefox | 90+ | Primary |
| Safari | 14+ | Primary |
| Edge | 90+ | Primary |
| Samsung Internet | 15+ | Secondary |
| Opera | 76+ | Secondary |
| IE 11 | Not supported | N/A |

### Device Support
- **Desktop:** 1280px+ (full dashboard experience)
- **Tablet:** 768px-1279px (adapted dashboard, full valet app)
- **Mobile:** 320px-767px (valet app optimized, simplified dashboard)

### Progressive Enhancement
- Core functionality works without JavaScript (login form, basic pages)
- Enhanced functionality with JavaScript (real-time updates, animations)
- Graceful degradation for unsupported features (WebSocket → polling, camera → manual input)

---

## Frontend Animation & Transition Rules

### Transition Standards
- Use Vue's `<Transition>` and `<TransitionGroup>` components
- Standard durations:
  ```css
  --duration-fast: 150ms;    /* hover effects, micro-interactions */
  --duration-normal: 250ms;  /* page transitions, modals */
  --duration-slow: 350ms;    /* complex animations, drawer open */
  ```
- Easing: `cubic-bezier(0.4, 0, 0.2, 1)` (Material Design standard)
- Respect `prefers-reduced-motion` media query — disable animations for users who prefer it
- No animation on first page load — only on subsequent interactions
- Loading skeletons use subtle pulse animation (not spinning loaders on dashboards)

### Animation Types
| Element | Animation | Duration |
|---------|-----------|----------|
| Page transition | Fade + slight slide | 250ms |
| Modal open/close | Scale + fade | 250ms |
| Sidebar toggle | Slide left/right | 250ms |
| Toast notification | Slide in from top-right | 150ms |
| List item add/remove | Height + fade | 200ms |
| Dropdown open | Scale Y from top | 150ms |
| Button click | Scale down 95% → back | 100ms |
| Skeleton loader | Pulse opacity 0.4-1.0 | 1.5s loop |
| Real-time counter update | Number roll/fade | 300ms |

---

## Print Stylesheet Rules (CSS @media print)

### General Print Rules
- Hide: navigation, sidebar, footer, action buttons, filters, toasts
- Show: data tables, reports, ticket details, receipts
- Black and white optimized (no colored backgrounds)
- Font size: 12pt for body, 14pt for headings
- Page margins: 1cm all sides
- Force page break before each major section
- Table headers repeat on every page (`thead { display: table-header-group }`)

### Printable Views
- Ticket detail page → prints as parking ticket
- Receipt page → prints as payment receipt
- Report pages → prints as formatted report with date range header
- Slot map → prints as occupancy grid

---

## Error Code Registry

### Backend Error Codes
All error codes are string constants, prefixed by module:

```
AUTH_001  → Invalid credentials
AUTH_002  → Account locked
AUTH_003  → Token expired
AUTH_004  → Insufficient permissions
AUTH_005  → 2FA required
AUTH_006  → Invalid 2FA code
AUTH_007  → Session limit exceeded

TENANT_001 → Tenant not found
TENANT_002 → Tenant suspended
TENANT_003 → Tenant subscription expired
TENANT_004 → Feature not enabled for tenant
TENANT_005 → Tenant storage quota exceeded

TICKET_001 → Ticket not found
TICKET_002 → Ticket already closed
TICKET_003 → Invalid state transition
TICKET_004 → Parking lot full
TICKET_005 → Vehicle already has active ticket
TICKET_006 → Lost ticket penalty required
TICKET_007 → Ticket expired (auto-close)

PAYMENT_001 → Payment failed
PAYMENT_002 → Invalid payment amount
PAYMENT_003 → Coupon not valid
PAYMENT_004 → Coupon expired
PAYMENT_005 → Coupon usage limit reached
PAYMENT_006 → Refund exceeds original amount
PAYMENT_007 → Payment gateway unavailable

SLOT_001 → Slot not found
SLOT_002 → Slot already occupied
SLOT_003 → Slot under maintenance
SLOT_004 → Vehicle type not allowed in slot
SLOT_005 → No available slots

USER_001 → User not found
USER_002 → Email already registered
USER_003 → Invalid role assignment
USER_004 → Cannot delete own account
USER_005 → Password does not meet requirements

VEHICLE_001 → Vehicle blacklisted
VEHICLE_002 → Invalid plate number format
VEHICLE_003 → Vehicle already has active ticket

FILE_001 → File type not allowed
FILE_002 → File size exceeds limit
FILE_003 → Upload failed

WEBHOOK_001 → Webhook URL unreachable
WEBHOOK_002 → Webhook disabled (too many failures)

GENERAL_001 → Rate limit exceeded
GENERAL_002 → Validation failed
GENERAL_003 → Resource not found
GENERAL_004 → Server error
GENERAL_005 → Service temporarily unavailable
```

### Error Code Rules
- Every error response MUST include `error_code` field
- Error codes are documented in API docs with description and resolution
- Frontend maps error codes to localized user-friendly messages
- New error codes require: code assignment, documentation update, frontend translation
- Error codes are NEVER reused or reassigned
- Log error code with every error for searchability

---

## Data Import & Export Rules

### Import Rules
- Supported formats: CSV, XLSX
- Max file size: 50MB
- Max rows per import: 10,000
- Import process:
  ```
  1. Upload file → validate format and size
  2. Parse and validate each row → show preview with errors highlighted
  3. User confirms → queue import job
  4. Process rows in chunks (100 per batch)
  5. Return summary: imported, skipped, failed (with row-level errors)
  ```
- Column mapping UI: map CSV columns to system fields
- Required fields enforced, optional fields have defaults
- Duplicate detection: skip or update based on unique identifier
- Dry-run mode: validate without importing
- Import history logged: who, when, file, result summary

### Export Rules
- Supported formats: CSV, XLSX, PDF
- Small exports (<1000 rows): synchronous download
- Large exports (>1000 rows): queued job → download link via notification
- Export files available for 24 hours, then auto-deleted
- All exports include: generation timestamp, filters applied, tenant name
- Export filename: `{resource}_{tenant}_{date}.{ext}` (e.g., `tickets_dha_20250115.csv`)
- PDF exports use tenant branding (logo, colors)
- All exports are tenant-scoped — cannot export cross-tenant data

---

## Incident Response

### Severity Levels
| Level | Description | Response Time | Example |
|-------|-------------|---------------|---------|
| **P0 - Critical** | System down, all tenants affected | 15 minutes | Database crash, app unreachable |
| **P1 - High** | Major feature broken, multiple tenants affected | 1 hour | Payment processing down |
| **P2 - Medium** | Feature degraded, some tenants affected | 4 hours | Reports not generating |
| **P3 - Low** | Minor issue, workaround available | 24 hours | UI glitch, non-critical notification failure |

### Incident Process
```
1. Detection (monitoring alert or user report)
2. Triage (assign severity, assign responder)
3. Communication (notify affected tenants if P0/P1)
4. Investigation (identify root cause)
5. Mitigation (temporary fix to restore service)
6. Resolution (permanent fix deployed)
7. Post-mortem (document: timeline, cause, fix, prevention)
```

### Post-Mortem Template
```markdown
# Incident: {Title}
**Date:** YYYY-MM-DD
**Severity:** P0/P1/P2/P3
**Duration:** X hours Y minutes
**Affected Tenants:** all / specific tenant IDs

## Timeline
- HH:MM — Issue detected
- HH:MM — Investigation started
- HH:MM — Root cause identified
- HH:MM — Mitigation applied
- HH:MM — Full resolution deployed

## Root Cause
{What caused the incident}

## Impact
{What was affected, how many tenants/users/tickets}

## Resolution
{What was done to fix it}

## Prevention
{What changes will prevent this from happening again}

## Action Items
- [ ] Action 1 — owner — due date
- [ ] Action 2 — owner — due date
```

---

## SLA Definitions

### System SLA (Super Admin → Tenants)
| Metric | Starter | Professional | Enterprise |
|--------|---------|-------------|------------|
| Uptime | 99.0% | 99.5% | 99.9% |
| API Response Time (p95) | < 2s | < 1s | < 500ms |
| Support Response (P0) | 4 hours | 1 hour | 15 minutes |
| Support Response (P1) | 24 hours | 4 hours | 1 hour |
| Data Backup Frequency | Daily | Daily | Hourly |
| Data Recovery | 24 hours | 4 hours | 1 hour |

### Uptime Calculation
- Uptime = (Total minutes - Downtime minutes) / Total minutes × 100
- Scheduled maintenance excluded from downtime calculation
- Maintenance window: Sundays 2:00-4:00 AM UTC (announced 48h in advance)
- Unscheduled downtime tracked per-tenant

### SLA Breach Handling
- Track SLA metrics automatically (uptime monitor + APM)
- Alert super admin when approaching SLA thresholds
- Credit system: tenant receives account credit for SLA breaches
  - 99.9% → 99.5%: 5% monthly credit
  - 99.5% → 99.0%: 10% monthly credit
  - Below 99.0%: 25% monthly credit

---

## Content Security Policy (CSP)

### Backend CSP Headers
```
Content-Security-Policy:
  default-src 'self';
  script-src 'self' 'unsafe-inline' cdn.example.com;
  style-src 'self' 'unsafe-inline' fonts.googleapis.com;
  img-src 'self' data: blob: *.amazonaws.com;
  font-src 'self' fonts.gstatic.com;
  connect-src 'self' api.example.com wss://ws.example.com;
  frame-src 'none';
  object-src 'none';
  base-uri 'self';
  form-action 'self';
  frame-ancestors 'none';

X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(self), microphone=(), geolocation=(self)
```

### Security Headers Rules
- All responses include security headers via middleware
- CSP violations logged and monitored (report-uri)
- Camera permission allowed (for QR scanning)
- Geolocation allowed (for valet GPS tracking)
- Microphone and other permissions denied
- Frame embedding denied (prevent clickjacking)

---

## Database Seeding Strategy

### Seeder Hierarchy
```
DatabaseSeeder
  ├── SystemSeeder (run FIRST — always)
  │   ├── RolePermissionSeeder
  │   ├── PlanSeeder
  │   └── SystemSettingsSeeder
  │
  ├── SuperAdminSeeder (creates super admin user)
  │
  ├── TenantSeeder (creates demo tenants)
  │   ├── TenantSettingsSeeder
  │   ├── TenantUserSeeder (admin, supervisor, staff, cashier)
  │   ├── ParkingSlotSeeder
  │   ├── VehicleSeeder
  │   ├── TicketSeeder
  │   ├── PaymentSeeder
  │   └── CouponSeeder
  │
  └── DemoDataSeeder (realistic test data — dev only)
```

### Seeding Rules
- System seeders are idempotent — safe to run multiple times (use `firstOrCreate`)
- Demo data uses factories with realistic Faker data
- Demo data amounts:
  - 3 demo tenants (different plans, different configs)
  - 10 users per tenant (mixed roles)
  - 50 parking slots per tenant
  - 500 tickets per tenant (various statuses)
  - 300 payments per tenant
  - 10 coupons per tenant
- Seeder for each environment:
  - `local`: full demo data
  - `staging`: sanitized copy of production-like data
  - `production`: system seeders ONLY (roles, plans, settings)
- NEVER seed user passwords in production — use invite/reset flow

---

## Code Ownership (CODEOWNERS)

### Ownership Rules
```
# Backend Core
backend/app/Models/         → Backend Lead
backend/app/Services/       → Backend Lead
backend/database/migrations/ → Backend Lead (review required)

# Frontend Core
frontend/src/components/    → Frontend Lead
frontend/src/stores/        → Frontend Lead
frontend/src/router/        → Frontend Lead

# Security Critical (require 2 approvals)
backend/app/Http/Middleware/ → Backend Lead + Security
backend/app/Policies/        → Backend Lead + Security
backend/config/auth.php      → Backend Lead + Security
backend/config/cors.php      → Backend Lead + Security

# Infrastructure
docker/                     → DevOps Lead
.github/workflows/          → DevOps Lead

# Billing (require 2 approvals)
backend/app/Services/Billing* → Backend Lead + Product Owner
backend/app/Services/Payment* → Backend Lead + Product Owner
```

### Ownership Principles
- Every file has a clear owner — no orphan code
- Ownership = accountability for code quality in that area
- Owner reviews all PRs touching their area
- Security-critical and billing code requires 2 approvals
- Shared code (`Traits/`, `Helpers/`, `utils/`) owned by tech lead

---

## Configuration Inheritance (Tenant Config)

### Config Resolution Order
```
System Default → Plan Default → Tenant Override → User Preference
```

Example:
```
System default:   max_tickets_per_day = 100
Plan (Enterprise): max_tickets_per_day = unlimited
Tenant override:  max_tickets_per_day = 5000
→ Resolved value: 5000

System default:   theme_color = #3B82F6
Plan:            (no override)
Tenant override: theme_color = #2563EB
→ Resolved value: #2563EB
```

### Config Rules
- System defaults are hardcoded fallbacks (never null)
- Plan defaults override system defaults
- Tenant overrides override plan defaults (only if plan allows)
- User preferences override tenant defaults (only for personal settings like language, notifications)
- Config resolution is cached per tenant (invalidate on update)
- Config schema validated on save — reject invalid values

---

## API Deprecation Strategy

### Deprecation Process
```
1. Announce deprecation (6 months before removal)
   → Add `Sunset` header to deprecated endpoints
   → Add `Deprecated` warning to API docs
   → Notify tenant admins via email

2. Migration period (3-6 months)
   → Old and new endpoints both work
   → Log usage of deprecated endpoints
   → Provide migration guide

3. Warning phase (last month)
   → Return `Warning: 299 - "Deprecated API"` header
   → Send final reminder to tenants still using deprecated endpoints

4. Removal
   → Remove endpoint
   → Return 410 Gone with migration URL
```

### Deprecation Headers
```
Sunset: Sat, 15 Jun 2025 00:00:00 GMT
Deprecation: true
Link: </api/v2/tickets>; rel="successor-version"
```

### Versioning Rules
- Never break v1 after release — create v2 for breaking changes
- v1 and v2 can coexist indefinitely
- Each version has its own controller namespace: `Api/V1/`, `Api/V2/`
- Shared business logic in Services — only controllers differ between versions
- Version support lifecycle:
  - Current (v2): full support
  - Previous (v1): security fixes only, 12-month sunset
  - Legacy (v0): unsupported, returns 410
