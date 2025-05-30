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