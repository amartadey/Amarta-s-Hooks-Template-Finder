<?php
/**
 * Plugin Name: Amarta's Hooks  and Template Finder
 * Version: 0.1
 * Description: A menu "Amarta's Hooks & Template Finder" will be added in your wordpress admin bar menu where you can display all the hooks, filters and Template.
 * Author: Amarta Dey
 * Author URI: https://amartadey.com/
 * License: GPLv2 or later
 * Text Domain: wphftftf_domain
 */

define( 'wphftf_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );

/**
 * Adding style
 * 
 * @since 1.0
 * @version 1.0
 */
function wphftf_style() {
    wp_enqueue_style( 'wphftf-style', wphftf_PLUGIN_PATH . 'assets/css/style.css' );    
}
add_action( 'wp_enqueue_scripts', 'wphftf_style' );
add_action( 'admin_enqueue_scripts', 'wphftf_style' );

/**
 * Adding menu in the Admin Bar Menu
 * 
 * @since 1.0
 * @version 1.2
 */
add_action('admin_bar_menu', 'wphftf_add_toolbar_items', 99 );

function wphftf_add_toolbar_items( $admin_bar ){

    if( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $page_url = wphftf_clean_url($page_url);
    $page_request = parse_url( $page_url );

    $admin_bar->add_menu( array(
        'id'    => 'wp-hooks-finder',
        'title' => __('Amarta\'s Hooks & Template Finder', 'wphftftf_domain'),
        'href'  => '#',
        'meta'  => array(
            'title' => __('WP Hooks Finder', 'wphftftf_domain'),      
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => wphftf_is_active( 'wphftf', 'All Action & Filter ' ),
        'href'  => wphftf_is_url( $page_url, $page_request, 'wphftf' ),
        'meta'  => array(
            'title' => wphftf_is_active( 'wphftf', 'All Action & Filter ' ),
            // 'target' => '_blank',
            'class' => 'wphftf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-action-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => wphftf_is_active( 'wphftfa', 'Action' ),
        'href'  => wphftf_is_url( $page_url, $page_request, 'wphftfa' ),
        'meta'  => array(
            'title' => wphftf_is_active( 'wphftfa', 'Action' ),
            // 'target' => '_blank',
            'class' => 'wphftf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'enable-disable-filter-hooks',
        'parent' => 'wp-hooks-finder',
        'title' => wphftf_is_active( 'wphftff', 'Filter' ),
        'href'  => wphftf_is_url( $page_url, $page_request, 'wphftff' ),
        'meta'  => array(
            'title' => wphftf_is_active( 'wphftff', 'Filter' ),
            // 'target' => '_blank',
            'class' => 'wphftf-menu'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'page-template-name',
        'parent' => 'wp-hooks-finder',
        'title' => wphftf_page_template_is_active( 'wphftft', 'Page Template Name' ),
        'href'  => wphftf_is_url( $page_url, $page_request, 'wphftft' ),
        'meta'  => array(
            'title' => wphftf_is_active( 'wphftft', 'Page Template Name' ),
            // 'target' => '_blank',
            'class' => 'wphftf-menu'
        ),
    ));
}

/**
 * Return the URL depending on the request
 */
function wphftf_is_url( $page_url, $page_request, $id ) {

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
function wphftf_is_active( $id, $title ) {

    if( isset( $_GET[$id] ) && $_GET[$id] == 1 ) {
        return sprintf( __( 'Hide %s Hooks', 'wphftftf_domain' ), $title );
    } else {
        return sprintf( __( 'Show %s Hooks', 'wphftftf_domain' ), $title );
    }
}

function wphftf_page_template_is_active( $id, $title ) {

    if( isset( $_GET[$id] ) && $_GET[$id] == "1" ) {
        return sprintf( __( 'Hide %s ', 'wphftftf_domain' ), $title );
    } else {
        return sprintf( __( 'Show %s ', 'wphftftf_domain' ), $title );
    }
}

/**
 * Reset the URL
 */
function wphftf_clean_url( $url ) {

    $query_url = array( '?wphftf=1', '?wphftf=0', '&wphftf=0', '&wphftf=1', '?wphftfa=1', '?wphftfa=0', '&wphftfa=0', '&wphftfa=1', '?wphftff=1', '?wphftff=0', '&wphftff=0', '&wphftff=1', '?wphftft=1', '?wphftft=0', '&wphftft=0', '&wphftft=1' );

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
add_action( 'all', 'wphftf_display_all_hooks' );

function wphftf_display_all_hooks( $tag ) {

    if( ( !isset( $_GET['wphftf'] ) || $_GET['wphftf'] == 0 ) &&
    ( !isset( $_GET['wphftfa'] ) || $_GET['wphftfa'] == 0 ) &&
    ( !isset( $_GET['wphftff'] ) || $_GET['wphftff'] == 0 ) ) return;

    global $debug_tags; global $wp_actions;
    
    if( !isset( $debug_tags ) )
        $debug_tags = array();

    if ( in_array( $tag, $debug_tags ) ) {
        return;
    }

    if( isset( $wp_actions[$tag] ) && ( isset( $_GET['wphftf'] ) || isset( $_GET['wphftfa'] ) ) ) {
        echo "<div id='wphftf-action' title=' Action Hook'><img src='".wphftf_PLUGIN_PATH."assets/img/action.png' />" . '<a href="https://www.google.com/search?q='.$tag.'&btnI" target="_blank">'.$tag.'</a>' . "</div>";
    } else if( isset( $_GET['wphftf'] ) || isset( $_GET['wphftff'] ) ) {
        echo "<div id='wphftf-filter' title='Filter Hook'><img src='".wphftf_PLUGIN_PATH."assets/img/filter.png' />" . '<a href="https://www.google.com/search?q='.$tag.'&btnI" target="_blank">' . $tag . '</a>' . "</div>";
    }

     

    $debug_tags[] = $tag;


}


function footerTemplatePrint(){
    if(isset($_GET['wphftft']) && $_GET['wphftft'] !=='0'){
            echo "<pre>";
            global $template;
            echo basename($template);
            echo "</pre>";          
       

    }
}

add_action('wp_footer', 'footerTemplatePrint');

