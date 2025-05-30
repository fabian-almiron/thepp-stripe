<?php get_header();


$term = get_queried_object();
$term_name = $term->name;
$term_slug = $term->slug;
$term_description = $term->description;

$args = array(
  'post_type' => 'courses',
  'tax_query' => array(
    array(
      'taxonomy' => 'courselevel',
      'field'    => 'slug',
      'terms'    => $term_slug,
    ),
  ),
);

$query = new WP_Query( $args ); 
?>


<div class="header-section level-header">
<div class="container padder">
    <h2><?php single_cat_title(); ?></h2>
    <h3>
    <?php
$terms = get_the_terms( get_the_ID(), 'courselevel' );
if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
    $term = array_shift( $terms );
    echo '<h2>' . esc_html( $term->name ) . '</h2>';
}
?>
</h3>
    </div>
</div>



<div class="newest-videos">
    <div class="container padder">
    <?php get_template_part( 'template-parts/newest', 'videos' ); ?>
    </div>
</div>






<div class="container padder_bot py-5">
   <div class="row">
      <div class="col-12">
      <svg xmlns="http://www.w3.org/2000/svg" width="265.431" height="1" viewBox="0 0 265.431 1">
  <path id="Path_15009" data-name="Path 15009" d="M7198,4407.171h265.431" transform="translate(-7198 -4406.671)" fill="none" stroke="#707070" stroke-width="1"/>
</svg>
 <h1 class="mb-4">Video Catalog</h1>
      </div>
   </div>
   <div class="row">

      <div class="col-md-3">
         <?php echo do_shortcode('[caf_filter id="301"]'); ?>
      </div>

      <div class="col-md-9">
         <div class="row">
         <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <div class="col-md-4 mb-5">
            <a href="<?php the_permalink(); ?>" class="card-wrap">
            <div class="slide-card">
            <?php if (has_post_thumbnail()) : ?>
                <img src="<?php the_post_thumbnail_url('medium_large'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
            <?php else : ?>
                <img src="https://vumbnail.com/<?php the_field('video_id');?>.jpg" class="card-img-top" alt="<?php the_title(); ?>">
            <?php endif; ?>

                <div class="featured-title">
                 <span><?php the_title(); ?></span>
                 <span id="save-post"><img src="/wp-content/uploads/2023/03/save-icon.svg"></span>
                </div>
              </div>
              </a>
            </div>
            
            <?php endwhile; ?>
            <?php else : ?>
           <div class="col-12"> </div>
      
  
      <?php endif; ?>
      </div>
         <!---- end row of col-md-4 cards--->
      </div>
      <!--- end col-md-9---->
   </div>
   <?php if (function_exists('bootstrap_pagination')) bootstrap_pagination(); ?>
</div>
</div>






<?php get_footer(); ?>