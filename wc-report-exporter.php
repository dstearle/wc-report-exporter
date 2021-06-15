<?php
/**
 * Plugin Name: WC Report Exporter
 * Plugin URI: https://github.com/dstearle/wc-report-exporter
 * Description: A WordPress plugin for exporting WooCommerce reports.
 * Version: 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Admin Menu (Top Level)
function wc_report_admin_menu() {
  
  add_menu_page('WC Report Export', 'WC Report Export', 10, 'wc_report_export', 'wc_report_export_admin_page');

}

add_action('admin_menu', 'wc_report_admin_menu');

// Admin Page
function wc_report_export_admin_page() {

	?>
	<div class="wrap">

        <h2>Stock Report Export to CSV</h2>
        <p>Click export below to generate a stock report of the products on this site.</p>

      	<form method="post" id="export-form" action="">
            <?php submit_button('WC Report Export', 'primary', 'download_csv' ); ?>
        </form>

    </div>
    <?php 
}


// Admin Init
function wc_report_export_admin_init() {

	global $plugin_page;

	if ( isset($_POST['download_csv']) && $plugin_page == 'wc_report_export' ) {
	   
	   	generate_report_csv();
	    
	    die();

	}

}

add_action('admin_init', 'wc_report_export_admin_init');

// Generate Report
function generate_report_csv() {

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

// Scheduled Event
function cronstarter_activation() {

    if( !wp_next_scheduled( 'mycronjob' ) ) { 

       wp_schedule_event( time(), 'daily', 'mycronjob' );

    }

}

add_action('wp', 'cronstarter_activation');

// Scheduled Reports
function my_repeat_function() {

    $recepients = 'dearle@krgops.com';
    $subject = 'LBS Reports';
    $message = 'This is a test mail sent by WordPress automatically as per your schedule.';

    // Mails the reports 
    mail($recepients, $subject, $message);

}

// Hooks onto the scheduled event
add_action ('mycronjob', 'my_repeat_function'); 