
<?php
global $post;
$tabs_accordions = get_field('tabs_accordions', $post->ID);

if($tabs_accordions): 
?>

<div class="fct-tabs-container">

    <!-- Tab Headers -->
    <div class="fct-tab-headers">
    <?php foreach($tabs_accordions[0]['tab_item'] as $tab): ?>
        <h5 class="fct-tab-header"><?php echo esc_html($tab['tab_title']); ?></h5>
    <?php endforeach; ?>
    </div>

    <!-- Tab Content -->
    <?php foreach($tabs_accordions[0]['tab_item'] as $tab): ?>
        <div class="fct-tab-content">
            <?php if(isset($tab['accordion'])): ?>

            <div class="fct-accordions">

            <?php foreach($tab['accordion'] as $key => $accordion): ?>
                 <div class="fct-accordion <?php echo $key == 0 ? 'open' : ''; ?>">
                        <!-- Display Accordion Title -->
                        <h5><?php echo esc_html($accordion['accordion_item_title']); ?><span class="toggle-icon">+</span></h5>

                        <!-- Display Accordion Content -->
                        <div class="fct-accordion-content">
                            <?php echo ($accordion['accordion_item_content']); ?>
                        </div>
                    </div> <!-- .accordion -->

                <?php endforeach; ?>

            </div> <!-- .accordions -->

            <?php endif; ?>
        </div> <!-- .tab-content -->
    <?php endforeach; ?>

</div> <!-- .tabs-container -->

<?php 
endif; 
?>

<style>

button.qty-button.decrement {
    transform: scaleX(-1);
}

button.single_add_to_cart_button.button.alt.disabled.wc-variation-selection-needed:before{ 
 content: '';
    height: 1px;
    width: 100%;
    position: absolute;
    background-color: black;
    left: 0px;
    bottom: -14px;
}


button.single_add_to_cart_button.button.alt.disabled.wc-variation-selection-needed:after{ 
    content: '';
    height: 1px;
    width: 100%;
    position: absolute;
    background-color: black;
    left: 0px;
    bottom: -8px;
}

button.single_add_to_cart_button.button.alt:before{
 content: '';
    height: 1px;
    width: 100%;
    position: absolute;
    background-color: black;
    left: 0px;
    bottom: -14px;
  
}

button.single_add_to_cart_button.button.alt:after{
      content: '';
    height: 1px;
    width: 100%;
    position: absolute;
    background-color: black;
    left: 0px;
    bottom: -8px;
  
}

button.single_add_to_cart_button.button.alt {
    padding: 19px 50px;
    text-transform: uppercase;
    background-color: #fff;
    color: black;
    border: 1px solid;
    font-weight: 100;
}


button.single_add_to_cart_button.button.alt:hover {
    transition: all 0.3s ease;
    background-color: black;
    color: white;
}



.woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.alt.disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.alt.disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.alt:disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.alt:disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.alt:disabled[disabled], .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) #respond input#submit.alt:disabled[disabled]:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.alt.disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.alt.disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.alt:disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.alt:disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.alt:disabled[disabled], .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) a.button.alt:disabled[disabled]:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.alt.disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.alt.disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.alt:disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.alt:disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.alt:disabled[disabled], .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) button.button.alt:disabled[disabled]:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.alt.disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.alt.disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.alt:disabled, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.alt:disabled:hover, .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.alt:disabled[disabled], .woocommerce:where(body:not(.woocommerce-block-theme-has-button-styles)) input.button.alt:disabled[disabled]:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce #respond input#submit.alt.disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce #respond input#submit.alt.disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce #respond input#submit.alt:disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce #respond input#submit.alt:disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce #respond input#submit.alt:disabled[disabled], :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce #respond input#submit.alt:disabled[disabled]:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce a.button.alt.disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce a.button.alt.disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce a.button.alt:disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce a.button.alt:disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce a.button.alt:disabled[disabled], :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce a.button.alt:disabled[disabled]:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce button.button.alt.disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce button.button.alt.disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce button.button.alt:disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce button.button.alt:disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce button.button.alt:disabled[disabled], :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce button.button.alt:disabled[disabled]:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce input.button.alt.disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce input.button.alt.disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce input.button.alt:disabled, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce input.button.alt:disabled:hover, :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce input.button.alt:disabled[disabled], :where(body:not(.woocommerce-block-theme-has-button-styles)) .woocommerce input.button.alt:disabled[disabled]:hover {
    background-color: #0c0c0c;
    color: #fff;
}

.woocommerce div.product div.images .flex-control-thumbs li {
    width: 25%;
    float: left;
    margin: 0;
    list-style: none;
    padding-right: 20px;
    padding-top: 20px;
}

