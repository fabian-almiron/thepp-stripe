<?php
/**
 * Template Name: Single Product
 */

// Bootstrap WordPress if not already
if (!defined('ABSPATH')) {
    // For standalone usage, include WordPress bootstrap
    $wp_load_path = dirname(__FILE__);
    
    // Navigate up until wp-load.php is found
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($wp_load_path . '/wp-load.php')) {
            require_once($wp_load_path . '/wp-load.php');
            break;
        }
        $wp_load_path = dirname($wp_load_path);
    }
}

get_header();

// Start session if not already started
// Session cart is not strictly needed for direct to Stripe, but harmless if other parts of site use it.
if (!session_id()) {
    session_start();
}

// The old PHP block for handling 'add_to_cart' via POST has been removed.
// The new JavaScript AJAX logic below now handles this.

?>

<!-- Include Tailwind CSS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<style>
.variation-button.selected {
    border-color: none !important;
    background-color: black !important;
    color: white !important;
}



.thumbnail-image {
    width: 164px;
    height: 140px;
    margin: 10px;
    background-size: cover;
    background-position: center;
    border: 2px solid transparent;
    cursor: pointer;
}
.product-title {
    font-size: 23px;
    line-height: 33px;
    font-weight: 100;
}
h3 {
    font-size: 1.75rem;
    color: #515151;
    font-weight: 500;
    line-height: 1.2;
    margin-bottom: 15px;
}

li {
    list-style: disc;
    text-align: -webkit-match-parent;
    unicode-bidi: isolate;
    margin-bottom: 5px;
    font-family: 'Sofia Pro Regular';
    font-size: 16px;
}


strong {
    font-weight: 600;
    font-family: 'Sofia Pro Regular';
    font-size: 16px;
}

p {
    margin-block-start: 0;
    margin-block-end: .9rem;
}

 ul {
    margin-block-start: 0;
    margin-block-end: 0;
    border: 0;
    outline: 0;
    font-size: 100%;
    vertical-align: baseline;
    background: transparent;
    padding-left: 20px;
}
.add-to-cart {
    position: relative;
    padding: 12px 46px !important;
    text-transform: uppercase;
    background-color: #fff;
    color: white !important;
    border: none !important;
    font-weight: 100;
    border: 1px solid #929292 !important;
    background-color: #929292 !important;
}

.add-to-cart:hover {
    border: 1px solid !important;
    background-color: white !important;
}
.add-to-cart:before {
    content: '';
    height: 1px;
    width: 100%;
    position: absolute;
    background-color: black;
    left: 0px;
    bottom: -14px;
}

.add-to-cart {
    padding: 19px 50px;
    text-transform: uppercase;
    background-color: #fff;
    color: black;
    border: 1px solid;
    font-weight: 100;
}



.add-to-cart:after{
    content: '';
    height: 1px;
    width: 100%;
    position: absolute;
    background-color: black;
    left: 0px;
    bottom: -8px;
}
    
