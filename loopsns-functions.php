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
