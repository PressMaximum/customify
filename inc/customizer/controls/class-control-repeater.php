<?php

class Customify_Customizer_Control_Repeater extends Customify_Customizer_Control_Base {
	static function field_template() {
		?>
		<script type="text/html" id="tmpl-field-customify-repeater">
			<?php
			self::before_field();
			?>
			<?php echo self::field_header(); ?>
			<div class="customify-field-settings-inner">
			</div>
			<?php
			self::after_field();
			?>
		</script>
		<script type="text/html" id="tmpl-customize-control-repeater-item">
			<div class="customify--repeater-item">
				<div class="customify--repeater-item-heading">
					<label class="customify--repeater-visible" title="<?php esc_attr_e( 'Toggle item visible', 'customify' ); ?>">
						<input type="checkbox" class="r-visible-input">
						<span class="r-visible-icon"></span>
						<span class="screen-reader-text"><?php _e( 'Show', 'customify' ); ?></label>
					<span class="customify--repeater-live-title"></span>
					<div class="customify-nav-reorder">
						<span class="customify--down" tabindex="-1">
							<span class="screen-reader-text"><?php _e( 'Move Down', 'customify' ); ?></span></span>
						<span class="customify--up" tabindex="0">
							<span class="screen-reader-text"><?php _e( 'Move Up', 'customify' ); ?></span>
						</span>
					</div>
					<a href="#" class="customify--repeater-item-toggle">
						<span class="screen-reader-text"><?php _e( 'Close', 'customify' ); ?></span></a>
				</div>
				<div class="customify--repeater-item-settings">
					<div class="customify--repeater-item-inside">
						<div class="customify--repeater-item-inner"></div>
						<# if ( data.addable ){ #>
						<a href="#" class="customify--remove"><?php _e( 'Remove', 'customify' ); ?></a>
						<# } #>
					</div>
				</div>
			</div>
		</script>
		<script type="text/html" id="tmpl-customize-control-repeater-inner">
			<div class="customify--repeater-inner">
				<div class="customify--settings-fields customify--repeater-items"></div>
				<div class="customify--repeater-actions">
				<a href="#" class="customify--repeater-reorder"
				data-text="<?php esc_attr_e( 'Reorder', 'customify' ); ?>"
				data-done="<?php _e( 'Done', 'customify' ); ?>"><?php _e( 'Reorder', 'customify' ); ?></a>
					<# if ( data.addable ){ #>
					<button type="button"
							class="button customify--repeater-add-new"><?php _e( 'Add an item', 'customify' ); ?></button>
					<# } #>
				</div>
			</div>
		</script>
		<?php
	}
}