button.variation-button.border.rounded-md {
    padding: 8px 15px;
    list-style: none;
}
    .md\:h-60-custom {
        height: 34rem;
    }
    .thumbnail-image {
        width: 100px;
        height: 100px;
        margin: 10px;
        background-size: cover;
        background-position: center;
        border: 2px solid transparent;
        cursor: pointer;
    }
    .thumbnail:hover .thumbnail-image, .selected .thumbnail-image {
        border-color: red;
    }
    .variation-button {
        cursor: pointer;
        border: 2px solid transparent;
    }
    .variation-button.selected {
        border-color: blue;
    }
  
    #quantity {
    width: 27px;
    text-align: center;
    border: 1px solid #000000;
    border-radius: 0;
    padding: 0px;
    margin: 0px;
    height: 20px;
    font-size: 12px;
}

    /* Hide arrows in number input for Chrome, Safari, Edge, and Opera */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    label.block.text-sm.font-medium.text-gray-700.mr-2 {
    font-weight: normal;
    font-family: 'Sofia Pro Regular';
    font-size: 16px;
}

    /* Hide arrows in number input for Firefox */
    input[type="number"] {
        -moz-appearance: textfield;
    }

    #quantity:focus,
    button#decrement:focus,
    button#increment:focus {
        outline: none;
    }

    button#decrement, button#increment {
    background-color: #000000;
    border: 1px solid #000000;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    padding: 1px 10px;
    border-radius: 0;
    transition: background-color 0.3s;
    color: white;
    font-size: 16px;
}

    button#decrement:hover, button#increment:hover {
        background-color: #ddd;
    }

    /* Thumbnail Gallery Wrapper */
    .thumbnail-gallery-wrapper {
        position: relative; /* For absolute positioning of arrows */
        padding: 0 30px; /* Add space for arrows */
    }

    /* Thumbnail scrolling container */
    .thumbnail-scroll-container {
        overflow-x: hidden;  /* Hide horizontal scrollbar */
        white-space: nowrap; /* Prevent thumbnails from wrapping */
        justify-content: flex-start; /* Align thumbnails to the left */
        scroll-behavior: smooth; /* Smooth scrolling effect */
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    .thumbnail-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .thumbnail {
        display: inline-block;
        vertical-align: top;
    }

    .scroll-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        padding: 10px 5px;
        cursor: pointer;
        z-index: 10;
        border-radius: 3px;
        font-size: 16px;
        line-height: 1;
    }
    .scroll-arrow:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }
    .scroll-arrow.left {
        left: -5;
        height: 82%;
        width: 0px ! IMPORTANT;
        padding: 21px;
        border: none;
        background-color: #e1d8c7c7;
    }

    .scroll-arrow:disabled {
        opacity: 0.3;
        cursor: default;
    }

    .scroll-arrow.right {
        right: -5px;
        height: 82%;
        width: 0px ! IMPORTANT;
        padding: 21px;
        border: none;
        background-color: #e1d8c7c7;
    }

