<?php
/*
Template used for the [gallery] shortcode, when it has the attribute loopsns-carousel=1.
*/
global $loopsns_loop;

if ( have_posts() ){
    while( have_posts() ){
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <figure class="gallery-item">
                <?php echo wp_get_attachment_image( get_the_ID(), $loopsns_loop->gallery_atts['size'] ); ?>
                <?php
                $title = get_the_title();
                $excerpt = get_the_excerpt();
                if ($title || $excerpt){
                    ?>
                    <figcaption>
                        <?php the_title( '<h4 class="loopsns-slide-title">', '</h4>' ); ?>
                        <?php the_excerpt(); ?>
                    </figcaption>
                    <?php
                }
                ?>
            </figure>
            <?php
            ?>
        </article>
   <?php 
    }

}
?>