<?php

/**
 * Custom panel for the Header Builder — adds a React mount point for the items list.
 */
class Customify_Header_Builder_Panel extends WP_Customize_Panel {

	public $panel;
	public $type = 'customify_header_builder_panel';
	public $auto_expand_sole_section = true;

	public function json() {
		$array                   = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type', 'panel' ) );
		$array['title']          = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$array['content']        = $this->get_content();
		$array['active']         = $this->active();
		$array['instanceNumber'] = $this->instance_number;
		return $array;
	}

	protected function content_template() {
		?>
		<li class="panel-meta customize-info accordion-section <# if ( ! data.description ) { #>cannot-expand<# } #>">
			<button class="customize-panel-back" tabindex="-1" type="button">
				<span class="screen-reader-text"><?php _e( 'Back', 'customify' ); ?></span>
			</button>
			<div class="accordion-section-title">
				<span class="preview-notice"><?php
					printf(
						/* translators: %s: panel title */
						esc_html__( 'You are customizing %s', 'customify' ),
						'<strong class="panel-title">{{ data.title }}</strong>'
					);
				?></span>
				<# if ( data.description ) { #>
				<button class="customize-help-toggle dashicons dashicons-editor-help" type="button" aria-expanded="false">
					<span class="screen-reader-text"><?php _e( 'Help', 'customify' ); ?></span>
				</button>
				<div class="customize-panel-description">{{{ data.description }}}</div>
				<# } #>
			</div>
			<div class="customize-control-notifications-container"></div>
			<div id="customify-hb-panel-items" class="customify-builder-panel-items"></div>
		</li>
		<?php
	}
}

class Customify_Footer_Builder_Panel extends WP_Customize_Panel {

	public $panel;
	public $type = 'customify_footer_builder_panel';
	public $auto_expand_sole_section = true;

	public function json() {
		$array                   = wp_array_slice_assoc( (array) $this, array( 'id', 'description', 'priority', 'type', 'panel' ) );
		$array['title']          = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$array['content']        = $this->get_content();
		$array['active']         = $this->active();
		$array['instanceNumber'] = $this->instance_number;
		return $array;
	}

	protected function content_template() {
		?>
		<li class="panel-meta customize-info accordion-section <# if ( ! data.description ) { #>cannot-expand<# } #>">
			<button class="customize-panel-back" tabindex="-1" type="button">
				<span class="screen-reader-text"><?php _e( 'Back', 'customify' ); ?></span>
			</button>
			<div class="accordion-section-title">
				<span class="preview-notice"><?php
					printf(
						/* translators: %s: panel title */
						esc_html__( 'You are customizing %s', 'customify' ),
						'<strong class="panel-title">{{ data.title }}</strong>'
					);
				?></span>
				<# if ( data.description ) { #>
				<button class="customize-help-toggle dashicons dashicons-editor-help" type="button" aria-expanded="false">
					<span class="screen-reader-text"><?php _e( 'Help', 'customify' ); ?></span>
				</button>
				<div class="customize-panel-description">{{{ data.description }}}</div>
				<# } #>
			</div>
			<div class="customize-control-notifications-container"></div>
			<div id="customify-fb-panel-items" class="customify-builder-panel-items"></div>
		</li>
		<?php
	}
}

class Customify_WP_Customize_Panel extends WP_Customize_Panel {

	public $panel;
	public $type = 'customify_panel';

	/**
	 * Auto-expand a section in a panel when the panel is expanded when the panel only has the one section.
	 *
	 * @since 4.7.4
	 * @var bool
	 */
	public $auto_expand_sole_section = true;

	public function json() {

		$array = wp_array_slice_assoc(
			(array) $this,
			array(
				'id',
				'description',
				'priority',
				'type',
				'panel',
			)
		);
		$array['title']          = html_entity_decode( $this->title, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$array['content']        = $this->get_content();
		$array['active']         = $this->active();
		$array['instanceNumber'] = $this->instance_number;
		return $array;

	}

}
