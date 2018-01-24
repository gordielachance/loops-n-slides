<?php
/*
Handles what is relative to the loopsns-loop post type
*/

class LoopsNSlides_Posts_Loop{

    static $shortcode_slug = 'loops-n-slides';
    static $loop_post_type = 'loopsns-loop';
    static $qargs_metakey = 'loopsns-query-args';
    static $cargs_metakey = 'loopsns-carousel-args';
    static $carousel_metakey = 'loopsns-carousel';
    static $template_metakey = 'loopsns-template';

    function __construct() {
        add_action('init', array($this,'init_loop_post_type') );
        add_action( sprintf('manage_edit-%s_columns',self::$loop_post_type), array($this,'loops_listing_columns') );
        add_action( 'manage_posts_custom_column', array($this,'loops_listing_columns_content') );
        add_action( 'current_screen',  array($this, 'populate_single_loop_backend'));
        add_action( 'add_meta_boxes', array($this,'register_loop_metaboxes') );
        add_action( 'save_post', array( $this, 'save_query_metabox' ), 10, 2 );
        add_action( 'edit_form_after_title', array( $this, 'after_title_text' ) );
    }

    /**
     * Initilize the plugin
     */
    function init_loop_post_type() {
        $this->register_loop_post_type();
        add_shortcode(self::$shortcode_slug, array($this,'register_shortcode') );
    }

    function register_loop_post_type(){
        $labels = array(
            'name'               => _x( 'Loops', 'post type general name', 'loopsns' ),
            'singular_name'      => _x( 'Loop', 'post type singular name', 'loopsns' ),
            'menu_name'          => _x( 'Loops', 'admin menu', 'loopsns' ),
            'name_admin_bar'     => _x( 'Loop', 'add new on admin bar', 'loopsns' ),
            'add_new'            => _x( 'Add New', 'loop', 'loopsns' ),
            'add_new_item'       => __( 'Add New Loop', 'loopsns' ),
            'new_item'           => __( 'New Loop', 'loopsns' ),
            'edit_item'          => __( 'Edit Loop', 'loopsns' ),
            'view_item'          => __( 'View Loop', 'loopsns' ),
            'all_items'          => __( 'All Loops', 'loopsns' ),
            'search_items'       => __( 'Search Loops', 'loopsns' ),
            'parent_item_colon'  => __( 'Parent Loops:', 'loopsns' ),
            'not_found'          => __( 'No loops found.', 'loopsns' ),
            'not_found_in_trash' => __( 'No loops found in Trash.', 'loopsns' )
        );

        register_post_type(self::$loop_post_type, array(
            'public' => true,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'labels' => $labels,
            'capability_type' => 'post',
            'supports' => array('title'),
            'show_in_menu'    => loopsns()->menu_slug,
        ));
    }

    /**
     * Add custom column filters in administration
     * @param array $columns
     */
    function loops_listing_columns($columns) {
        $col = array(
            'loopsns-loop-shortcode' => __('Shortcode','loopsns')
        );
        $columns = array_slice($columns, 0, 2) + $col + array_slice($columns, 2, null);

        return $columns;
    }
    
    /**
     * Add custom column contents in administration
     * @param string $columnName
     */
    function loops_listing_columns_content($columnName) {
        global $post;
        if ($columnName == 'loopsns-loop-shortcode') {
            ?>
            <code><?php printf('[loopsns id=%s]',$post->ID);?></code>
            <?php
            
        }
    }
    
    static function is_single_loop_admin(){
        $screen = get_current_screen();
        
        if ( ( $screen->base == 'post' ) && ( $screen->post_type == self::$loop_post_type )  ){
            return true;
        }
    }

    function populate_single_loop_backend(){
        global $post;
        global $loopsns_loop;
        
        if ( self::is_single_loop_admin()  ){
            $post_id = isset($_GET['post']) ? $_GET['post'] : null;
            //set global
            $loopsns_loop = new LoopsNSlides_Instance($post_id);
        }
    }
    
