# Booking Module — Implementation Report

**Branch:** `feature/booking-module`  
**Date:** 2026-04-08  
**Status:** Phase 1 Complete (Backend + Core Frontend)

---

## Architecture Decision: Option A (Polymorphic Orders)

A single `orders` table serves both **catalog** (product sales) and **booking** (service appointments) via a `module_type` discriminator column. This avoids duplicating order status lifecycle, history tracking, and dashboard UI.

```
orders table:
├── module_type: 'catalog' | 'booking'    ← routes the entire order flow
├── orderable_type: 'App\Models\Product' | 'App\Models\Service'
├── orderable_id
├── booking_date (nullable)
├── booking_time_slot (nullable)
├── booking_duration_min (nullable)
└── ... all existing columns unchanged
```

### Tier Gating

| Plan  | Catalog | Booking |
|-------|---------|---------|
| Free  | ✅ (1 module only) | ✅ (1 module only) |
| Premium | ✅ | ✅ (both simultaneously) |

---

## Database Changes (4 Migrations)

### 1. `2026_04_08_000001_add_booking_fields_to_orders_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| `module_type` | string, default `'catalog'` | Discriminator: `'catalog'` or `'booking'` |
| `orderable_type` | string, nullable | Polymorphic: `'App\Models\Product'` or `'App\Models\Service'` |
| `orderable_id` | unsignedBigInteger, nullable | FK to Product or Service |
| `booking_date` | date, nullable | Session date for bookings |
| `booking_time_slot` | string, nullable | Time range: `"14:00-14:30"` |
| `booking_duration_min` | unsignedInteger, nullable | Session length in minutes |

Index added on `module_type` for filtering.

### 2. `2026_04_08_000002_add_module_fields_to_tenants_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| `enabled_modules` | JSON, nullable | Array: `['catalog']`, `['booking']`, or `['catalog', 'booking']` |
| `onboarding_completed` | boolean, default `false` | Tracks if tenant completed setup wizard |

### 3. `2026_04_08_000003_create_services_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| `tenant_id` | FK → tenants | Owner |
| `name`, `slug` | string | Service name, URL slug |
| `description` | text, nullable | Service details |
| `price` | decimal(10,2) | Session price |
| `currency` | string, default `'IDR'` | Currency code |
| `duration_min` | unsignedInteger, default `30` | Session length |
| `buffer_min` | unsignedInteger, default `0` | Break between sessions |
| `time_slots` | JSON, nullable | Custom slot overrides |
| `available_days` | JSON, nullable | `[1,2,3,4,5]` (Mon-Fri default) |
| `default_start` | time, default `'09:00'` | Operating start |
| `default_end` | time, default `'18:00'` | Operating end |
| `is_available` | boolean, default `true` | Visibility toggle |
| `sort_order` | unsignedInteger, default `0` | Display ordering |
| `image_urls` | JSON, nullable | Service images |

### 4. `2026_04_08_000004_add_orderable_to_order_items_table.php`

| Column | Type | Purpose |
|--------|------|---------|
| `orderable_type` | string, nullable | Polymorphic type |
| `orderable_id` | unsignedBigInteger, nullable | Polymorphic FK |

---

## Backend Implementation

### Models Modified

#### `app/Models/Order.php`
- Added `MorphTo` import
- Added to `$fillable`: `module_type`, `orderable_type`, `orderable_id`, `booking_date`, `booking_time_slot`, `booking_duration_min`
- Added to `$casts`: `booking_date` → `date`, `booking_duration_min` → `integer`
- **New methods:**
  - `orderable(): MorphTo` — Polymorphic relation to Product or Service
  - `isBooking(): bool` — Returns true if `module_type === 'booking'`
  - `isCatalog(): bool` — Returns true if `module_type === 'catalog'`
  - `getFormattedBookingDateAttribute(): ?string` — Formatted booking date
  - `getGoogleCalendarLinkAttribute(): ?string` — Generates Google Calendar add-event URL (no OAuth)

