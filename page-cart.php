<?php
/**
 * Template Name: Cart Page
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Start session if not already started
if (!session_id()) {
    session_start();
}

// Create nonce for cart actions
$cart_action_nonce = wp_create_nonce('cart_action_nonce');
error_log('Cart Page Nonce Created: ' . $cart_action_nonce); // DEBUG
$_SESSION['cart_page_nonce_debug'] = $cart_action_nonce; // Store it in session for comparison // DEBUG

// Handle remove item action from GET request
if (isset($_GET['action']) && $_GET['action'] === 'remove_cart_item' && isset($_GET['item_key'])) {
    if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'cart_action_nonce')) {
        $item_key_to_remove = sanitize_text_field(wp_unslash($_GET['item_key']));
        if (isset($_SESSION['cart'][$item_key_to_remove])) {
            unset($_SESSION['cart'][$item_key_to_remove]);
            // Redirect to the cart page without query parameters to prevent re-removal on refresh
            wp_redirect(home_url('/cart/'));
            exit;
        }
    } else {
        // Nonce verification failed or nonce not set
        wp_die('Security check failed.');
    }
}

$cart = $_SESSION['cart'] ?? [];

// Filter out subscription products for display on the cart page
$filtered_cart = [];
$subscriptions_removed = false;
if (function_exists('get_field')) { // Ensure ACF function is available
    foreach ($cart as $item_key => $item) {
        if (isset($item['product_id'])) {
            $product_type = get_field('product_type', $item['product_id']);
            if ($product_type !== 'subscription') {
                $filtered_cart[$item_key] = $item;
            } else {
                $subscriptions_removed = true;
            }
        }
    }
} else {
    // Fallback if get_field is not available, though this is unlikely if ACF is active
    $filtered_cart = $cart; 
}
$cart = $filtered_cart; // Replace original cart with filtered cart for page rendering

// $cart_update_nonce = wp_create_nonce('cart_update_nonce'); // Replaced by $cart_action_nonce
$total_price = 0;

get_header();
?>

<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<style>
body {    
    background-image: url(https://thepipedpeony.com/wp-content/uploads/2023/04/header-supply.svg);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.product-text {
    font-size: 16px;
}
    .cart-item-image {
        width: 100px;
        height: auto;
        margin-right: 1rem;
        @apply sm:w-[150px];
    }
    .quantity-input {
        width: 60px !important;
        text-align: center;
        border: 1px solid #d1d5db; /* gray-300 */
        padding: 0.25rem 0.5rem;
        @apply sm:w-[70px] !important;
    }
    .remove-button {
        background-color: transparent;
        border: none;
        color: #ef4444; /* red-500 */
        cursor: pointer;
        padding: 0.25rem;
    }
    .remove-button:hover {
        color: #b91c1c; /* red-700 */
    }
    .update-cart-button {
        background-color: #3b82f6; /* blue-500 */
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
    }
    .update-cart-button:hover {
        background-color: #2563eb; /* blue-600 */
    }
    .checkout-button {
        background-color: #10b981; /* green-500 */
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 1.1rem;
    }
    .checkout-button:hover {
        background-color: #059669; /* green-600 */
    }
    .cart-container {
        max-width: 1040px;
        margin: 2rem auto;
        padding: 4rem;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        @apply sm:p-8 md:p-16 md:my-16;
    }

    .empty-cart-message {
        text-align: center;
        padding: 2rem;
        color: #6b7280; /* gray-500 */
    }
    table tbody>tr:nth-child(odd)>td, table tbody>tr:nth-child(odd)>th {
    background-color: transparent;
}


tr.block.relative.md\:table-row.bg-transparent.rounded-lg.p-4.mb-4.shadow-lg.md\:shadow-none.md\:rounded-none.md\:p-0.md\:border-b.md\:static {
    border-left: 0.5px solid #cbc8c8;
}

table tbody>tr:nth-child(odd)>td, table tbody>tr:nth-child(odd)>th {
    background-color: transparent;
    border: 1px solid #bcbcbc;
}