    function register_loop_metaboxes(){
        add_meta_box( 'loopsns-editor', __( 'Loop Editor', 'loopsns' ), array($this,'metabox_loop_editor_content'), self::$loop_post_type );
        add_meta_box( 'loopsns-preview', __( 'Loop Preview', 'loopsns' ), array($this,'metabox_loop_preview_content'), self::$loop_post_type );
    }
    
    function after_title_text(){
        global $loopsns_loop;
        
        if ( !self::is_single_loop_admin()  ) return;
        
        ?>
        <h2><?php _e('Shortcode','loopsns');?></h2>
        <p>
            <?php _e('To use the shortcode, copy/paste it in the post or page where you want to display the loop.','loopsns');?>
        </p>
        <p>
            <code><?php printf('[loopsns id=%s]',$loopsns_loop->id);?></code>
        </p>
        <?php
    }
    
    function metabox_loop_editor_content( $post ){
        global $loopsns_loop;
        
        $json_qargs = ($qargs = $loopsns_loop->get_query_args()) ? json_encode($qargs) : null;
        
        //

        $default_cargs = loopsns()->options['default-carousel-options'];
        $cargs = loopsns()->options_default['default-carousel-options'];
        if ($default_cargs == $cargs) unset($cargs);
        
        $json_default_cargs = $default_cargs ? json_encode($default_cargs) : null;
        $json_cargs = $cargs ? json_encode($cargs) : null;
        
        //
        
        $default_gallery_cargs = loopsns()->options['default-gallery-carousel-options'];
        $gallery_cargs = loopsns()->options_default['default-gallery-carousel-options'];
        if ($default_gallery_cargs == $gallery_cargs) unset($gallery_cargs);
        
        $json_default_gallery_cargs = $default_gallery_cargs ? json_encode($default_gallery_cargs) : null;
        $json_gallery_cargs = $gallery_cargs ? json_encode($gallery_cargs) : null;

        ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row">
                        <label for="loop-query"><?php _e('Query','loopsns');?></label>
                    </th>
                    <td>
                        <p>
                            <textarea class="loopsns-json fullwidth" id="loop-query" placeholder="<?php echo esc_textarea(json_encode(self::get_query_placeholder()));?>" name="loopsns_qargs_json" class="fullwidth"><?php echo esc_textarea($json_qargs);?></textarea>
                        </p>
                        <p>
                            <?php _e('Json-encoded array of query parameters that will be used to fetch the loop items.','loopsns');?>
                            <br/>
                            <?php _e('This requires a little work for newbies but allows unlimited possiblities!','loopsns');?>
                        </p>
                        <p>
                            <?php
                                $example_link = sprintf('<a href="%s" target="_blank">%s</a>','http://jsoneditoronline.org/?id=ce5bd86606f0c4f283bc80939613c37b',__('here','loopsns'));
                                printf(__('See an example and edit it %s.','loopsns'),$example_link);
                            ?>  <?php
                            $codex_url = 'https://codex.wordpress.org/Class_Reference/WP_Query#Parameters';
                            $codex_link = sprintf('<a href="%s" target="_blank">%s</a>',$codex_url,__('Wordpress Codex','loopsns'));
                            printf(__('See the full list of available parameters on the %s.','loopsns'),$codex_link);
                            ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="loop_template"><?php _e('Loop Template','loopsns');?></label>
                    </th>
                    <td>
                        <p>
                            <select id="loop-template" name="loopsns_template">
                                <?php
                                foreach ( $this->get_loop_templates() as $title => $file ) {
                                    $filename = basename( $file );
                                    $selected_attr = selected( $file, $loopsns_loop->template );
                                    printf('<option value="%s" %s>%s</option>',esc_attr( $filename ),$selected_attr,$title);
                                }
                                ?>
                            </select>
                        </p>
                        <p>
                            <?php printf(__('You can add your own templates by creating a %s directory in your theme.','loopsns'),'<code>/loopsns</code>');?>
                        </p>
                        <p>
                            <?php _e('The templates that you create in this directory should have an opening PHP comment at the top of the file that states the template’s name:','loopsns');?><br/>
                            <code>/* Loops 'n Slides Loop: Example Template */</code>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="loop_template"><?php _e('Carousel','loopsns');?></label>
                    </th>
                    <td>
                        <?php
                        $checked_attr = checked( $loopsns_loop->is_carousel(), true, false );
                        $owl_url = 'https://owlcarousel2.github.io/OwlCarousel2/';
                        $owl_link = sprintf('<a href="%s" target="_blank">Owl Carousel</a>',$owl_url);
                        $desc = sprintf(__('Enable %s for this loop.','loopsns'),$owl_link);
                        printf(__('<input type="checkbox" id="loopsns-carousel" name="loopsns_carousel" value="on" %s> %s'),$checked_attr,$desc);
        
                        ?>
                        <p
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="carousel_options"><?php _e('Carousel options','loopsns');?></label>
                    </th>
                    <td>
                        <p>
                            <textarea class="loopsns-json fullwidth" id="carousel-options" placeholder="<?php echo esc_textarea($json_default_cargs);?>" name="loopsns_cargs_json" class="fullwidth"><?php echo esc_textarea($json_cargs);?></textarea>
                        </p>
                        <p>
                            <?php _e('Json-encoded array of options for the carousel.','loopsns');?>
                        </p>
                        <p>
                            <?php
                            /*
                            $example_link = sprintf('<a href="%s" target="_blank">%s</a>','http://jsoneditoronline.org/?id=ce5bd86606f0c4f283bc80939613c37b',__('here','loopsns'));
                            printf(__('See an example and edit it %s.','loopsns'),$example_link);
                            */
                            $url = 'https://owlcarousel2.github.io/OwlCarousel2/docs/api-options.html';
                            $codex_link = sprintf('<a href="%s" target="_blank">%s</a>',$url,__('full list of available parameters','loopsns'));
                            printf(__('See the %s for Owl Carousel.','loopsns'),$codex_link);
                            ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        // Add nonce for security and authentication.
        wp_nonce_field( 'loopsns_main_metabox', 'loopsns_main_metabox_nonce' );
        
    }
    