#### `app/Models/OrderItem.php`
- Added `MorphTo` import
- Added to `$fillable`: `orderable_type`, `orderable_id`
- **New method:**
  - `orderable(): MorphTo` — Polymorphic relation

#### `app/Models/Tenant.php`
- Added to `$fillable`: `enabled_modules`, `onboarding_completed`
- Added to `$casts`: `enabled_modules` → `array`, `onboarding_completed` → `boolean`
- **New relationships:**
  - `services(): HasMany` — Tenant's services
  - `availableServices(): HasMany` — Available services only
- **New methods:**
  - `getModulesAttribute(): array` — Returns `enabled_modules` or default `['catalog']`
  - `hasModule(string $module): bool`
  - `hasCatalog(): bool`
  - `hasBooking(): bool`
  - `canEnableModule(string $module): bool` — Tier enforcement (free = 1 module, premium = both)
  - `enableModule(string $module): bool`
  - `disableModule(string $module): bool` — Cannot disable last module
  - `isPremium(): bool` — Checks `subscription_status === 'premium'`
  - `getPrimaryModule(): ?string` — First enabled module

### Models Created

#### `app/Models/Service.php`
- Full model with all service fields
- `boot()` — Auto-generates slug, sets default available days (Mon-Fri)
- `tenant(): BelongsTo`
- `bookings(): HasMany` — Polymorphic orders for this service
- `getFormattedPriceAttribute(): string`
- `getFirstImageAttribute(): ?string`
- `getAvailableDayNamesAttribute(): array`
- **`generateTimeSlotsForDate(Carbon $date): array`** — Core logic:
  - Checks day availability
  - Uses custom `time_slots` if set, otherwise generates from `default_start` to `default_end` using `duration_min + buffer_min`
- Scopes: `forTenant()`, `available()`

### Controllers Modified

#### `app/Http/Controllers/OrderController.php` (Public)
- **Refactored:** `store()` → routes to `storeCatalogOrder()` or `storeBooking()` based on `module_type`
- `storeCatalogOrder()` — Original product order logic + added `module_type`, `orderable_type/id` to creation
- `storeBooking()` — New method:
  - Validates: `service_id`, `booking_date`, `booking_time_slot`, `booking_duration_min`, `price`, `service_name`
  - Creates Order with `module_type='booking'`, `orderable_type='App\Models\Service'`
  - Creates single OrderItem for the service

#### `app/Http/Controllers/StorefrontController.php`
- Added `Service` import
- `home()` — Now also fetches services if tenant has booking enabled, passes `modules` array
- `catalog()` — Conditionally fetches products and/or services based on enabled modules, passes `modules` array
- `showProduct()` — Now passes `modules` array
- **`showService()` — New method:** Renders `Storefront/Service` page with service details

#### `app/Http/Controllers/Dashboard/OrderController.php`
- `index()` — Added `module_type`, `booking_date`, `booking_time_slot`, `booking_duration_min` to order data
- `show()` — Added booking fields + `google_calendar_link` to order detail data

#### `app/Http/Controllers/DashboardController.php`
- **`updateModules()` — New method:**
  - Validates `modules` array
  - Enforces tier: free tier can't have >1 module
  - Sets `onboarding_completed = true`

### Controllers Created

#### `app/Http/Controllers/Dashboard/ServiceController.php`
- Full CRUD for services
- `index()` — Lists all tenant services
- `create()` — Returns create form with default schedule config
- `store()` — Validates and creates service
- `edit()` — Returns edit form
- `update()` — Updates service
- `destroy()` — Soft deletes service
- `toggleAvailability()` — Toggles `is_available` boolean
- All methods enforce tenant ownership

#### `app/Http/Controllers/Dashboard/OnboardingController.php`
- `create()` — Renders onboarding wizard page
- `store()` — Accepts `business_type` (`catalog`, `booking`, `both`), sets `enabled_modules` and `onboarding_completed`, redirects to appropriate setup page

