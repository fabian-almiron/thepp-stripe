<?php
/* 
Template Name: Shop Pages
*/ 
get_header();
?>

<div class="padder" id="content ">

    <!-- Add a new tab for Shawn -->
    <ul class="tabs">
        <li><a href="#shawn">Shawn</a></li>
        <!-- Add other tabs here if necessary -->
    </ul>

    <div id="shawn" class="tab-content">
        <h3>Welcome to Shawn's Tab</h3>
        <p>This is the content for the Shawn tab.</p>
    </div>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

        <div class="post container " id="post-<?php the_ID(); ?>">
            <h3><?php the_title(); ?></h3>
            <div class="entry ">
                <?php the_content(); ?>
            </div>
        </div>

    <?php endwhile; endif; ?>

</div>

<?php get_footer(); ?>
