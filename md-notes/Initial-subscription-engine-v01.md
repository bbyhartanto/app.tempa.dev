# Subscription System Engineering Plan - v0.1

**Date:** April 6, 2026
**Status:** Planning Phase
**Target:** Tenant Subscription Management System

---

## Current System Analysis

### 1. Tenant Model (`app/Models/Tenant.php`)

**Current Database Fields:**

| Field | Type | Notes |
|---|---|---|
| `id` | auto-increment | Primary key |
| `name` | string | Store name |
| `slug` | string (unique) | URL-friendly, auto-generated from name |
| `store_link` | string (unique) | Public-facing URL path (e.g., `/mystore`) |
| `email` | string (unique) | Contact email |
| `phone` | string (nullable) | E.164 format |
| `whatsapp_number` | string (nullable) | E.164 format |
| `logo_url` | string (nullable) | |
| `background_image` | string (nullable) | |
| `description` | text (nullable) | |
| `google_maps_link` | string (nullable) | |
| `address`, `city`, `province`, `streetname` | string (nullable) | Location fields |
| `latitude`, `longitude` | decimal (nullable) | GPS coordinates |
| `opening_schedule` | json (nullable) | Structure: `{mon: {open: "09:00", close: "18:00", closed: false}, ...}` |
| `template_slug` | string (default: `'minimal'`) | References template by slug |
| `settings` | json (nullable) | Template-specific settings |
| `store_links` | json (nullable) | External platform links (GrabFood, ShopeeFood, etc.) |
| `status` | enum (`'pending'`, `'active'`, `'suspended'`) | Default: `'pending'` |
| `approved_at` | timestamp (nullable) | Set when super admin approves |
| `remember_token` | string | Auth token |
| `created_at`, `updated_at` | timestamps | |

**Key Relationships:**
- `products()` -- HasMany
- `availableProducts()` -- HasMany (scoped to `is_available = true`)
- `orderLogs()` -- HasMany
- `orders()` -- HasMany
- `template()` -- HasMany (links Template by `slug` to `template_slug`)

**Scopes & Helpers:**
- `scopeActive()` / `scopePending()` -- query filters by status
- `isActive()` -- boolean check
- `getFormattedWhatsAppNumberAttribute()` -- cleans number for `wa.me` links
- `isOpenNow()` -- checks opening schedule against current time
- Boot hook auto-generates `slug` and `store_link` from `name`

---

### 2. Database Migrations

**Initial migration** (`database/migrations/2026_03_30_093538_create_tenants_table.php`):
- Creates the full `tenants` table with all fields listed above.
- Indexes on `store_link` and `status` for efficient tenant resolution.

**Additive migration** (`database/migrations/2026_04_01_034330_add_store_links_to_tenants_table.php`):
- Adds `store_links` JSON column for external platform links (GrabFood, ShopeeFood, etc.).

---

### 3. Controllers

#### TenantManagementController (`app/Http/Controllers/Admin/TenantManagementController.php`)

Super-admin-only controller (protected by `role:super_admin` middleware). Actions:

| Method | Route | Action |
|---|---|---|
| `index()` | GET `/admin/tenants` | Lists all tenants with optional `?status=` filter, paginated (20/page) |
| `show()` | GET `/admin/tenants/{id}` | Shows tenant details with products count and orders count |
| `approve()` | POST `/admin/tenants/{id}/approve` | Sets status to `active`, sets `approved_at` |
| `suspend()` | POST `/admin/tenants/{id}/suspend` | Sets status to `suspended` |
| `reactivate()` | POST `/admin/tenants/{id}/reactivate` | Sets status to `active` |
| `destroy()` | DELETE `/admin/tenants/{id}` | Hard deletes tenant and all associated data |
| `templates()` | GET `/admin/templates` | Lists all templates |
| `storeTemplate()` | POST `/admin/templates` | Creates a new template |
| `updateTemplate()` | PUT `/admin/templates/{template}` | Updates a template |

#### RegisteredTenantController (`app/Http/Controllers/Auth/RegisteredTenantController.php`)

Handles public tenant registration:
- Creates a `Tenant` record with `status = 'pending'` and `template_slug = 'minimal'`
- Creates a `User` with `role = 'tenant_owner'`
- Fires `Registered` event, logs in the user, stores `current_tenant_id` in session
- Redirects to dashboard

---

### 4. Middleware

#### ResolveTenant (`app/Http/Middleware/ResolveTenant.php`)
- Extracts `store_link` from the first URL segment
- Queries `Tenant::where('store_link', $link)->active()->first()`
- Aborts with 404 if tenant not found or not active
- Stores tenant in request attributes for downstream access

