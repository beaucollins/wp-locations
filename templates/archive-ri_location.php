<?php get_header(); ?>

<?php if( !have_posts() ):?>
  
<?php else: while( have_posts() ): the_post(); ?>

  <div id="locations-index">
    <div class="vcard">
      <a class="url fn org" href="<?php the_permalink();?>"><?php the_title(); ?></a>
      <span class="address">
        <?php ri_formatted_address(); ?>
        <?php ri_geo(); ?>
      </span>
    </div>
  </div>
<?php endwhile; endif; ?>
<?php get_footer(); ?>