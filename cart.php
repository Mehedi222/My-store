<?php
// cart.php - Shopping Cart Page
require_once 'config.php';

// Handle Client Cart Sync API endpoint
if (isset($_GET['action']) && $_GET['action'] === 'sync') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $_SESSION['cart'] = isset($data['cart']) ? $data['cart'] : [];
    echo json_encode(['status' => 'success', 'items_count' => count($_SESSION['cart'])]);
    exit;
}

require_once 'includes/header.php';
?>

<div class="row g-4 mb-4">
    <!-- Header Title -->
    <div class="col-12">
        <div class="card card-glass border-0 p-4 mb-2">
            <h2 class="fw-bold mb-1"><i class="fas fa-shopping-bag me-2 text-primary"></i>My Shopping Cart</h2>
            <p class="text-muted mb-0 small">Review the items in your order before checking out.</p>
        </div>
    </div>
    
    <!-- Left Column - Dynamic Cart Items List -->
    <div class="col-lg-8">
        <div id="cart_items_container">
            <!-- Loading Skeleton -->
            <div class="card card-glass border-0 p-5 text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mb-0">Synchronizing cart database...</p>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Order Summary Panel -->
    <div class="col-lg-4" id="summary_panel_container" style="display: none;">
        <div class="card card-glass border-0 p-4 sticky-lg-top" style="top: 90px;">
            <h4 class="fw-bold mb-4">Order Summary</h4>
            
            <div class="d-flex justify-content-between mb-3 border-bottom border-color pb-2">
                <span class="text-muted">Total Products</span>
                <span class="fw-bold" id="summary_count">0</span>
            </div>
            
            <div class="d-flex justify-content-between mb-4">
                <h5 class="fw-bold mb-0">Estimated Subtotal</h5>
                <h5 class="fw-bold text-gradient mb-0" id="summary_subtotal">$0.00</h5>
            </div>
            
            <a href="checkout.php" class="btn btn-premium w-100 py-3 mb-2"><i class="fas fa-credit-card me-2"></i>Proceed to Checkout</a>
            <a href="products.php" class="btn btn-outline-primary rounded-pill w-100 py-2.5">Continue Shopping</a>
        </div>
    </div>
</div>

<!-- Dynamic Rendering Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    renderCartPage();
});

function renderCartPage() {
    const cart = getCart();
    const container = document.getElementById('cart_items_container');
    const summaryPanel = document.getElementById('summary_panel_container');
    
    if (cart.length === 0) {
        summaryPanel.style.display = 'none';
        container.innerHTML = `
            <div class="card card-glass border-0 p-5 text-center text-muted">
                <i class="fas fa-shopping-bag fa-4x mb-4 text-primary"></i>
                <h3 class="fw-bold">Your Cart is Empty</h3>
                <p class="small mb-4 col-md-6 mx-auto">It looks like you haven't added any digital assets yet. Browse our selection of templates, boilerplate code, and UI kits to get started.</p>
                <a href="products.php" class="btn btn-premium px-5 py-3">Explore Products</a>
            </div>
        `;
        return;
    }
    
    // Calculate subtotals
    let subtotal = 0;
    let itemsHtml = '<div class="d-flex flex-column gap-3">';
    
    cart.forEach(item => {
        subtotal += item.price;
        // Format price in local script
        const formattedPrice = '$' + item.price.toFixed(2);
        
        itemsHtml += `
            <div class="card card-glass border-0 overflow-hidden" style="background: var(--card-bg);">
                <div class="card-body p-3">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <img src="${item.image}" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;" alt="${item.title}">
                        </div>
                        <div class="col">
                            <h5 class="fw-bold mb-1 text-truncate">${item.title}</h5>
                            <span class="badge bg-primary px-2.5 py-1 rounded-pill small">Instant Delivery</span>
                        </div>
                        <div class="col-auto text-end me-3">
                            <h4 class="fw-bold text-gradient mb-0">${formattedPrice}</h4>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-link text-danger p-2 hover-scale" onclick="removeFromCart(${item.id})" aria-label="Delete product">
                                <i class="fas fa-trash fa-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    itemsHtml += '</div>';
    container.innerHTML = itemsHtml;
    
    // Update summary panel
    document.getElementById('summary_count').innerText = cart.length;
    document.getElementById('summary_subtotal').innerText = '$' + subtotal.toFixed(2);
    summaryPanel.style.display = 'block';
}
</script>

<?php require_once 'includes/footer.php'; ?>
