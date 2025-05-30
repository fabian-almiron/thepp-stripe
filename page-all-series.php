<?php
/**
 * Template Name: All Series
 */
get_header();
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<div class="header-section level-header all-videos">
<div class="container padder_lg">
    <h1 class="jumbo">All Series</h1>
    <p>Watch our academy’s videos to stay up-to-date on the latest<br>techniques, trends, and piping recipes.</p>
    
</div>
</div>


<div class="container padder">
  <div class="row">
  <?php
$categories = get_categories();

foreach ($categories as $category) :
  if ($category->slug === 'flower-piping-series') {
    continue; // Skip this category
  }
  $category_link = get_category_link($category->term_id);
  $category_image_url = get_field('category_image_url', 'category_' . $category->term_id);
?>
  <div class="col-md-4 mb-4">
    <div class="slide-card">
      <a href="<?php echo esc_url($category_link); ?>">
        <img src="<?php echo esc_url($category_image_url); ?>" alt="" class="card-img-top">
      </a>
      <div class="featured-title">
        <span><?php echo esc_html($category->name); ?></span>
        <span id="save-post"><img src="/wp-content/uploads/2023/03/save-icon.svg"></span>
      </div>
    </div>
  </div>
<?php endforeach; ?>

  </div>
</div>

<?php get_footer(); ?>
