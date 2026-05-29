<?php
/**
 * Instellingenpagina voor de ATS Vacatures plugin.
 *
 * @package ATS_Vacatures
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATS_Vacatures_Settings {

	const OPTION_KEY = 'ats_vacatures_options';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Haal een instelling op.
	 *
	 * @param string $key     Sleutel.
	 * @param mixed  $default Standaardwaarde.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		$options = get_option( self::OPTION_KEY, array() );
		return isset( $options[ $key ] ) && '' !== $options[ $key ] ? $options[ $key ] : $default;
	}

	public function add_menu() {
		add_options_page(
			__( 'ATS Vacatures', 'ats-vacatures' ),
			__( 'ATS Vacatures', 'ats-vacatures' ),
			'manage_options',
			'ats-vacatures',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'ats_vacatures_group',
			self::OPTION_KEY,
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'ats_vacatures_section',
			__( 'ATS-koppeling', 'ats-vacatures' ),
			function () {
				echo '<p>' . esc_html__( 'Koppel deze site aan je ATS-applicatie.', 'ats-vacatures' ) . '</p>';
			},
			'ats-vacatures'
		);

		add_settings_field(
			'api_url',
			__( 'ATS API-URL', 'ats-vacatures' ),
			array( $this, 'field_api_url' ),
			'ats-vacatures',
			'ats_vacatures_section'
		);

		add_settings_field(
			'api_key',
			__( 'API-sleutel', 'ats-vacatures' ),
			array( $this, 'field_api_key' ),
			'ats-vacatures',
			'ats_vacatures_section'
		);

		add_settings_field(
			'cache_ttl',
			__( 'Cacheduur (seconden)', 'ats-vacatures' ),
			array( $this, 'field_cache_ttl' ),
			'ats-vacatures',
			'ats_vacatures_section'
		);
	}

	/**
	 * Sanitize instellingen.
	 *
	 * @param array $input Ruwe input.
	 * @return array
	 */
	public function sanitize( $input ) {
		return array(
			'api_url'   => isset( $input['api_url'] ) ? esc_url_raw( untrailingslashit( trim( $input['api_url'] ) ) ) : '',
			'api_key'   => isset( $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : '',
			'cache_ttl' => isset( $input['cache_ttl'] ) ? absint( $input['cache_ttl'] ) : 600,
		);
	}

	public function field_api_url() {
		printf(
			'<input type="url" name="%s[api_url]" value="%s" class="regular-text" placeholder="https://ats.voorbeeld.nl" />',
			esc_attr( self::OPTION_KEY ),
			esc_attr( self::get( 'api_url' ) )
		);
		echo '<p class="description">' . esc_html__( 'De basis-URL van je ATS, zonder /api/v1.', 'ats-vacatures' ) . '</p>';
	}

	public function field_api_key() {
		printf(
			'<input type="text" name="%s[api_key]" value="%s" class="regular-text" autocomplete="off" />',
			esc_attr( self::OPTION_KEY ),
			esc_attr( self::get( 'api_key' ) )
		);
		echo '<p class="description">' . esc_html__( 'Wordt gebruikt voor het indienen van sollicitaties (serverzijde).', 'ats-vacatures' ) . '</p>';
	}

	public function field_cache_ttl() {
		printf(
			'<input type="number" min="0" step="60" name="%s[cache_ttl]" value="%s" class="small-text" />',
			esc_attr( self::OPTION_KEY ),
			esc_attr( self::get( 'cache_ttl', 600 ) )
		);
		echo '<p class="description">' . esc_html__( 'Hoe lang vacaturedata gecachet wordt. 0 = niet cachen.', 'ats-vacatures' ) . '</p>';
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'ATS Vacatures', 'ats-vacatures' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'ats_vacatures_group' );
				do_settings_sections( 'ats-vacatures' );
				submit_button();
				?>
			</form>
			<hr />
			<h2><?php echo esc_html__( 'Gebruik', 'ats-vacatures' ); ?></h2>
			<p><?php echo esc_html__( 'Plaats de volgende shortcode op een pagina om de vacatures te tonen:', 'ats-vacatures' ); ?></p>
			<p><code>[ats_vacatures]</code></p>
		</div>
		<?php
	}
}
