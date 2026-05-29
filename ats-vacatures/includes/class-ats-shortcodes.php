<?php
/**
 * Shortcodes voor het tonen van vacatures.
 *
 * @package ATS_Vacatures
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ATS_Vacatures_Shortcodes {

	public function __construct() {
		add_shortcode( 'ats_vacatures', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	public function register_assets() {
		wp_register_style(
			'ats-vacatures',
			ATS_VACATURES_URL . 'assets/ats.css',
			array(),
			ATS_VACATURES_VERSION
		);

		wp_register_script(
			'ats-vacatures',
			ATS_VACATURES_URL . 'assets/ats.js',
			array(),
			ATS_VACATURES_VERSION,
			true
		);

		wp_localize_script(
			'ats-vacatures',
			'atsVacatures',
			array(
				'restUrl' => esc_url_raw( rest_url( 'ats/v1/apply' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'sending' => __( 'Bezig met verzenden…', 'ats-vacatures' ),
					'success' => __( 'Bedankt! Je sollicitatie is ontvangen.', 'ats-vacatures' ),
					'error'   => __( 'Er ging iets mis. Controleer je gegevens en probeer opnieuw.', 'ats-vacatures' ),
				),
			)
		);
	}

	/**
	 * Router: toont detail bij ?ats_vacature=slug, anders de lijst.
	 *
	 * @param array $atts Shortcode-attributen.
	 * @return string
	 */
	public function render( $atts ) {
		wp_enqueue_style( 'ats-vacatures' );

		$client = new ATS_Vacatures_Api_Client();

		if ( ! $client->is_configured() ) {
			return $this->notice( __( 'De ATS-koppeling is nog niet ingesteld (Instellingen → ATS Vacatures).', 'ats-vacatures' ) );
		}

		$slug = isset( $_GET['ats_vacature'] ) ? sanitize_title( wp_unslash( $_GET['ats_vacature'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( '' !== $slug ) {
			return $this->render_detail( $client, $slug );
		}

		return $this->render_list( $client, (array) $atts );
	}

	private function render_list( ATS_Vacatures_Api_Client $client, array $atts ) {
		$filters   = shortcode_atts(
			array(
				'department'      => '',
				'location'        => '',
				'employment_type' => '',
			),
			$atts,
			'ats_vacatures'
		);
		$vacancies = $client->get_vacancies( $filters );

		if ( empty( $vacancies ) ) {
			return $this->notice( __( 'Er zijn op dit moment geen openstaande vacatures.', 'ats-vacatures' ) );
		}

		ob_start();
		echo '<div class="ats-vacatures-list">';
		foreach ( $vacancies as $vacancy ) {
			$link = esc_url( add_query_arg( 'ats_vacature', rawurlencode( $vacancy['slug'] ) ) );
			echo '<a class="ats-vacature-card" href="' . $link . '">';
			echo '<h3 class="ats-vacature-title">' . esc_html( $vacancy['title'] ) . '</h3>';
			echo '<ul class="ats-vacature-meta">';
			$this->meta_item( $vacancy, 'department' );
			$this->meta_item( $vacancy, 'location' );
			$this->meta_item( $vacancy, 'employment_type_label' );
			$this->meta_item( $vacancy, 'hours' );
			echo '</ul>';
			echo '<span class="ats-vacature-more">' . esc_html__( 'Bekijk vacature →', 'ats-vacatures' ) . '</span>';
			echo '</a>';
		}
		echo '</div>';

		return ob_get_clean();
	}

	private function render_detail( ATS_Vacatures_Api_Client $client, $slug ) {
		$vacancy = $client->get_vacancy( $slug );

		if ( null === $vacancy ) {
			return $this->notice( __( 'Deze vacature is niet (meer) beschikbaar.', 'ats-vacatures' ) )
				. $this->back_link();
		}

		ob_start();
		echo '<article class="ats-vacature-detail">';
		echo $this->back_link();
		echo '<h1 class="ats-vacature-title">' . esc_html( $vacancy['title'] ) . '</h1>';

		echo '<ul class="ats-vacature-meta">';
		$this->meta_item( $vacancy, 'department' );
		$this->meta_item( $vacancy, 'location' );
		$this->meta_item( $vacancy, 'employment_type_label' );
		$this->meta_item( $vacancy, 'hours' );
		if ( ! empty( $vacancy['salary']['formatted'] ) ) {
			echo '<li class="ats-meta-salary">' . esc_html( $vacancy['salary']['formatted'] ) . '</li>';
		}
		echo '</ul>';

		if ( ! empty( $vacancy['description'] ) ) {
			echo '<div class="ats-vacature-section">' . wp_kses_post( $vacancy['description'] ) . '</div>';
		}

		if ( ! empty( $vacancy['requirements'] ) ) {
			echo '<h2>' . esc_html__( 'Wat we vragen', 'ats-vacatures' ) . '</h2>';
			echo '<div class="ats-vacature-section">' . wp_kses_post( $vacancy['requirements'] ) . '</div>';
		}

		wp_enqueue_script( 'ats-vacatures' );
		echo $this->apply_form( $vacancy );
		echo '</article>';

		return ob_get_clean();
	}

	private function apply_form( array $vacancy ) {
		ob_start();
		?>
		<section class="ats-apply" id="solliciteren">
			<h2><?php echo esc_html__( 'Solliciteer op deze vacature', 'ats-vacatures' ); ?></h2>
			<form class="ats-apply-form" data-slug="<?php echo esc_attr( $vacancy['slug'] ); ?>" enctype="multipart/form-data">
				<div class="ats-field">
					<label for="ats-name"><?php esc_html_e( 'Naam', 'ats-vacatures' ); ?> *</label>
					<input type="text" id="ats-name" name="name" required />
				</div>
				<div class="ats-field">
					<label for="ats-email"><?php esc_html_e( 'E-mailadres', 'ats-vacatures' ); ?> *</label>
					<input type="email" id="ats-email" name="email" required />
				</div>
				<div class="ats-field">
					<label for="ats-phone"><?php esc_html_e( 'Telefoonnummer', 'ats-vacatures' ); ?></label>
					<input type="tel" id="ats-phone" name="phone" />
				</div>
				<div class="ats-field">
					<label for="ats-motivation"><?php esc_html_e( 'Motivatie', 'ats-vacatures' ); ?></label>
					<textarea id="ats-motivation" name="motivation" rows="5"></textarea>
				</div>
				<div class="ats-field">
					<label for="ats-cv"><?php esc_html_e( 'CV (PDF, DOC of DOCX, max 5 MB)', 'ats-vacatures' ); ?></label>
					<input type="file" id="ats-cv" name="cv" accept=".pdf,.doc,.docx" />
				</div>
				<div class="ats-field ats-consent">
					<label>
						<input type="checkbox" name="consent" value="1" required />
						<?php esc_html_e( 'Ik ga akkoord met de verwerking van mijn gegevens voor deze sollicitatie.', 'ats-vacatures' ); ?> *
					</label>
				</div>
				<div class="ats-hp" aria-hidden="true">
					<label><?php esc_html_e( 'Laat dit veld leeg', 'ats-vacatures' ); ?>
						<input type="text" name="company_website" tabindex="-1" autocomplete="off" />
					</label>
				</div>
				<div class="ats-form-message" role="status" aria-live="polite"></div>
				<button type="submit" class="ats-button"><?php esc_html_e( 'Verstuur sollicitatie', 'ats-vacatures' ); ?></button>
			</form>
		</section>
		<?php
		return ob_get_clean();
	}

	private function meta_item( array $vacancy, $key ) {
		if ( ! empty( $vacancy[ $key ] ) ) {
			echo '<li class="ats-meta-' . esc_attr( $key ) . '">' . esc_html( $vacancy[ $key ] ) . '</li>';
		}
	}

	private function back_link() {
		$url = esc_url( remove_query_arg( 'ats_vacature' ) );
		return '<p class="ats-vacature-back"><a href="' . $url . '">&larr; ' . esc_html__( 'Alle vacatures', 'ats-vacatures' ) . '</a></p>';
	}

	private function notice( $message ) {
		return '<p class="ats-vacatures-notice">' . esc_html( $message ) . '</p>';
	}
}
