<?php
/**
 * API-client die met het ATS communiceert.
 *
 * @package ATS_Vacatures
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATS_Vacatures_Api_Client {

	/**
	 * Basis-URL van de ATS (zonder trailing slash).
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Cacheduur in seconden.
	 *
	 * @var int
	 */
	private $cache_ttl;

	public function __construct() {
		$this->base_url  = ATS_Vacatures_Settings::get( 'api_url' );
		$this->cache_ttl = (int) ATS_Vacatures_Settings::get( 'cache_ttl', 600 );
	}

	public function is_configured() {
		return ! empty( $this->base_url );
	}

	/**
	 * Haal de lijst met gepubliceerde vacatures op.
	 *
	 * @param array $filters Optionele filters (department, location, employment_type, q).
	 * @return array Lijst van vacatures (kan leeg zijn).
	 */
	public function get_vacancies( array $filters = array() ) {
		$filters = array_filter(
			array(
				'department'      => isset( $filters['department'] ) ? sanitize_text_field( $filters['department'] ) : '',
				'location'        => isset( $filters['location'] ) ? sanitize_text_field( $filters['location'] ) : '',
				'employment_type' => isset( $filters['employment_type'] ) ? sanitize_text_field( $filters['employment_type'] ) : '',
				'q'               => isset( $filters['q'] ) ? sanitize_text_field( $filters['q'] ) : '',
			)
		);

		$url  = add_query_arg( $filters, $this->base_url . '/api/v1/vacancies' );
		$data = $this->request( $url, 'list_' . md5( $url ) );

		return is_array( $data ) ? $data : array();
	}

	/**
	 * Haal één vacature op via slug.
	 *
	 * @param string $slug Slug.
	 * @return array|null
	 */
	public function get_vacancy( $slug ) {
		$slug = sanitize_title( $slug );
		if ( '' === $slug ) {
			return null;
		}

		$url  = $this->base_url . '/api/v1/vacancies/' . rawurlencode( $slug );
		$data = $this->request( $url, 'item_' . md5( $url ) );

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Voer een GET-request uit met transient-caching.
	 *
	 * @param string $url       Volledige URL.
	 * @param string $cache_key Cachesleutel.
	 * @return mixed Gedecodeerde 'data' of null.
	 */
	private function request( $url, $cache_key ) {
		if ( ! $this->is_configured() ) {
			return null;
		}

		$transient = 'ats_vac_' . $cache_key;

		if ( $this->cache_ttl > 0 ) {
			$cached = get_transient( $transient );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 8,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== (int) $code ) {
			return null;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $body['data'] ) ) {
			return null;
		}

		$data = $body['data'];

		if ( $this->cache_ttl > 0 ) {
			set_transient( $transient, $data, $this->cache_ttl );
		}

		return $data;
	}
}
