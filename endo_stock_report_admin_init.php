<?php 

add_action('admin_init', 'endo_stock_report_admin_init');
function endo_stock_report_admin_init() {

	global $plugin_page;

	if ( isset($_POST['download_csv']) && $plugin_page == 'endo_stock_report' ) {
	   
	   	generate_stock_report_csv();
	    
	    die();

	}

}