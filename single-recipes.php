<?php
/**
 * The template for displaying all single recepies.
 *
 * 
 */

get_header(); ?>

<!--- dependencies ---->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!--- dependencies ---->

<section class="recipes-section padder_lg_top padder_lg_bot">
  <div class="container">
    <div class="row align-items-center r-header-box">

      <div class="col-md-4">
      <img src="<?php the_post_thumbnail_url('medium_large'); ?>" class="card-img-top" alt="<?php the_title(); ?>">
      </div>

      <div class="col-md-8">
        <div class="r-header">
           <h1><?php the_field('header_title');?></h1>
           <span class="method"><?php the_field('method_label');?></span>
           <p class="hdescription"> <?php the_field('short_description'); ?></p>
			
<!--          
			<div class="meta-info margin_sm">
           
          <span><img src="/wp-content/uploads/2023/04/clock-solid.svg"> Time: 60 min.</span>  
            <span><img src="/wp-content/uploads/2023/04/chef-hat.svg"> Difficulty: 60 min.</span>  
            
            
            <form class="yield-math">
              <span><img src="/wp-content/uploads/2023/04/utensils-solid.svg"> Yield: 60 min.</span> 
              <input type="text" class="form-control" id="input1" placeholder="4QT">
              <input type="text" class="form-control" id="input2" placeholder="6.5QT">
            </form>

          </div>
          <p>k atwood 05.09 removing since we don't have this copy now
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
 Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat
</p> -->

          </div>
        </div>
          
        </div><!-- end row -->
  </div><!-- end container -->
</section><!-- end recipes-section -->

<section class="recipes-section padder_lg_bot">
  <div class="container">
    <div class="row ">

      <div class="col-md-3 left-side">
      <h4>Equipment</h4>
      <?php if( have_rows('equipment') ): ?>
      <ul>
    <?php while( have_rows('equipment') ): the_row(); 
        ?>
        <li>
            <?php the_sub_field('equipment_item'); ?>
        </li>
    <?php endwhile; ?>
    </ul>
<?php endif; ?>

        <h4>Ingredients</h4>
        <?php if( have_rows('ingredients') ): ?>
      <ul>

          <?php while( have_rows('ingredients') ): the_row(); ?>
          <li>
          <?php the_sub_field('ingredients_item'); ?>
          </li>
          <?php endwhile; ?>

        </ul>
        <?php endif; ?>



      </div>

      <div class="col-md-9 right-side">
        <h4>Directions</h4>
        <?php if( have_rows('important') ): ?>
        <ul>
        <?php while( have_rows('important') ): the_row(); ?>
          <li> <?php the_sub_field('important_items'); ?>  </li>
          <?php endwhile; ?>
        </ul>
        <?php endif; ?>

        <h4>Notes</h4>
        <?php if( have_rows('notes') ): ?>
        <ul class="liw-padding">
        <?php while( have_rows('notes') ): the_row(); ?>
          <li class="li-wnumbers"> <?php the_sub_field('note_item'); ?>  </li>
          <?php endwhile; ?>
        </ul>
        <?php endif; ?>
        

          </div>
          
        </div><!-- end row -->
  </div><!-- end container -->
</section><!-- end recipes-section -->





<?php get_footer();?>

