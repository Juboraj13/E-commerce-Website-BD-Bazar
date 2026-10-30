// Common JavaScript functions for BazarBD website

// Cart functionality
let cart = JSON.parse(localStorage.getItem('bazarbd_cart')) || [];
let cartCount = 0;

// Update cart count display
function updateCartCount() {
    cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = cartCount;
    }
}

// Add item to cart
function addToCart(productTitle, productPrice, productImage) {
    const existingItem = cart.find(item => item.title === productTitle);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            title: productTitle,
            price: productPrice,
            image: productImage,
            quantity: 1,
            id: Date.now()
        });
    }
    
    localStorage.setItem('bazarbd_cart', JSON.stringify(cart));
    updateCartCount();
    showNotification(`${productTitle} added to cart!`, 'success');
}

// Remove item from cart
function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    localStorage.setItem('bazarbd_cart', JSON.stringify(cart));
    updateCartCount();
    showNotification('Item removed from cart!', 'info');
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Search functionality
function initializeSearch() {
    const searchBox = document.querySelector('.search-box');
    const searchBtn = document.querySelector('.search-btn');
    
    if (searchBox && searchBtn) {
        searchBtn.addEventListener('click', performSearch);
        searchBox.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
}

function performSearch() {
    const searchBox = document.querySelector('.search-box');
    const query = searchBox.value.trim().toLowerCase();
    
    if (!query) {
        showNotification('Please enter a search term', 'error');
        return;
    }
    
    const products = document.querySelectorAll('.product');
    let foundCount = 0;
    
    products.forEach(product => {
        const title = product.querySelector('.product-title').textContent.toLowerCase();
        const price = product.querySelector('.product-price').textContent.toLowerCase();
        
        if (title.includes(query) || price.includes(query)) {
            product.style.display = 'block';
            foundCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    if (foundCount === 0) {
        showNotification(`No products found for "${query}"`, 'info');
    } else {
        showNotification(`Found ${foundCount} product(s)`, 'success');
    }
}

// Filter products by price range
function filterByPrice(minPrice, maxPrice) {
    const products = document.querySelectorAll('.product');
    let foundCount = 0;
    
    products.forEach(product => {
        const priceText = product.querySelector('.product-price').textContent;
        const price = parseFloat(priceText.replace(/[^\d.]/g, '')) || 0;
        
        if (price >= minPrice && price <= maxPrice) {
            product.style.display = 'block';
            foundCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    showNotification(`Showing ${foundCount} products in price range`, 'success');
}

// Sort products
function sortProducts(criteria) {
    const productsContainer = document.querySelector('.all-products');
    if (!productsContainer) return;
    
    const products = Array.from(document.querySelectorAll('.product'));
    
    products.sort((a, b) => {
        if (criteria === 'name') {
            const nameA = a.querySelector('.product-title').textContent.toLowerCase();
            const nameB = b.querySelector('.product-title').textContent.toLowerCase();
            return nameA.localeCompare(nameB);
        } else if (criteria === 'price-low') {
            const priceA = parseFloat(a.querySelector('.product-price').textContent.replace(/[^\d.]/g, '')) || 0;
            const priceB = parseFloat(b.querySelector('.product-price').textContent.replace(/[^\d.]/g, '')) || 0;
            return priceA - priceB;
        } else if (criteria === 'price-high') {
            const priceA = parseFloat(a.querySelector('.product-price').textContent.replace(/[^\d.]/g, '')) || 0;
            const priceB = parseFloat(b.querySelector('.product-price').textContent.replace(/[^\d.]/g, '')) || 0;
            return priceB - priceA;
        }
    });
    
    // Clear container and re-append sorted products
    productsContainer.innerHTML = '';
    products.forEach(product => productsContainer.appendChild(product));
    
    showNotification(`Products sorted by ${criteria}`, 'success');
}

// Initialize buy now buttons
function initializeBuyNowButtons() {
    const buyButtons = document.querySelectorAll('.product-btn');
    
    buyButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const product = this.closest('.product');
            const title = product.querySelector('.product-title').textContent.trim();
            const price = product.querySelector('.product-price').textContent.trim();
            const image = product.querySelector('img').src;
            
            // Check if product is out of stock
            if (price.toLowerCase().includes('out of stock')) {
                showNotification('Sorry, this product is out of stock!', 'error');
                return;
            }
            
            addToCart(title, price, image);
        });
    });
}

// Initialize mobile menu toggle
function initializeMobileMenu() {
    // Add mobile menu button if it doesn't exist
    const header = document.querySelector('.header-main .container');
    if (header && !document.querySelector('.mobile-menu-btn')) {
        const mobileBtn = document.createElement('button');
        mobileBtn.className = 'mobile-menu-btn';
        mobileBtn.innerHTML = '☰';
        mobileBtn.style.cssText = `
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #333;
        `;
        
        header.appendChild(mobileBtn);
        
        mobileBtn.addEventListener('click', toggleMobileMenu);
    }
}

function toggleMobileMenu() {
    const headerActions = document.querySelector('.header-actions');
    const searchContainer = document.querySelector('.search-container');
    
    if (headerActions && searchContainer) {
        const isVisible = headerActions.style.display === 'flex';
        headerActions.style.display = isVisible ? 'none' : 'flex';
        searchContainer.style.display = isVisible ? 'none' : 'flex';
    }
}

// Add CSS animations
function addAnimations() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .product {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .product:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .product-btn {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        
        .product-btn:hover {
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block !important;
            }
            
            .header-actions,
            .search-container {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                flex-direction: column;
                gap: 10px;
            }
        }
    `;
    document.head.appendChild(style);
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    initializeSearch();
    initializeBuyNowButtons();
    initializeMobileMenu();
    addAnimations();
    
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Export functions for use in other scripts
window.BazarBD = {
    addToCart,
    removeFromCart,
    updateCartCount,
    showNotification,
    sortProducts,
    filterByPrice,
    performSearch
};