</style>
<div class="container mx-auto px-4 padder ">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <div class="mx-auto bg-white flex flex-col md:flex-row gap-12">
            <div class="product-thumbnail md:w-1/2">
                <?php 
                $gallery_images = get_field('product_gallery'); 
                $first_image = !empty($gallery_images) && is_array($gallery_images) ? $gallery_images[0] : get_the_post_thumbnail_url(get_the_ID(), 'full');
                ?>
                <!-- Main Image -->
                <div id="main-image" class="w-full h-60 md:h-60-custom bg-cover bg-center" 
                     style="background-image: url('<?php echo esc_url($first_image); ?>'); transition: opacity 0.5s;">
                </div>
                <!-- Thumbnails -->
                <div class="thumbnail-gallery-wrapper mt-4">
                     <button class="scroll-arrow left" id="scroll-left">&lt;</button>
                     <div class="thumbnail-scroll-container flex">
                         <?php
                         if (!empty($gallery_images) && is_array($gallery_images)) :
                             foreach ($gallery_images as $image_url) : ?>
                                 <a href="#" class="thumbnail" data-image="<?php echo esc_url($image_url); ?>">
                                     <div class="thumbnail-image" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
                                 </a>
                             <?php endforeach;
                         endif;
                         ?>
                     </div>
                     <button class="scroll-arrow right" id="scroll-right">&gt;</button>
                </div>
            </div>
            <div class=" md:w-1/2">
                <h1 class="product-title text-3xl font-bold mb-5"><?php the_title(); ?></h1>
                
                <?php
                $product_type = get_field('product_type');
                $price = '';
                
                if ($product_type === 'single') {
                    $price = get_field('product_price');
                } elseif ($product_type === 'subscription') {
                    $price = get_field('subscription_price');
                    // For subscriptions, the main stripe_price_id from post meta is used.
                    // Variation logic below handles specific price IDs for variations.
                } elseif ($product_type === 'variation') {
                    $variations = get_field('variation_product');
                    // Initial price for variations can be the first one or empty until one is selected
                    if (!empty($variations) && isset($variations[0]['variable_product_price'])) {
                         $price = $variations[0]['variable_product_price'];
                    }
                }

                // Retrieve main Stripe IDs (used for single/subscription or as fallback)
                $stripe_product_id_main = get_post_meta(get_the_ID(), 'stripe_product_id', true);
                $stripe_price_id_main = get_post_meta(get_the_ID(), 'stripe_price_id', true);
                ?>

                <!-- Display Stripe IDs for debugging purposes (can be removed in production) -->
                 <!-- <div class="prod-id-price-container">
                    <p>Stripe Product ID: <span id="displayed-stripe-product-id"><?php // echo esc_html($stripe_product_id_main); ?></span></p>
                    <p>Stripe Price ID: <span id="displayed-stripe-price-id"><?php //echo esc_html($stripe_price_id_main); ?></span></p>
                </div> -->

                <?php if ($product_type === 'subscription') : ?>
                    <?php 
                    // Ensure 'subscription_free_trial_days' is the correct ACF field name
                    $free_trial_days = get_field('subscription_free_trial_days', get_the_ID()); 
                    $display_price = ($free_trial_days && $free_trial_days > 0) ? '0.00' : number_format((float)$price, 2);
                    $trial_text = ($free_trial_days && $free_trial_days > 0) ? " (First {$free_trial_days} days free)" : '';
                    ?>
                    <p class="text-xl text-gray-800 mb-4">$<?php echo esc_html($display_price) . esc_html($trial_text); ?></p>
                <?php elseif ($product_type === 'variation' && !empty($variations)) : ?>
                    <p id="price" class="text-xl text-gray-800 mb-4">$<?php echo esc_html(number_format((float)$price, 2)); // Display initial price ?></p>
                    <div class="mb-5">
                        <div class="flex space-x-2 mt-5 mb-5">
                            <?php foreach ($variations as $index => $variation) : 
                                // Get Stripe IDs for variation using the INDEX from post meta
                                $variation_name = $variation['product_variation'];
                                // Corrected meta keys based on your functions.php save_product_meta
                                $variation_stripe_product_id = get_post_meta(get_the_ID(), 'stripe_variation_product_id_' . $index, true);
                                $variation_stripe_price_id = get_post_meta(get_the_ID(), 'stripe_variation_price_id_' . $index, true);
                            ?>
                                <button type="button" 
                                    class="variation-button px-4 py-2 border rounded-md" 
                                    data-price="<?php echo esc_attr($variation['variable_product_price']); ?>" 
                                    data-variation="<?php echo esc_attr($variation['product_variation']); ?>"
                                    data-stripe-product-id="<?php echo esc_attr($variation_stripe_product_id); ?>" 
                                    data-stripe-price-id="<?php echo esc_attr($variation_stripe_price_id); ?>">
                                    <?php echo esc_html($variation['product_variation']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : // Single product type ?>
                    <p class="text-xl text-gray-800 mb-5">$<?php echo esc_html(number_format((float)$price, 2)); ?></p>
                <?php endif; ?>

                <form class="add-to-cart-form mt-8" method="POST"> <!-- Changed to POST, though AJAX overrides -->
                    <input type="hidden" name="product_id_hidden_field" value="<?php echo get_the_ID(); ?>"> <!-- For JS if needed -->
                    <input type="hidden" name="variation" id="selected-variation" value="">
                    <!-- These hidden inputs will be updated by JS for variations -->
                    <input type="hidden" name="stripe_product_id" id="stripe-product-id" value="<?php echo esc_attr($stripe_product_id_main); ?>">
                    <input type="hidden" name="stripe_price_id" id="stripe-price-id" value="<?php echo esc_attr($stripe_price_id_main); ?>">
                    <input type="hidden" name="product_type" id="product-type" value="<?php echo esc_attr($product_type); ?>"> <!-- Added product type -->
                    
                    <div class="mb-10 flex items-center">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mr-2">Quantity</label>
                        <div class="flex items-center">
                            <button type="button" id="decrement" class="px-2">‹</button>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" class="w-12 text-center border-gray-300 rounded-md">
                            <button type="button" id="increment" class="px-2">›</button>
                        </div>
                    </div>
                    <button type="submit" name="add_to_cart" class="add-to-cart bg-blue-500 text-white px-4 py-2 mb-8 rounded-md hover:bg-blue-600">Add to Cart</button>
                </form>

                <div class="product-content woocommerce-product-details__short-description text-gray-700 mb-4">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    <?php endwhile; else : ?>
        <p class="text-center text-gray-500"><?php _e('Product not found.', 'textdomain'); ?></p>
    <?php endif; ?>
</div>
<hr>
<div class="padder">

<?php
// Include the tabs and accordion template part
get_template_part('template-parts/shop-accordion');
?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');
    const variationButtons = document.querySelectorAll('.variation-button');
    const selectedVariationInput = document.getElementById('selected-variation');
    const priceDisplay = document.getElementById('price');
    
    const stripeProductIdInput = document.getElementById('stripe-product-id'); // Hidden input for Stripe Product ID
    const stripePriceIdInput = document.getElementById('stripe-price-id');   // Hidden input for Stripe Price ID
    
    const displayedStripeProductId = document.getElementById('displayed-stripe-product-id'); // Span for displaying
    const displayedStripePriceId = document.getElementById('displayed-stripe-price-id');   // Span for displaying

    const addToCartForm = document.querySelector('form.add-to-cart-form');
    const quantityInput = document.getElementById('quantity');

    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function(e) {
            e.preventDefault();
            thumbnails.forEach(t => t.classList.remove('selected'));
            this.classList.add('selected');
            mainImage.style.opacity = 0;
            setTimeout(() => {
                mainImage.style.backgroundImage = `url(${this.dataset.image})`;
                mainImage.style.opacity = 1;
            }, 500);
        });
    });

    variationButtons.forEach((button, index) => { // Added index
        button.addEventListener('click', function() {
            variationButtons.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            
            selectedVariationInput.value = this.dataset.variation;
            
            if (priceDisplay) {
                priceDisplay.textContent = `$${parseFloat(this.dataset.price).toFixed(2)}`;
            }
            
            // Update the Stripe IDs in hidden inputs from data attributes
            const variationStripeProductId = this.dataset.stripeProductId;
            const variationStripePriceId = this.dataset.stripePriceId;

            if (stripeProductIdInput && variationStripeProductId) {
                stripeProductIdInput.value = variationStripeProductId;
            }
            
            if (stripePriceIdInput && variationStripePriceId) {
                stripePriceIdInput.value = variationStripePriceId;
            }
            
            // Update the displayed Stripe IDs on the page (for debugging)
            if (displayedStripeProductId && variationStripeProductId) {
                displayedStripeProductId.textContent = variationStripeProductId;
            }
            
            if (displayedStripePriceId && variationStripePriceId) {
                displayedStripePriceId.textContent = variationStripePriceId;
            }
        });
         // Set the first variation as selected by default if it's a variation product
        <?php if ($product_type === 'variation' && !empty($variations)): ?>
        if (index === 0 && variationButtons.length > 0) {
            button.click(); 
        }
        <?php endif; ?>
    });


    const incrementButton = document.getElementById('increment');
    const decrementButton = document.getElementById('decrement');

    if (incrementButton) {
        incrementButton.addEventListener('click', function() {
            quantityInput.value = parseInt(quantityInput.value) + 1;
        });
    }

    if (decrementButton) {
        decrementButton.addEventListener('click', function() {
            if (quantityInput.value > 1) {
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        });
    }

    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(event) {
            event.preventDefault(); 

            const productId = <?php echo get_the_ID(); ?>;
            const currentQuantity = parseInt(quantityInput.value) || 1;
            // Get the Stripe Price ID from the hidden input, which is updated by variation logic
            let currentStripePriceId = stripePriceIdInput.value;
            const selectedVariation = selectedVariationInput.value; // Get selected variation name

            if (!currentStripePriceId) {
                // Attempt to get it from the first variation button if it's a variation product and none selected explicitly
                <?php if ($product_type === 'variation' && !empty($variations)) : ?>
                if (variationButtons.length > 0) {
                    currentStripePriceId = variationButtons[0].dataset.stripePriceId;
                     // If a variation IS selected, that one takes precedence (already set by variation click handler)
                    const selectedButton = document.querySelector('.variation-button.selected');
                    if (selectedButton && selectedButton.dataset.stripePriceId) {
                        currentStripePriceId = selectedButton.dataset.stripePriceId;
                    }
                }
                <?php endif; ?>
            }
             // If still no price ID (e.g. single product where it wasn't set by variation logic but should be in the input)
            if (!currentStripePriceId) {
                 currentStripePriceId = document.getElementById('stripe-price-id').value; // Fallback to initial value
            }


            if (!currentStripePriceId) {
                alert('Could not determine the product price ID. Please select a variation if available or refresh and try again.');
                return;
            }

            const submitButton = addToCartForm.querySelector('button[name="add_to_cart"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.innerHTML = 'Processing...';
            submitButton.disabled = true;

            const formData = new FormData();
            formData.append('action', 'add_to_cart_and_redirect');
            formData.append('nonce', '<?php echo wp_create_nonce("add_to_cart_and_redirect_nonce"); ?>');
            formData.append('product_id', productId);
            formData.append('quantity', currentQuantity);
            formData.append('stripe_price_id', currentStripePriceId);
            formData.append('variation', selectedVariation); // Send variation name

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitButton.innerHTML = originalButtonText; // Restore button text regardless of outcome
                submitButton.disabled = false; // Re-enable button

                if (data.success && data.data.redirect_url) {
                    console.log('Add to cart successful, redirecting to:', data.data.redirect_url);
                    window.location.href = data.data.redirect_url; 
                } else {
                    // Use a more specific error message
                    alert('Error adding product to cart: ' + (data.data && data.data.error ? data.data.error : 'Could not add product to cart. Please try again.'));
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                // Use a more specific error message
                alert('An unexpected error occurred while adding to cart. Please check the console and try again.');
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        });
    }

    const scrollContainer = document.querySelector('.thumbnail-scroll-container');
    const scrollLeftButton = document.getElementById('scroll-left');
    const scrollRightButton = document.getElementById('scroll-right');

    function checkScrollButtons() {
        if (!scrollContainer) return;
        const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;
        scrollLeftButton.disabled = scrollContainer.scrollLeft <= 0;
        scrollRightButton.disabled = scrollContainer.scrollLeft >= maxScrollLeft - 1; 
    }

    if (scrollContainer && scrollLeftButton && scrollRightButton) {
        scrollLeftButton.addEventListener('click', () => {
            const scrollAmount = scrollContainer.clientWidth * 0.8; 
            scrollContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });

        scrollRightButton.addEventListener('click', () => {
            const scrollAmount = scrollContainer.clientWidth * 0.8;
            scrollContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });

        scrollContainer.addEventListener('scroll', checkScrollButtons);
        setTimeout(checkScrollButtons, 100);
        window.addEventListener('resize', checkScrollButtons);

    } else {
        if(scrollLeftButton) scrollLeftButton.style.display = 'none';
        if(scrollRightButton) scrollRightButton.style.display = 'none';
    }

});
</script>

<?php
get_footer();