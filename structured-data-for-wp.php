<?php
/*
Plugin Name: Structured Data for WP
Plugin URI: http://ampforwp.com/structured-data/
Description: Add Structured data in your site. 
Version: 1.2.4
Text Domain: structured-data
Author: Mohammed Kaludi, AMPforWP Team
Author URI: http://ampforwp.com
Donate link: https://www.paypal.me/Kaludi/25
License: GPL2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define('STRUCTURED_DATA_VERSION', '1.2.4');
define('STRUCTURED_DATA_DIR_NAME', dirname( __FILE__ ));

if ( ! defined( 'STRUCTURED_DATA_VERSION' ) ) {
  define( 'STRUCTURED_DATA_VERSION', '1.2.4' );
}
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define( 'STRUCTURED_DATA_STORE_URL', 'https://accounts.ampforwp.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of your product. This should match the download name in EDD exactly
define( 'STRUCTURED_DATA_ITEM_NAME', 'Structured Data for WP' );

// the download ID. This is the ID of your product in EDD and should match the download ID visible in your Downloads list (see example below)
//define( 'AMPFORWP_ITEM_ID', 2502 );
// the name of the settings page for the license input to be displayed
define( 'STRUCTURED_DATA_LICENSE_PAGE', 'structured-data-for-wp-license' );
if(! defined('AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME')){
    $folderName = basename(__DIR__);
    define( 'AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME', $folderName );
}

// Redux panel inclusion code
if ( !class_exists( 'ReduxFramework' ) ) {
    require_once STRUCTURED_DATA_DIR_NAME .'/includes/options/redux-core/framework.php';
}
// Register all the main options
require_once STRUCTURED_DATA_DIR_NAME .'/includes/options/admin-config.php';
// including the output file
require_once STRUCTURED_DATA_DIR_NAME .'/output/function.php';
require_once STRUCTURED_DATA_DIR_NAME .'/output/output.php';


add_action('init', function() {
});


add_action('wp_head','custom_breadcrumbs',99);

// Breadcrumbs
function custom_breadcrumbs($sd_data) {
	global $sd_data;	

    $variables1_titles = array();   
    $variables2_links = array();   
    // Settings
    $separator          = '&gt;';
    $breadcrums_id      = 'breadcrumbs';
    $breadcrums_class   = 'breadcrumbs';
    $home_title         = 'Homepage';
      
    // If you have any custom post types with custom taxonomies, put the taxonomy name below (e.g. product_cat)
    $custom_taxonomy    = 'product_cat';
       
    // Get the query & post information
    global $post,$wp_query;
       
    // Do not display on the homepage
    if ( !is_front_page() ) {
       
        // Build the breadcrums
        // Home page
        $variables1_titles[] = $home_title;
        $variables2_links[] = get_home_url();


        if ( is_archive() && !is_tax() && !is_category() && !is_tag() && !is_author() ) {
            $archive_title = post_type_archive_title($prefix, false);
              $variables1_titles[] = $archive_title;


        } else if  ( is_author() ) {
	    		global $author;
	    		
	            $userdata = get_userdata( $author ); 
	            $author_url= get_author_posts_url($userdata->ID);

	            // author name
	            $variables1_titles[]= $userdata->display_name;
	            $variables2_links[]= $author_url;

        } else if ( is_archive() && is_tax() && !is_category() && !is_tag() ) {
              
            // If post is a custom post type
            $post_type = get_post_type();
              
            // If it is a custom post type display name and link
            if($post_type != 'post') {
                  
                $post_type_object = get_post_type_object($post_type);
                $post_type_archive = get_post_type_archive_link($post_type);
                $variables1_titles[] = $post_type_object->labels->name;
                $variables2_links[] = $post_type_archive;
              
            }
              
            $custom_tax_name = get_queried_object()->name;
              $variables1_titles[] = $custom_tax_name;

        } else if ( is_single() ) {
              
            // If post is a custom post type
            $post_type = get_post_type();
              
            // If it is a custom post type display name and link
            if($post_type != 'post') {
                  
                $post_type_object = get_post_type_object($post_type);
                $post_type_archive = get_post_type_archive_link($post_type);
              
                $variables1_titles[]= $post_type_object->labels->name;
                $variables2_links[]= $post_type_archive;              
            }
              
            // Get post category info
            $category = get_the_category();
             
            if(!empty($category)) {
              $category_values = array_values( $category );
              foreach ($category_values as $category_value) {
                  $category_name = get_category($category_value);
                  $cat_name = $category_name->name;
                  $variables1_titles[]=$cat_name;
                  $variables2_links[]=get_category_link( $category_value );
              
              }
                // Get last category post is in
                $last_category = end(($category));
                  $category_name = get_category($last_category);
                // Get parent any categories and create array
                $get_cat_parents = rtrim(get_category_parents($last_category->term_id, true, ','),',');
                $cat_parents = explode(',',$get_cat_parents);
                  
                // Loop through parent categories and store in variable $cat_display
                $cat_display = '';
                foreach($cat_parents as $parents) {
                    $cat_display .= '<li class="item-cat">'.$parents.'</li>';
                    $cat_display .= '<li class="separator"> ' . $separator . ' </li>';
                }
            }
              
            // If it's a custom post type within a custom taxonomy
            $taxonomy_exists = taxonomy_exists($custom_taxonomy);
            if(empty($last_category) && !empty($custom_taxonomy) && $taxonomy_exists) {
                   
                $taxonomy_terms = get_the_terms( $post->ID, $custom_taxonomy );
                $cat_id         = $taxonomy_terms[0]->term_id;
                $cat_nicename   = $taxonomy_terms[0]->slug;
                $cat_link       = get_term_link($taxonomy_terms[0]->term_id, $custom_taxonomy);
                $cat_name       = $taxonomy_terms[0]->name;

            }
              
            // Check if the post is in a category
            if(!empty($last_category)) {
               // echo $cat_display;
               // echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '" title="' . get_the_title() . '">' . get_the_title() . '</strong></li>';
                  
            // Else if post is in a custom taxonomy
            } else if(!empty($cat_id)) {
              $variables1_titles[]= $cat_name;
              $variables2_links[]=$cat_link;

            } else {
                if($post_type == 'post') { 
                     $variables1_titles[]= get_the_title();
                }
            }
              
        } else if ( is_category() ) {
                $category = get_the_category();
             
            if(!empty($category)) {
              $category_values = array_values( $category );
              foreach ($category_values as $category_value) {
                  $category_name = get_category($category_value);
                  $cat_name = $category_name->name;
                  $variables1_titles[]=$cat_name;
                  $variables2_links[]=get_category_link( $category_value );
              
              }
          }
            // Category page
           // echo '<li class="item-current item-cat"><strong class="bread-current bread-cat">' . single_cat_title('', false) . '</strong></li>';
            //$cat_ttile= single_cat_title('', false);    
            //$variables1_titles[]= $cat_ttile;

               
        } else if ( is_page() ) {
               
            // Standard page
            if( $post->post_parent ){
                   
                // If child page, get parents 
                $anc = get_post_ancestors( $post->ID );
                   
                // Get parents in the right order
                $anc = array_reverse($anc);
                   
                // Parent page loop
                if ( !isset( $parents ) ) $parents = null;
                foreach ( $anc as $ancestor ) {
                    $parents .= '<li class="item-parent item-parent-' . $ancestor . '"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink($ancestor) . '" title="' . get_the_title($ancestor) . '">' . get_the_title($ancestor) . '</a></li>';
                    $parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
                    $variables1_titles[]= get_the_title($ancestor);
                    $variables2_links[]=get_permalink($ancestor);
                }
                   
                // Display parent pages
              //  echo $parents;
                   
                // Current page
              //  echo '<li class="item-current item-' . $post->ID . '"><strong title="' . get_the_title() . '"> ' . get_the_title() . '</strong></li>';
                $variables1_titles[]= get_the_title();
                $variables2_links[]=get_permalink();
                   
            } else {
                   
                // Just display current page if not parents
               // echo '<li class="item-current item-' . $post->ID . '"><strong class="bread-current bread-' . $post->ID . '"> ' . get_the_title() . '</strong></li>';
                   $variables1_titles[]=get_the_title();
                   $variables2_links[]=get_permalink();
            }
               
        } else if ( is_tag() ) {
               
            // Tag page
               
            // Get tag information
            $term_id        = get_query_var('tag_id');
            $taxonomy       = 'post_tag';
            $args           = 'include=' . $term_id;
            $terms          = get_terms( $taxonomy, $args );
            $get_term_id    = $terms[0]->term_id;
            $get_term_slug  = $terms[0]->slug;
            $get_term_name  = $terms[0]->name;
            $term_link      = get_term_link($get_term_id );
               
            // Tag name and link

            $variables1_titles[] = $get_term_name;
            $variables2_links[] = $term_link;
           
        } elseif ( is_day() ) {
               
            // Day archive
               
            // Year link
          //  echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
          //  echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
               
            // Month link
          //  echo '<li class="item-month item-month-' . get_the_time('m') . '"><a class="bread-month bread-month-' . get_the_time('m') . '" href="' . get_month_link( get_the_time('Y'), get_the_time('m') ) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</a></li>';
          //  echo '<li class="separator separator-' . get_the_time('m') . '"> ' . $separator . ' </li>';
               
            // Day display
            //echo '<li class="item-current item-' . get_the_time('j') . '"><strong class="bread-current bread-' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' Archives</strong></li>';
               
        } else if ( is_month() ) {
               
            // Month Archive
               
            // Year link
           // echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link( get_the_time('Y') ) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
           // echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>'//;
               
            // Month display
          //  echo '<li class="item-month item-month-' . get_the_time('m') . '"><strong class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</strong></li>';
               
        } else if ( is_year() ) {
               
            // Display year archive
           // echo '<li class="item-current item-current-' . get_the_time('Y') . '"><strong class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</strong></li>';
               
        } else if ( is_author() ) {
            // Auhor archive
               
            // Get the author information

           
        } else if ( get_query_var('paged') ) {

            // Paginated archives
          //  echo '<li class="item-current item-current-' . get_query_var('paged') . '"><strong class="bread-current bread-current-' . get_query_var('paged') . '" title="Page ' . get_query_var('paged') . '">'.__('Page') . ' ' . get_query_var('paged') . '</strong></li>';
               
        } else if ( is_search() ) {
           
            // Search results page
           // echo '<li class="item-current item-current-' . get_search_query() . '"><strong class="bread-current bread-current-' . get_search_query() . '" title="Search results for: ' . get_search_query() . '">Search results for: ' . get_search_query() . '</strong></li>';
           
        } elseif ( is_404() ) {
               
            // 404 page
            //echo '<li>' . 'Error 404' . '</li>';
        } 

         
          $sd_data['titles']= $variables1_titles;
          $sd_data['links']= $variables2_links;
         
    }
   
    
}
// Non amp checker
if ( ! function_exists('ampforwp_sd_non_amp') ){  
  function ampforwp_sd_non_amp(){
    $non_amp = true;
    if(function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint() ) {
      $non_amp = false;
    }
    return $non_amp;
  }
}

// Schema App by Hunch Manifest compatibility
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(is_plugin_active('schema-app-structured-data-for-schemaorg/hunch-schema.php')) {
  add_action('amp_post_template_head','ampforwp_scheme_app_remove_hook',9);
  function ampforwp_scheme_app_remove_hook(){
    if(function_exists('ampforwp_is_amp_endpoint') && ampforwp_is_amp_endpoint()){
      global $HunchSchemaFront;
      remove_action( 'amp_post_template_head', array( $HunchSchemaFront, 'AMPPostTemplateHead' ),10,1); 
    }
  }
}

add_action('amp_init','ampforwp_with_scheme_app_output',9);
function ampforwp_with_scheme_app_output(){
  global $sd_data;

  $url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH),'/' );
  $explode_path = explode('/', $url_path); 
  
  if(isset($sd_data['sd-for-ampforwp-with-scheme-app']) && 1 == $sd_data['sd-for-ampforwp-with-scheme-app'] && 'amp' === end( $explode_path) ){ 

        $scheme_amp_app = new SchemaFront;
        $ampforwp_scheme_app_output = $scheme_amp_app->hunch_schema_add(true);
        remove_filter( 'amp_init', 'ampforwp_structured_data');    

  }
  return $ampforwp_scheme_app_output;
}
// Schema App end here
require_once STRUCTURED_DATA_DIR_NAME.'/admin_section/structure_admin.php';
require_once dirname( __FILE__ ) . '/updater/EDD_SL_Plugin_Updater.php';

// Check for updates
function structured_data_plugin_updater() {

  // retrieve our license key from the DB
  //$license_key = trim( get_option( 'amp_ads_license_key' ) );
  $selectedOption = get_option('redux_builder_amp',true);
    $license_key = '';//trim( get_option( 'amp_ads_license_key' ) );
    $pluginItemName = '';
    $pluginItemStoreUrl = '';
    $pluginstatus = '';
    if( isset($selectedOption['amp-license']) && "" != $selectedOption['amp-license'] && isset($selectedOption['amp-license'][AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME])){

       $pluginsDetail = $selectedOption['amp-license'][AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME];
       $license_key = $pluginsDetail['license'];
       $pluginItemName = $pluginsDetail['item_name'];
       $pluginItemStoreUrl = $pluginsDetail['store_url'];
       $pluginstatus = $pluginsDetail['status'];
    }
  
  // setup the updater
  $edd_updater = new Sturctured_Data_EDD_SL_Plugin_Updater( STRUCTURED_DATA_STORE_URL, __FILE__, array(
      'version'   => STRUCTURED_DATA_VERSION,        // current version number
      'license'   => $license_key,            // license key (used get_option above to retrieve from DB)
      'license_status'=>$pluginstatus,
      'item_name' => STRUCTURED_DATA_ITEM_NAME,      // name of this plugin
      'author'  => 'Mohammed Kaludi',           // author of this plugin
      'beta'    => false,
    )
  );
}
add_action( 'admin_init', 'structured_data_plugin_updater', 0 );

// Notice to enter license key once activate the plugin

$path = plugin_basename( __FILE__ );
  add_action("after_plugin_row_{$path}", function( $plugin_file, $plugin_data, $status ) {
    global $redux_builder_amp;
    if(! defined('AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME')){
    $folderName = basename(__DIR__);
            define( 'AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME', $folderName );
        }
        $pluginsDetail = $redux_builder_amp['amp-license'][AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME];
        $pluginstatus = $pluginsDetail['status'];

        if(empty($redux_builder_amp['amp-license'][AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME]['license'])){
      echo "<tr class='active'><td>&nbsp;</td><td colspan='2'><a href='".esc_url(  self_admin_url( 'admin.php?page=amp_options&tabid=opt-go-premium' )  )."'>Please enter the license key</a> to get the <strong>latest features</strong> and <strong>stable updates</strong></td></tr>";
          }elseif($pluginstatus=="valid"){
            $update_cache = get_site_transient( 'update_plugins' );
            $update_cache = is_object( $update_cache ) ? $update_cache : new stdClass();
            if(isset($update_cache->response[ AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME ]) 
                && empty($update_cache->response[ AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME ]->download_link) 
              ){
               unset($update_cache->response[ AMP_STRUCTURED_DATA_ITEM_FOLDER_NAME ]);
            }
            set_site_transient( 'update_plugins', $update_cache );
            
        }
    }, 10, 3 );
  