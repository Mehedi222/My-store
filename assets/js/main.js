// main.js - Core JavaScript and UI State Management

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initActiveNav();
    updateCartBadges();
    initCheckoutSim();
});

// ==========================================
// 1. Theme Management (Light / Dark)
// ==========================================
function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);

    const themeToggleBtns = document.querySelectorAll('.theme-toggle-btn');
    themeToggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    });
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Update theme toggle icons
    const icons = document.querySelectorAll('.theme-toggle-btn i');
    icons.forEach(icon => {
        if (theme === 'dark') {
            icon.className = 'fas fa-sun text-warning';
        } else {
            icon.className = 'fas fa-moon text-primary';
        }
    });
}

// ==========================================
// 2. Active Route Navigation Sync
// ==========================================
function initActiveNav() {
    const currentUrl = window.location.pathname;
    const pageName = currentUrl.substring(currentUrl.lastIndexOf('/') + 1) || 'index.php';
    
    // Bottom Nav (Mobile)
    const bottomNavItems = document.querySelectorAll('.bottom-nav-item');
    bottomNavItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href === pageName || (pageName === 'index.php' && href === 'index.php') || (pageName === 'product-detail.php' && href === 'products.php')) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    // Top Nav (Desktop)
    const desktopNavLinks = document.querySelectorAll('.desktop-navbar .nav-link');
    desktopNavLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === pageName) {
            link.classList.add('active');
        }
    });
}

// ==========================================
// 3. Persistent Client-side Cart System
// ==========================================
function getCart() {
    const cartData = localStorage.getItem('yst_cart');
    return cartData ? JSON.parse(cartData) : [];
}

function saveCart(cart) {
    localStorage.setItem('yst_cart', JSON.stringify(cart));
    updateCartBadges();
}

function addToCart(id, title, price, image) {
    let cart = getCart();
    // Prevent duplicate products (since digital items are purchased once)
    const exists = cart.some(item => item.id === id);
    if (exists) {
        showToast("Product already in cart!", "warning");
        return;
    }
    
    cart.push({ id, title, price: parseFloat(price), image });
    saveCart(cart);
    showToast(`${title} added to cart!`, "success");
    
    // Sync cart to server session using fetch
    syncCartSession(cart);
}

function removeFromCart(id) {
    let cart = getCart();
    cart = cart.filter(item => item.id !== id);
    saveCart(cart);
    showToast("Product removed from cart.", "info");
    
    syncCartSession(cart);
    // Reload if on cart page to update lists
    if (window.location.pathname.includes('cart.php')) {
        window.location.reload();
    }
}

function clearCart() {
    localStorage.removeItem('yst_cart');
    updateCartBadges();
    syncCartSession([]);
}

function updateCartBadges() {
    const cart = getCart();
    const count = cart.length;
    
    const badges = document.querySelectorAll('.cart-badge');
    badges.forEach(badge => {
        badge.innerText = count;
        if (count > 0) {
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    });
}

function syncCartSession(cart) {
    fetch('cart.php?action=sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ cart })
    })
    .catch(err => console.error("Session sync error: ", err));
}

// ==========================================
// 4. Toast Notifications UI
// ==========================================
function showToast(message, type = "success") {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1090';
        document.body.appendChild(toastContainer);
    }
    
    const toastId = 'toast_' + Date.now();
    const colorClass = type === 'success' ? 'bg-success' : (type === 'warning' ? 'bg-warning text-dark' : 'bg-info');
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
    
    const toastHtml = `
      <div id="${toastId}" class="toast align-items-center text-white ${colorClass} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            <i class="fas ${iconClass} me-2"></i> ${message}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast" aria-label="Close" onclick="document.getElementById('${toastId}').remove()"></button>
        </div>
      </div>
    `;
    
    toastContainer.innerHTML += toastHtml;
    
    // Auto remove after 3.5 seconds
    setTimeout(() => {
        const toast = document.getElementById(toastId);
        if (toast) toast.remove();
    }, 3500);
}

// ==========================================
// 5. Checkout / Payment Gateway Simulator
// ==========================================
function initCheckoutSim() {
    const gatewaySelect = document.getElementById('payment_gateway');
    if (!gatewaySelect) return;
    
    gatewaySelect.addEventListener('change', (e) => {
        const val = e.target.value;
        const submitBtn = document.getElementById('place_order_btn');
        if (submitBtn) {
            submitBtn.innerText = `Pay with ${val.charAt(0).toUpperCase() + val.slice(1)}`;
        }
    });
}
