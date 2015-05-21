/* dashboard pagination */
jQuery( function( $ ) {

	/**
	 * Dashboard Live Update
	 */
	if ( undefined !== wp.heartbeat && undefined !== wp.heartbeat.interval ) {
		wp.heartbeat.interval( 'fast' );
	}

	var widget = $( '#dashboard_stream_activity' ),
		list   = widget.find( '.inside ul' );

	// Add alternate classes to initial items
	$( 'li:odd', list ).addClass( 'alternate');

	// Add Stream widget params to heartbeat API requests
	$( document ).on( 'heartbeat-send.stream', function( e, data ) {
		data['wp-stream-heartbeat']           = 'dashboard-update';
		data['wp-stream-heartbeat-last-time'] = $( 'li:first', list ).data( 'datetime' ) || 1;
	});

	// Listen for "heartbeat-tick" on $(document).
	$( document ).on( 'heartbeat-tick.stream', function( e, data ) {
		// If this no rows return then we kill the script
		if ( undefined === typeof data['wp-stream-heartbeat'] || 0 === data['wp-stream-heartbeat'].length ) {
			return;
		}

		var $new_items = $( data['wp-stream-heartbeat'].replace( /(\r\n|\n|\r)/gm, '' ) ).removeClass().addClass( 'new-row' );

		// Apply the good class to the list
		var even_or_odd = ( $new_items.length % 2 && ! $( 'li:first', list ).hasClass( 'alternate' ) ) ? ':even' : ':odd';

		// Add class to nth child because there is more than one element
		$new_items.filter(even_or_odd).addClass( 'alternate' );

		// Remove the number of element added to the end of the list table
		var show_on_screen = data.per_page || 5;
		var slice_items    = show_on_screen - ( $new_items.length + $( 'li', list ).length );

		if ( slice_items < 0 ) {
			$( 'li', list ).slice( slice_items ).remove();
		}

		// Remove the no items paragraph
		widget.find( 'p.no-items' ).remove();

		// Add element to the dom
		$new_items.slice( 0, data.per_page ).prependTo( list );

		// Update pagination
		var total_items_i18n = data.total_items_i18n || '';
		if ( total_items_i18n ) {
			$( '.total-pages', widget ).text( data.total_pages_i18n );
			$( '.pagination-links', widget ).find( '.next-page, .last-page' ) .toggleClass( 'disabled', data.total_pages === $( '.current-page' ).val() );
			$( '.pagination-links .last-page', widget ).attr( 'data-page', data.total_pages ).attr( 'href', data.last_page_link );
		}

		// Remove background after a certain amount of time
		setTimeout( function() {
			$new_items.addClass( 'fadeout' );
			setTimeout( function() {
				$new_items.removeClass( 'new-row fadeout' );
			}, 500 );
		}, 3000 );

	});

	// Pagination links
	widget.on( 'click', '.pagination-links a', function( e ) {
		e.preventDefault();

		var data = {
			'action': 'stream_activity_dashboard_update',
			'stream-paged': $( this ).data( 'page' )
		};

		$.post( window.ajaxurl, data, function( response ) {
			$( '.inside', widget ).html( response );
			list = widget.find( '.inside ul' );
		});
	});

});
