import { ref } from 'vue';

/**
 * useCheckout Composable
 * 
 * Handles checkout flow: order creation, WhatsApp message generation, and success state.
 * Shareable across any storefront page that needs checkout functionality.
 */
export function useCheckout(tenant, formatCurrency, onCheckoutSuccess = null) {
    const showCheckoutForm = ref(false);
    const showSuccessModal = ref(false);
    const lastOrderNumber = ref(null);
    const lastReceiptUrl = ref(null);

    /**
     * Generate WhatsApp message with order details
     */
    function generateWhatsAppMessage(cartItems, tenant, customerName, customerPhone, customerAddress, receiptUrl) {
        let subtotal = 0;
        const currency = cartItems[0]?.currency || 'IDR';

        const lineItems = cartItems.map(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            return `• ${item.title} x${item.quantity}
  ${formatCurrency(item.price, item.currency)} = ${formatCurrency(itemTotal, item.currency)}`;
        }).join('\n\n');

        const message = `*NEW ORDER*

*Order Details:*
${lineItems}

*Subtotal:* ${formatCurrency(subtotal, currency)}

*Customer Information:*
Name: ${customerName}
Phone: ${customerPhone}
Address: ${customerAddress || '-'}

Ongkos kirim dan total akhir akan dihitung setelah admin menghubungi Anda.
Silahkan pantau Status orderan dan pengiriman Anda melalui link berikut: ${receiptUrl}

Sent from ${tenant.name}`;

        return message;
    }

    /**
     * Handle checkout flow: save order to database, then redirect to WhatsApp
     */
    async function handleCheckout({ customer_name, customer_phone, customer_address, cartItems }) {
        try {
            // 1. Get tenant info
            const storeLink = tenant.value?.store_link;
            if (!storeLink) {
                throw new Error('Store link not available');
            }

            // 2. Save order to database
            const orderUrl = window.location.origin + '/' + storeLink + '/orders';
            const response = await fetch(orderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    customer_name,
                    customer_phone,
                    customer_address,
                    items: cartItems.map(item => ({
                        product_id: item.id,
                        product_name: item.title,
                        product_sku: item.sku || null,
                        quantity: item.quantity,
                        unit_price: item.price,
                        currency: item.currency || 'IDR',
                    })),
                }),
            });

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to create order');
            }

            // 3. Get WhatsApp number
            const waNumber = tenant.value.formatted_whatsapp_number ||
                            (tenant.value.phone ? tenant.value.phone.replace(/[^0-9]/g, '') : null);

            if (!waNumber) {
                alert('Tenant WhatsApp number not configured.');
                throw new Error('WhatsApp number not configured');
            }

            // 4. Build message WITH receipt URL
            const receiptUrl = result.receipt_url;
            const message = generateWhatsAppMessage(cartItems, tenant.value, customer_name, customer_phone, customer_address, receiptUrl);

            // 5. Build wa.me URL
            const waLink = `https://wa.me/${waNumber}?text=${encodeURIComponent(message)}`;

            // 6. Close checkout form
            showCheckoutForm.value = false;

            // 7. Store receipt info
            lastOrderNumber.value = result.order_number;
            lastReceiptUrl.value = receiptUrl;

            // 8. Clear cart
            if (onCheckoutSuccess) {
                onCheckoutSuccess();
            }

            // 9. Show success modal briefly, then redirect
            showSuccessModal.value = true;
            setTimeout(() => {
                window.location.href = waLink;
            }, 1500);

        } catch (error) {
            console.error('Checkout error:', error);
            throw error;
        }
    }

    function openCheckout() {
        showCheckoutForm.value = true;
    }

    function closeSuccessModal() {
        showSuccessModal.value = false;
        lastOrderNumber.value = null;
        lastReceiptUrl.value = null;
    }

    return {
        // State
        showCheckoutForm,
        showSuccessModal,
        lastOrderNumber,
        lastReceiptUrl,
        // Actions
        handleCheckout,
        generateWhatsAppMessage,
        openCheckout,
        closeSuccessModal,
    };
}
