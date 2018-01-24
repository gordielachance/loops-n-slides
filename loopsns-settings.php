<?php
/*
Handles what is relative to the loopsns-loop post type
*/

class LoopsNSlides_Settings{

    function __construct() {
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ), 8 );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
    }
    
    function create_admin_menu(){
        //http://wordpress.stackexchange.com/questions/236896/remove-or-move-admin-submenus-under-a-new-menu/236897#236897

        /////Create our custom menu

        $menu_page = add_menu_page( 
            __( "Loops 'n Slides", 'loopsns' ), //page title
            __( "Loops 'n Slides", 'loopsns' ), //menu title
            'manage_options', //capability //TO FIX TO CHECK
            loopsns()->menu_slug,
            array($this,'settings_page'), //this function will output the content of the 'Music' page.
            'dashicons-images-alt' // an image would be 'plugins_url( 'myplugin/images/icon.png' )'; but for core icons, see https://developer.wordpress.org/resource/dashicons 
        );
        
        //create a submenu page that has the same slug so we don't have the menu title name for the first submenu page, see http://wordpress.stackexchange.com/questions/66498/add-menu-page-with-different-name-for-first-submenu-item

        add_submenu_page(
            loopsns()->menu_slug,
            __( "Loops 'n Slides Settings", 'loopsns' ),
            __( 'Settings' ),
            'manage_options',
            loopsns()->menu_slug, // same slug than the menu page
            array($this,'settings_page') // same output function too
        );
    }

    function settings_sanitize( $input ){
        $new_input = array();

        if( isset( $input['reset_options'] ) ){
            
            $new_input = loopsns()->options_default;
            
        }else{ //sanitize values
            
            //gallery default
            $new_input['gallery-carousel'] = ( isset($input['gallery-carousel']) ) ? 'on' : 'off';
            
            //loops carousel defaults
            $carousel_defaults_json = ( isset($_POST[ 'default-carousel-options-json' ]) ) ? stripslashes_deep($_POST[ 'default-carousel-options-json' ]) : null;
            $new_input['default-carousel-options'] = json_decode($carousel_defaults_json,true);
            
            //gallery carousel defaults
            $gallery_carousel_defaults_json = ( isset($_POST[ 'default-gallery-carousel-options-json' ]) ) ? stripslashes_deep($_POST[ 'default-gallery-carousel-options-json' ]) : null;
            $new_input['default-gallery-carousel-options'] = json_decode($gallery_carousel_defaults_json,true);

        }

        return $new_input;
        
        
    }

    function settings_init(){

        register_setting(
            'loopsns-option-group', // Option group
            loopsns()->meta_name_options, // Option name
            array( $this, 'settings_sanitize' ) // Sanitize
         );
        
        /*
        Loops
        */
        add_settings_section(
            'loop-settings', // ID
            __('Loops','loopsns'), // Title
            array( $this, 'section_desc_empty' ), // Callback
            'loopsns-settings-page' // Page
        );
        add_settings_field(
            'loop-carousel-options', 
            __('Default Carousel options','wpsstm'), 
            array( $this, 'loop_carousel_options_callback' ), 
            'loopsns-settings-page', 
            'loop-settings'
        );

        /*
        Gallery
        */
        add_settings_section(
            'gallery-carousel-settings', // ID
            __('Carousel for galleries','loopsns'), // Title
            array( $this, 'section_desc_gallery_support' ), // Callback
            'loopsns-settings-page' // Page
        );
        
        add_settings_field(
            'gallery-carousel', 
            __('Default','wpsstm'), 
            array( $this, 'gallery_carousel_callback' ), 
            'loopsns-settings-page', 
            'gallery-carousel-settings'
        );
        
        add_settings_field(
            'gallery-carousel-options', 
            __('Default Options','wpsstm'), 
            array( $this, 'gallery_carousel_options_callback' ), 
            'loopsns-settings-page', 
            'gallery-carousel-settings'
        );
        
        /*
        System
        */

        add_settings_section(
            'settings_system', // ID
            __('System','wpsstm'), // Title
            array( $this, 'section_desc_empty' ), // Callback
            'loopsns-settings-page' // Page
        );
        
        add_settings_field(
            'reset_options', 
            __('Reset Options','wpsstm'), 
            array( $this, 'reset_options_callback' ), 
            'loopsns-settings-page', // Page
            'settings_system'//section
        );
    }
    
    function section_desc_empty(){
        
    }
    
    function loop_carousel_options_callback(){
        $default_cargs = loopsns()->options['default-carousel-options'];
        $cargs = loopsns()->options_default['default-carousel-options'];
        
        if ($default_cargs == $cargs) unset($cargs);
        
        $json_default_cargs = $default_cargs ? json_encode($default_cargs) : null;
        $json_cargs = $cargs ? json_encode($cargs) : null;
        ?>
        <p>
            <textarea class="loopsns-json fullwidth" placeholder="<?php echo esc_textarea($json_default_cargs);?>" name="default-carousel-options-json" class="fullwidth"><?php echo esc_textarea($json_cargs);?></textarea>
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
        <?php
    }
    
    function section_desc_gallery_support(){
        ?>
        <p>
        <?php _e("Loops 'n Slides can convert your Wordpress galleries to carousels.",'loopsns');?>
        <br/>
        <?php printf(__("You can enable this by default; or enable it gallery-per-gallery by adding the attribute %s to a gallery shortcode.",'loopsns'),'<code>loopsns-carousel=1</code>');?>
        </p>
        <?php
    }
    
    function gallery_carousel_callback(){
        $option = loopsns()->options['gallery-carousel'];

        printf(
            '<input type="checkbox" name="%1$s[gallery-carousel]" value="on" %2$s /><span>%3$s</span>',
            loopsns()->meta_name_options,
            checked($option, 'on', false),
            __('Enabled','loopsns')
        );
    }
    function gallery_carousel_options_callback(){
        $default_cargs = loopsns()->options['default-gallery-carousel-options'];
        $cargs = loopsns()->options_default['default-gallery-carousel-options'];
        if ($default_cargs == $cargs) unset($cargs);
        
        $json_default_cargs = $default_cargs ? json_encode($default_cargs) : null;
        $json_cargs = $cargs ? json_encode($cargs) : null;
        
        ?>
        <p>
            <textarea class="loopsns-json fullwidth" placeholder="<?php echo esc_textarea($json_default_cargs);?>" name="default-gallery-carousel-options-json" class="fullwidth"><?php echo esc_textarea($json_cargs);?></textarea>
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
        <?php
    }
    
    function reset_options_callback(){
        printf(
            '<input type="checkbox" name="%s[reset_options]" value="on"/> %s',
            loopsns()->meta_name_options,
            __("Reset options to their default values.","loopsns")
        );
    }
    
	function settings_page() {
        ?>
        <div class="wrap">
            <h2><?php _e("Loops 'n Slides Settings",'wpsstm');?></h2>  
            
            <?php

            settings_errors('loopsns-option-group');

            ?>
            <form method="post" action="options.php">
                <?php

                // This prints out all hidden setting fields
                settings_fields( 'loopsns-option-group' );   
                do_settings_sections( 'loopsns-settings-page' );
                submit_button();

                ?>
            </form>

        </div>
        <?php
	}
    
}