table tbody>tr:nth-child(even)>td, table tbody>tr:nth-child(even)>th {
    background-color: transparent;
    border: 1px solid #bcbcbc;
}
@media (max-width: 640px) {
    .cart-price {
    margin-top: 16px;
    margin-bottom: 5px;
}
.cart-total {
    display: none;
}

.cart-remove {
    border: none;
    height: 1px !important;
    padding: 0px;
}

tr.block.relative.md\:table-row.bg-transparent.rounded-lg.p-4.mb-4.shadow-lg.md\:shadow-none.md\:rounded-none.md\:p-0.md\:border-b.md\:static {
    border-left: none;
}
table tbody>tr:nth-child(odd)>td, table tbody>tr:nth-child(odd)>th {
    background-color: transparent;
    border: none;
}
}
</style>
<h1 class="text-2xl sm:text-3xl font-bold text-center mb-[16px] mt-8 sm:mt-16">Your Cart</h1>

<div class="cart-container main-content-bg">
    <?php 
    if ($subscriptions_removed) {
        echo '<div class="woocommerce-info p-4 mb-4 bg-blue-100 border border-blue-200 text-blue-700 rounded-md">Subscription products have been removed from this cart. They need to be purchased separately through the signup page.</div>';
    }
    ?>
    <?php if (!empty($cart)) : ?>
        <form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="update-cart-form">
            <input type="hidden" name="action" value="update_cart_quantities_custom">
            <input type="hidden" name="cart_nonce" value="<?php echo esc_attr($cart_action_nonce); // Use the new nonce ?>">

            <table class="w-full mb-6 border-collapse">
                <thead class="hidden md:table-header-group">
                    <tr class="border-b">
                        <th class="text-left p-2" colspan="2">Product</th>
                        <th class="text-left p-2 cart-price md:w-28">Price</th>
                        <th class="text-center p-2 md:w-24 ">Quantity</th>
                        <th class="text-right p-2 md:w-28 ">Total</th>
                        <th class="p-2 md:w-12 "></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $key_index = 0; // Index for form input names
                    foreach ($cart as $item_key => $item) :
                        $product_id = $item['product_id'];
                        $item_name = $item['name'] ?? 'Product Name Unavailable';
                        $item_price = $item['price'] ?? 0;
                        $item_image_url = $item['image_url'] ?? '';
                        $line_total = $item_price * $item['quantity'];
                        $total_price += $line_total;
                    ?>
                        <tr class="block relative md:table-row bg-transparent rounded-lg p-4 mb-4 shadow-lg md:shadow-none md:rounded-none md:p-0 md:border-b md:static">
                            <td class="block md:table-cell p-0 md:p-2 border-0" colspan="2">
                                <div class="flex items-center">
                                    <?php if (!empty($item_image_url)) : ?>
                                        <img src="<?php echo esc_url($item_image_url); ?>" alt="<?php echo esc_attr($item_name); ?>" class="cart-item-image">
                                    <?php else: ?>
                                        <div class="cart-item-image flex-shrink-0" style="width: 100px; height: 100px; background-color: #f0f0f0; display:flex; align-items:center; justify-content:center; color: #ccc;">No Image</div>
                                    <?php endif; ?>
                                    <span class="product-text font-medium ml-2 md:ml-0"><?php echo esc_html($item_name); ?></span>
                                </div>
                            </td>
                            <td class="block md:table-cell text-left md:text-left cart-price product-text p-0 md:p-2 border-0" data-label="Price: ">
                                <span class="md:hidden font-semibold ">Price: </span>$<?php echo esc_html(number_format($item_price, 2)); ?>
                            </td>
                            <td class="block md:table-cell md:text-center p-0 cart-quantity md:p-2 border-0" data-label="Quantity: ">
                                <span class="md:hidden font-semibold ">Quantity: </span>
                                <input type="number" name="cart_item_quantities[<?php echo $key_index; ?>]" value="<?php echo esc_attr($item['quantity']); ?>" min="1" class="quantity-input inline-block ml-2 md:ml-0">
                            </td>
                            <td class="block md:table-cell text-left md:text-right cart-total font-medium p-0 md:p-2 border-0" data-label="Total: ">
                                <span class="md:hidden font-semibold ">Total: </span>$<?php echo esc_html(number_format($line_total, 2)); ?>
                            </td>
                            <td class="block md:table-cell md:text-center md:align-middle md:p-2 cart-remove">
                                <?php
                                $remove_link_url = add_query_arg([
                                    'action' => 'remove_cart_item',
                                    'item_key' => $item_key,
                                    '_wpnonce' => $cart_action_nonce
                                ], home_url('/cart/'));
                                ?>
                                <a href="<?php echo esc_url($remove_link_url); ?>" class="remove-button absolute top-2 right-2 md:static" title="Remove item">
                                    <i class="fas fa-times"></i>
                                </a>
                            </td>
                        </tr>
                    <?php
                        $key_index++; // Increment index for the next item
                    endforeach;
                    ?>
                </tbody>
            </table>

            <div class="flex flex-col md:flex-row justify-between items-center mb-12">
                <div class="mb-4 md:mb-0">
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>" class="text-blue-500 hover:underline">Continue Shopping</a>
                </div>
                <button type="submit" class="update-cart-button w-full md:w-auto">Update Cart</button>
            </div>

        </form> <!-- End Update Cart Form -->

        <div class="text-center md:text-right">
            <h3 class="text-xl sm:text-2xl font-semibold mb-4">Cart Total: $<?php echo esc_html(number_format($total_price, 2)); ?></h3>
            <button type="button" id="proceed-to-stripe-checkout-button" class="checkout-button w-full md:w-auto">
                Proceed to Checkout
            </button>
        </div>

    <?php else : ?>
        <p class="empty-cart-message">Your cart is currently empty.</p>
         <div class="text-center mt-4">
            <a href="<?php echo home_url('/shop'); ?>" class="text-blue-500 hover:underline">Continue Shopping</a>
        </div>
    <?php endif; ?>

