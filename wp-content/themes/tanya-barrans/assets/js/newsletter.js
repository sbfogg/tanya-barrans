(function () {
	var form = document.querySelector( '.tb-newsletter-form' );

	if ( ! form || typeof tanyaNewsletter === 'undefined' ) {
		return;
	}

	var emailInput = form.querySelector( 'input[name="email"]' );
	var nameInput = form.querySelector( 'input[name="name"]' );
	var hpInput = form.querySelector( 'input[name="website"]' );
	var button = form.querySelector( '.tb-newsletter-submit' );
	var status = form.querySelector( '.tb-newsletter-status' );

	function setStatus( message, state ) {
		status.textContent = message;
		status.setAttribute( 'data-state', state );
	}

	form.addEventListener( 'submit', function ( event ) {
		event.preventDefault();

		var email = ( emailInput.value || '' ).trim();

		if ( ! email || ! emailInput.checkValidity() ) {
			setStatus( 'Please enter a valid email address.', 'error' );
			emailInput.focus();
			return;
		}

		button.disabled = true;
		button.classList.add( 'is-loading' );
		setStatus( 'Adding you…', 'pending' );

		fetch( tanyaNewsletter.endpoint, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': tanyaNewsletter.nonce
			},
			body: JSON.stringify( {
				email: email,
				name: ( nameInput.value || '' ).trim(),
				website: hpInput ? hpInput.value : ''
			} )
		} )
			.then( function ( res ) {
				return res.json().then( function ( data ) {
					return { ok: res.ok, data: data };
				} );
			} )
			.then( function ( result ) {
				var message = ( result.data && result.data.message ) || 'Something went wrong. Please try again.';

				if ( result.ok ) {
					setStatus( message, 'success' );
					form.reset();
				} else {
					setStatus( message, 'error' );
					button.disabled = false;
				}
			} )
			.catch( function () {
				setStatus( 'Network error. Please try again in a moment.', 'error' );
				button.disabled = false;
			} )
			.then( function () {
				button.classList.remove( 'is-loading' );
			} );
	} );
})();
