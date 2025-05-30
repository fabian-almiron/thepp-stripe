<?php
/**
 * Template Name: Get User info
 */
get_header();
?>

<div class="container padder_top">
    <div class="row">
<?php the_content();?>
</div>
</div>

<?php
$current_user_id = get_current_user_id();
echo 'User ID:' . $current_user_id . '<br>' ;

// Check if the user has a subscription matching the status
if (wcs_user_has_subscription($atts['user'], '', $status)) {
    $btn_code = '';

    // Get user subscriptions
    $users_subscriptions = wcs_get_users_subscriptions($current_user_id);
    foreach ($users_subscriptions as $subscription) {
        $current_subscription_id = $subscription->ID;
        echo 'Subscription ID:' . $current_subscription_id;

        // Generate the URL with the subscription ID and nonce
        $nonce = wp_create_nonce('resubscribe_nonce');
        $resubscribe_link = add_query_arg(array('resubscribe' => $current_subscription_id, '_wpnonce' => $nonce), 'https://thepipedpeony.com/my-account/');

        // Output the link
        //echo '<a href="' . esc_url($resubscribe_link) . '">Resubscribe</a>';

        // Your code logic here
    }
}


$current_user = wp_get_current_user(); // Get the current user object
$user_roles = $current_user->roles; // Get the roles assigned to the current user

if (is_array($user_roles) && !empty($user_roles)) {
    $current_user_role = $user_roles[0]; // Get the first role assigned to the user

    // Now you can use the $current_user_role variable
    echo 'Current user role: ' . $current_user_role;
}



?>




<style>
#customer_details {
    padding-bottom: 50px;
}
</style> <!--- never do this issue with elementor:
  <div data-elementor-type="footer" data-elementor-id="81" class="elementor elementor-81 elementor-location-footer">
--->

</body>
<?php get_footer(); 