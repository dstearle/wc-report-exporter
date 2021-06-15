<?php 

// create top level admin menu
add_action('admin_menu', 'endo_stock_report_admin_menu');
function endo_stock_report_admin_menu() {
  
  add_menu_page('Stock Report Export', 'Stock Report Export', 10, 'endo_stock_report', 'endo_admin_stock_report_page');

}