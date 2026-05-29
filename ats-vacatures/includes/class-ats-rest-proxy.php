<?php
/**
 * REST-proxy: ontvangt het sollicitatieformulier en stuurt het serverzijdig
 * door naar het ATS, met de API-sleutel in een header (nooit in de browser).
 *
 * @package ATS_Vacatures
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATS_Vacatures_Rest_Proxy {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'ats/v1',
			'/apply',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_apply' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Verwerk een sollicitatie en stuur deze door naar het ATS.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function handle_apply( WP_REST_Request $request ) {
		$base_url = ATS_Vacatures_Settings::get( 'api_url' );
		$api_key  = ATS_Vacatures_Settings::get( 'api_key' );

		if ( empty( $base_url ) || empty( $api_key ) ) {
			return new WP_REST_Response(
				array( 'message' => __( 'De sollicitatiefunctie is nog niet geconfigureerd.', 'ats-vacatures' ) ),
				503
			);
		}

		$slug = sanitize_title( (string) $request->get_param( 'slug' ) );
		if ( '' === $slug ) {
			return new WP_REST_Response(
				array( 'message' => __( 'Onbekende vacature.', 'ats-vacatures' ) ),
				400
			);
		}

		$fields = array(
			'name'            => sanitize_text_field( (string) $request->get_param( 'name' ) ),
			'email'           => sanitize_email( (string) $request->get_param( 'email' ) ),
			'phone'           => sanitize_text_field( (string) $request->get_param( 'phone' ) ),
			'motivation'      => sanitize_textarea_field( (string) $request->get_param( 'motivation' ) ),
			'consent'         => $request->get_param( 'consent' ) ? '1' : '0',
			// Honeypot wordt doorgestuurd; het ATS handelt spam af.
			'company_website' => sanitize_text_field( (string) $request->get_param( 'company_website' ) ),
		);

		$files = $request->get_file_params();
		$cv    = isset( $files['cv'] ) ? $files['cv'] : null;

		$url      = trailingslashit( $base_url ) . 'api/v1/vacancies/' . rawurlencode( $slug ) . '/applications';
		$boundary = wp_generate_password( 24, false );
		$body     = $this->build_multipart_body( $fields, $cv, $boundary );

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'X-ATS-Key'    => $api_key,
					'Accept'       => 'application/json',
					'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array( 'message' => __( 'Er ging iets mis bij het versturen. Probeer het later opnieuw.', 'ats-vacatures' ) ),
				502
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $data ) ) {
			$data = array( 'message' => __( 'Onverwacht antwoord van de server.', 'ats-vacatures' ) );
		}

		return new WP_REST_Response( $data, $code > 0 ? $code : 502 );
	}

	/**
	 * Bouw een multipart/form-data body (incl. optioneel bestand).
	 *
	 * @param array      $fields   Tekstvelden.
	 * @param array|null $file     Bestand uit $_FILES (cv).
	 * @param string     $boundary Boundary.
	 * @return string
	 */
	private function build_multipart_body( array $fields, $file, $boundary ) {
		$eol  = "\r\n";
		$body = '';

		foreach ( $fields as $name => $value ) {
			$body .= '--' . $boundary . $eol;
			$body .= 'Content-Disposition: form-data; name="' . $name . '"' . $eol . $eol;
			$body .= $value . $eol;
		}

		if ( is_array( $file ) && ! empty( $file['tmp_name'] ) && is_uploaded_file( $file['tmp_name'] ) ) {
			$content  = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$filename = isset( $file['name'] ) ? basename( $file['name'] ) : 'cv';
			$type     = ! empty( $file['type'] ) ? $file['type'] : 'application/octet-stream';

			$body .= '--' . $boundary . $eol;
			$body .= 'Content-Disposition: form-data; name="cv"; filename="' . $filename . '"' . $eol;
			$body .= 'Content-Type: ' . $type . $eol . $eol;
			$body .= $content . $eol;
		}

		$body .= '--' . $boundary . '--' . $eol;

		return $body;
	}
}