    function metabox_loop_preview_content( $post) {
        global $loopsns_loop;
        
        ?>
        <p class="loopsns-notice">
            <?php echo _e("The look of the loop might be slightly different frontend.",'loopsns');?>
        </p>
        <?php
        
        echo $loopsns_loop->get_loop_render();
    }
    
    static function carousel_styles_scripts(){
        
        //CSS
        wp_register_style('loopsns-loop', loopsns()->plugin_url . '_inc/css/loopsns-loop.css',null,loopsns()->version);
        wp_enqueue_style('loopsns-loop');
        
        //JS
        wp_register_script('loopsns-loop', loopsns()->plugin_url . '_inc/js/loopsns-loop.js',array('jquery'),loopsns()->version,true);
        wp_enqueue_script('loopsns-loop');
        
        wp_register_style('jquery.owlcarousel-theme', '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.1/assets/owl.theme.default.min.css','2.2.1');
        wp_register_style('jquery.owlcarousel', '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.1/assets/owl.carousel.min.css',array('jquery.owlcarousel-theme'),'2.2.1');
        wp_register_script('jquery.owlcarousel', '//cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.2.1/owl.carousel.min.js',array('jquery'),'2.2.1',true);
        //TO FIX enqueue only with shortcode ? Is this possible ?
        wp_enqueue_script('jquery.owlcarousel');
        wp_enqueue_style('jquery.owlcarousel');
    }

    static function get_query_placeholder(){
        return array(
            'post_type' =>      LoopsNSlides_Posts_Slide::$slide_post_type,
            'posts_per_page' => 5
        );
    }

