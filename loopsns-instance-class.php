<?php

class LoopsNSlides_Instance {
    var $id = null;
    var $unique_id = null;
    var $query_args = null;
    var $carousel_args = null;
    var $query = null;
    var $template = null;
    var $is_carousel = null;
    var $options = array();
    function __construct($post_id = null){
        $this->id = $post_id;
        $this->unique_id = uniqid(); //in case we don't have a post ID; useful for JS
    }
    
    public static function get_defaults($keys = null){
        $defaults = array(
            'id' => null,
            'query_args' => loopsns()->get_options('query_args'),
            'carousel_args' => loopsns()->get_options('carousel_args'),
            'template' => loopsns()->get_options('template'),
            
        );
        return loopsns_get_array_value($keys,$defaults);
    }
    
    function is_carousel(){
        if ($this->is_carousel === null){
            $this->is_carousel = (bool)get_post_meta( $this->id, LoopsNSlides_Posts_Loop::$carousel_metakey, true );
        }
        return $this->is_carousel;
    }

    function get_template(){
        $template = null;

        if ( $filename = get_post_meta( $this->id, LoopsNSlides_Posts_Loop::$template_metakey, true ) ){

            $file = loopsns_locate_template( $filename );
            if ( !is_wp_error(LoopsNSlides_Posts_Loop::is_loop_template($file) ) ){
                $template = $file;
            }
            
        }

        //default
        if (!$template){
            $template = static::get_defaults('template');
        }

        return $template;
    }
    
    function get_query_args(){
        if ($this->query_args === null){
            $meta = get_post_meta( $this->id, LoopsNSlides_Posts_Loop::$qargs_metakey, true );
            $this->query_args = $meta ? $meta : $this->get_defaults('query_args');
        }
        return $this->query_args;
    }
    
    function get_carousel_args(){
        if ($this->carousel_args === null){
            $meta = get_post_meta( $this->id, LoopsNSlides_Posts_Loop::$cargs_metakey, true );
            $this->carousel_args = $meta ? $meta : $this->get_defaults('carousel_args');
        }
        return $this->carousel_args;
    }
    
    function get_query(){

        if ($this->query === null){
            if ( $args = $this->get_query_args() ){
                if ( !is_array($args) ){
                    $this->query = false;
                    return new WP_Error( 'loopsns_missing_query_args', __('Query args should be an array.','loopsns') );
                }
            }else{
                $this->query = false;
                return new WP_Error( 'loopsns_missing_query_args', __('Query args missing.','loopsns') );
            }
            
            $this->query = new WP_Query($args);
            loopsns()->debug_log($GLOBALS['wp_query']->request,'LoopsNSlides_Instance::get_query');
        }

        return $this->query;
    }
    
    function setup_loop(){
        global $wp_query;
        global $loopsns_loop;
        
        $loopsns_loop = $this; //setup global
        $query = $this->get_query(); //override WP Query

        if ( is_wp_error($query) ) return $query;
        
        $wp_query = $query;
        
        return true;
    }
    function reset_loop(){
        wp_reset_query();
        $loopsns_loop = null;
    }
    
    function get_classes(){
        
        $template = $this->get_template();
        $template_parts = pathinfo($template);
        
        $classes = array(
            'loopsns-loop',
            'loopsns-loop-template-' . sanitize_title( $template_parts['filename'] )
        );
        
        if ( $this->is_carousel() ){
            $classes[] = 'loopsns-carousel';
            $classes[] = 'owl-carousel';
            $classes[] = 'owl-theme';
        }
        
        return apply_filters('loopsns_get_loop_classes',$classes,$this);
    }
    
    function maybe_setup_carousel(){
        /* carousel */
        if ( !$this->is_carousel() ) return;

        ob_start();
        ?>
        jQuery('[data-loopsns-loop-id="<?php echo $this->unique_id;?>"]').owlCarousel(<?php echo json_encode($this->get_carousel_args());?>);
        <?php
        $inline = ob_get_clean();
        
        wp_add_inline_script('jquery.owlcarousel', $inline);

    }

