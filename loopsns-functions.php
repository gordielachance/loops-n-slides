<?php

//https://stackoverflow.com/questions/18081625/how-do-i-map-an-associative-array-to-html-element-attributes
/*
Make a string from an array of attributes
*/
function loopsns_get_html_attr($arr=null){
    $str = null;
    $arr = (array)$arr;
    
    //attributes with values
    if (!empty($arr) ){
        $arr = (array)$arr;
        $str .= join(' ', array_map(function($key) use ($arr){
           if(is_bool($arr[$key])){
              return $arr[$key]?$key:'';
           }
           return $key.'="'.$arr[$key].'"';
        }, array_keys($arr)));
    }

    return $str;
}

/*
Locate a template & fallback in plugin's folder
*/
function loopsns_locate_template( $template_name, $load = false, $require_once = true ) {

    if ( !$located = locate_template( 'loops-n-slides/' . $template_name ) ) { //get from theme directory
        $located = loopsns()->templates_dir . $template_name; //get from directory 'templates' in plugin
    }
    
    if ( $load && ('' != $located) ){
        load_template( $located, $require_once );
    }
    
    return $located;
}

function loopsns_json_container($name,$args=null,$defaults=null){
    
    $defaults = null;

    if ($args && !loopsns_is_json($args) ) $args = json_encode($args);
    if ($defaults && !loopsns_is_json($defaults) ) $defaults = json_encode($defaults);
    if ($args == $defaults) $args= '';
    
    ?>
    <div class="loopsns-json-container">
        <ul class="loopsns-json-tabs">
            <li><a href="#<?php echo $name;?>-json-view" class="button"><?php _e('View','loopsns');?></a></li>
            <li><a href="#<?php echo $name;?>-json-edit" class="button"><?php _e('Edit','loopsns');?></a></li>
        </ul>
        <div id="<?php echo $name;?>-json-view" class="loopsns-json-display loopsns-json-display-read"><!--populated through JS--></div>
        <div id="<?php echo $name;?>-json-edit" class="loopsns-json-display loopsns-json-display-edit">
            <textarea class="fullwidth" placeholder="<?php echo esc_attr($defaults);?>" name="<?php echo $name;?>" class="fullwidth"><?php echo esc_textarea($args);?></textarea>
        </div>

    </div>
    <?php
}

function loopsns_is_json($string) {
    if ( !is_string($string) ) return false;
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