#### TenantRole (`app/Http/Middleware/TenantRole.php`)
- Registered as `'tenant'` alias in `bootstrap/app.php`
- Ensures the authenticated user has `role === 'tenant_owner'`
- Aborts with 403 if a `super_admin` tries to access tenant dashboards

#### TenantScope (`app/Models/Scopes/TenantScope.php`)
- An optional global scope for tenant data isolation (NOT enabled by default)
- Designed for explicit use: `Product::forTenant($tenantId)->get()`
- Rationale: super admin needs cross-tenant queries

---

### 5. Routes (`routes/web.php`)

**Super Admin routes** (prefix `/admin`, requires `auth` + `role:super_admin`):
```
GET    /admin/tenants              -> admin.tenants.index
GET    /admin/tenants/{tenant}     -> admin.tenants.show
POST   /admin/tenants/{tenant}/approve   -> admin.tenants.approve
POST   /admin/tenants/{tenant}/suspend   -> admin.tenants.suspend
POST   /admin/tenants/{tenant}/reactivate -> admin.tenants.reactivate
DELETE /admin/tenants/{tenant}     -> admin.tenants.destroy
```

**Public storefront routes** (wildcard `/{store_link}`, uses `ResolveTenant` middleware):
```
GET    /{store_link}          -> storefront.home
GET    /{store_link}/catalog  -> storefront.catalog
POST   /{store_link}/orders   -> storefront.orders.store
```

**Tenant dashboard routes** (prefix `/dashboard`, requires `auth` + `tenant` middleware)

---

### 6. Vue Component (`resources/js/Pages/Admin/Tenants/Index.vue`)

A single-file Vue 3 component using `<script setup>` with Composition API:

**Props:**
- `tenants` (Object) -- paginated collection from controller
- `filters` (Object) -- current filter state (`status`)

**Features:**
- Status filter buttons (All / Pending / Active / Suspended) using Inertia `router.get()` with `preserveState`
- Table displays: Store name + store_link, Contact email, Template slug, Status badge, Created date
- Per-row actions: View link, Approve (if pending), Suspend (if active), Reactivate (if suspended), Delete
- Confirmation dialogs before all mutating actions
- Pagination with page links (shows up to 5 pages)

**Notable gap:** There is NO `Show.vue` page (glob search returned no matches), even though the controller's `show()` method tries to render `Admin/Tenants/Show`. This would cause a runtime error if the "View" link is clicked.

---

### 7. Subscription / Trial Logic

**There is no existing subscription, trial, billing, or plan logic anywhere in this codebase.** The only reference to "plan" was a comment about "translation strings" in `config/app.php`. The tenant lifecycle is purely: `pending` -> `active` or `suspended`, with no time-based expiration or monetization.

---

## Subscription System Requirements

### User Requirements

1. **Subscription Status Tracking:**
   - Trial mode
   - Subscribed (active)
   - Grace period (subscription expired, renewal window)
   - Expired (past grace period)

2. **Subscription Plans:**
   - 2 billing cycles: 3 months, 1 year
   - 2 merchant tiers:
     - Basic: 0-25 items
     - Standard: 0-60 items
   - Each tier has different pricing for each billing cycle
   - **Pricing must be configurable via admin input form**

3. **Manual Subscription Management:**
   - No payment gateway integration
   - Admin confirms subscriptions manually via toggle
   - Switch from trial → subscribed
   - Automatically transitions to grace period if not renewed after end date

4. **Dashboard Integration:**
   - Display subscription status in tenant list
   - Visual indicators for each status

---

## Final Subscription System Plan

### Subscription Tiers & Billing

| Tier | Items Range | 3-Month Price | 1-Year Price |
|------|-------------|---------------|--------------|
| **Basic** | 0-25 items | Configurable | Configurable |
| **Standard** | 0-60 items | Configurable | Configurable |

**Pricing stored in database** → Admin can adjust via settings page

### Tenant Subscription Lifecycle

```
Registration → TRIAL (7 days)
                 ↓
         SUBSCRIBED (admin activates)
                 ↓
    [end_date reached without renewal]
                 ↓
         GRACE_PERIOD (7 days)
                 ↓
    [still not renewed]
                 ↓
           EXPIRED
```

### Database Schema

**1. `subscription_plans` table** (configurable pricing):
- `id`
- `tier` (enum: `basic`, `standard`)
- `billing_cycle` (enum: `3_months`, `1_year`)
- `price` (decimal)
- `item_limit` (integer: 25 or 60)
- `is_active` (boolean)