### Middleware Created

#### `app/Http/Middleware/RequireOnboarding.php`
- Checks if authenticated user's tenant has `onboarding_completed = false`
- Redirects to `dashboard.onboarding` route
- Skips for onboarding routes themselves, logout, and guests

#### `app/Http/Middleware/RequireModule.php`
- Route middleware: `require.module:booking`
- Checks if tenant has the required module enabled
- Returns 403 JSON for API requests, redirects to settings for web requests
- Indicates if upgrade is needed via `requires_upgrade` flag

### Middleware Registration (`bootstrap/app.php`)
```php
'onboarding' => \App\Http\Middleware\RequireOnboarding::class,
'require.module' => \App\Http\Middleware\RequireModule::class,
```

### Routes Added (`routes/web.php`)

**Onboarding:**
```
GET  /dashboard/onboarding              → onboarding
POST /dashboard/onboarding              → onboarding.store
```

**Services (behind `require.module:booking`):**
```
GET    /dashboard/services              → services.index
GET    /dashboard/services/create       → services.create
POST   /dashboard/services              → services.store
GET    /dashboard/services/{service}    → services.show
GET    /dashboard/services/{service}/edit → services.edit
PUT    /dashboard/services/{service}    → services.update
DELETE /dashboard/services/{service}    → services.destroy
PUT    /dashboard/services/{service}/toggle → services.toggle-availability
```

**Storefront Service Detail:**
```
GET /{store_link}/services/{serviceSlug} → storefront.services.show
```

**Settings Module Management:**
```
POST /dashboard/settings/modules → settings.modules.update
```

---

## Storefront Module System

**Location:** `resources/js/Components/modules/`

### Architecture
Home.vue acts as the **orchestrator** — reads `tenant.modules` from props, conditionally renders module components. Each module is **self-contained** with its own state, composables, cart/checkout/booking logic.

```
Home.vue (orchestrator)
├── reads tenant.modules from props
├── v-if has 'catalog' → <CatalogModule :products="products" :tenant="tenant" />
└── v-if has 'booking' → <BookingModule :services="services" :tenant="tenant" />
```

### `CatalogModule.vue`
- **Props:** `products[]`, `tenant`
- **Contains:** Product grid, cart (FloatingCartButton, CartDrawer), checkout (CheckoutModal)
- **Composables:** `useCartStore`, `useCheckout`
- **UI:** Same yellow header → products section with "Lihat semua produk" link
- **Interactions:** Add to cart → cart drawer → checkout → WhatsApp redirect

### `BookingModule.vue`
- **Props:** `services[]`, `tenant`
- **Contains:** Services list (vertical cards), BookingModal, success modal
- **Composables:** `useBooking`
- **UI:** Service cards with image, name, price, duration, "Book" button
- **Interactions:** Click "Book" → BookingModal → date/time picker → customer info → WhatsApp redirect + Google Calendar link

### How It Works
```vue
<!-- Home.vue -->
<script setup>
const enabledModules = props.tenant.modules || ['catalog'];
</script>

<CatalogModule v-if="enabledModules.includes('catalog')" :products="products" :tenant="tenant" />
<BookingModule v-if="enabledModules.includes('booking')" :services="services" :tenant="tenant" />
```

- Tenant with `modules: ['catalog']` → sees Products section only
- Tenant with `modules: ['booking']` → sees Services section only  
- Tenant with `modules: ['catalog', 'booking']` (Premium) → sees both, Products first then Services

---

## Shared Navigation Header Components

**Created:** `resources/js/Components/navigation/`

### Naming Convention
Components use the pattern: `Tenant{Location}{ComponentName}`
- `TenantDashboardHeader` — for `/dashboard/*` pages
- `TenantStorefrontHeader` — for `/{store_link}/*` storefront pages

