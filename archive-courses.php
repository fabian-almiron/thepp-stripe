<?php get_header();

$term = get_queried_object();
$term_name = $term->name;
$term_slug = $term->slug;
$term_description = $term->description;

$args = array(
  'post_type' => 'courses',
  'posts_per_page' => 12, // Display 12 posts per page
);

$query = new WP_Query( $args );

?>


<?php the_content();?>

<?php echo do_shortcode('[wcm_restrict]');?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<div class="header-section level-header all-videos">
<div class="container padder_lg">
    <h1 class="jumbo">All Videos</h1>
    <p>Watch our academy’s videos to stay up-to-date on the latest<br>techniques, trends, and piping recipes.</p>
    
</div>
</div>




<div class="newest-videos padder_top">
    <div class="container padder_bot">
    <?php get_template_part( 'template-parts/newest', 'videos' ); ?>
    </div>
</div>





<div class="container py-5">
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
<?php echo do_shortcode('[fe_widget]');?>
      </div>

      <div class="col-md-9">
         <div id="post-container" class="row ajax-posts">
            <?php if (have_posts()) : while (have_posts()) : the_post();?>

        
            <?php get_template_part( 'template-parts/content', 'slidecards' ); ?>

            
            <?php endwhile; ?>

            
            <?php else : ?>
           <div class="col-12"> <p>No courses found.</p></div>
      
  
      <?php endif; ?>
      </div>

      <div class="text-center">
            <button id="load-more-posts">Load More</button>
        </div>
      <!-- <div class="text-center">
            <button id="load-more-posts">Load More</button>
        </div> -->
         <!---- end row of col-md-4 cards--->
      </div>
      <!--- end col-md-9---->
   </div>
   <?php if (function_exists('bootstrap_pagination')) bootstrap_pagination(); ?>
</div>
</div>







<?php get_footer(); ?>