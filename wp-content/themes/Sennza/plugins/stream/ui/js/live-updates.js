/* globals wp_stream_live_updates */
jQuery( function( $ ) {

	$( document ).ready( function() {

		// Only run on page 1 when the order is desc and on page wp_stream_live_updates
		if ( 'toplevel_page_wp_stream' !== wp_stream_live_updates.current_screen || '1' !== wp_stream_live_updates.current_page || 'asc' === wp_stream_live_updates.current_order ) {
			return;
		}

		var list_sel = '.toplevel_page_wp_stream #the-list';

		// Set initial beat to fast. WP is designed to slow this to 15 seconds after 2.5 minutes.
		wp.heartbeat.interval( 'fast' );

		$( document ).on( 'heartbeat-send.stream', function( e, data ) {

			data['wp-stream-heartbeat'] = 'live-update';

			var last_item = $( list_sel + ' tr:first .column-date time' ),
				last_time = 1;

			if ( 0 !== last_item.length ) {
				last_time = ( '' === last_item.attr( 'datetime' ) ) ? 1 : last_item.attr( 'datetime' );
			}

			data['wp-stream-heartbeat-last-time'] = last_time;
			data['wp-stream-heartbeat-query']     = wp_stream_live_updates.current_query;
		});

		// Listen for "heartbeat-tick" on $(document).
		$( document ).on( 'heartbeat-tick.stream', function( e, data ) {

			// If this no rows return then we kill the script
			if ( ! data['wp-stream-heartbeat'] || 0 === data['wp-stream-heartbeat'].length ) {
				return;
			}

			var show_on_screen = $( '#edit_stream_per_page' ).val(),
				$current_items = $( list_sel + ' tr' ),
				$new_items     = $( data['wp-stream-heartbeat'] );

			// Remove all class to tr added by WP and add new row class
			$new_items.removeClass().addClass( 'new-row' );

			// Check if first tr has the alternate class
			var has_class = ( $current_items.first().hasClass( 'alternate' ) );

			// Apply the good class to the list
			if ( 1 === $new_items.length && ! has_class ) {
				$new_items.addClass( 'alternate' );
			} else {
				var even_or_odd = ( 0 === $new_items.length % 2 && ! has_class ) ? 'even' : 'odd';
				// Add class to nth child because there is more than one element
				$new_items.filter( ':nth-child(' + even_or_odd + ')' ).addClass( 'alternate' );
			}

			// Add element to the dom
			$( list_sel ).prepend( $new_items );

			$( '.metabox-prefs input' ).each(function() {
				if ( true !== $( this ).prop( 'checked' ) ) {
					var label = $( this ).val();
					$( 'td.column-' + label ).hide();
				}
			});

			// Remove the number of element added to the end of the list table
			var slice_rows = show_on_screen - ( $new_items.length + $current_items.length );

			if ( slice_rows < 0 ) {
				$( list_sel + ' tr' ).slice( slice_rows ).remove();
			}

			// Remove the no items row
			$( list_sel + ' tr.no-items' ).remove();

			// Update pagination
			var total_items_i18n = data.total_items_i18n || '';

			if ( total_items_i18n ) {
				$( '.displaying-num' ).text( total_items_i18n );
				$( '.total-pages' ).text( data.total_pages_i18n );
				$( '.tablenav-pages' ).find( '.next-page, .last-page' ).toggleClass( 'disabled', data.total_pages === $( '.current-page' ).val() );
				$( '.tablenav-pages .last-page' ).attr( 'href', data.last_page_link );
			}

			// Allow others to hook in, ie: timeago
			$( list_sel ).parent().trigger( 'updated' );

			// Remove background after a certain amount of time
			setTimeout( function() {
				$( '.new-row' ).addClass( 'fadeout' );
				setTimeout( function() {
					$( list_sel + ' tr' ).removeClass( 'new-row fadeout' );
				}, 500 );
			}, 3000 );

		});

	});

});
