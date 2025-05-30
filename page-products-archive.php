<?php
/**
 * Template Name: Products Archive
 */

// Ensure WordPress environment is loaded
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}



get_header(); 
?>

<!-- Move CSS to proper enqueue in functions.php instead of inline -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<div class="container p-archive-header padder px-10 text-center">
   
    <img src="https://thepipedpeony.com/wp-content/uploads/2023/10/shop-welcome.png" alt="<?php esc_attr_e('Products Archive Header', 'your-theme-text-domain'); ?>">
    <span>
        <?php esc_html_e('Watch our academy\'s videos to stay up-to-date on the latest techniques, trends, and piping recipes.', 'your-theme-text-domain'); ?>
    </span>
</div>

<div class="container mx-auto px-4 pt-8 pb-24">
    <?php
    // Sanitize and validate pagination input
    $paged = absint(get_query_var('paged')) ?: 1;

    // Add nonce for any forms or actions
    $nonce = wp_create_nonce('product_archive_nonce');

    // Query for products with security measures
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'paged' => $paged,
        'post_status' => 'publish', // Only published posts
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => false,
        'update_post_term_cache' => false, // Performance optimization
        'update_post_meta_cache' => true
    );

    // Add rate limiting for expensive queries
    $rate_limit_key = 'product_query_' . date('Y-m-d-H');
    $rate_limit = get_transient($rate_limit_key);
    
    if ($rate_limit && $rate_limit > 100) {
        wp_die(__('Too many requests. Please try again later.', 'your-theme-text-domain'));
    }

    set_transient($rate_limit_key, ($rate_limit ? $rate_limit + 1 : 1), HOUR_IN_SECONDS);

    $product_query = new WP_Query($args);

    if ($product_query->have_posts()) : ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-20">
            <?php 
            while ($product_query->have_posts()) : $product_query->the_post(); 
                // Sanitize product data
                $product_id = get_the_ID();
                $product_url = wp_nonce_url(get_permalink(), 'view_product_' . $product_id);
                $product_type = esc_attr(get_field('product_type'));
                ?>
                <div class="bg-white product-box overflow-hidden <?php echo esc_html($product_id); ?> flex flex-col justify-between">
                  
                    <div class="product-thumbnail">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php echo esc_url($product_url); ?>">
                                <?php the_post_thumbnail('medium', [
                                    'class' => 'w-full h-48 object-cover',
                                    'alt' => esc_attr(get_the_title())
                                ]); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex-grow text-center">
                        <h2 class="woocommerce-loop-product__title">
                            <a href="<?php echo esc_url($product_url); ?>" class="hover:underline">
                                <?php echo esc_html(get_the_title()); ?>
                            </a>
                        </h2>
                        <div class="product-price mt-4">
                            <?php
                            // Sanitize and format prices
                            switch ($product_type) {
                                case 'single':
                                    $price = floatval(get_field('product_price'));
                                    echo esc_html(sprintf('$%.2f', $price));
                                    break;
                                case 'subscription':
                                    $price = floatval(get_field('subscription_price'));
                                    $length = esc_html(get_field('subscription_length'));
                                    echo esc_html(sprintf('$%.2f / %s', $price, $length));
                                    break;
                                case 'variation':
                                    if (have_rows('variation_product')) {
                                        the_row();
                                        $price = floatval(get_sub_field('variable_product_price'));
                                        echo esc_html(sprintf('$%.2f', $price));
                                    }
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                    <div class="p-4 flex justify-center">
                        <a href="<?php echo esc_url($product_url); ?>" class="options-button">
                            <?php esc_html_e('Select Options', 'your-theme-text-domain'); ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php
        // Secure pagination
        echo wp_kses_post(paginate_links(array(
            'total' => $product_query->max_num_pages,
            'current' => $paged,
            'mid_size' => 2,
            'prev_text' => esc_html__('« Previous', 'your-theme-text-domain'),
            'next_text' => esc_html__('Next »', 'your-theme-text-domain'),
            'class' => 'flex justify-center'
        )));
        ?>

    <?php else : ?>
        <p class="text-center text-gray-500">
            <?php esc_html_e('No products found.', 'your-theme-text-domain'); ?>
        </p>
    <?php 
    endif;
    wp_reset_postdata();
    ?>
</div>

<style>

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.bg-white.product-box.overflow-hidden.\34 1936.flex.flex-col.justify-between {
    display: none;
}

.md\:h-60-custom {
    height: 34rem;
}

.object-cover {
    object-fit: cover;
    height: 149px;
}

.container.p-archive-header.padder {
    display: flex;
    flex-direction: column;
    align-content: center;
    align-items: center;
}
h2.woocommerce-loop-product__title {
    font-size: 25px !important;
    text-align: center;
    line-height: 1.3 !important;
    margin: auto;
    font-family: "Playfair Display", Sans-serif !important;
}
.options-button {
    color: #000000;
    background-color: #FFFFFF;
    font-size: 16px;
    font-weight: 100;
    text-transform: lowercase;
    border-style: solid;
    border-width: 1px 1px 1px 1px;
    border-radius: 0px 0px 0px 0px;
    padding: 5px 10px;
    border: 1px solid #000000;
}
.options-button:hover { 
color: #FFFFFF;
    background-color: #000000;
}

h2.woocommerce-loop-product__title a {
    font-family: "Playfair Display", Sans-serif !important;
}

h2.woocommerce-loop-product__title a:hover {
    text-decoration: none !important;
    color: gray;
}

.product-thumbnail {
    padding: 7px;
}

.product-price {
    font-weight: normal;
    font-family: 'Sofia Pro Regular';
    font-size: 16px;
    color: black;
}
.product-box {
    position: relative;
    overflow: inherit;
    border: 1px solid;
}
.product-box:after {
    content: '';
    border: 1px solid;
    height: 100%;
    width: 100%;
    position: absolute;
    left: 12px;
    top: 11px;
    z-index: -1;
}
</style>

<?php
// Move styles to proper stylesheet
get_footer();