import { ref, computed } from 'vue';

/**
 * useWhatsAppOrder - WhatsApp Order Message Generator
 * 
 * Core feature: Formats cart items into a WhatsApp message
 * and generates wa.me link for one-click ordering.
 * 
 * Tradeoffs:
 * - Uses wa.me API (no WhatsApp Business API required for MVP)
 * - Message is URL-encoded for maximum compatibility
 * - Customer data is collected before redirect (for order logging)
 */

export function useWhatsAppOrder() {
    const customerName = ref('');
    const customerPhone = ref('');
    const shippingAddress = ref('');
    const notes = ref('');

    /**
     * Format a single line item for the WhatsApp message
     */
    function formatLineItem(product, quantity) {
        const itemTotal = product.price * quantity;
        return `• ${product.title} x${quantity}
  ${product.formatted_price} = ${formatCurrency(itemTotal, product.currency)}`;
    }

    /**
     * Format currency based on currency code
     */
    function formatCurrency(amount, currency = 'IDR') {
        if (currency === 'IDR') {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        }
        return `${currency} ${Number(amount).toFixed(2)}`;
    }

    /**
     * Generate the complete WhatsApp message
     */
    function generateMessage(cartItems, tenant) {
        if (!cartItems || cartItems.length === 0) {
            return '';
        }

        // Calculate totals
        let subtotal = 0;
        const currency = cartItems[0]?.currency || 'IDR';
        
        const lineItems = cartItems.map(item => {
            subtotal += item.price * item.quantity;
            return formatLineItem(item, item.quantity);
        }).join('\n\n');

        // Build message with emoji for better UX
        const message = `🛒 *NEW ORDER*

📦 *Order Details:*
${lineItems}

💰 *Subtotal:* ${formatCurrency(subtotal, currency)}

👤 *Customer Information:*
Name: ${customerName.value || '-'}
Phone: ${customerPhone.value || '-'}
Address: ${shippingAddress.value || '-'}

📝 *Notes:*
${notes.value || '-'}

---
Sent from ${tenant.name}`;

        return message;
    }

    /**
     * Generate WhatsApp wa.me link
     * Uses tenant's WhatsApp number or falls back to phone
     */
    function generateWaLink(message, tenant) {
        // Get WhatsApp number (cleaned of +, spaces, dashes)
        const waNumber = tenant.formatted_whatsapp_number || 
                        (tenant.phone ? tenant.phone.replace(/[^0-9]/g, '') : null);

        if (!waNumber) {
            throw new Error('Tenant WhatsApp number not configured');
        }

        // URL encode the message
        const encodedMessage = encodeURIComponent(message);
        
        // Generate wa.me link (universal WhatsApp API)
        return `https://wa.me/${waNumber}?text=${encodedMessage}`;
    }

    /**
     * Complete order flow: generate message → log order → redirect to WhatsApp
     */
    function sendOrder(cartItems, tenant, logOrderCallback = null) {
        const message = generateMessage(cartItems, tenant);
        const waLink = generateWaLink(message, tenant);

        // Log order before redirect (if callback provided)
        if (logOrderCallback) {
            logOrderCallback({
                product_snapshot: cartItems,
                customer_name: customerName.value,
                customer_phone: customerPhone.value,
                shipping_address: shippingAddress.value,
                message_sent_to_wa: message,
                wa_link: waLink,
            });
        }

        // Open WhatsApp in new tab
        window.open(waLink, '_blank');

        return { message, waLink };
    }

    /**
     * Reset form fields
     */
    function resetForm() {
        customerName.value = '';
        customerPhone.value = '';
        shippingAddress.value = '';
        notes.value = '';
    }

    /**
     * Validate customer information
     */
    function validateForm() {
        const errors = {};

        if (!customerName.value?.trim()) {
            errors.customerName = 'Name is required';
        }

        if (!customerPhone.value?.trim()) {
            errors.customerPhone = 'Phone number is required';
        } else if (!/^[\d+\-\s()]+$/.test(customerPhone.value)) {
            errors.customerPhone = 'Invalid phone number format';
        }

        if (!shippingAddress.value?.trim()) {
            errors.shippingAddress = 'Shipping address is required';
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors,
        };
    }

    return {
        // Form state
        customerName,
        customerPhone,
        shippingAddress,
        notes,

        // Actions
        generateMessage,
        generateWaLink,
        sendOrder,
        resetForm,
        validateForm,
        formatCurrency,

        // Computed helpers
        hasCustomerInfo: computed(() => 
            customerName.value && customerPhone.value && shippingAddress.value
        ),
    };
}
