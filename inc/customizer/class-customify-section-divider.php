<?php
/**
 * Panel group divider section.
 *
 * A non-expandable section that renders as a visual heading in the Customizer
 * panel list. Used to separate groups of top-level panels (e.g. theme settings
 * vs. WordPress core & plugin settings) without adding any settings of its own.
 *
 * The section opts out of expand/collapse behaviour via the `cannot-expand`
 * CSS class — clicking it does nothing.
 *
 * @package customify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WP_Customize_Section' ) && ! class_exists( 'Customify_WP_Customize_Section_Divider' ) ) {

	class Customify_WP_Customize_Section_Divider extends WP_Customize_Section {

		/**
		 * Section type — paired with the `register_section_type()` call in
		 * Customify_Customizer::register_panel_groups() so the Customizer JS
		 * picks up our `render_template()` instead of falling back to the
		 * default section template.
		 *
		 * @var string
		 */
		public $type = 'customify-divider';

		/**
		 * Underscore template for the divider row.
		 *
		 * No description, no help toggle, no settings — just a labelled `<li>`
		 * with `cannot-expand` so the accordion behaviour is suppressed.
		 */
		protected function render_template() {
			?>
			<li
				id="accordion-section-{{ data.id }}"
				class="accordion-section control-section control-section-{{ data.type }} cannot-expand customify-divider"
			>
				<h3>{{{ data.title }}}</h3>
			</li>
			<?php
		}
	}
}
