(function () {
	'use strict';

	var form = document.querySelector( '.tb-contact-form' );
	if ( ! form || typeof tanyaContact === 'undefined' ) {
		return;
	}

	var status = form.querySelector( '.tb-contact-status' );
	var button = form.querySelector( '.tb-contact-submit' );
	var label = button ? button.textContent : 'Send to Tanya';

	function say( text, state ) {
		if ( status ) {
			status.textContent = text;
			status.setAttribute( 'data-state', state );
		}
	}

	form.addEventListener( 'submit', function ( event ) {
		event.preventDefault();

		var data = {};
		new FormData( form ).forEach( function ( value, key ) {
			data[ key ] = value;
		} );

		// Mirror the server's required fields so obvious mistakes are caught
		// without a round trip. The server still validates everything.
		if ( ! ( data.name || '' ).trim() ) {
			say( 'Please add your name so Tanya knows who she is replying to.', 'error' );
			form.querySelector( '#tb-c-name' ).focus();
			return;
		}
		if ( ! ( data.email || '' ).trim() ) {
			say( 'Please add an email address so Tanya can reply.', 'error' );
			form.querySelector( '#tb-c-email' ).focus();
			return;
		}

		if ( button ) {
			button.disabled = true;
			button.classList.add( 'is-loading' );
			button.textContent = 'Sending…';
		}
		say( 'Sending your message…', 'pending' );

		fetch( tanyaContact.endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': tanyaContact.nonce
			},
			body: JSON.stringify( data )
		} )
			.then( function ( response ) {
				return response.json().then( function ( body ) {
					return { ok: response.ok, body: body };
				} );
			} )
			.then( function ( result ) {
				if ( result.ok ) {
					form.reset();
					say( result.body.message || 'Thanks — your message is on its way.', 'success' );
				} else {
					say( result.body.message || 'Something went wrong. Please email tanya@tanyabarrans.com.', 'error' );
				}
			} )
			.catch( function () {
				say( 'Something went wrong sending your message. Please email tanya@tanyabarrans.com or call (425) 537-4728.', 'error' );
			} )
			.finally( function () {
				if ( button ) {
					button.disabled = false;
					button.classList.remove( 'is-loading' );
					button.textContent = label;
				}
			} );
	} );
})();
