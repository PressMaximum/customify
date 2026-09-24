<?php
/**
 * Term picker control - searchable, AJAX paged multi-select of terms.
 *
 * Config keys (besides the usual ones):
 *
 * - `taxonomy`         Fixed taxonomy slug, or
 * - `taxonomy_setting` Id of a select setting holding the taxonomy slug. The
 *                      picker follows it live: switching the taxonomy clears
 *                      the selection (the old IDs belong to the old
 *                      taxonomy) and the next search queries the new one.
 *
 * Stored value: array of term IDs in the picked order. Pair the field with
 * `'sanitize_callback' => 'customify_term_picker_sanitize_setting'`.
 *
 * Only the labels of the selected terms are sent with the control; the
 * rest is fetched page by page through customify_term_picker_ajax_search()
 * (inc/customizer/term-picker.php). The JS side lives in
 * src/backend/customizer/js/controls/term-picker.js.
 *
 * @package customify
 * @since   0.4.26
 */
class Customify_Customizer_Control_Term_Picker extends Customify_Customizer_Control_Base {

	/**
	 * Fixed taxonomy slug.
	 *
	 * @var string
	 */
	public $taxonomy = '';

	/**
	 * Setting id the taxonomy is read from.
	 *
	 * @var string
	 */
	public $taxonomy_setting = '';

	/**
	 * Pass the picker data to the JS template.
	 */
	public function to_json() {
		parent::to_json();

		$ids      = customify_term_picker_sanitize_ids( $this->value() );
		$taxonomy = $this->current_taxonomy();

		$this->json['value']            = $ids;
		$this->json['taxonomy']         = $taxonomy;
		$this->json['taxonomy_setting'] = $this->taxonomy_setting;
		$this->json['term_choices']     = customify_term_picker_get_choices( $ids, $taxonomy );
		$this->json['nonce']            = wp_create_nonce( 'customify_term_picker' );
		$this->json['ajax_url']         = admin_url( 'admin-ajax.php' );
	}

	/**
	 * Taxonomy the saved IDs belong to.
	 *
	 * @return string
	 */
	protected function current_taxonomy() {
		if ( $this->taxonomy_setting ) {
			$setting = $this->manager->get_setting( $this->taxonomy_setting );

			if ( $setting ) {
				$value = $setting->value();

				if ( is_string( $value ) && '' !== $value ) {
					return sanitize_key( $value );
				}

				if ( is_string( $setting->default ) ) {
					return sanitize_key( $setting->default );
				}
			}
		}

		return sanitize_key( (string) $this->taxonomy );
	}

	/**
	 * Underscore template, compiled by customifyField.add().
	 */
	public static function field_template() {
		echo '<script type="text/html" id="tmpl-field-customify-term_picker">';
		self::before_field();
		?>
		<?php echo self::field_header(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static template markup. ?>
		<div class="customify-field-settings-inner customify-term-picker-wrap">
			<select class="customify-input customify-term-picker" multiple="multiple"
				data-name="{{ field.name }}"
				data-taxonomy="{{ field.taxonomy }}"
				data-taxonomy-setting="{{ field.taxonomy_setting }}"
				data-nonce="{{ field.nonce }}"
				data-ajax-url="{{ field.ajax_url }}"
				data-placeholder="<?php esc_attr_e( 'Search terms…', 'customify' ); ?>"
				data-no-results="<?php esc_attr_e( 'No terms found', 'customify' ); ?>"
				data-searching="<?php esc_attr_e( 'Searching…', 'customify' ); ?>"
				data-error="<?php esc_attr_e( 'The terms could not be loaded.', 'customify' ); ?>">
				<# _.each( field.term_choices, function( choice ) { #>
					<option value="{{ choice.id }}" selected="selected">{{ choice.text }}</option>
				<# } ); #>
			</select>
		</div>
		<?php
		self::after_field();
		echo '</script>';
	}
}
