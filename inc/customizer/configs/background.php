<?php
/**
 * Customizer config: Background.
 *
 * Note: The 3 background composite controls (Page / Content Area / Site Content)
 * previously registered here have been moved to inc/customizer/configs/colors.php
 * to consolidate all site-wide color/background settings into the new "Colors"
 * top-level section.
 *
 * The legacy empty `site_content_styling` section registered here (no controls
 * pointed to it) has been removed.
 *
 * Theme_mod storage keys are unchanged (`background`, `site_content_styling`,
 * `content_background`), preserving saved values on existing 30K+ sites.
 *
 * Class kept as a stub in case external code instantiates it; the config()
 * callback intentionally returns the input array unchanged.
 *
 * @package Customify
 */

class Customify_Advanced_Styling_Background {

	function __construct() {
		add_filter( 'customify/customizer/config', array( $this, 'config' ), 100 );
	}

	function config( $configs = array() ) {
		// Moved to inc/customizer/configs/colors.php.
		return $configs;
	}

}

new Customify_Advanced_Styling_Background();
