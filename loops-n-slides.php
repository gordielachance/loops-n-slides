<?php
/*
Plugin Name: Loops 'n Slides
Description: A simple yet powerful plugin that allows you to build custom posts loops and display them using a shortcode; eventually as a carousel of slides.
Plugin URI: https://github.com/gordielachance/loops-n-slides
Author: G.Breant
Author URI: https://profiles.wordpress.org/grosbouff/#content-plugins
Version: 1.1.2
License: GPL2
*/

class LoopsNSlides_Core {
    /** Version ***************************************************************/
    /**
    * @public string plugin version
    */
    public $version = '1.1.2';
    /**
    * @public string plugin DB version
    */
    public $db_version = 100;
    /** Paths *****************************************************************/
    public $file = '';
    /**
    * @public string Basename of the plugin directory
    */
    public $basename = '';
    /**
    * @public string Absolute path to the plugin directory
    */
    public $plugin_dir = '';
    public $templates_dir = '';
    public $plugin_url = '';
    
    public $meta_name_options = 'loops-n-slides-options';
    public $menu_slug = 'loops-n-slides';

    /**
    * @var The one true Instance
    */
    static $instance;

    public static function instance() {
        
            if ( ! isset( self::$instance ) ) {
                    self::$instance = new LoopsNSlides_Core;
                    self::$instance->includes();
                    self::$instance->setup_globals();
                    self::$instance->setup_actions();
            }
            return self::$instance;
    }
    
    /**
        * A dummy constructor to prevent plugin from being loaded more than once.
        *
        * @since bbPress (r2464)
        * @see bbPress::instance()
        * @see bbpress();
        */
    function __construct() { /* Do nothing here */ }
    
    function setup_globals() {
        
        /** Paths *************************************************************/
        $this->file       = __FILE__;
        $this->basename   = plugin_basename( $this->file );
        $this->plugin_dir = plugin_dir_path( $this->file );
        $this->templates_dir = trailingslashit( $this->plugin_dir . 'templates' ); 
        $this->plugin_url = plugin_dir_url ( $this->file );
        
        $this->options_default = array(
            'query_args'             => array(
                'post_type' =>      LoopsNSlides_Posts_Slide::$slide_post_type,
                'posts_per_page' => -1
            ),
            'carousel_args'  => array(
                'items' =>  3,
                'loop' =>   true,
                'autoplay' => true,
                'animateOut' => 'fadeOut'
            ),
            'template' => 'loop-list.php',
            
            'enable_gallery_carousels'          => 'on',
            'gallery_carousel_args'  => array(
                'items' =>  1,
                'loop' =>   true,
                'autoplay' => true,
                'animateOut' => 'fadeOut'
            ),
            'gallery_template' => 'loop-gallery.php'
        );
        
        $this->options = wp_parse_args(get_option( $this->meta_name_options), $this->options_default);

    }
    
    public function get_options($keys = null){
        return loopsns_get_array_value($keys,$this->options);
    }
    public function get_defaults($keys = null){
        return loopsns_get_array_value($keys,$this->options_default);
    }
    
    function includes(){
        require_once($this->plugin_dir . 'loopsns-functions.php');
        require_once($this->plugin_dir . 'loopsns-instance-class.php');
        require_once($this->plugin_dir . 'loopsns-post-loop-class.php');
        require_once($this->plugin_dir . 'loopsns-post-slide-class.php');
        require_once($this->plugin_dir . 'loopsns-gallery-class.php');
        require_once($this->plugin_dir . 'loopsns-settings.php');
    }
    
    function setup_actions(){

        add_action( 'plugins_loaded', array($this, 'upgrade') );
        add_action( 'admin_init', array($this,'load_textdomain') );

        add_action( 'wp_enqueue_scripts', array($this,'scripts_styles') );
        add_action( 'admin_enqueue_scripts', array($this,'admin_scripts_styles') );

        new LoopsNSlides_Settings();
        new LoopsNSlides_Posts_Loop(); //loop post type
        new LoopsNSlides_Posts_Slide(); //slide post type
        new LoopsNSlides_Gallery(); //galleries stuff

    }
    
    function load_textdomain() {
        load_plugin_textdomain( 'loopsns', false, $this->plugin_dir . '/languages' );
    }
    
    function upgrade(){
        global $wpdb;

        $current_version = get_option("_loopsns-db_version");

        if ($current_version==$this->db_version) return false;
        if(!$current_version){ //install

            

        }else{ //upgrade

            /*
            if ($current_version < 053){
            }
            */

        }
        
        //update DB version
        update_option("_loopsns-db_version", $this->db_version );
    }
    
    /**
     * List of JavaScript / CSS files for admin
     */
    function admin_scripts_styles() {
        
        if ( !$this->is_loopsnslides_admin() ) return;
        
        //JSON VIEWER
        wp_register_script('jquery.json-viewer', $this->plugin_url . '_inc/js/jquery.json-viewer/jquery.json-viewer.js',array('jquery')); //TOFIX version
        wp_register_style('jquery.json-viewer', $this->plugin_url . '_inc/js/jquery.json-viewer/jquery.json-viewer.css',null); //TOFIX version
        
        //CSS
        wp_register_style('loopsns-admin', $this->plugin_url . '_inc/css/loopsns-admin.css',array('jquery.json-viewer'),$this->version);
        wp_enqueue_style('loopsns-admin');

        //JS
        
        wp_register_script('loopsns-admin', $this->plugin_url . '_inc/js/loopsns-admin.js',array('jquery.json-viewer','jquery-ui-tabs'),$this->version);

        wp_enqueue_script('loopsns-admin');
        
        if ( LoopsNSlides_Posts_Loop::is_single_loop_admin() ){ //for loops preview
            LoopsNSlides_Posts_Loop::carousel_styles_scripts();
        }
    }

    /**
     * List of JavaScript / CSS files for frontend
     */
    function scripts_styles() {
        //TO FIX TO CHECK conditionnal embed only if shortcode has run ?
        LoopsNSlides_Posts_Loop::carousel_styles_scripts();
    }
    
    function is_loopsnslides_admin(){
        
        if ( !is_admin() ) return;

        $screen = get_current_screen();
        $post_type = $screen->post_type;
        $allowed_post_types = array(
            LoopsNSlides_Posts_Loop::$loop_post_type,
            LoopsNSlides_Posts_Slide::$slide_post_type
        );

        $is_allowed_post_type =  ( in_array($post_type,$allowed_post_types) );
        $is_top_menu = ($screen->id == 'toplevel_page_loops-n-slides');

        if (!$is_allowed_post_type && !$is_top_menu) return;

        return true;
    }
    
    public function debug_log($message,$title = null) {

        if (WP_DEBUG_LOG !== true) return false;

        $prefix = '[wpsstm] ';
        if($title) $prefix.=$title.': ';

        if (is_array($message) || is_object($message)) {
            error_log($prefix.print_r($message, true));
        } else {
            error_log($prefix.$message);
        }
    }
    
}

function loopsns() {
	return LoopsNSlides_Core::instance();
}

loopsns();
