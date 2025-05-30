<?php
/* 
Template Name: Video Library
*/ 
get_header();
?>
<?php the_content();?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<div class="header-section level-header ">
<div class="container padder">
<h2 class="script">welcome to the</h2>
    <h1 class="jumbo">Video library</h1>
    <p>Watch our academy’s videos to stay up-to-date on the latest<br>techniques, trends, and piping recipes.</p>
    
</div>
</div>
<div class="levels-section padder_lg_bot">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <div class="level-card margin_sm_bot">
          <a href="/courselevel/beginner/">
            <div class="image" style="background-image: url('/wp-content/uploads/2023/05/sunflower.png');">
            
            </div>
            <div class="l-title">
              <h4> Beginner</h4>
            </div>
          </a>
        </div>
      </div>
      <div class="col-md-4 margin_sm_bot">
        <div class="level-card">
          <a href="/courselevel/intermediate/">
            <div class="image" style="background-image: url('/wp-content/uploads/2023/05/Video-Library-Intermediate-thumbnail.jpg');">
          
            </div>
            <div class="l-title">
              <h4> Intermediate</h4>
            </div>
          </a>
        </div>
      </div>
      <div class="col-md-4 margin_sm_bot">
        <div class="level-card">
          <a href="/courselevel/advanced/">
            <div class="image" style="background-image: url('/wp-content/uploads/2023/05/Persian-Buttercup-scaled.jpg');">
             
            </div>
            <div class="l-title">
              <h4>Advanced</h4>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div><!--- end course level sections --->




<div class="newest-videos">
    <div class="container padder_bot">
    <?php get_template_part( 'template-parts/newest', 'videos' ); ?>
    </div>
</div>

<section class="video-series padder_lg">
  <div class="container">
  <div class="text-center padder_xsm_bot">
    <h2>Video Series</h2>
</div>





      <?php if( have_rows('video_series') ): ?>
   <?php while( have_rows('video_series') ): the_row(); ?>
    <div class="row series-card margin_bot align-items-center">

      <div class="col-md-5">

        <h3> <?php the_sub_field('title'); ?></h3>
        <span class="gray d_block padder_xsm_bot"> <?php the_sub_field('parts'); ?></span>
        <p class="padder_xsm_bot"> <?php the_sub_field('content'); ?></p>
        <a href=" <?php the_sub_field('button_link'); ?>" class="btn btn-secondary">View Series</a>
       </div>
       <div class="col-md-7 spacer-height" style="background-image: url(<?php the_sub_field('image'); ?>);">
      </div>

    </div>
    <?php endwhile; ?>
      <?php endif; ?>

  <div class="text-center padder_top">
    <a href="/all-series" class="btn btn-primary">View All Series</a>
  </div>
</section>


<div class="flower-piping-videos">
    <div class="container padder">
    <?php get_template_part( 'template-parts/flower', 'piping' ); ?>
    <div class="text-center padder_top">
    <a href="/category/flower-piping-series/" class="btn btn-primary">view all flower piping videos</a>
</div>
    </div>
</div>



<?php get_template_part( 'template-parts/business', 'series' ); ?>




<?php get_footer(); ?>