### `TenantDashboardHeader.vue`
- **Props:** `title` (required), `backUrl` (default: `/dashboard`), `showLogout` (default: `false`)
- **Slots:** `#actions` — right-side action buttons (e.g., "+ Add" on Products/Services lists)
- **Layout:** White background, sticky top, back arrow + title on left, actions + optional logout on right
- **Applied to:**
  - `Dashboard/Home.vue` — title: "Dashboard", logout: true
  - `Dashboard/Orders/Index.vue` — title: "Orders"
  - `Dashboard/Products/Index.vue` — title: "Products", action: "+ Add"
  - `Dashboard/Products/Create.vue` — title: "Add Product"
  - `Dashboard/Products/Edit.vue` — title: "Edit Product"
  - `Dashboard/Services/Index.vue` — title: "Services", action: "+ Add"
  - `Dashboard/Services/Create.vue` — title: "Add Service"
  - `Dashboard/Services/Edit.vue` — title: "Edit Service"
  - `Dashboard/Links/Index.vue` — title: "Links"

### `TenantStorefrontHeader.vue`
- **Props:** `title` (required), `backUrl` (default: `/`)
- **Layout:** White background, sticky top, back arrow + title on left
- **Not yet applied** — storefront pages still use inline headers (backlog item)

### Pages Created

#### `resources/js/Pages/Dashboard/Onboarding.vue`
- Business type selection cards:
  - 🛍️ "I sell products" → sets `['catalog']`
  - 📅 "I offer services" → sets `['booking']`
  - ✨ "Both — I do both" → visual only (requires premium upgrade)
- Form submission to `dashboard.onboarding.store`

#### `resources/js/Pages/Dashboard/Services/Index.vue`
- Table view: name, duration, price, availability status
- Toggle availability button (Available/Hidden)
- Edit and Delete actions
- Empty state with CTA to create first service

#### `resources/js/Pages/Dashboard/Services/Create.vue`
- Form fields: name, description, price, currency, duration, buffer, start/end time, available days (checkboxes), availability toggle
- Day picker buttons (Mon-Sun toggle)

#### `resources/js/Pages/Dashboard/Services/Edit.vue`
- Same form as Create, pre-populated with service data

#### `resources/js/Pages/Storefront/Service.vue`
- Service detail page: image, name, price, duration, description
- "Book via WhatsApp" button
- Integrates `useBooking` composable
- BookingModal + Success Modal

### Components Created

#### `resources/js/Components/Storefront/BookingModal.vue`
- Full-screen booking modal (same UX pattern as CheckoutForm)
- Date picker (min date = today)
- Time slot grid (generated dynamically based on selected date)
- Customer info fields (name, phone, address)
- Google Calendar "Add to Calendar" link (appears when date+time selected)
- WhatsApp submit button with processing state

#### `resources/js/Components/orders/OrderHeader.vue` (Modified)
- Added booking badge: 📅 Booking (purple pill)
- Added booking details row: date • time (duration min)

### Composables Created

#### `resources/js/Composables/useBooking.js`
- **State:** `showBookingForm`, `showSuccessModal`, `lastOrderNumber`, `lastReceiptUrl`, `processing`, `errors`, `form`
- **Methods:**
  - `generateTimeSlots(date)` — Generates slots from service config
  - `getMinDate()` — Today's date string
  - `generateGoogleCalendarLink(date, timeSlot)` — Google Calendar add-event URL
  - `generateWhatsAppMessage(receiptUrl)` — Formatted message with booking details
  - `submitBooking()` — POST to orders endpoint, then redirect to wa.me
  - `openBooking()`, `closeBooking()`, `closeSuccessModal()`

---

## What Works Now

1. ✅ Tenant onboarding — pick business type on first login
2. ✅ Service CRUD — create, edit, delete, toggle availability
3. ✅ Module enforcement — `require.module:booking` middleware guards service routes
4. ✅ Booking order creation — POST with `module_type='booking'` saves to orders table
5. ✅ WhatsApp redirect — same flow as catalog orders
6. ✅ Google Calendar link — free tier, no OAuth needed
7. ✅ Dashboard orders — shows booking badge with date/time details
8. ✅ Service detail page — storefront page with booking modal
9. ✅ Build passes — `npm run build` clean

