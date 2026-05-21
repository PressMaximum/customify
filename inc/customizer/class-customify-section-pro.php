<?php
if ( ! defined( 'ABSPATH' ) ) { // Prevent direct access.
	exit;
}

if ( class_exists( 'WP_Customize_Section' ) && ! class_exists( 'Customify_WP_Customize_Section_Pro' ) ) {

	class Customify_WP_Customize_Section_Pro extends WP_Customize_Section {
		/**
		 * The type of customize section being rendered.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $type = 'customify-pro';
		/**
		 * Custom button text to output.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $pro_text = '';
		/**
		 * Custom plus section URL.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $pro_url = '';
		/**
		 * Custom section ID.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var    string
		 */
		public $id = '';

		public $teaser = false;
		public $features = array();

		/**
		 * Add custom parameters to pass to the JS via JSON.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return array
		 */
		public function json() {
			$json             = parent::json();
			$json['pro_text'] = $this->pro_text;
			$json['pro_url']  = $this->pro_url;
			$json['id']       = $this->id;
			$json['teaser']   = $this->teaser;
			$json['features'] = $this->features;

			return $json;
		}

		/**
		 * Outputs the Underscore.js template.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return void
		 */
		protected function render_template() { ?>
			<# if (  data.teaser ) { #>
			<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">
				<div class="customify-pro-teaser">
					<h3>{{{ data.title }}}</h3>
					<# if ( data.description ) { #>
					<p class="description">{{{ data.description }}}</p>
					<# } #>
					<# if ( 0 < data.features.length ) { #>
					<ul>
						<# _.each( data.features, function( feature, i ) { #>
						<li>{{{ feature }}}</li>
						<# }); #>
					</ul>
					<# } #>
					<a href="{{ data.pro_url }}" class="button button-primary" target="_blank" rel="noopener">
						<?php echo esc_html_x( 'Learn More', 'Customify Pro upsell', 'customify' ); ?>
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false" class="customify-pro-teaser__external-icon">
							<path d="M19.5 4.5h-7V6h4.44l-5.97 5.97 1.06 1.06L18 7.06v4.44h1.5v-7Zm-13 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3H17v3a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h3V5.5h-3Z" fill="currentColor"/>
						</svg>
					</a>
				</div>
			</li>
			<# } else { #>
			<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }} cannot-expand">
				<h3><a href="{{ data.pro_url }}" target="_blank">{{{ data.pro_text }}}</a></h3>
			</li>
			<# }  #>
		<?php }
	}


}
