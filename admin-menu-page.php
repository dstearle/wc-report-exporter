<?php

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