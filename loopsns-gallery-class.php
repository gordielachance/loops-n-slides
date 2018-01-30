<?php
/*
Handles what is relative to Wordpress Galleries.
*/

class LoopsNSlides_Gallery{
    
    static $default_carousel_options = array(
        'items' => 3
    );

    function __construct() {
        add_shortcode( 'gallery', array($this,'gallery') );
        add_filter( 'post_gallery', array($this,'handle_carousel_gallery'), 10, 3 );

    }
    
    function gallery($atts){ //hijack the gallery shortcode
        
        if (!$atts) $atts = array();

        //carousel enabled : custom or default ?
        if (!isset($atts['loopsns-carousel'])){
            $default_enabled = ( loopsns()->get_options('gallery-carousel') == 'on' );
            if ($default_enabled){
                $atts['loopsns-carousel'] = 1;
            }
            
            //default size
            if (!isset($atts['size'])){
                $atts['size'] = 'large';
            }
            
        }

        //carousel options : custom or default ?
        //TO FIX json not supported in shortcodes, find a solution for this
        /*
        if ($atts['loopsns-carousel']){
            if (!isset($atts['loopsns-carousel-options'])){
                $atts['loopsns-carousel-options'] = json_encode(self::$default_carousel_options);
            }else{
                
            }
        }
        */
        return gallery_shortcode( $atts );
    }

    function handle_carousel_gallery($output = '', $attr, $instance) {
        global $post;

        $carousel_enabled = ( isset($attr['loopsns-carousel'] ) && ( $attr['loopsns-carousel'] ) );
        //$carousel_options = isset($atts['loopsns-carousel-options'] ) ? $atts['loopsns-carousel-options'] : null;

        $do_carousel = ( $carousel_enabled && !is_feed() );

        if ($do_carousel){
            $gallery_loop = new LoopsNSlides_Gallery_Instance();
            $gallery_loop->load_gallery_attributes($attr,$post);
            $output = $gallery_loop->get_loop_render();
            return $output;

        }

        return $output;
        
    }
}
