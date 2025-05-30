<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<?php
if (is_user_logged_in()) {
    $user = wp_get_current_user();
    $user_roles = $user->roles;
    
    if (in_array('wpfs_no_access', $user_roles)) {
        echo '<style>.show-not-member { display: block !important; } .hide-logged-out {display: none !important;}</style>';
    } else {
        echo '<style>.show-not-member { display: none !important; } </style>';
    }
} else {
    echo '<style>.show-not-member { display: none !important; } </style>';
}
?>






<!doctype html>
<html <?php language_attributes(); ?>>
<head>

<!--- microsoft clarity --->
<!-- <script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "p133b78ss7");
</script> -->
<!--- microsoft clarity --->


<!-- Google Tag Manager -->
<!-- old tag disabled 11/18/2024-->
<!-- <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WX96X4D');</script> -->

<!-- new tag --->

<!-- <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MBNRCRZM');</script> -->
<!-- End Google Tag Manager -->


	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>


<!-- Google Tag Manager (noscript) -->
<!-- old tag disabled 11/18/2024 <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WX96X4D"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript> -->

<!-- <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MBNRCRZM"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript> -->

<!-- End Google Tag Manager (noscript) -->

<?php wp_body_open(); ?>

<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor' ); ?></a>
<?php } ?>

<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
		get_template_part( 'template-parts/dynamic-header' );
	} else {
		get_template_part( 'template-parts/header' );
	}
}
