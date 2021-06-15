<?php
/**
 * Plugin Name: Endo Stock Report Exporter
 * Plugin URI: http://www.endocreative.com
 * Description: A custom stock report exporter plugin for WooCommerce
 * Version: 1.0.0
 * Author: Endo Creative
 * Author URI: http://www.endocreative.com
 * License: GPL2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// create top level admin menu
add_action('admin_menu', 'endo_stock_report_admin_menu');
function endo_stock_report_admin_menu() {
  
  add_menu_page('Stock Report Export', 'Stock Report Export', 10, 'endo_stock_report', 'endo_admin_stock_report_page');

}

function endo_admin_stock_report_page() {

	?>
	<div class="wrap">

        <h2>Stock Report Export to CSV</h2>
        <p>Click export below to generate a stock report of the products on this site.</p>

      	<form method="post" id="export-form" action="">
            <?php submit_button('Export Stock Report', 'primary', 'download_csv' ); ?>
        </form>

    </div>
    <?php 
}


add_action('admin_init', 'endo_stock_report_admin_init');
function endo_stock_report_admin_init() {

	global $plugin_page;

	if ( isset($_POST['download_csv']) && $plugin_page == 'endo_stock_report' ) {
	   
	   	generate_stock_report_csv();
	    
	    die();

	}

}


function generate_stock_report_csv() {

	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');

	// set file name with current date
	header('Content-Disposition: attachment; filename=endo-stock-report-' . date('Y-m-d') . '.csv');

	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');

	// set the column headers for the csv
	$headings = array( 'Product', 'Stock' );

	// output the column headings
	fputcsv($output, $headings );

	// get all simple products where stock is managed
	$args = array(
	'post_type'			=> 'product',
	'post_status' 		=> 'publish',
    'posts_per_page' 	=> -1,
    'orderby'			=> 'title',
    'order'				=> 'ASC',
	'meta_query' 		=> array(
        array(
            'key' 	=> '_manage_stock',
            'value' => 'yes'
        )
    ),
		'tax_query' => array(
			array(
				'taxonomy' 	=> 'product_type',
				'field' 	=> 'slug',
				'terms' 	=> array('simple'),
				'operator' 	=> 'IN'
			)
		)
	);

	$loop = new WP_Query( $args );

	while ( $loop->have_posts() ) : $loop->the_post();
	
        global $product;

        $row = array( $product->get_title(), $product->stock );

        fputcsv($output, $row);
		
	endwhile; 

	// get all product variations where stock is managed
	$args = array(
		'post_type'			=> 'product_variation',
		'post_status' 		=> 'publish',
        'posts_per_page' 	=> -1,
        'orderby'			=> 'title',
        'order'				=> 'ASC',
		'meta_query' => array(
			array(
				'key' 		=> '_stock',
				'value' 	=> array('', false, null),
				'compare' 	=> 'NOT IN'
			)
		)
	);
	
	$loop = new WP_Query( $args );

	while ( $loop->have_posts() ) : $loop->the_post();
	
        $product = new WC_Product_Variation( $loop->post->ID );
		
		$row = array( $product->get_title() . ', ' . get_the_title( $product->variation_id ), $product->stock );

        fputcsv($output, $row);

	endwhile;

}