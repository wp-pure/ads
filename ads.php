<?php

/*
Plugin Name: Ads
Plugin URI: https://github.com/wp-pure/ads
description: A simple ad plugin, no frills, just a simple ad area in the dashboard with ad groups and basic javascript ad rotation embedded via shortcode.
Version: 0.0.1
Author: James Pederson
Author URI: https://jpederson.com
License: GPL2
*/


// snippet custom post type function
function ad_post_type() { 

	// register our custom post type
	register_post_type( 'ad',

		// custom post type information
		array(
			'labels' => array(
				'name' => __( 'Ads', 'ptheme' ), /* This is the Title of the Group */
				'singular_name' => __( 'Ad', 'ptheme' ), /* This is the individual type */
				'all_items' => __( 'All Ads', 'ptheme' ), /* the all items menu item */
				'add_new' => __( 'Add New', 'ptheme' ), /* The add new menu item */
				'add_new_item' => __( 'Add New Ad', 'ptheme' ), /* Add New Display Title */
				'edit' => __( 'Edit', 'ptheme' ), /* Edit Dialog */
				'edit_item' => __( 'Edit Ads', 'ptheme' ), /* Edit Display Title */
				'new_item' => __( 'New Ad', 'ptheme' ), /* New Display Title */
				'view_item' => __( 'View Ad', 'ptheme' ), /* View Display Title */
				'search_items' => __( 'Search Ad', 'ptheme' ), /* Search Custom Type Title */ 
				'not_found' =>  __( 'Nothing found in the database.', 'ptheme' ), /* This displays if there are no entries yet */ 
				'not_found_in_trash' => __( 'Nothing found in Trash', 'ptheme' ), /* This displays if there is nothing in the trash */
				'parent_item_colon' => ''
			),
			'description' => __( 'Manage Ads.', 'ptheme' ), /* Custom Type Description */
			'public' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 25, /* this is what order you want it to appear in on the left hand side menu */ 
			'menu_icon' => 'dashicons-admin-site', /* the icon for the custom post type menu */
			'has_archive' => false, /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => array( 'title', 'revisions' )
		)

	);

}
add_action( 'init', 'ad_post_type');


// now let's add custom categories (these act like categories)
register_taxonomy( 'ad_group', 
	array( 'ad' ), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
	array('hierarchical' => true,     /* if this is true, it acts like categories */
		'labels' => array(
			'name' => __( 'Ad Groups', 'ptheme' ), /* name of the custom taxonomy */
			'singular_name' => __( 'Ad Group', 'ptheme' ), /* single taxonomy name */
			'search_items' =>  __( 'Search Ad Groups', 'ptheme' ), /* search title for taxomony */
			'all_items' => __( 'All Ad Groups', 'ptheme' ), /* all title for taxonomies */
			'parent_item' => __( 'Parent Ad Group', 'ptheme' ), /* parent title for taxonomy */
			'parent_item_colon' => __( 'Parent Ad Group:', 'ptheme' ), /* parent taxonomy title */
			'edit_item' => __( 'Edit Ad Group', 'ptheme' ), /* edit custom taxonomy title */
			'update_item' => __( 'Update Ad Group', 'ptheme' ), /* update title for taxonomy */
			'add_new_item' => __( 'Add New Ad Group', 'ptheme' ), /* add new title for taxonomy */
			'new_item_name' => __( 'New Ad Group Name', 'ptheme' ) /* name title for taxonomy */
		),
		'show_admin_column' => true, 
		'show_ui' => true,
		'query_var' => false,
		'rewrite' => false,
		'public' => false
	)
);


// ad metabox(es)
function ad_metabox( $meta_boxes ) {

    // event metabox
    $ad_metabox = new_cmb2_box( array(
        'id' => 'ad_metabox',
        'title' => 'Ad Info',
        'object_types' => array( 'ad' ), // post type
        'context' => 'normal',
        'priority' => 'high',
    ) );

    $ad_metabox->add_field( array(
        'name' => 'Ad Image',
        'id'   => 'ad_image',
        'type' => 'file',
        'desc' => 'Upload an image for this ad.'
    ) );

    $ad_metabox->add_field( array(
        'name' => 'Link',
        'id'   => 'ad_link',
        'type' => 'text'
    ) );

    $ad_metabox->add_field( array(
        'name' => 'Expiry',
        'id'   => 'ad_expire',
        'type' => 'text_datetime_timestamp'
    ) );

    $ad_metabox->add_field( array(
        'name' => 'Notes',
        'id'   => 'ad_notes',
        'type' => 'textarea',
    ) );

}
add_filter( 'cmb2_admin_init', 'ad_metabox' );



