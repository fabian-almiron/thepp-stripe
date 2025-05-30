<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<?php
/*
Template Name: Shop
*/

// Ensure WordPress environment is loaded
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



get_header();

require 'vendor/autoload.php';

// Get Stripe key from wp-config.php or environment variable
$stripe_secret_key = defined('STRIPE_SECRET_KEY') ? STRIPE_SECRET_KEY : getenv('STRIPE_SECRET_KEY');
if (empty($stripe_secret_key)) {
    wp_die('Stripe configuration error', 'Configuration Error');
}

// Set up rate limiting
$rate_limit_key = 'stripe_api_calls_' . date('Y-m-d-H');
$rate_limit = get_transient($rate_limit_key);
if ($rate_limit && $rate_limit > 100) { // Adjust limit as needed
    wp_die('Too many requests. Please try again later.', 'Rate Limit Exceeded');
}

try {
    \Stripe\Stripe::setApiKey($stripe_secret_key);
    
    // Increment rate limit counter
    set_transient($rate_limit_key, ($rate_limit ? $rate_limit + 1 : 1), HOUR_IN_SECONDS);
    
    $products = \Stripe\Product::all([
        'limit' => 10,
        'active' => true
    ]);

    if (!empty($products->data)) {
          echo 'shop page';
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
        
        foreach ($products->data as $product) {
            $product_url = wp_nonce_url(
                add_query_arg('product_id', sanitize_text_field($product->id), home_url('/single-product/')),
                'view_product_' . $product->id
            );
            
            ?>
            <div class="bg-white shadow-md rounded-lg p-6">
                <a href="<?php echo esc_url($product_url); ?>">
                    <h2 class="text-xl font-bold mb-2"><?php echo esc_html($product->name); ?></h2>
                </a>
                <p class="text-gray-700"><?php echo wp_kses_post($product->description); ?></p>
            </div>
            <?php
        }
        
        echo '</div>';
        echo '</div>';
       
    } else {
        echo '<p class="text-center text-gray-500">' . esc_html__('No products found in Stripe.', 'your-theme-text-domain') . '</p>';
    }
} catch (Exception $e) {
    error_log('Stripe Error: ' . $e->getMessage());
    echo '<p class="text-red-500">' . esc_html__('An error occurred while retrieving products. Please try again later.', 'your-theme-text-domain') . '</p>';
}

get_footer();