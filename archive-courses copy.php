<?php
/**
 * Custom email template
 *
 * This template can be customized by copying it to your theme and modifying it.
 */

defined( 'ABSPATH' ) || exit;

// Add your email header action here
do_action( 'custom_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Hi %s,', 'your-text-domain' ), esc_html( $user_first_name ) ); ?></p>

<p><?php printf( esc_html__( 'Thanks for joining us at %1$s. Your username is %2$s. You can access your account area to view orders, change your password, and more at: %3$s', 'your-text-domain' ), esc_html( $blogname ), '<strong>' . esc_html( $user_login ) . '</strong>', make_clickable( esc_url( wc_get_page_permalink( 'myaccount' ) ) ) ); ?></p>

<?php if ( 'yes' === get_option( 'your_option_name' ) && $password_generated && $set_password_url ) : ?>
	<p><a href="<?php echo esc_attr( $set_password_url ); ?>"><?php esc_html_e( 'Click here to set your new password.', 'your-text-domain' ); ?></a></p>
<?php endif; ?>

<p>Join our community on our Facebook page! <a href="https://www.facebook.com/yourpage/">HERE</a></p>

<?php
// Show additional content if set
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

// Add your email footer action here
do_action( 'custom_email_footer', $email );
