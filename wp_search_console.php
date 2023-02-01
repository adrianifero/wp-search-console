<?php
/*
Plugin Name: WP Search Console
Plugin URI: https://adriantoro.com
Description: Jump into monitoring your website's clicks, positions, and optimize your site for better search performance for any page with just one click
Version: 1.0.0
Author: Adrian T.
Author URI: https://adriantoro.com
*/

class wp_search_console_class {
	
	public function __construct(){
		
		add_action('admin_bar_menu', array( $this, 'add_item'), 100);

	}
	
	public function add_item( $admin_bar ){
	  	global $pagenow;
		$focus_keyword = '';
		
		if ( 
			!is_admin() && 
			current_user_can('manage_options')
		   ):
		

			$focus_keyword = get_post_meta( get_the_ID(), '_yoast_wpseo_focuskw', true );


			$href = 'https://search.google.com/search-console/performance/search-analytics?resource_id='.get_site_url().'%2F&metrics=CLICKS%2CPOSITION)&page=*' . get_permalink() . '&query=*' . $focus_keyword;

			$href = 'https://search.google.com/search-console/performance/search-analytics?resource_id='.get_site_url().'%2F&metrics=CLICKS%2CPOSITION)&page=*' . get_permalink();

			$args = array( 
				'id'	=>		'view-gsc',
				'title'	=>	'View in Search Console',
				'meta' 	=> 	array('target' => '_blank' ),
				'href'	=>	$href
			);

			$admin_bar->add_menu( $args );
		
		endif;
	}
	



	
}

$wp_search_console_class = new wp_search_console_class();