</div>

<script>
console.log('Cart page script block is being parsed.'); // DEBUG LINE 1

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired on cart page.'); // DEBUG LINE 2
    const cartContainer = document.querySelector('.cart-container'); // Get the main cart container

    if (cartContainer) {
     
    }

    // AJAX for Proceed to Stripe Checkout
    const proceedToStripeButton = document.getElementById('proceed-to-stripe-checkout-button');
    if (proceedToStripeButton) {
        proceedToStripeButton.addEventListener('click', function() {
            const thisButton = this;
            thisButton.disabled = true;
            thisButton.innerHTML = 'Processing...';

            // Create a new nonce for this action if needed, or reuse one if appropriate and secure.
            // For simplicity, we'll reuse the cart_action_nonce for now, but ideally, create a specific one.
            const nonce = "<?php echo esc_js($cart_action_nonce); ?>"; 

            const formData = new FormData();
            formData.append('action', 'cart_to_stripe_checkout');
            formData.append('cart_nonce', nonce); // Or your specific nonce for this action

            fetch("<?php echo esc_js(admin_url('admin-ajax.php')); ?>", {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                thisButton.disabled = false;
                thisButton.innerHTML = 'Proceed to Checkout';
                if (data.success && data.data.url) {
                    window.location.href = data.data.url;
                } else {
                    alert('Error: ' + (data.data && data.data.message ? data.data.message : 'Could not proceed to Stripe checkout. Please try again.'));
                }
            })
            .catch(error => {
                thisButton.disabled = false;
                thisButton.innerHTML = 'Proceed to Checkout';
                console.error('AJAX Error:', error);
                alert('An unexpected error occurred. Please check the console and try again.');
            });
        });
    }
});
</script>

<?php
get_footer();
?>