**2. `tenant_subscriptions` table**:
- `id`
- `tenant_id` (FK)
- `plan_id` (FK → subscription_plans)
- `start_date` (date)
- `end_date` (date)
- `status` (enum: `active`, `expired`)
- `created_at`, `updated_at`

**3. Modify `tenants` table**:
- `subscription_status` (enum: `trial`, `subscribed`, `grace_period`, `expired`)
- `trial_started_at` (timestamp, nullable)
- `trial_ends_at` (timestamp, nullable)
- `current_subscription_id` (FK, nullable)

### Key Features

1. **Admin Dashboard - Tenant List**
   - Subscription status badge (color-coded)
   - Trial end date / Subscription end date
   - Days remaining indicator
   - "Manage Subscription" button → opens modal

2. **Subscription Management Modal**
   - Toggle: Trial ↔ Subscribed
   - Plan selector (Basic / Standard)
   - Billing cycle selector (3 months / 1 year)
   - Displays current price (from database)
   - Start date picker (default: today)
   - Auto-calculated end date preview
   - "Activate Subscription" button

3. **Pricing Configuration Page** (separate admin page)
   - Table showing all 4 price combinations
   - Editable price inputs
   - "Save Changes" button

4. **Automated System**
   - **Cron job** runs daily:
     - Check expired subscriptions → update status
     - Trial expired without subscription → mark as `expired`
     - Subscription expired → `grace_period` → after 7 days → `expired`
   - Product limit enforcement in tenant dashboard

5. **Status Badges (Frontend)**
   - 🔵 **Trial** - Blue badge, shows days remaining
   - 🟢 **Subscribed** - Green badge, shows end date
   - 🟠 **Grace Period** - Orange warning, urgent renewal notice
   - 🔴 **Expired** - Red badge, subscription inactive

---

## Implementation Tasks

### Phase 1: Database & Models
- [ ] Create migration: `subscription_plans` table
- [ ] Create migration: `tenant_subscriptions` table
- [ ] Create migration: Add subscription fields to `tenants`
- [ ] Create `SubscriptionPlan` model
- [ ] Create `TenantSubscription` model
- [ ] Seed default pricing data

### Phase 2: Backend Logic
- [ ] Create `SubscriptionService` (activation, renewal, status transitions)
- [ ] Create `SubscriptionManagementController`
- [ ] Create `PricingController` (for admin pricing config)
- [ ] Create artisan command: `CheckSubscriptionExpirations`
- [ ] Register cron job in `Kernel.php`
- [ ] Add routes for subscription management

### Phase 3: Frontend - Admin Panel
- [ ] Modify `Index.vue` - Add subscription columns & badges
- [ ] Create `SubscriptionModal.vue` - Plan selection & activation
- [ ] Create `PricingConfig.vue` - Admin pricing management page
- [ ] Add "Manage Subscription" action button to tenant list

### Phase 4: Integration
- [ ] Modify `RegisteredTenantController` - Set trial on registration
- [ ] Add product limit enforcement middleware
- [ ] Update tenant dashboard to show subscription status
- [ ] Add notification system for expiring subscriptions

---

## Architecture Notes

### Current System Architecture

1. **Multi-tenant pattern:** Single-database, path-based tenant resolution (`/{store_link}`). Each tenant gets a `tenant_owner` user account.
2. **Approval workflow:** New tenants register as `pending`, require super admin approval to become `active`.
3. **Role-based access:** `super_admin` (admin panel) vs `tenant_owner` (dashboard). Separate middleware enforces this.
4. **No subscription/billing:** The system has no concept of paid plans, trial periods, or billing cycles.
5. **Missing Vue page:** The `Show.vue` component referenced in the controller does not exist on disk.

### Design Decisions

1. **Separate subscription plans table** instead of hardcoding prices → Allows admin to adjust pricing without code changes
2. **Manual subscription management** → No payment gateway complexity, full admin control
3. **Grace period implementation** → Gives tenants buffer time before full expiration
4. **Item limit enforcement** → Ensures tenants stay within their tier limits
5. **Trial auto-expiration** → Prevents indefinite free usage

---

## Next Steps

1. ✅ Complete planning & requirements gathering
2. ⏳ Get user approval to proceed
3. ⏳ Begin Phase 1 implementation (Database & Models)
4. ⏳ Work through phases systematically
5. ⏳ Test & verify each phase before proceeding

---

**Status:** Awaiting implementation approval
