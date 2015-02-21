<form action="" method="post" id="notifications-bulk-management">
	<table class="notifications">
		<thead>
			<tr>
				<th class="icon"></th>
				<th><label class="bp-screen-reader-text" for="select-all-notifications"><?php _e( 'Select all', 'buddypress' ); ?></label><input id="select-all-notifications" type="checkbox"></th>
				<th class="title"><?php _e( 'Notification', 'buddypress' ); ?></th>
				<th class="date"><?php _e( 'Date Received', 'buddypress' ); ?></th>
				<th class="actions"><?php _e( 'Actions',    'buddypress' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php while ( bp_the_notifications() ) : bp_the_notification(); ?>

				<tr>
					<td></td>
					<td><input id="<?php bp_the_notification_id(); ?>" type="checkbox" name="notifications[]" value="<?php bp_the_notification_id(); ?>" class="notification-check"></td>
					<td><?php bp_the_notification_description();  ?></td>
					<td><?php bp_the_notification_time_since();   ?></td>
					<td><?php bp_the_notification_action_links(); ?></td>
				</tr>

			<?php endwhile; ?>

		</tbody>
	</table>

	<div class="notifications-options-nav">
		<?php bp_notifications_bulk_management_dropdown(); ?>
	</div><!-- .notifications-options-nav -->

	<?php wp_nonce_field( 'notifications_bulk_nonce', 'notifications_bulk_nonce' ); ?>
</form>
