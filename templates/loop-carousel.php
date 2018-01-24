<?php
/**
 * Loops 'n Slides Loop: Carousel
 *
 * Display a post title and excerpt
 */
global $loopsns_loop;
?>

<?php if ( have_posts() ){ ?>
    <?php 
    while( have_posts() ){
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php
            if ( has_post_thumbnail() ) {
                ?>
                <figure>
                    <?php echo get_the_post_thumbnail( get_the_ID(), 'large' ); ?>
                    <figcaption>
                        <?php the_title( '<h4 class="loopsns-slide-title">', '</h4>' ); ?>
                        <?php the_excerpt(); ?>
                    </figcaption>
                </figure>
                <?php
            }
            ?>
        </article>

   <?php 
    }

}
?>