.woovr-variation.woovr-variation-radio.woovr-variation-active {
    background-color: black;
    color: white;
}

.woovr-variations .woovr-variation > div {
    max-width: 100%;
    padding: 0;
}

.woovr-variation {
    display: inline-block;
    border: 2px solid #ddd;
    padding: 8px 20px;
}
</style>


<style>

.woovr-variations .woovr-variation {
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-left: -5px;
    margin-right: -5px;
    border: 1px solid #000;
    border-radius: 0px;
    margin-right: 20px;
    text-align: center;
    text-align: ce;
}

.woovr-variations {
    padding-top: 5px;
    padding-bottom: 5px;
    display: flex;
}

/* Hide original radio buttons */


/* Style the parent div to look like buttons */
.woovr-variation {
    display: inline-block;
    border: 2px solid #ddd;
    padding: 10px 20px;
    margin-right: 10px; /* Space between buttons */
    cursor: pointer;
    background-color: #fff;
    border-radius: 4px;
    transition: all 0.3s ease;
}

/* Hover effect */
.woovr-variation:hover {
    background-color: #f5f5f5;
    border-color: #aaa;
}

/* Style for the active (checked) radio */
.woovr-variation-selector input[type="radio"]:checked ~ .woovr-variation-info {
    background-color: #333;
    color: #fff;
    border-color: #333;
}
</style>


<style>

.fct-accordion h5 {
    position: relative;
    cursor: pointer;
}

.toggle-icon {
    position: absolute;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    transition: transform 0.3s;
    font-size: 30px;
}

.fct-accordion-content {
    display: none;
}

.fct-accordion.open .fct-accordion-content {
    display: block;
}

.fct-accordion.open .toggle-icon {
    transform: translateY(-50%) rotate(45deg);
}


.fct-tab-content {
    display: none;
}

.fct-tab-content {
    padding: 5% 8%;
    padding-bottom: 8%;
}

.fct-tab-headers {
    border-bottom: 1px solid !important;
    padding: 0px 50px;
    font-family: 'Sofia Pro Regular' !important;
}

.fct-tab-header {
    cursor: pointer;
    padding: 15px 20px;
    padding-top: 17px;
    display: inline-block;
    margin-right: 5px;
    margin: 0px;
    font-family: 'Sofia Pro Regular' !important;
    font-size: 16px !important;
}

.fct-accordion {
    border-bottom: 1px solid;
}


.fct-tab-header.active {
    /* background-color: #d1d1d1; */
    border-bottom: 1px solid;
}

.fct-tabs-container {
    border-bottom: 1px solid;
    border: 1px solid;
    max-width: 950px;
}

.fct-tab {
 
}

/* .fct-tab:last-child {
    border-bottom: none;
} */

.tab-container {
    border-bottom: 1px solid;
    padding: 10px;
}

.fct-tab h5 {
    font-size: 20px !important;
    color: #333;
    margin-top: 0;
    cursor: pointer;
    font-family: 'Sofia Pro Regular' !important;
    margin-bottom: 0px;
}



.fct-accordion h5 {
    font-size: 20px;
    color: #000;
    cursor: pointer;
    margin-top: 10px;
    /* line-height: 0 !important; */
    margin-bottom: 0px;
    margin-top: 0px;
    padding-bottom: 12px;
    padding-top: 12px;
    padding-left: 10px;
    padding-right: 10px;
    font-family: 'Sofia Pro Regular';
}

.fct-accordion-content {
    display: none;
    padding: 10px;
    /* background-color: #f7f7f7; */
    /* border-top: 1px solid #ddd; */
}


</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Tab functionality
    $(".fct-tab-header").click(function() {
        // Remove active class from other headers and contents
        $(".fct-tab-header").removeClass("active");
        $(".fct-tab-content").hide();

        // Show the content associated with clicked header
        var index = $(this).index();
        $(this).addClass("active");
        $(".fct-tab-content").eq(index).show();
    });

    // Open the first tab by default
    $(".fct-tab-header:first").click();

    // Accordion functionality
    $(".fct-accordion h5").click(function() {
        $(this).next(".fct-accordion-content").slideToggle(200);
        $(".fct-accordion-content").not($(this).next(".fct-accordion-content")).slideUp(200);
    });
});


</script>

<script>
jQuery(document).ready(function($) {
    $('.fct-accordion h5').on('click', function() {
        // Close other accordions in the same tab
        $(this).parent().siblings('.fct-accordion').removeClass('open');

        // Toggle current accordion
        $(this).parent().toggleClass('open');
    });

    // This part isn't necessary since the first accordion is already open via PHP,
    // but if you ever render the HTML differently, this ensures the first accordion is open
    $('.fct-tab-content').each(function() {
        $(this).find('.fct-accordion').first().addClass('open');
    });
});

</script>