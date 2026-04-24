# Receipt & Order Management System - Implementation Plan

## System Overview

**MyMenu** is a landing page catalogue empowering local businesses to accept orders. Customers browse products, add to cart, and checkout via WhatsApp. This plan adds **persistent order storage** linked to tenants, with receipt links for both customers and tenants.

---

## Current State

### Cart Implementation
- **Location**: `/Users/bobhartanto/te_01/resources/js/Pages/Storefront/Catalog.vue` (Lines 33-93)
- **Type**: Client-side only (no database persistence)
- **Features**:
  - State: `cart` (ref array), `showCart` (visibility)
  - Computed: `cartTotal`, `cartCount`
  - Functions: `addToCart()`, `removeFromCart()`, `updateQuantity()`, `clearCart()`, `checkout()`

### Supporting Files
- `/Users/bobhartanto/te_01/resources/js/Composables/useWhatsAppOrder.js` - Formats cart into WhatsApp message
- `/Users/bobhartanto/te_01/app/Http/Controllers/StorefrontController.php` - Serves catalog page

---

## Goals

1. **Persist cart data** to database when customer checks out (linked to tenant)
2. **Tenant dashboard** to view incoming orders with customer information
3. **Tenant capabilities**:
   - Add shipping cost
   - Add payment method notes
   - Update item quantities (out of stock scenarios)
   - Remove items from orders (out of stock)
   - Upload shipping receipt (no resi) for tracking
4. **Dual access views**:
   - **Public**: `/receipt/{order_number}` - Customer receipt link
   - **Tenant**: `/dashboard/orders/{id}` - Tenant order management (authenticated)

---

## Database Schema

### 1. `orders` Table
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
tenant_id           BIGINT UNSIGNED NOT NULL (FK -> tenants.id, CASCADE)
order_number        VARCHAR(50) UNIQUE NOT NULL  -- Format: ORD-YYYYMMDD-XXXX
customer_name       VARCHAR(255) NOT NULL
customer_phone      VARCHAR(50) NOT NULL
customer_address    TEXT NULL
status              ENUM('pending','confirmed','processing','shipped','completed','cancelled') DEFAULT 'pending'
payment_status      ENUM('unpaid','paid') DEFAULT 'unpaid'

-- Pricing
original_subtotal   DECIMAL(10,2) NOT NULL        -- Customer's original cart total
adjusted_subtotal   DECIMAL(10,2) NOT NULL        -- After tenant modifications
shipping_cost       DECIMAL(10,2) DEFAULT 0
total               DECIMAL(10,2) NOT NULL        -- adjusted_subtotal + shipping_cost

-- Tenant additions
payment_notes       TEXT NULL
shipping_receipt    VARCHAR(255) NULL             -- File path to uploaded receipt
adjustment_notes    TEXT NULL                     -- Why items were changed

-- Timestamps
created_at          TIMESTAMP
updated_at          TIMESTAMP

INDEX idx_tenant_status (tenant_id, status)
INDEX idx_order_number (order_number)
```

### 2. `order_items` Table
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
order_id            BIGINT UNSIGNED NOT NULL (FK -> orders.id, CASCADE DELETE)
product_id          BIGINT UNSIGNED NULL          -- NULL if product deleted from system
product_name        VARCHAR(255) NOT NULL         -- Snapshot (immutable)
product_sku         VARCHAR(100) NULL             -- Snapshot (immutable)
original_quantity   INT NOT NULL                  -- What customer ordered (immutable)
current_quantity    INT NOT NULL                  -- After tenant adjustment
unit_price          DECIMAL(10,2) NOT NULL        -- Snapshot at order time (immutable)
currency            VARCHAR(10) DEFAULT 'IDR'
is_removed          BOOLEAN DEFAULT FALSE         -- Soft delete flag
removed_at          TIMESTAMP NULL

INDEX idx_order_id (order_id)
```

### 3. `order_histories` Table (Audit Trail)
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
order_id            BIGINT UNSIGNED NOT NULL (FK -> orders.id, CASCADE DELETE)
changed_by          BIGINT UNSIGNED NULL          (FK -> users.id, SET NULL)
action              VARCHAR(100) NOT NULL          -- e.g., 'created', 'item_quantity_updated', 'item_removed', 'shipping_added', 'receipt_uploaded'
old_values          JSON NULL
new_values          JSON NULL
notes               TEXT NULL                      -- Tenant's reason for change
created_at          TIMESTAMP

