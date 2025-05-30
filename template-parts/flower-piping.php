<div class="continue-series">
<div class="text-center padder_sm_bot">

    <h2>Flower Piping</h2>
</div>
<!---- splide beta cdn --->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/css/splide.min.css">
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/js/splide.min.js"></script>
<script>
    // initiates splide + settings  
    // OPTIONS: https://splidejs.com/guides/options/
    // ARROWS: https://splidejs.com/guides/arrows/
    // PAGINATION: https://splidejs.com/guides/pagination/
    
document.addEventListener('DOMContentLoaded', function() {
    var splide = new Splide('#flower-piping', {
         type         : 'loop',
         perPage      : 4,
         rewind : true,
         autoplay     : true,
         interval     : 15000,

         breakpoints: {
            1200: { perPage: 3, gap: '1rem' },
            640 : { perPage: 1, gap: 0 },
        },

        'arrowPath': 'm15.5 0.932-4.3 4.38 14.5 14.6-14.5 14.5 4.3 4.4     14.6-14.6 4.4-4.3-4.4-4.4-14.6-14.6z',

    }).mount();
});
</script>


<?php
$related_courses_args = array(
    'post_type' => 'courses',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'post__not_in' => array( get_the_ID() ),
    'tax_query' => array(
        array(
            'taxonomy' => 'category',
            'field' => 'id',
            'terms' => 658
        )
    ),
    'orderby' => 'date',
    'order' => 'ASC'
);
$related_courses_query = new WP_Query( $related_courses_args );
?>

<div id="flower-piping" class="splide">
    <div class="splide__track">
        <?php if ( $related_courses_query->have_posts() ) { ?>
            <ul class="splide__list">
                <?php while ( $related_courses_query->have_posts() ) { $related_courses_query->the_post(); ?>
                    <li class="splide__slide">
                        <div class="slide-card">
                            <a href="<?php echo esc_url( get_permalink() );?>">
                                <img src="https://vumbnail.com/<?php the_field('video_id');?>.jpg">
                                <div class="featured-title">
                                    <span><?php echo esc_html( get_the_title() ); ?></span>
                                </div>
                            </a>
                        </div>
                    </li>
                <?php } // end of while loop ?>
            </ul>
            <?php wp_reset_postdata(); ?>
        <?php } // end of if statement ?>
    </div>
</div>



</div><!--- end col-12 -->


<style>
.slide-card {
    height: 246px;
}

@media only screen and (max-width: 600px) {
    .slide-card {
    height: auto;
}
}

</style>
