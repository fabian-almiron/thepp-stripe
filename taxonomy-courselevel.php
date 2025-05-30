<?php
get_header();

// $term = get_queried_object();
// $term_name = $term->name;
// $term_slug = $term->slug;
// $term_description = $term->description;

// $args = array(
//   'post_type' => 'courses',
//   'tax_query' => array(
//     array(
//       'taxonomy' => 'courselevel',
//       'field'    => 'slug',
//       'terms'    => $term_slug,
      
//     ),
//   ),
// );

//$query = new WP_Query( $args ); ?> 

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<div class="header-section category-header">
<div class=" padder">
    <h1 class="jumbo">Skill Level</h1>
   <h2 class="script"><?php echo $term_name;?></h2>
</div>
</div>

<!--katwood 05.18.23
<div class="suggested-series">
  <div class="container padder">
  <div class="text-center padder_xsm_bot">
    <h2>Suggested Series</h2>
</div>
    <div class="row align-items-center">
      <div class="col-md-6">
      <div class="row series-card  align-items-center">
      <div class="col-md-8 padder_h_left d_block">
        <h3>Basic Piping</h3>
        <p>Discover the fundamental techniques and essential tools necessary to make your piping creations blossom.</p>
        <a href="/category/the-basic-piping-series/" class="btn btn-secondary">View Series</a>
      </div>
      <div class="col-md-4 spacer-height" style="background-image: url(/wp-content/uploads/2023/04/video-series.jpg);">
       
      </div>
    </div>


      </div>
      <div class="col-md-6 ">
      <div class="row series-card align-items-center">
      <div class="col-md-8 padder_h_left">
        <h3>Coloring</h3>
        <p>By utilizing the color wheel and the three primary colors, you can discover the art of creating secondary and tertiary colors with ease.</p>
        <a href="/category/coloring-series/" class="btn btn-secondary">View Series</a>
      </div>
      <div class="col-md-4 spacer-height" style="background-image: url(/wp-content/uploads/2023/04/coloring.jpg);">
      </div>
    </div>
  </div>
      </div>
    </div>
  </div>
</div>


<div class="recommended-videos">
    <div class="container padder">
    <//?php get_template_part( 'template-parts/recomended', 'slider' ); ?>
    </div>
</div>
-->

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
          <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

             <?php get_template_part( 'template-parts/content', 'slidecards' ); ?>

            
            <?php endwhile; ?>
            <?php else : ?>
           <div class="col-12"> <p>No courses found.</p></div>
      
  
      <?php endif; ?>
      </div>

      <div class="text-center">
            <button id="load-more-posts">Load More</button>
        </div>

         <!---- end row of col-md-4 cards--->
      </div>
      <!--- end col-md-9---->
   </div>
   <?php if (function_exists('bootstrap_pagination')) bootstrap_pagination(); ?>
</div>
</div>


<?php 
get_footer();
?>