INDEX idx_order_id (order_id)
```

---

## Immutability Strategy

### IMMUTABLE (Never Change):
- ✅ `original_subtotal` - Customer's original cart total
- ✅ `order_items.product_name` - Product name snapshot
- ✅ `order_items.product_sku` - SKU snapshot
- ✅ `order_items.original_quantity` - What customer originally ordered
- ✅ `order_items.unit_price` - Price at order time
- ✅ `created_at` - Order creation timestamp

### MUTABLE (Can Change):
- 🔧 `order_items.current_quantity` - Tenant can adjust (out of stock)
- 🔧 `order_items.is_removed` - Tenant can remove/restore items
- 🔧 `adjusted_subtotal` - Recalculated when items change
- 🔧 `shipping_cost` - Tenant can add/update
- 🔧 `total` - Recalculated (adjusted_subtotal + shipping_cost)
- 🔧 `payment_notes` - Tenant instructions
- 🔧 `shipping_receipt` - Upload/update proof
- 🔧 `status` - Order lifecycle progression
- 🔧 `payment_status` - Payment confirmation
- 🔧 `adjustment_notes` - Tenant explanation for changes

### Why This Matters:
- **Audit integrity**: Original order preserved for dispute resolution
- **Price changes**: Historical orders remain accurate if tenant changes prices
- **Transparency**: Customers see what they ordered vs what's fulfilled
- **Legal compliance**: Receipts reflect actual transaction

---

## URL Structure

| View | URL | Access |
|------|-----|--------|
| **Public Receipt** | `/receipt/{order_number}` | Anyone with link |
| **Tenant Order List** | `/dashboard/orders` | Authenticated tenant |
| **Tenant Order Detail** | `/dashboard/orders/{id}` | Authenticated tenant |

---

## Access Control Matrix

| Feature | Public (Customer) | Tenant (Authenticated) |
|---------|------------------|------------------------|
| View order details | ✅ | ✅ |
| View items & prices | ✅ | ✅ |
| See original vs adjusted quantities | ✅ | ✅ |
| View payment notes | ✅ | ✅ |
| View shipping receipt | ✅ | ✅ |
| Edit item quantities | ❌ | ✅ |
| Remove/restore items | ❌ | ✅ |
| Add shipping cost | ❌ | ✅ |
| Add payment notes | ❌ | ✅ |
| Upload shipping receipt | ❌ | ✅ |
| Update order status | ❌ | ✅ |
| Add adjustment notes | ❌ | ✅ |

---

## Customer Flow

```
1. Customer browses catalog /{store_link}/catalog
2. Adds items to cart
3. Clicks "Checkout" → Checkout form appears
   - Name (required)
   - Phone (required)
   - Address (optional)
4. Clicks "Order via WhatsApp"
   - WhatsApp opens with formatted message
   - Order saved to database (triggered by visibility change)
5. Customer sees success message with receipt link
6. Customer can revisit /receipt/{order_number} anytime
```

---

## Tenant Flow

```
1. Tenant logs into dashboard
2. Navigates to /dashboard/orders
3. Sees list of all orders (filterable by status)
4. Clicks order → /dashboard/orders/{id}
5. Can:
   - View customer info
   - Edit item quantities (out of stock)
   - Remove/restore items
   - Add shipping cost
   - Add payment notes
   - Upload shipping receipt
   - Update order status
   - Add adjustment notes
