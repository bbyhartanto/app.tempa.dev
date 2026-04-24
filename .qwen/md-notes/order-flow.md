# Order Flow Documentation

## Overview

This application is a **multi-tenant storefront** where customers place orders through storefronts, and merchants (tenants) manage them through a clear approval workflow with full audit trail.

**Tech Stack:** Laravel (backend) + Vue.js/Inertia (frontend)

---

## 1. Order Status Workflow

```
pending (yellow) → confirmed (blue) → paid (green)
```

- **pending**: Order created by customer, awaiting merchant confirmation
- **confirmed**: Merchant has accepted the order
- **paid**: Payment received, order completed

---

## 2. Order Creation Flow (Customer → Storefront)

**Endpoint:** `POST /{store_link}/orders`  
**Controller:** `App\Http\Controllers\OrderController@store`  
**Validation:** `App\Http\Requests\StoreOrderRequest`

### Steps:
1. Customer fills checkout form on storefront
2. Validates request (customer_name, phone, items with quantities & prices)
3. Finds tenant by `store_link`
4. Calculates `original_subtotal` from items
5. **Transaction begins:**
   - Creates `Order` with `status='pending'`, auto-generates order_number (`ORD-YYYYMMDD-XXXX`)
   - Creates `OrderItem` records for each cart item
   - Auto-creates `OrderHistory` entry for `order_created`
6. **Transaction commits**
7. Returns `order_number` and `receipt_url`

### Request Payload:
```json
{
  "customer_name": "string (required)",
  "customer_phone": "string (required)",
  "customer_address": "string (nullable)",
  "items": [
    {
      "product_id": "integer (required, exists:products)",
      "product_name": "string (required)",
      "product_sku": "string (nullable)",
      "quantity": "integer (required, min:1)",
      "unit_price": "numeric (required, min:0)",
      "currency": "string (nullable, default: IDR)"
    }
  ]
}
```

---

## 3. Order Management Flow (Merchant Dashboard)

**Routes:** `GET /dashboard/orders` → List orders with pagination

### Merchant Actions:

#### A. Accept Order (pending → confirmed)
```
PUT /dashboard/orders/{id}/accept
```
- Only works for `pending` orders
- Updates status to `confirmed`
- Logs to `OrderHistory` with action=`order_accepted`

#### B. Mark as Paid (confirmed → paid)
```
PUT /dashboard/orders/{id}/mark-paid
```
- Only works for `confirmed` orders
- Updates status to `paid`
- Logs to `OrderHistory` with action=`order_paid`

#### C. Update Order Details
```
PATCH /dashboard/orders/{id}
```
- Can update: `status`, `payment_status`, `shipping_cost`, `payment_notes`, `adjustment_notes`, `shipping_receipt` (file upload)
- Each field change creates separate `OrderHistory` entry
- Auto-recalculates `total` when `shipping_cost` or `adjusted_subtotal` changes

**Validation:** `App\Http\Requests\UpdateOrderRequest`

---

## 4. Order Item Management

### A. Update Quantity
```
PATCH /dashboard/orders/{orderId}/items/{itemId}/quantity
```
- Updates `current_quantity`
- Logs `item_quantity_updated` to history
- Recalculates order's `adjusted_subtotal`

**Validation:** `App\Http\Requests\UpdateOrderItemQuantityRequest`

### B. Remove Item
```
DELETE /dashboard/orders/{orderId}/items/{itemId}
```
- Sets `is_removed=true`, `current_quantity=0`
- Logs `item_removed` to history
- Recalculates `adjusted_subtotal`

### C. Restore Removed Item
```
POST /dashboard/orders/{orderId}/items/{itemId}/restore
```
- Sets `is_removed=false`, restores `current_quantity` to `original_quantity`
- Logs `item_restored` to history
- Recalculates `adjusted_subtotal`

---

## 5. Key Business Logic

### Order Model (`App\Models\Order`)

**Auto-generation:**
- Order number format: `ORD-YYYYMMDD-XXXX` (sequential daily, padded to 4 digits)

**Auto-calculation:**
- `total = adjusted_subtotal + shipping_cost`
- Triggered on `saving` event when `shipping_cost` or `adjusted_subtotal` changes

**Tracking Modifications:**
- `is_modified` attribute detects if tenant changed quantities, prices, or removed items
- Compares `original_subtotal` vs `adjusted_subtotal`
- Checks for removed items or quantity changes

**Soft Deletes:**
- Items are not hard deleted; use `is_removed` flag with `removed_at` timestamp

**Recalculation:**
- `recalculateAdjustedSubtotal()` method sums `(current_quantity × unit_price)` for active items
- Creates `OrderHistory` entry for `subtotal_recalculated`

### Pricing Logic:

| Field | Description |
|-------|-------------|
| `original_subtotal` | What customer ordered (never changes) |
| `adjusted_subtotal` | Sum of (current_quantity × unit_price) for active items |
| `shipping_cost` | Optional shipping fee added by merchant |
| `total` | `adjusted_subtotal + shipping_cost` |

### Audit Trail (`App\Models\OrderHistory`)

Every change creates an `OrderHistory` record with:
- `order_id`, `changed_by` (user ID)
- `action` (machine-readable action type)
- `old_values`, `new_values` (JSON arrays)
- `notes` (optional merchant notes)
- `created_at` timestamp