    function get_loop_render(){

        if ($this->id){
            if ( get_post_type($this->id) !== LoopsNSlides_Posts_Loop::$loop_post_type ){
                $this->id = null;
                return new WP_Error( 'loopsns_not_loop_type', __('This is not a loop post.','loopsns') );
            }
        }

        $init = $this->setup_loop();
        
        //handle errors
        if ( is_wp_error($init) ) {
            
            $notice = sprintf('<strong>%s</strong> %s <small>%s</small>',__('Cannot display Loop:','loopsns'),$init->get_error_message(),__("This notice won't appear for visitors.",'loopsns'));
            
            $post_type = get_post_type($this->id);
            $post_type_obj = get_post_type_object( $post_type );
            $required_cap = $post_type_obj->cap->edit_posts;
            $can_edit = current_user_can($required_cap,$this->id);
            if ( is_admin() ){
                
            }
            //display debug if user can edit post
            if ( $can_edit ){
                return sprintf('<p class="loopsns-notice">%s</p>',$notice);
            }
        }
        
        $template = $this->get_template();

        $this->maybe_setup_carousel();
        
        ob_start();
        load_template( $template, false );
        $content = ob_get_clean();

        $this->reset_loop();
        
        //container
        $attr = array(
            'id' => ($this->id) ? 'loopsns-loop-' . $this->id : null,
            'data-loopsns-loop-id' => $this->unique_id,
            'class' => implode(' ',$this->get_classes()),
        );
        $attr = apply_filters('loopsns_get_loop_attributes',$attr,$this);
        $attr = array_filter($attr);
        $attr_str = ($attr) ? loopsns_get_html_attr($attr) : null;

        return sprintf('<div %s>%s</div>',$attr_str,$content);
        
    }

}

class LoopsNSlides_Gallery_Instance extends LoopsNSlides_Instance{
    
    var $gallery_atts = array();
    
    public static function get_defaults($keys = null){
        $parent_defaults = parent::get_defaults();
        $defaults = array(
            'carousel_args' => loopsns()->get_options('gallery_carousel_args'),
            'template' => loopsns()->get_options('gallery_template'),
        );
        $defaults = wp_parse_args($defaults,$parent_defaults);
        return loopsns_get_array_value($keys,$defaults);
    }
    
    function load_gallery_attributes($attr = array(),$post = null){
        //default atts - copyied from core function gallery_shortcode()
        $html5 = current_theme_supports( 'html5', 'gallery' );
        $atts = shortcode_atts( array(
            'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
            'id'         => $post ? $post->ID : 0,
            'itemtag'    => $html5 ? 'figure'     : 'dl',
            'icontag'    => $html5 ? 'div'        : 'dt',
            'captiontag' => $html5 ? 'figcaption' : 'dd',
            'columns'    => 3,
            'size'       => 'thumbnail',
            'include'    => '',
            'exclude'    => '',
            'link'       => ''
        ), $attr, 'gallery' );
        
        $this->gallery_atts = $atts;
        $this->query_args = $this->get_gallery_query_atts();
    }
    /*
    Get Query for the gallery.
    Adapted from core function gallery_shortcode().
    */
    function get_gallery_query_atts(){
        
        $atts = $this->gallery_atts;
        $qargs = array();
        
        $id = intval( $atts['id'] );

        if ( ! empty( $atts['include'] ) ) {
            $qargs = array(
                'post__in' => explode(',',$atts['include']),
                'post_status' => 'inherit',
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'order' => $atts['order'],
                'orderby' => $atts['orderby']
            );

        } elseif ( ! empty( $atts['exclude'] ) ) {
            $qargs = array( 
                'post__not_in' => explode(',',$atts['exclude']), 
                'post_status' => 'inherit', 
                'post_type' => 'attachment', 
                'post_mime_type' => 'image', 
                'order' => $atts['order'], 
                'orderby' => $atts['orderby'] 
            );
        } else {
            $qargs = array( 
                'post_parent' => $id, 
                'post_status' => 'inherit', 
                'post_type' => 'attachment', 
                'post_mime_type' => 'image', 
                'order' => $atts['order'], 
                'orderby' => $atts['orderby'] 
            );
        }
        loopsns()->debug_log($qargs,'LoopsNSlides_Gallery_Instance::get_gallery_query_atts');
        return $qargs;
    }

    function is_carousel(){
        return true;
    }
    
    function get_classes(){
        $classes = parent::get_classes();
        $classes[] = 'loopsns-gallery';
        return $classes;
    }
}


?>