```

---

## Implementation Plan

### Phase 1: Database & Backend (Foundation)

#### 1.1 Database Migrations
**Files to create:**
```
database/migrations/xxxx_create_orders_table.php
database/migrations/xxxx_create_order_items_table.php
database/migrations/xxxx_create_order_histories_table.php
```

**Tasks:**
- Create `orders` table with all fields
- Create `order_items` table with soft delete support
- Create `order_histories` table for audit trail
- Add foreign key constraints and indexes

#### 1.2 Eloquent Models
**Files to create:**
```
app/Models/Order.php
app/Models/OrderItem.php
app/Models/OrderHistory.php
```

**Order.php features:**
```php
- Relationships: tenant(), items(), histories()
- Attributes: order_number generation, total calculation
- Scopes: byStatus(), byTenant(), recent()
- Events: log creation to history
```

**OrderItem.php features:**
```php
- Relationships: order(), product()
- Methods: restore(), markRemoved(), updateQuantity()
- Computed: subtotal (current_quantity * unit_price)
```

**OrderHistory.php features:**
```php
- Relationships: order(), changedBy()
- JSON casting for old_values, new_values
```

#### 1.3 Controllers
**Files to create:**
```
app/Http/Controllers/OrderController.php              // Checkout API
app/Http/Controllers/ReceiptController.php             // Public receipt view
app/Http/Controllers/Dashboard/OrderController.php     // Tenant order list
app/Http/Controllers/Dashboard/OrderItemController.php // Tenant item modifications
```

**OrderController (Public):**
```php
POST   /{store_link}/orders                    // Create order from checkout
```

**ReceiptController (Public):**
```php
GET    /receipt/{order_number}                 // Show public receipt page
```

**Dashboard/OrderController (Tenant):**
```php
GET    /dashboard/orders                       // List all tenant orders
GET    /dashboard/orders/{id}                  // Order detail view
PATCH  /dashboard/orders/{id}                  // Update order (shipping, notes, status)
POST   /dashboard/orders/{id}/receipt          // Upload shipping receipt
```

**Dashboard/OrderItemController (Tenant):**
```php
PATCH  /dashboard/orders/{orderId}/items/{itemId}/quantity  // Update quantity
DELETE /dashboard/orders/{orderId}/items/{itemId}           // Remove item
POST   /dashboard/orders/{orderId}/items/{itemId}/restore   // Restore item
```

#### 1.4 Form Requests (Validation)
**Files to create:**
```
app/Http/Requests/StoreOrderRequest.php
app/Http/Requests/UpdateOrderRequest.php
app/Http/Requests/UpdateOrderItemQuantityRequest.php
```

#### 1.5 Routes
**Modify:** `routes/web.php`

Add routes for:
- Public order creation
- Public receipt view
- Tenant order management (with auth middleware)

---

### Phase 2: Frontend (UI Components)

#### 2.1 Checkout Form Component
**File to create:**
```
resources/js/Components/CheckoutForm.vue
```

**Features:**
- Modal/drawer component
- Fields: name (required), phone (required), address (optional)
- Validation
- Emits submit event with customer data + cart items
- Triggers WhatsApp + order creation

#### 2.2 Modify Catalog.vue
**File to modify:**
```
resources/js/Pages/Storefront/Catalog.vue
```

**Changes:**
- Import CheckoutForm component
- Replace direct WhatsApp checkout with form trigger
- Add API call to create order after WhatsApp opens
- Show success message with receipt link after order creation
- Use `visibilitychange` event to detect WhatsApp redirect

#### 2.3 Modify useWhatsAppOrder.js
**File to modify:**
```
resources/js/Composables/useWhatsAppOrder.js
```

**Changes:**
- Add `createOrder()` function that calls API
- Modify `sendOrder()` to:
  1. Open WhatsApp
  2. Call `createOrder()` after redirect
  3. Return order_number for receipt link

#### 2.4 Public Receipt Page
**File to create:**
```
resources/js/Pages/Receipt/Show.vue
```

**Features:**
- Order header (number, date, status badge)
- Customer information section
- Items table showing:
  - Product name, SKU
  - Original quantity vs current quantity
  - Unit price, subtotal
  - Visual indicators for removed/adjusted items
- Pricing summary:
  - Original subtotal
  - Adjusted subtotal
  - Shipping cost
  - Total
- Payment notes (if added)
- Shipping receipt image (if uploaded)
- Adjustment notes (if tenant modified order)
- Responsive design, print-friendly

#### 2.5 Tenant Order List Page
**File to create:**
```
resources/js/Pages/Dashboard/Orders/Index.vue
```

**Features:**
- Table of all tenant orders
- Columns: Order #, Customer, Date, Status, Total, Actions
- Filter by status (tabs or dropdown)
- Search by order number or customer name
- Pagination
- Quick status badges
- Link to order detail

#### 2.6 Tenant Order Detail Page
**File to create:**
```
resources/js/Pages/Dashboard/Orders/Show.vue
```

**Features:**

**Section 1: Customer Info**
- Name, phone, address
- Order date, status dropdown

**Section 2: Order Items (Editable)**
```
┌──────────────────────────────────────────────────┐
│ Items Ordered                                    │
├──────────────────────────────────────────────────┤
│ ✓ Product A                        Qty: [2]      │
│   @ Rp 50,000                      Rp 100,000    │
│   [Update Qty] [Remove]                          │
├──────────────────────────────────────────────────┤
│ ⚠ Product B                        Qty: [0]      │
│   @ Rp 75,000                      Rp 0          │
│   (OUT OF STOCK)                                 │
│   [Restore]                                      │
├──────────────────────────────────────────────────┤
│ ✗ Product C (REMOVED)              Qty: 3        │
│   @ Rp 30,000                      Rp 90,000     │
│   [Restore]                                      │
├──────────────────────────────────────────────────┤
│ Original Subtotal:               Rp 265,000      │
│ Adjusted Subtotal:               Rp 100,000      │
└──────────────────────────────────────────────────┘
```

**Section 3: Order Management**
- Shipping cost input
- Payment notes textarea
- Adjustment notes textarea
- Shipping receipt upload (with preview)
- Status dropdown
- [Save Changes] button

**Section 4: Order History (Audit Trail)**
- Timeline of all changes
- Who made change, when, what changed
- Notes from tenant

#### 2.7 Supporting Components
**Files to create:**
```
resources/js/Components/OrderItemRow.vue              // Tenant order item row with edit controls
resources/js/Components/OrderStatusBadge.vue          // Status badge component
resources/js/Components/OrderHistoryTimeline.vue      // Audit trail display
resources/js/Components/ReceiptUpload.vue             // Shipping receipt upload
resources/js/Components/QuantityEditModal.vue         // Modal to update quantity
```

---

### Phase 3: Integration & Testing

#### 3.1 Wire Up Order Creation
**Tasks:**
- Modify Catalog.vue checkout flow
- Add API integration to create order after WhatsApp opens
- Handle success/error states
- Show receipt link to customer
- Store order_number in localStorage for quick access

#### 3.2 Tenant Dashboard Integration
**Tasks:**
- Add "Orders" link to tenant dashboard navigation
- Connect order list page to API
- Connect order detail page to API
- Implement real-time updates (or manual refresh)
- Test all CRUD operations

#### 3.3 File Upload Setup
**Tasks:**
- Configure storage link for shipping receipts
- Add validation (file type, size)
- Implement upload endpoint
- Add image preview in tenant view
- Display receipt in public receipt page

#### 3.4 Edge Cases & Error Handling
**Tasks:**
- Handle duplicate order submissions
- Handle failed API calls (retry logic)
- Handle deleted products in existing orders
- Handle tenant accessing non-existent orders
- Handle invalid order numbers in receipt links
- Add loading states throughout

#### 3.5 Testing
**Tasks:**
- Test full flow: cart → checkout → order → receipt
- Test tenant modifications (qty update, remove, restore)
- Test shipping cost & notes addition
- Test receipt upload
- Test public receipt page displays correctly
- Test tenant order list filtering
- Test on mobile devices

---

## File Structure Summary

### New Files (30 total)

**Database (3):**
```
database/migrations/xxxx_create_orders_table.php
database/migrations/xxxx_create_order_items_table.php
database/migrations/xxxx_create_order_histories_table.php
```

**Models (3):**
```
app/Models/Order.php
app/Models/OrderItem.php
app/Models/OrderHistory.php
```

**Controllers (4):**
```
app/Http/Controllers/OrderController.php
app/Http/Controllers/ReceiptController.php
app/Http/Controllers/Dashboard/OrderController.php
app/Http/Controllers/Dashboard/OrderItemController.php
```

**Form Requests (3):**
```
app/Http/Requests/StoreOrderRequest.php
app/Http/Requests/UpdateOrderRequest.php
app/Http/Requests/UpdateOrderItemQuantityRequest.php
```

**Pages (4):**
```
resources/js/Pages/Receipt/Show.vue
resources/js/Pages/Dashboard/Orders/Index.vue
resources/js/Pages/Dashboard/Orders/Show.vue
resources/js/Pages/Storefront/CheckoutSuccess.vue (optional)
```

**Components (6):**
```
resources/js/Components/CheckoutForm.vue
resources/js/Components/OrderItemRow.vue
resources/js/Components/OrderStatusBadge.vue
resources/js/Components/OrderHistoryTimeline.vue
resources/js/Components/ReceiptUpload.vue
resources/js/Components/QuantityEditModal.vue
```

### Modified Files (4)

```
routes/web.php
resources/js/Pages/Storefront/Catalog.vue
resources/js/Composables/useWhatsAppOrder.js
resources/js/Layouts/DashboardLayout.vue (add Orders nav link)
```

---

## API Endpoints Summary

### Public Endpoints (No Auth)
```
POST   /{store_link}/orders                    Create order from checkout
GET    /receipt/{order_number}                 View public receipt
```

### Tenant Endpoints (Auth Required)
```
GET    /dashboard/orders                       List all orders
GET    /dashboard/orders/{id}                  Order detail view
PATCH  /dashboard/orders/{id}                  Update order (shipping, notes, status, receipt)
PATCH  /dashboard/orders/{orderId}/items/{itemId}/quantity  Update item quantity
DELETE /dashboard/orders/{orderId}/items/{itemId}           Remove item from order
POST   /dashboard/orders/{orderId}/items/{itemId}/restore   Restore removed item
```

---

## Order Status Flow

```
pending → confirmed → processing → shipped → completed
   ↓