---

## Backlog (Next Iteration)

### P0 — Required for Booking to be Functional

- [ ] **Update `Catalog.vue` to render services tab + "Book Now" buttons**
  - Current `Catalog.vue` only renders products grid
  - Need to add tab switcher when both modules enabled: "Products" | "Services"
  - Services grid with "Book Now" CTA → opens `BookingModal`
  - Pass `tenant.modules` from backend (already done) to conditionally render tabs
  - File: `resources/js/Pages/Storefront/Catalog.vue`

- [ ] **Wire up onboarding middleware to dashboard routes**
  - `RequireOnboarding` middleware exists but not applied to dashboard routes
  - Should wrap dashboard routes: `->middleware(['auth', 'tenant', 'onboarding'])`
  - Exception: onboarding routes themselves must be outside this group
  - File: `routes/web.php`

- [ ] **Add navigation link to Services in dashboard sidebar**
  - Dashboard layout/sidebar needs "Services" nav item (only visible if booking module enabled)
  - Mirror existing "Products" link
  - File: wherever the dashboard layout/sidebar component lives

### P1 — Settings & UX Polish

- [ ] **Update Settings page with module toggle UI**
  - Checkboxes for "Catalog" and "Booking"
  - Show upgrade prompt when free tenant tries to enable second module
  - Submit to `POST /dashboard/settings/modules`
  - File: `resources/js/Pages/Dashboard/Settings.vue`

- [ ] **Update `Home.vue` storefront to show services section**
  - Already receives `services` prop from backend
  - Need to render services grid alongside/afer products on home page
  - File: `resources/js/Pages/Storefront/Home.vue`

- [ ] **Handle `StoreOrderRequest` validation for booking fields**
  - Current `StoreOrderRequest` may not allow `module_type` or booking-specific fields
  - May need to relax validation or create separate request
  - File: `app/Http/Requests/StoreOrderRequest.php`

### P2 — Advanced Features

- [ ] **Booking availability calendar (conflict prevention)**
  - Query existing bookings for a date to filter out taken slots
  - Requires checking `orders` table where `module_type='booking'`, `booking_date=X`, `booking_time_slot=Y`, `status != 'cancelled'`
  - Frontend: calendar component showing available/taken slots
  - Backend: endpoint `GET /{store_link}/services/{slug}/availability?date=YYYY-MM-DD`
  - Consider: race condition handling (two customers booking same slot simultaneously)

- [ ] **Google Calendar API integration (Premium feature)**
  - Current implementation: add-event URL (works for any user)
  - Premium: OAuth integration, auto-create event on tenant's calendar, send invite to customer
  - Requires: Google API credentials, OAuth flow, refresh token storage

- [ ] **Booking reminders / cancellation flow**
  - Tenant can cancel a booking → sends WhatsApp notification
  - Automated reminder X hours before booking
  - Status flow addition: `cancelled` state for bookings

- [ ] **Recurring bookings**
  - Customer books "every week for 4 weeks"
  - Creates 4 orders in one go

---

## Known Trade-offs

| Decision | Trade-off | Why |
|----------|-----------|-----|
| Shared `orders` table | Nullable columns for booking fields | Reuse status lifecycle, history, dashboard. Worth the nullable columns. |
| No availability checking (MVP) | Double-booking possible | Fastest to ship. Calendar + conflict prevention is P2. |
| Google Calendar URL (not API) | Doesn't auto-sync to tenant calendar | Zero setup, works for all customers. API integration is premium upsell. |
| Single service per booking | Can't book multiple services at once | Matches the "single service" requirement. Multi-service cart is future work. |
| `orderable_type` stored as string | No DB-level FK constraint for polymorphic | Standard Laravel polymorphic pattern. Application-level integrity checks. |
