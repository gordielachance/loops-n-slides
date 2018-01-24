<?php
/*
Handles what is relative to the loopsns-slide post type
*/

class LoopsNSlides_Posts_Slide{

    static $slide_post_type = 'loopsns-slide';

    function __construct() {
        add_action('init', array($this,'init_slide_post_type') );
        add_action( 'admin_notices', array($this,'slide_post_type_description') );
        add_action( sprintf('manage_edit-%s_columns',self::$slide_post_type), array($this,'slides_listing_columns') );
        add_action( 'manage_posts_custom_column', array($this,'slides_listing_columns_content') );
    }

    /**
     * Initilize the plugin
     */
    function init_slide_post_type() {
        $this->register_slide_post_type();
    }
    
    function register_slide_post_type(){
        $labels = array(
            'name'               => _x( 'Slides', 'post type general name', 'loopsns' ),
            'singular_name'      => _x( 'Slide', 'post type singular name', 'loopsns' ),
            'menu_name'          => _x( 'Slides', 'admin menu', 'loopsns' ),
            'name_admin_bar'     => _x( 'Slide', 'add new on admin bar', 'loopsns' ),
            'add_new'            => _x( 'Add New', 'slide', 'loopsns' ),
            'add_new_item'       => __( 'Add New Slide', 'loopsns' ),
            'new_item'           => __( 'New Slide', 'loopsns' ),
            'edit_item'          => __( 'Edit Slide', 'loopsns' ),
            'view_item'          => __( 'View Slide', 'loopsns' ),
            'all_items'          => __( 'All Slides', 'loopsns' ),
            'search_items'       => __( 'Search Slides', 'loopsns' ),
            'parent_item_colon'  => __( 'Parent Slides:', 'loopsns' ),
            'not_found'          => __( 'No slides found.', 'loopsns' ),
            'not_found_in_trash' => __( 'No slides found in Trash.', 'loopsns' )
        );

        register_post_type(self::$slide_post_type, array(
            'public' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'labels' => $labels,
            'capability_type' => 'post',
            'supports' => array(
                'title',
                'editor',
                'thumbnail'
            ),
            'taxonomies' => array( 'category', 'post_tag' ),
            'show_in_menu'    => loopsns()->menu_slug,
        ));
    }
    
    function slide_post_type_description(){ //TO FIX better hook for this ?
        $screen = get_current_screen();
        $can_show = ( ( $screen->base == 'edit' ) && ( $screen->post_type == self::$slide_post_type )  );
        
        if (!$can_show) return;

        ?>
        <div class="notice">
            <p><?php 
            _e( "Slides are just a post type designed to be specifically queried by Loops.", 'loopsns' ); 
            ?></p>
            <p><?php 
            _e( "You are not required to use them if you don't want to - Loops can load any type of posts - but they might be useful as they don't appear on archive or search pages.", 'loopsns' ); 
            ?></p>
        </div>
        <?php
    }
    
    /**
     * Add custom column filters in administration
     * @param array $columns
     */
    function slides_listing_columns($columns) {
        $col = array(
            'loopsns-slide-thumb' => __('Image','loopsns')
        );
        $col_idx = 1;
        $columns = array_slice($columns, 0, $col_idx) + $col + array_slice($columns, $col_idx, null);

        return $columns;
    }
    
    /**
     * Add custom column contents in administration
     * @param string $columnName
     */
    function slides_listing_columns_content($columnName) {
        global $post;
        if ($columnName == 'loopsns-slide-thumb') {
            echo edit_post_link(get_the_post_thumbnail($post->ID, 'thumbnail'), null, null, $post->ID);
        }
    }

}
