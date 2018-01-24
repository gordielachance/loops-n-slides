<?php
/**
 * Loops 'n Slides Loop: List of titles
 *
 * Display a post title
 */
?>
<?php if ( have_posts() ){ ?>
    <ul>
    <?php 
    while( have_posts() ){
        the_post();
        ?>
        <li id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <strong class="loopsns-slide-title">
                <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Permalink to %s' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
            </strong>
        </li>
   <?php } ?>
    </ul>

<?php } ?>

