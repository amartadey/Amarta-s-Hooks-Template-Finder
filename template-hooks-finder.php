<?php
/**
 * Plugin Name: Template and Hooks Finder
 * Version: 0.2
 * Description: Template and Hooks finder will be added in your wordpress admin bar menu where you can display all the hooks, filters and Template. NOTE: This is a developmental plugin. Errors and Bugs will appear.
 * Author: Amarta Dey
 * Author URI: https://amartadey.com/
 * License: GPLv3 or later
 * Text Domain: thf_amarta
 */

define( 'thfamrf_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

/**
 * Adding style
 * 
 * @since 1.0
 * @version 1.0
 */
function thfamrf_style() {
    wp_enqueue_style( 'thfamrf-style', thfamrf_PLUGIN_PATH . 'assets/css/style.css' );    
}
add_action( 'wp_enqueue_scripts', 'thfamrf_style' );
add_action( 'admin_enqueue_scripts', 'thfamrf_style' );

/**
 * Adding menu in the Admin Bar Menu
 * 
 * @since 1.0
 * @version 1.2
 */
add_action('admin_bar_menu', 'thfamrf_add_toolbar_items', 99 );

function thfamrf_add_toolbar_items( $admin_bar ){

    if( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $page_url = thfamrf_clean_url($page_url);
    $page_request = parse_url( $page_url );

    $admin_bar->add_menu( array(
        'id'    => 'wp-hooks-finder',
        'title' => __('Template and Hooks Finder', 'thf_amarta'),
        'href'  => '#',
        'meta'  => array(
            'title' => __('WP Hooks Finder', 'thf_amarta'),      
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => thfamrf_is_active( 'thfamrf', 'All Action & Filter ' ),
        'href'  => thfamrf_is_url( $page_url, $page_request, 'thfamrf' ),
        'meta'  => array(
            'title' => thfamrf_is_active( 'thfamrf', 'All Action & Filter ' ),
            // 'target' => '_blank',
            'class' => 'thfamrf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-action-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => thfamrf_is_active( 'thfamrfa', 'Action' ),
        'href'  => thfamrf_is_url( $page_url, $page_request, 'thfamrfa' ),
        'meta'  => array(
            'title' => thfamrf_is_active( 'thfamrfa', 'Action' ),
            // 'target' => '_blank',
            'class' => 'thfamrf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-filter-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => thfamrf_is_active( 'thfamrff', 'Filter' ),
        'href'  => thfamrf_is_url( $page_url, $page_request, 'thfamrff' ),
        'meta'  => array(
            'title' => thfamrf_is_active( 'thfamrff', 'Filter' ),
            // 'target' => '_blank',
            'class' => 'thfamrf-menu'
        ),
    ));
}

/**
 * Return the URL depending on the request
 */
function thfamrf_is_url( $page_url, $page_request, $id ) {

    if( isset( $_GET[$id] ) && $_GET[$id] == 1 ) {
        $link = $page_url . ( isset( $page_request['query'] ) ? '&' : '?' ) . $id . '=0';
    } else {
        $link = $page_url . ( isset( $page_request['query'] ) ? '&' : '?' ) . $id . '=1';
    }

    return $link;
}

/**
 * Return what title should be render on menu
 */
function thfamrf_is_active( $id, $title ) {

    if( isset( $_GET[$id] ) && $_GET[$id] == 1 ) {
        return sprintf( __( 'Hide %s Hooks', 'thf_amarta' ), $title );
    } else {
        return sprintf( __( 'Show %s Hooks', 'thf_amarta' ), $title );
    }
}

/**
 * Reset the URL
 */
function thfamrf_clean_url( $url ) {

    $query_url = array( '?thfamrf=1', '?thfamrf=0', '&thfamrf=0', '&thfamrf=1', '?thfamrfa=1', '?thfamrfa=0', '&thfamrfa=0', '&thfamrfa=1', '?thfamrff=1', '?thfamrff=0', '&thfamrff=0', '&thfamrff=1', );

    foreach( $query_url as $q_url ) {
        if( strpos(  $url, $q_url ) !== false ) {
            $clean_url = str_replace( $q_url, '',$url );
            return $clean_url;            
        }
    }

    return $url;
}

/**
 * WordPress action hook "all", which is responsible to display hooks & filters
 * 
 * @since 1.0
 * @version 1.0
 */
add_action( 'all', 'thfamrf_display_all_hooks' );

function thfamrf_display_all_hooks( $tag ) {

    if( ( !isset( $_GET['thfamrf'] ) || $_GET['thfamrf'] == 0 ) &&
    ( !isset( $_GET['thfamrfa'] ) || $_GET['thfamrfa'] == 0 ) &&
    ( !isset( $_GET['thfamrff'] ) || $_GET['thfamrff'] == 0 ) ) return;

    global $debug_tags; global $wp_actions;
    
    if( !isset( $debug_tags ) )
        $debug_tags = array();

    if ( in_array( $tag, $debug_tags ) ) {
        return;
    }

    if( isset( $wp_actions[$tag] ) && ( isset( $_GET['thfamrf'] ) || isset( $_GET['thfamrfa'] ) ) ) {
        echo "<div id='thfamrf-action' title=' Action Hook'><img src='".thfamrf_PLUGIN_PATH."assets/img/action.png' />" . '<a href="https://www.google.com/search?q='.$tag.'&btnI" target="_blank">'.$tag.'</a>' . "</div>";
    } else if( isset( $_GET['thfamrf'] ) || isset( $_GET['thfamrff'] ) ) {
        echo "<div id='thfamrf-filter' title='Filter Hook'><img src='".thfamrf_PLUGIN_PATH."assets/img/filter.png' />" . '<a href="https://www.google.com/search?q='.$tag.'&btnI" target="_blank">' . $tag . '</a>' . "</div>";
    }

     

    $debug_tags[] = $tag;


}




//  Template Name in Admin Bar 


function thfamrf_adminBarText($admin_bar){
  if(!is_admin()){
    $admin_bar->add_menu( array(
        'id'    => 'custom-id',
        'title' => "Template Name: <b style='color:#50FA64'>".thfamrf_customTemplateName()."</b>",
        'href'  => admin_url().'theme-editor.php?file='.thfamrf_customTemplateName(),
    ));
  }
  
}
add_action('admin_bar_menu','thfamrf_adminBarText',99);

function thfamrf_customTemplateName(){
  global $template;
  return basename($template);
}
?>