**Action Types:**
- `order_created` - Customer placed order
- `order_accepted` - Merchant accepted (pending → confirmed)
- `order_paid` - Merchant marked paid (confirmed → paid)
- `status_updated` - Status changed manually
- `item_quantity_updated` - Item quantity modified
- `item_removed` - Item soft-deleted
- `item_restored` - Item restored from removal
- `shipping_added` / `shipping_updated` - Shipping cost changes
- `payment_notes_added` / `payment_notes_updated` - Payment notes
- `receipt_uploaded` - Shipping receipt file upload
- `adjustment_notes_added` - Adjustment notes
- `subtotal_recalculated` - Auto-recalculation after item changes

---

## 6. Database Structure

### Orders Table (`orders`)

| Column | Type | Description |
|--------|------|-------------|
| `tenant_id` | FK | Links to tenant |
| `order_number` | string | Auto-generated (ORD-YYYYMMDD-XXXX) |
| `customer_name` | string | Customer's full name |
| `customer_phone` | string | Customer's phone |
| `customer_address` | text (nullable) | Customer's address |
| `status` | enum | pending / confirmed / paid |
| `payment_status` | string | Payment status |
| `original_subtotal` | decimal | Original order total (never changes) |
| `adjusted_subtotal` | decimal | Current total after modifications |
| `shipping_cost` | decimal | Shipping fee |
| `total` | decimal | Final total (adjusted + shipping) |
| `payment_notes` | text (nullable) | Notes about payment |
| `shipping_receipt` | string (nullable) | Path to uploaded receipt |
| `adjustment_notes` | text (nullable) | Notes about order adjustments |

### Order Items Table (`order_items`)

| Column | Type | Description |
|--------|------|-------------|
| `order_id` | FK | Links to order |
| `product_id` | FK | Links to product (nullable if deleted) |
| `product_name` | string | Snapshot of product name |
| `product_sku` | string (nullable) | Product SKU |
| `original_quantity` | integer | Quantity customer ordered |
| `current_quantity` | integer | Current quantity (can be modified) |
| `unit_price` | decimal | Price per unit |
| `currency` | string | Currency code (default: IDR) |
| `is_removed` | boolean | Soft delete flag |
| `removed_at` | datetime (nullable) | When item was removed |

### Order Histories Table (`order_histories`)

| Column | Type | Description |
|--------|------|-------------|
| `order_id` | FK | Links to order |
| `changed_by` | FK (nullable) | User who made the change |
| `action` | string | Machine-readable action type |
| `old_values` | JSON | Previous values |
| `new_values` | JSON | New values |
| `notes` | text (nullable) | Merchant notes |
| `created_at` | datetime | When change occurred |

---

## 7. Route Organization

### Public Routes (Customer-facing)
```
POST   /{store_link}/orders              → Create order (checkout)
GET    /receipt/{order_number}           → View receipt
```

### Dashboard Routes (Merchant-facing, requires auth + tenant middleware)
```
GET    /dashboard/orders                              → List orders
GET    /dashboard/orders/{id}                         → Show order details
PATCH  /dashboard/orders/{id}                         → Update order
PUT    /dashboard/orders/{id}/accept                  → Accept order
PUT    /dashboard/orders/{id}/mark-paid               → Mark as paid
PATCH  /dashboard/orders/{orderId}/items/{itemId}/quantity  → Update item qty
DELETE /dashboard/orders/{orderId}/items/{itemId}          → Remove item
POST   /dashboard/orders/{orderId}/items/{itemId}/restore → Restore item
```

---

## 8. Key Files Reference

### Backend - Models
- `app/Models/Order.php` - Main order model with relationships, scopes, status colors
- `app/Models/OrderItem.php` - Order item with soft delete and quantity management
- `app/Models/OrderHistory.php` - Audit trail with action labels and change summaries

### Backend - Controllers
- `app/Http/Controllers/OrderController.php` - Public order creation (storefront checkout)
- `app/Http/Controllers/Dashboard/OrderController.php` - Dashboard order management (list, show, update, accept, markPaid)
- `app/Http/Controllers/Dashboard/OrderItemController.php` - Order item operations (updateQuantity, destroy, restore)
- `app/Http/Controllers/ReceiptController.php` - Receipt display

### Backend - Form Requests
- `app/Http/Requests/StoreOrderRequest.php` - Validation for creating orders
- `app/Http/Requests/UpdateOrderRequest.php` - Validation for updating orders
- `app/Http/Requests/UpdateOrderItemQuantityRequest.php` - Validation for quantity updates

### Frontend - Vue Components
- `resources/js/Components/orders/OrderManagementForm.vue` - Order management form
- `resources/js/Components/orders/OrderCard.vue` - Order summary card
- `resources/js/Components/orders/OrderHistory.vue` - Order history/audit trail display
- `resources/js/Components/orders/OrderHeader.vue` - Order header component
- `resources/js/Components/orders/OrderItemRow.vue` - Individual order item row
- `resources/js/Components/orders/OrderItems.vue` - Order items list

### Frontend - Composables
- `resources/js/Composables/useOrderForm.js` - Order form logic
- `resources/js/Composables/useOrderItems.js` - Order item management
- `resources/js/Composables/useWhatsAppOrder.js` - WhatsApp integration

---

## 9. Tenant Relationship

Orders belong to tenants via `tenant_id` foreign key.  
**Tenant Resolution:**
- Dashboard: `Tenant::where('email', Auth::user()->email)->first()`
- Storefront: Route middleware `ResolveTenant` resolves by `{store_link}` URL segment

**Tenant Access Control:**
- All dashboard queries scoped to current tenant's orders
- Merchants can only see and manage their own orders

---

*Last updated: 2026-04-05*