// function to handle the snippet shortcode
function ad_shortcode( $atts ) {

	// if we have a slug specified
	if ( !empty( $atts ) ) {

		// let's set the args for our select
		if ( isset( $atts['slug'] ) ) {
			$args = array(
				'name' => $atts['slug'],
				'post_type' => 'ad',
				'numberposts' => 1
			);
		} else if ( $atts['group'] ) {
			$args = array(
				'post_type' => 'ad',
				'numberposts' => -1,
				'ad_group' => $atts['group'],
				'orderby' => 'rand',
				'order' => 'ASC'
			);
		}


		// get the snippet
		$ads = get_posts( $args );

		// check to make sure it's not empty
		if ( isset( $ads ) ) {

			// if this is more than one ad, use a rotator
			if ( count( $ads ) > 1 ) {

				$return = '<div class="promos">';
				foreach ( $ads as $ad ) {				
					$ad_info = get_post_meta( $ad->ID );
					$return .= '<div class="promo"><a href="' . $ad_info['ad_link'][0] . '"><img src="' . $ad_info['ad_image'][0] . '"></a></div>';
				}
				$return .= '</div>';

			// otherwise just show the one ad.
			} else {

				$ad_info = get_post_meta( $ads[0]->ID );
				$return = '<div class="promo"><a href="' . $ad_info['ad_link'][0] . '"><img src="' . $ad_info['ad_image'][0] . '"></a></div>';

			}

			return $return;

		} else {

			// return nothing if the snippet doesn't exist
			return '';
		}
		
	}
}
add_shortcode( 'ad', 'ad_shortcode' );



// a function to output an ad group
function do_ad_group( $group = '' ) {

	// make sure a group has been specified
	if ( !empty( $group ) ) {

		// just output the shortcode.
		print do_shortcode( '[ad group="' . $group . '" /]' );

	}
	
}



// custom admin listing column to output the shortcode for each snipper
function ad_columns( $columns ) {

	// don't include the data column (it's really not necessary)
	unset($columns['date']);

	// add a shortcode column
	$columns['ad_shortcode'] = 'Shortcode';

	// return the columns list
	return $columns;
}
add_filter( 'manage_ad_posts_columns', 'ad_columns' );



// populate the new column with the shortcode so users can easily copy it
function ad_columns_content( $column, $post_id ) {

	// only affect the appropriate column
    if ( $column === 'ad_shortcode' ) {

    	// get the slug
    	$slug = get_post_field( 'post_name', $post_id );
		
		// output the shortcode for easy copying
		echo '[ad slug="' . $slug . '"]';

	}

}
add_action( 'manage_posts_custom_column' , 'ad_columns_content', 10, 2 );



// remove data filtering from snippets admin listing
function ad_remove_date_filter( $months ) {

	// get post type
    global $typenow;

    // only remove this for snippet post type
    if ( $typenow == 'ad' ) {
        return array();
    }

    // otherwise keep date filter
    return $months;
}
add_filter( 'months_dropdown_results', 'ad_remove_date_filter' );



// remove the description column from the ad_group taxonomy
function ad_remove_tax_description( $columns ) {
	if ( isset( $columns['description'] ) ) {
		unset( $columns['description'] );   
	}

	return $columns;
} 
add_filter( 'manage_edit-ad_group_columns', 'ad_remove_tax_description' );



// add
function ad_group_shortcode_column( $columns ) {
    $columns = array(
    	'cb' => $columns['cb'],
    	'name' => 'Name',
    	'slug' => 'Slug',
    	'shortcode' => 'Shortcode',
    	'posts' => 'Count',
    );
    return $columns;
}
add_filter( 'manage_edit-ad_group_columns', 'ad_group_shortcode_column' );



// set the shortcode column content
function ad_group_shortcode_column_content( $content,$column_name,$term_id ) {
    $term = get_term( $term_id, 'ad_group');
    switch ( $column_name ) {
        case 'shortcode':
            //do your stuff here with $term or $term_id
            $content = '[ad group="' . $term->slug . '" /]';
            break;
        default:
            break;
    }
    return $content;
}
add_filter( 'manage_ad_group_custom_column', 'ad_group_shortcode_column_content', 10, 3 );



// sort snippets by title in the dashboard listing.
function ad_order( $query ) {

	// get post type
    global $typenow;

    // only remove this for snippet post type
    if ( $typenow == 'ad' ) {
		$query->set( 'order' , 'asc' );
		$query->set( 'orderby', 'title');
		return;
	}
}
add_action( 'pre_get_posts', 'ad_order', 1 );



// include the main.js script in the header on the front-end.
function ad_assets() {
	wp_enqueue_style( 'ads-css', plugin_dir_url( __FILE__ ) . 'ads.css' );
	wp_enqueue_script( 'ads-js', plugin_dir_url( __FILE__ ) . 'ads.js', array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'ad_assets' );


