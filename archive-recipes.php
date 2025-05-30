  <?php get_header(); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<div class="header-section level-header">
<div class="container padder">
<h2 class="script">welcome to the</h2>
    <h1 class="jumbo">Recipes library</h1>
    
</div>
</div>



<div class="container py-5">
   <div class="row">
      <div class="col-12">
      <svg xmlns="http://www.w3.org/2000/svg" width="265.431" height="1" viewBox="0 0 265.431 1">
  <path id="Path_15009" data-name="Path 15009" d="M7198,4407.171h265.431" transform="translate(-7198 -4406.671)" fill="none" stroke="#707070" stroke-width="1"/>
</svg>
 <h1 class="mb-4">Recipe Catalog</h1>
      </div>
   </div>
   <div class="row">

      <div class="col-md-3">
         <?php echo do_shortcode('[fe_widget id="4603"]'); ?>
      </div>

      <div class="col-md-9">
         <div class="row ajax-posts">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
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
                </div>
              </div>
              </a>
            </div>
            
            <?php endwhile; ?>
            <?php else : ?>
           <div class="col-12"> <p>No courses found.</p></div>
      
  
      <?php endif; ?>
      </div>
         <!---- end row of col-md-4 cards--->
      </div>
      <!--- end col-md-9---->
   </div>
   <?php if (function_exists('bootstrap_pagination')) bootstrap_pagination(); ?>
</div>
</div>





<?php get_template_part( 'template-parts/business', 'series' ); ?>



<?php the_content();?>

<?php get_footer(); ?>