/* ATS Vacatures - sollicitatieformulier (verstuurt naar de WP-REST proxy). */
( function () {
	'use strict';

	function ready( fn ) {
		if ( 'loading' !== document.readyState ) {
			fn();
		} else {
			document.addEventListener( 'DOMContentLoaded', fn );
		}
	}

	function collectErrors( body ) {
		if ( ! body || ! body.errors ) {
			return '';
		}
		var parts = [];
		Object.keys( body.errors ).forEach( function ( key ) {
			parts.push( [].concat( body.errors[ key ] ).join( ' ' ) );
		} );
		return parts.join( ' ' );
	}

	ready( function () {
		if ( 'undefined' === typeof window.atsVacatures ) {
			return;
		}

		var forms = document.querySelectorAll( '.ats-apply-form' );

		Array.prototype.forEach.call( forms, function ( form ) {
			form.addEventListener( 'submit', function ( event ) {
				event.preventDefault();

				var message = form.querySelector( '.ats-form-message' );
				var button = form.querySelector( 'button[type="submit"]' );
				var data = new FormData( form );
				data.append( 'slug', form.getAttribute( 'data-slug' ) );

				message.className = 'ats-form-message ats-pending';
				message.textContent = window.atsVacatures.i18n.sending;
				button.disabled = true;

				fetch( window.atsVacatures.restUrl, {
					method: 'POST',
					headers: {
						'X-WP-Nonce': window.atsVacatures.nonce,
						Accept: 'application/json'
					},
					body: data
				} ).then( function ( response ) {
					return response.json().then( function ( body ) {
						return { ok: response.ok, body: body };
					} );
				} ).then( function ( result ) {
					if ( result.ok ) {
						form.reset();
						message.className = 'ats-form-message ats-success';
						message.textContent = ( result.body && result.body.message ) || window.atsVacatures.i18n.success;
						button.style.display = 'none';
					} else {
						var text = collectErrors( result.body ) ||
							( result.body && result.body.message ) ||
							window.atsVacatures.i18n.error;
						message.className = 'ats-form-message ats-error';
						message.textContent = text;
						button.disabled = false;
					}
				} ).catch( function () {
					message.className = 'ats-form-message ats-error';
					message.textContent = window.atsVacatures.i18n.error;
					button.disabled = false;
				} );
			} );
		} );
	} );
}() );
