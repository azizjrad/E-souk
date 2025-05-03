/**
 * E-Souk Tounsi - Cart Functionality
 * Handles cart operations only
 */

// Make sure ROOT_URL is defined
if (typeof ROOT_URL === 'undefined') {
    console.error('ROOT_URL is not defined. Please define it before including cart.js');
}

/**
 * Initialize cart functionality on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    initAddToCartButtons();
    cart.ensureToastContainer();
});

// Namespace for cart functionality
const cart = {
    /**
     * Show toast notification (cart-specific implementation)
     */
    showToast: function(message, type = 'primary') {
        const container = this.ensureToastContainer();
        
        const toastId = 'cart-toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        container.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    },
    
    /**
     * Ensure toast container exists
     */
    ensureToastContainer: function() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1050';
            document.body.appendChild(container);
        }
        return container;
    }
};

/**
 * Initialize Add To Cart buttons
 */
function initAddToCartButtons() {
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        if (!btn.hasAttribute('data-cart-initialized')) {
            btn.setAttribute('data-cart-initialized', 'true');
            
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                const quantity = parseInt(this.getAttribute('data-quantity') || 1);
                
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
                this.disabled = true;
                
                addToCart(productId, quantity, this, originalText);
            });
        }
    });
}

/**
 * Add product to cart
 */
function addToCart(productId, quantity, button, originalText) {
    fetch(ROOT_URL + 'public/pages/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `product_id=${productId}&quantity=${quantity}`,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (button) {
            if (data.success) {
                button.innerHTML = '<i class="fas fa-check"></i> Ajouté!';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 1000);
            } else {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        if (data.success) {
            cart.showToast('Produit ajouté au panier', 'success');
            
            updateCartCountBadge(data.cart_count || 0);
        } else {
            cart.showToast(data.message || 'Erreur lors de l\'ajout au panier', 'danger');
        }
    })
    .catch(error => {
        console.error('Cart error:', error);
        
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
        
        cart.showToast('Une erreur s\'est produite', 'danger');
    });
}

/**
 * Update cart count badge in navbar
 */
function updateCartCountBadge(count) {
    document.querySelectorAll('.cart-count').forEach(badge => {
        badge.textContent = count.toString();
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    });
}

/**
 * Update cart quantity
 */
function updateCartQuantity(productId, quantity, element) {
    // Validate quantity
    if (quantity < 1) {
        cart.showToast('La quantité doit être au moins 1', 'warning');
        return;
    }
    
    fetch(ROOT_URL + 'public/pages/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `product_id=${productId}&quantity=${quantity}`,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update price display
            const row = element.closest('.cart-item');
            if (row) {
                const itemTotal = row.querySelector('.item-total');
                if (itemTotal) {
                    itemTotal.textContent = data.item_total + ' DT';
                }
            }
            
            // Update cart totals
            updateCartTotals(data.subtotal, data.shipping, data.total);
            
            // Update cart count
            updateCartCountBadge(data.cart_count || 0);
        } else {
            cart.showToast(data.message || 'Erreur lors de la mise à jour', 'danger');
        }
    })
    .catch(error => {
        console.error('Update cart error:', error);
        cart.showToast('Une erreur s\'est produite', 'danger');
    });
}

/**
 * Remove item from cart
 */
function removeCartItem(productId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet article?')) {
        fetch(ROOT_URL + 'public/pages/remove_from_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `product_id=${productId}`,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove row with animation
                const row = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
                if (row) {
                    row.classList.add('removing');
                    setTimeout(() => {
                        row.remove();
                        
                        // Check if cart is empty
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            const cartContent = document.querySelector('.cart-content');
                            if (cartContent) {
                                cartContent.innerHTML = getEmptyCartHTML();
                            }
                        }
                    }, 300);
                }
                
                // Update cart totals
                updateCartTotals(data.subtotal, data.shipping, data.total);
                
                // Update cart count
                updateCartCountBadge(data.cart_count || 0);
                
                cart.showToast('Article supprimé du panier', 'info');
            } else {
                cart.showToast(data.message || 'Erreur lors de la suppression', 'danger');
            }
        })
        .catch(error => {
            console.error('Remove from cart error:', error);
            cart.showToast('Une erreur s\'est produite', 'danger');
        });
    }
}

/**
 * Update cart totals display
 */
function updateCartTotals(subtotal, shipping, total) {
    const subtotalEl = document.getElementById('cart-subtotal');
    const shippingEl = document.getElementById('cart-shipping');
    const totalEl = document.getElementById('cart-total');
    
    if (subtotalEl) subtotalEl.textContent = subtotal + ' DT';
    if (shippingEl) shippingEl.textContent = shipping + ' DT';
    if (totalEl) totalEl.textContent = total + ' DT';
}

/**
 * Get empty cart HTML
 */
function getEmptyCartHTML() {
    return `
        <div class="text-center py-5 empty-cart">
            <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
            <h3>Votre panier est vide</h3>
            <p class="mb-4">Ajoutez des articles à votre panier pour commencer vos achats</p>
            <a href="${ROOT_URL}public/pages/product.php" class="btn btn-primary">Découvrir les produits</a>
        </div>
    `;
}