cancelled (any stage)
```

**Default:** Orders start as `pending`  
**Tenant updates:** Progress through workflow  
**Cancelled:** Can be done by tenant (with notes)

---

## Order Number Generation

**Format:** `ORD-YYYYMMDD-XXXX`
- `YYYYMMDD`: Order date
- `XXXX`: Sequential number (0001-9999)

**Example:** `ORD-20260405-0001`

**Generation Logic:**
```php
$date = now()->format('Ymd');
$lastOrder = Order::whereDate('created_at', today())
    ->orderBy('id', 'desc')
    ->first();
$sequence = $lastOrder ? (intval(substr($lastOrder->order_number, -4)) + 1) : 1;
$orderNumber = 'ORD-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
```

---

## Security Considerations

1. **Public receipt links** are unguessable (order_number format with date + sequence)
2. **Tenant routes** protected by authentication middleware
3. **Tenant can only access their own orders** (scope queries by tenant_id)
4. **File uploads** validated (image types only, max 2MB)
5. **Rate limiting** on order creation to prevent spam
6. **XSS protection** - sanitize customer inputs before display
7. **CSRF protection** on all forms

---

## Future Enhancements (Backlog)

- [ ] Email notifications to customers on order status changes
- [ ] WhatsApp notifications when tenant modifies order
- [ ] Customer approval workflow for order modifications
- [ ] Shipping tracking integration (API to courier services)
- [ ] Multi-currency support
- [ ] Order analytics dashboard
- [ ] Export orders to CSV/PDF
- [ ] Bulk order status updates
- [ ] Customer order history page (with phone verification)
- [ ] Inventory auto-decrement on order creation
- [ ] Low stock alerts to tenants

---

## Implementation Sequence

**Day 1-2:** Phase 1 (Database & Backend)
- Migrations, models, controllers, routes

**Day 3-4:** Phase 2 (Frontend)
- Checkout form, receipt pages, tenant order management UI

**Day 5:** Phase 3 (Integration & Testing)
- Wire up flows, test end-to-end, fix bugs

**Total Estimated Time:** 3-5 days (depending on complexity & testing depth)

---

## Success Criteria

✅ Customer can checkout cart and order is saved to database  
✅ Customer receives receipt link to view their order  
✅ Tenant can view all incoming orders in dashboard  
✅ Tenant can update item quantities (out of stock scenarios)  
✅ Tenant can remove/restore items from orders  
✅ Tenant can add shipping cost, payment notes, upload receipt  
✅ Public receipt page shows original vs adjusted quantities  
✅ All tenant changes logged in audit trail  
✅ No automatic WhatsApp notifications (per requirements)  
✅ Changes apply automatically without customer approval  

---

*Last Updated: April 5, 2026*  
*Status: Ready for Implementation*