    function save_query_metabox($post_id, $post){
        //check save status
        $is_autosave = ( ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || wp_is_post_autosave($post_id) );
        $is_autodraft = ( get_post_status( $post_id ) == 'auto-draft' );
        $is_revision = wp_is_post_revision( $post_id );
        $is_valid_nonce = ( isset($_POST['loopsns_main_metabox_nonce']) && wp_verify_nonce( $_POST['loopsns_main_metabox_nonce'], 'loopsns_main_metabox' ) );
        if ( !$is_valid_nonce || $is_autodraft || $is_autosave || $is_revision ) return;
        
        /*query args*/
        $qargs_json = ( isset($_POST[ 'loopsns_qargs_json' ]) ) ? stripslashes_deep($_POST[ 'loopsns_qargs_json' ]) : null;
        $qargs = json_decode($qargs_json,true);

        if (!$qargs){
            delete_post_meta( $post_id, self::$qargs_metakey );
        }else{
            update_post_meta( $post_id, self::$qargs_metakey, $qargs );
        }
        
        /*template*/
        $template = ( isset($_POST[ 'loopsns_template' ]) ) ? $_POST[ 'loopsns_template' ] : null;
        if (!$template){
            delete_post_meta( $post_id, self::$template_metakey );
        }else{
            update_post_meta( $post_id, self::$template_metakey, $template );
        }
        
        /*carousel*/
        $is_carousel = $_POST['loopsns_carousel'];
        if ($is_carousel == 'on'){
            update_post_meta( $post_id, self::$carousel_metakey, true );
        }else{
            delete_post_meta( $post_id, self::$carousel_metakey );
        }

        /*carousel args*/
        $cargs_json = ( isset($_POST[ 'loopsns_cargs_json' ]) ) ? stripslashes_deep($_POST[ 'loopsns_cargs_json' ]) : null;
        $cargs = json_decode($cargs_json,true);

        if (!$cargs){
            delete_post_meta( $post_id, self::$cargs_metakey );
        }else{
            update_post_meta( $post_id, self::$cargs_metakey, $cargs );
        }
        
    }

    /**
     * Plugin main function
     * @param type $atts Owl parameters
     * @param type $content
     * @return string Owl HTML code
     */
    function register_shortcode($atts, $content = null) {

        $carousel = null;
        $attributes = array();
        
        $defaults = array('id'=>null);
        $atts = shortcode_atts($defaults,$atts,self::$shortcode_slug);

        $loop = new LoopsNSlides_Instance($atts['id']);
        $html = $loop->get_loop_render();
        if (is_wp_error($html) ){
            return $html->get_error_message();
        }else{
            return $html;
        }
    }

    /**
     * Get all the available Loop Templates
     * @return array Loop templates
     */
    static function get_loop_templates() {
            $dirs = array();
            $loop_templates = $potential_templates = array();

            //templates priority : the first directory from the array have the highest priority.
            //this means that child templates will override parent templates which will override default templates.

            $dirs[] =   get_stylesheet_directory(); //child theme path
            $dirs[] =   get_template_directory(); //parent theme path
            $dirs =     apply_filters( 'loopsns_templates_directories' , $dirs ); //allow plugins to add template paths
            $dirs[] =   loopsns()->templates_dir; //the loops templates path

            $dirs = array_unique( $dirs );

            foreach( (array) $dirs as $dir ){

                $files = (array) glob( trailingslashit( $dir ) . "*.php" );

                foreach ( $files as $template ) {

                    $filename = basename( $template );

                    if( in_array( $template , $loop_templates ) ) continue; //for priority

                    if( is_wp_error($template ) ) continue;
                    
                    $template_name = self::get_loop_template_name($template);

                    if ( !is_wp_error($template_name) ){
                        $loop_templates[$template_name] = $template;
                    }
                }

            }

        return $loop_templates;
    }
    
    /**
     * Check if a template file is a loop template and corresponds to the specified object type
     * @param string $file Template file name
     * @return string Template name
     */
    static function is_loop_template($file) {
        return self::get_loop_template_name($file);
    }
    
    static function get_loop_template_name($file){
        
        if ( !file_exists($file) ){
            return new WP_Error('loopsns-template',sprintf(__('The template file %s does not exists.','loopsns'),'<em>' . $file .'</em>') );
        }

        $data = get_file_data( $file, array(
            'name'    => "Loops 'n Slides Loop",
        ) );

        $template_name    = trim( $data['name'] );

        if ( empty( $template_name ) ){
            return new WP_Error('loopsns-template',sprintf(__("The file %s is not a Loops 'n slides template.",'loopsns'),'<em>' . $file .'</em>') );
        } 

        return $template_name;
    }
    
}
