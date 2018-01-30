<?php

/**
 * Get a value in a multidimensional array
 * http://stackoverflow.com/questions/1677099/how-to-use-a-string-as-an-array-index-path-to-retrieve-a-value
 * @param type $keys
 * @param type $array
 * @return type
 */
function loopsns_get_array_value($keys = null, $array){
    if (!$keys) return $array;
    
    $keys = (array)$keys;
    $first_key = $keys[0];
    if(count($keys) > 1) {
        if ( isset($array[$keys[0]]) ){
            return wpsstm_get_array_value(array_slice($keys, 1), $array[$keys[0]]);
        }
    }elseif (isset($array[$first_key])){
        return $array[$first_key];
    }
    
    return false;
}

//https://stackoverflow.com/questions/18081625/how-do-i-map-an-associative-array-to-html-element-attributes
/*
Make a string from an array of attributes
*/
function loopsns_get_html_attr($arr=null){
    $str = null;
    $arr = (array)$arr;
    $arr = array_filter($arr, function($value) { return $value !== ''; }); //remove empty strings
    
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

function loopsns_json_container($name,$value=null){

    if ($value && !loopsns_is_json($value) ) $value = json_encode($value);
    $placeholder = __('Enter a JSON string','loopsns');
    
    ?>
    <div class="loopsns-json-container">
        <ul class="loopsns-json-tabs">
            <li><a href="#<?php echo $name;?>-json-view" class="button"><?php _e('View JSON','loopsns');?></a></li>
            <li><a href="#<?php echo $name;?>-json-edit" class="button"><?php _e('Edit JSON','loopsns');?></a></li>
        </ul>
        <div id="<?php echo $name;?>-json-view" class="loopsns-json-display loopsns-json-display-read"><!--populated through JS--></div>
        <div id="<?php echo $name;?>-json-edit" class="loopsns-json-display loopsns-json-display-edit">
            <textarea class="fullwidth" placeholder="<?php echo esc_attr($placeholder);?>" name="<?php echo $name;?>" class="fullwidth"><?php echo esc_textarea($value);?></textarea>
        </div>

    </div>
    <?php
}

function loopsns_is_json($string) {
    if ( !is_string($string) ) return false;
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
