<?php
/* 
Template Name: Saved List 
*/ 
get_header();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>


<div class="container padder">
  <h1>Saved Courses</h1>

  <?php
$user_id = get_current_user_id();
$saved_courses = get_user_meta($user_id, 'saved_courses', true);

if (!empty($saved_courses)) :
  $args = array(
    'post_type' => 'courses',
    'post__in' => $saved_courses,
    'orderby' => 'post__in',
  );

  $saved_course_query = new WP_Query($args);

  if ($saved_course_query->have_posts()) :
    ?>
    <div class="row">



    <div class="col-md-12">
      <div class="row">
      <?php
      while ($saved_course_query->have_posts()) : $saved_course_query->the_post();
        ?>
        <div class="col-md-4 mb-4">
          <div class="slide-card my-favorites in-list">
            <?php if (has_post_thumbnail()) : ?>
              <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium', array('class' => 'card-img-top')); ?></a>
            <?php else : ?>
              <img src="https://vumbnail.com/<?php the_field('video_id');?>.jpg" class="card-img-top" alt="<?php the_title(); ?>">
            <?php endif; ?>
            <div class="featured-title">
               <span><?php the_title(); ?></span>
               </div>
               <div class="save-btns">
              <p class="card-text"><?php the_excerpt(); ?></p>
              <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Course</a>
              <a class="btn btn-danger remove-from-list" data-course-id="<?php the_ID(); ?>">Remove from List</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
            </div>
            </div>
    </div> 
  <?php else : ?>
    <h2>You haven't saved any courses yet.</h2>
  <?php endif; wp_reset_postdata(); else : ?>
    
<?php endif; ?>

<script>
  // Add JavaScript to remove courses from the saved list
  const removeFromListButtons = document.querySelectorAll(".remove-from-list");
  removeFromListButtons.forEach(function(button) {
    button.addEventListener("click", function(event) {
      event.preventDefault();
      const courseId = this.dataset.courseId;
      const data = new FormData();
      data.append('action', 'remove_course_from_list');
      data.append('course_id', courseId);
      fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
          method: 'POST',
          body: data
        })
        .then(function(response) {
          return response.json();
        })
        .then(function(data) {
          if (data.success) {
            location.reload();
          } else {
            console.log(data.message);
          }
        })
        .catch(function(error) {
          console.log(error);
        });
    });
  });
</script>

</div>


<style>
.slide-card.my-favorites.in-list {
    height: auto;
}
</style>

<?php
get_footer();
