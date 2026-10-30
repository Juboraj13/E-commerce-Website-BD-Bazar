/**
 * cart.js — Asynchronous Cart Management via Fetch API
 * Handles adding, updating, removing items and badge synchronization.
 */

/**
 * Add product to cart via Fetch
 */
async function addToCart(productId, quantity = 1) {
    try {
        const response = await fetch(`${BASE_URL}/backend/routes/actions/add_to_cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
            },
            body: JSON.stringify({ product_id: parseInt(productId), quantity: parseInt(quantity) })
        });

        const data = await response.json();

        if (data.success) {
            updateCartBadge(data.cartCount);
            showToast(data.message || 'Added to cart!', 'success');
        } else {
            showToast(data.message || 'Could not add to cart.', 'error');
        }
    } catch (err) {
        console.error('Add to cart error:', err);
        showToast('Network error while adding to cart.', 'error');
    }
}

/**
 * Update quantity of an item in the cart
 */
async function updateCartQuantity(productId, quantity, onComplete) {
    try {
        const response = await fetch(`${BASE_URL}/backend/routes/actions/update_cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
            },
            body: JSON.stringify({ product_id: parseInt(productId), quantity: parseInt(quantity) })
        });

        const data = await response.json();

        if (data.success) {
            updateCartBadge(data.cartCount);
            if (typeof onComplete === 'function') {
                onComplete(data);
            }
        } else {
            showToast(data.message || 'Could not update quantity.', 'error');
            if (typeof onComplete === 'function') {
                onComplete(data);
            }
        }
    } catch (err) {
        console.error('Update cart error:', err);
        showToast('Network error while updating cart.', 'error');
    }
}

/**
 * Remove an item from the cart
 */
async function removeCartItem(productId, onComplete) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}/backend/routes/actions/remove_from_cart.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': typeof CSRF_TOKEN !== 'undefined' ? CSRF_TOKEN : ''
            },
            body: JSON.stringify({ product_id: parseInt(productId) })
        });

        const data = await response.json();

        if (data.success) {
            updateCartBadge(data.cartCount);
            showToast(data.message || 'Item removed.', 'info');
            if (typeof onComplete === 'function') {
                onComplete(data);
            }
        } else {
            showToast(data.message || 'Could not remove item.', 'error');
        }
    } catch (err) {
        console.error('Remove cart item error:', err);
        showToast('Network error while removing item.', 'error');
    }
}

/**
 * Update Header Cart Badge
 */
function updateCartBadge(count) {
    const badge = document.getElementById('cartBadgeCount');
    if (badge) {
        badge.textContent = count;
        badge.style.transform = 'scale(1.3)';
        setTimeout(() => {
            badge.style.transform = 'scale(1)';
        }, 200);
    }
}

// Bind click listener for all .add-to-cart-btn buttons across the site
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = btn.getAttribute('data-product-id');
            if (productId) {
                addToCart(productId, 1);
            }
        });
    });
});

