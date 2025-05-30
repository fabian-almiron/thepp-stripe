<?php
/**
 * The template for displaying all single posts.
 *
 * 
 */

get_header(); ?>


<?php include_once get_stylesheet_directory() . '/global-styles.php'; ?>
<!--- gets a php file for style because we hate cache ---> 



<!--- dependencies ---->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!--- dependencies ---->


<div class="the-fold">

 <!--- start container --->
<div class="container padder">

<div class="row m-rl-3">
<div class="col-lg-6 ">
  <h1><?php the_title();?></h1>
  <?php
        $category = get_the_category();
        if ( ! empty( $category ) ) {
            $category_link = get_category_link( $category[0]->term_id );
            echo '<a href="' . esc_url( $category_link ) . '">' . esc_html( $category[0]->name ) . '</a>';
        }
        ?>
  </div>

  <div class="col-lg-6 text-right v-align-bottom">
    <div class="save-video-wrap">
    <a href="/my-videos"> SAVED VIDEOS</a> - <?php the_content();?>
    </div>
  </div>
</div>

<div class="d-flex align-items-stretch">

  <div class="col-lg-8 flex-fill m-3">
     <div class="classes-wrapp">



<div style="padding:56.25% 0 0 0;position:relative;">
<iframe src="https://player.vimeo.com/video/<?php the_field('video_id');?>?h=4666570e4c&title=1&byline=1&portrait=1" id="myPlayer" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
  </div>
<script src="https://player.vimeo.com/api/player.js"></script>

<!--- script to jump to the section could be added to anohter php file ---->
<script>
var player = null;

var iframe = document.querySelector('iframe');
//console.log(iframe); // log iframe to verify if it is correctly referenced
iframe.onload = function() {
  player = new Vimeo.Player(iframe);
 // console.log(player); // log player to verify if it is correctly initialized

  document.querySelectorAll('.jump-to-time').forEach(function(element) {
    var time = element.dataset.time;
    element.addEventListener('click', function() {
      jumpToTime(time);
    });
  });

};


/// jump to time 
function jumpToTime(time) {
  console.log('Jumping to time:', time);

  if (!player) {
    console.log('Player not loaded yet');
    return;
  }

  var timestamp;
  if (typeof time === 'string') {
    // Parse the time string as a floating point number
    timestamp = parseFloat(time);
  } else if (typeof time === 'number' && isFinite(time)) {
    timestamp = time;
  }

  if (isNaN(timestamp)) {
    console.log('Invalid timestamp:', time);
    return;
  }

  console.log('Setting timestamp:', timestamp);

  if (timestamp < 0 || timestamp >= player.getDuration()) {
    console.log('Timestamp out of range:', timestamp);
    return;
  }

  // Remove the "selected" class from all chapters
  var chapters = document.querySelectorAll(".chapters li");
  chapters.forEach(function(chapter) {
    chapter.classList.remove("selected");
  });

  // Add the "selected" class to the chapter that corresponds to the specified time
  var selectedChapter = document.querySelector('[data-time="' + time + '"]');
  if (selectedChapter) {
    selectedChapter.classList.add("selected");
  }

  function formatTime(seconds) {
  var minutes = Math.floor(seconds / 60);
  var secondsRemainder = seconds % 60;
  var timeString = minutes.toString().padStart(2, '0') + ':' + secondsRemainder.toString().padStart(2, '0');
  return timeString;
}

  player.setCurrentTime(timestamp).then(function(seconds) {
    var timeString = formatTime(seconds);
    console.log('Jumped to ' + timeString);
    player.play(); // play the video automatically
  }).catch(function(error) {
    console.log('Error jumping to ' + timestamp + ' seconds: ' + error);
  });
}
</script>
<!--- script to jump to the section could be added to anohter php file ---->
    </div>
  </div>

<div class="col-lg-4 flex-fill chapters-wrap m-3">
<div class="chapters-wrap-inner">
     <h3>Video Chapters</h3>
   </div>

   
      <?php if( have_rows('video_chapters') ): ?>
      <ul class="chapters">
        <?php while( have_rows('video_chapters') ): the_row(); ?>
          <?php
          
            $chapter_title = get_sub_field('chapter_title');
            $jump_to_time = get_sub_field('jump_to_time');
            $time_components = explode('.', $jump_to_time);
            $chapter_time = ($time_components[0] * 60 * 60) + ($time_components[1] * 60) + $time_components[2];
          ?>
          <li class="mb-2 jump-to-time" data-time="<?php echo $chapter_time; ?>"><?php echo $chapter_title; ?></li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>


  </div>
</div>

</div><!--- end d flex stretch --->
</div> <!--- end container --->
</div><!--- end the fold --->



<div class="below-the-fold">
  <div class="container padder">
  <div class="row">

        <div class="col-lg-9 m-3 padder_bot">
             <div class="tabs-area">
                <nav>
                  <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">About</button>
                    <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">what you'll need</button>
                  </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab"><?php the_field('about');?></div>
                  <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                   

                    <?php if( have_rows('what_youll_need') ): ?>
                        <ul class="list-of-items">
                        <?php while( have_rows('what_youll_need') ): the_row();  ?>
                            <li>
                               <?php the_sub_field('item'); ?>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    <?php endif; ?>

                  </div>
                </div>
              </div><!--- end tabs area --->
        </div><!--- end col-9 --->


<div class="col-lg-12 m-2">
  <?php get_template_part('template-parts/series','slider'); ?>
</div>

</div><!--- end row --->
</div><!--- end container--->
</div><!--- end below the fold--->


<style>
  @media only screen and (max-width: 1092px) {
  .d-flex {
    display: flex!important;
    flex-direction: column;
}
}


</style>

<?php get_footer();?>





