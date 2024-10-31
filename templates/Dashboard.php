<!-- dashboard for aodsi-suite shows AOD plugin collection -->
<?php
defined('ABSPATH') or die();
?>

<div class="wrap">

	<h1> <?php echo __( 'Art Of Data Suite', 'sequential-invoice-numbers' ); ?></h1>

	<br> 
	
	<p style="width: 50%"><?php echo __( 'We have made a collection of plugins to help you manage your order invoices, they are listed below and it could be worth considering whether installing some will improve the functionality of your website.', 'sequential-invoice-numbers' ); ?></p>

	<br>

	<h2><?php echo __( 'Our plugins', 'sequential-invoice-numbers' ); ?></h2>
		
	<table class="wp-list-table widefat fixed posts">

		<thead>
			<tr>
				<th id="plug_name" class="manage-column column-plug_name"><?php echo __( 'Plugin', 'sequential-invoice-numbers' ); ?></th>

				<th id="plug_description" style="width: 50%"class="manage-column column-plug_description"><?php echo __( 'Description', 'sequential-invoice-numbers' ); ?></th>
				
				<th id="plug_status" class="manage-column column-plug_status"><?php echo __( 'Status', 'sequential-invoice-numbers' ); ?></th>
			
			</tr>
		</thead>

		<tfoot>
			<tr>
				<th id="plug_name" class="manage-column column-plug_name"><?php echo __( 'Plugin', 'sequential-invoice-numbers' ); ?></th>

				<th id="plug_description" style="width: 50%"class="manage-column column-plug_description"><?php echo __( 'Description', 'sequential-invoice-numbers' ); ?></th>

				<th id="plug_status" class="manage-column column-plug_status"><?php echo __( 'Status', 'sequential-invoice-numbers' ); ?></th>

			</tr>
		</tfoot>


	<tbody>

	<?php

	if( ! empty( $aodsi_plugin_list ) ){
 		$aodsi_counter=1;
		foreach( $aodsi_plugin_list as $aodsi_row ) {

	?>

			<tr class="<?php if ( $aodsi_counter % 2 != 0) {echo 'alternate';}?>">
				<td> 
					<?php

					if ($aodsi_row['status'] == __( 'Active', 'sequential-invoice-numbers' ) ) {
						echo '<a href="'.$aodsi_row['link'].'" >';
					} 

					echo $aodsi_row['plugin_name'];

					if ( $aodsi_row['status'] == __( 'Active', 'sequential-invoice-numbers' ) ) {
						echo '</a>';
					} 

					?>	
				</td>

				<td>
					<?php 
					echo $aodsi_row['description']; 
					?>		
				</td>

				<td> 
					<?php

					if ($aodsi_row['status'] == __( 'Not Installed', 'sequential-invoice-numbers' ) ) {
						echo '<a href="'.$aodsi_row['install_link'].'" >';
					} 

					echo $aodsi_row['status']; 

					if ( $aodsi_row['status'] == __( 'Not Installed', 'sequential-invoice-numbers' ) ) {
						echo '</a>';
					}

					?>
					</td>
			</tr>

	<?php

		$aodsi_counter++;

		}
	} else { 

	?>


		<tr>
		<td colspan="3"><?php echo __('No data found', 'sequential-invoice-numbers') ?></td>
		</tr>
		 
	<?php
	}
	?>

	</tbody>


	</table>

</div>