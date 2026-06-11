<?php
/**
 * Customizer Style Guide template (see inc/style-guide.php).
 *
 * A standalone front-end page: wp_head()/wp_footer() bring the theme
 * stylesheets, the emitted token/auto CSS and the customize-preview
 * machinery, so every specimen below renders EXACTLY like the live site
 * and reacts to Customizer changes in real time. The guide's own chrome
 * (cards, labels, pencils) uses translucent neutrals derived from the
 * inherited text color, so it stays readable on any site background —
 * dark sites get a dark guide.
 *
 * Specimens deliberately carry NO font/size/color overrides: headings,
 * body copy, links, lists, quotes, the .button and the form fields are
 * styled by the theme alone.
 */

if ( ! is_customize_preview() ) {
	exit;
}

$customify_sg_logo_id  = (int) get_theme_mod( 'custom_logo' );
$customify_sg_logo_url = $customify_sg_logo_id ? wp_get_attachment_image_url( $customify_sg_logo_id, 'medium' ) : '';
$customify_sg_icon_url = get_site_icon_url( 64 );

// Color slot cards: label, control id, CSS token (chip background).
$customify_sg_slots = array(
	array( 'Primary', 'global_styling_color_primary', '--customify-primary' ),
	array( 'Secondary', 'global_styling_color_secondary', '--customify-secondary' ),
	array( 'Accent', 'customify_palette_accent', '--customify-accent' ),
	array( 'Text', 'customify_palette_text', '--customify-text' ),
	array( 'Surface', 'customify_palette_surface', '--customify-surface' ),
	array( 'Base', 'customify_palette_base', '--customify-base' ),
);

// Derived tokens (read-only, computed by the palette engine).
$customify_sg_derived = array(
	array( 'Text Muted', '--customify-text-muted' ),
	array( 'Link', '--customify-link' ),
	array( 'Heading', '--customify-heading' ),
	array( 'On Primary', '--customify-on-primary' ),
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
<style>
	/* Guide chrome only — specimens inherit the theme styles untouched. */
	.csg {
		--csg-line: color-mix(in srgb, currentColor 14%, transparent);
		--csg-hover: color-mix(in srgb, currentColor 5%, transparent);
		--csg-muted: color-mix(in srgb, currentColor 55%, transparent);
		max-width: 1120px;
		margin: 0 auto;
		padding: 40px 48px 72px;
	}

	/* The guide's own chrome buttons are UI, not specimens — the theme's
	   Button Styling (bare `button` selector + the user's saved colors)
	   must never skin them. !important beats the emitted styling rules
	   regardless of their specificity. */
	.csg .csg-edit,
	.csg .csg-close {
		padding: 0 !important;
		margin: 0 !important;
		border: none !important;
		min-height: 0 !important;
		line-height: 1 !important;
		letter-spacing: 0 !important;
		text-transform: none !important;
		text-decoration: none !important;
	}
	.csg .csg-edit {
		background: #fff !important;
		color: #1d2327 !important;
	}
	.csg .csg-edit:hover {
		background: var(--csg-primary, #2271b1) !important;
		color: #fff !important;
	}
	.csg .csg-close {
		background: transparent !important;
		color: inherit !important;
		box-shadow: inset 0 0 0 1px var(--csg-line) !important;
	}
	.csg .csg-close:hover {
		background: var(--csg-hover) !important;
	}
	.csg-sec-label {
		font-size: 11px;
		font-weight: 600;
		letter-spacing: .11em;
		text-transform: uppercase;
		color: var(--csg-muted);
		margin-bottom: 14px;
	}
	.csg-section {
		padding: 32px 0;
		border-top: 1px solid var(--csg-line);
	}
	.csg-section:first-of-type {
		border-top: none;
		padding-top: 0;
	}
	.csg-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 36px;
	}
	.csg-header .csg-title {
		font-size: 17px;
		font-weight: 700;
		margin: 0;
	}
	.csg-header .csg-title em {
		font-style: normal;
		font-weight: 500;
		color: var(--csg-muted);
		margin-left: 8px;
		font-size: 13px;
	}
	.csg-close {
		width: 40px;
		height: 40px;
		border-radius: 50%;
		border: none;
		background: transparent;
		box-shadow: inset 0 0 0 1px var(--csg-line);
		cursor: pointer;
		display: grid;
		place-items: center;
		color: inherit;
		transition: background .15s ease;
		flex: none;
		padding: 0;
	}
	.csg-close:hover {
		background: var(--csg-hover);
	}
	.csg-close svg {
		width: 17px;
		height: 17px;
	}

	/* Editable hover + pencil */
	.csg-editable {
		position: relative;
		border-radius: 10px;
		transition: background .15s ease;
	}
	.csg-editable:hover {
		background: var(--csg-hover);
	}
	.csg-edit {
		position: absolute;
		top: 6px;
		right: 6px;
		width: 26px;
		height: 26px;
		border-radius: 7px;
		border: none;
		background: #fff;
		box-shadow: 0 1px 4px rgba(15, 23, 42, .3);
		display: grid;
		place-items: center;
		cursor: pointer;
		color: #1d2327;
		opacity: 0;
		transform: scale(.92);
		transition: opacity .15s ease, transform .15s ease, background .15s ease;
		z-index: 2;
		padding: 0;
	}
	.csg-edit svg {
		width: 12px;
		height: 12px;
	}
	.csg-editable:hover .csg-edit,
	.csg-edit:focus-visible {
		opacity: 1;
		transform: scale(1);
	}
	.csg-edit:hover {
		background: var(--csg-primary, #2271b1);
		color: #fff;
	}

	/* Identity row */
	.csg-identity {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 16px;
		align-items: stretch;
	}
	.csg-id-card {
		min-height: 124px;
		border-radius: 12px;
		box-shadow: inset 0 0 0 1px var(--csg-line);
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		text-align: center;
		padding: 20px;
		gap: 4px;
	}
	.csg-logo-img {
		max-height: 64px;
		max-width: 80%;
		width: auto;
	}
	.csg-logo-mark {
		width: 44px;
		height: 44px;
		border-radius: 11px;
		background: var(--csg-primary, #2271b1);
		color: var(--customify-on-primary, #fff);
		display: grid;
		place-items: center;
		font-size: 20px;
		font-weight: 700;
	}
	.csg-browser-tab {
		display: flex;
		align-items: center;
		gap: 8px;
		background: var(--csg-hover);
		box-shadow: inset 0 0 0 1px var(--csg-line);
		border-radius: 8px;
		padding: 8px 14px;
		font-size: 12px;
	}
	.csg-browser-tab img {
		width: 16px;
		height: 16px;
		border-radius: 3px;
	}
	.csg-browser-tab .csg-dot {
		width: 15px;
		height: 15px;
		border-radius: 4px;
		background: var(--csg-primary, #2271b1);
		color: var(--customify-on-primary, #fff);
		display: grid;
		place-items: center;
		font-size: 9px;
		font-weight: 700;
	}

	/* Colors */
	.csg-colors {
		display: grid;
		grid-template-columns: repeat(5, 1fr);
		gap: 12px;
	}
	.csg-ccard {
		border-radius: 10px;
		overflow: hidden;
		box-shadow: inset 0 0 0 1px var(--csg-line);
	}
	.csg-chip {
		height: 52px;
		position: relative;
	}
	.csg-cmeta {
		padding: 9px 11px 10px;
	}
	.csg-cname {
		font-size: 12px;
		font-weight: 600;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.csg-cvalue {
		font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
		font-size: 10px;
		color: var(--csg-muted);
		margin-top: 3px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.csg-auto {
		position: absolute;
		top: 6px;
		right: 6px;
		font-size: 9px;
		font-weight: 600;
		letter-spacing: .06em;
		text-transform: uppercase;
		color: rgba(255, 255, 255, .95);
		background: rgba(15, 23, 42, .35);
		padding: 2.5px 7px;
		border-radius: 99px;
		pointer-events: none;
	}

	/* Elements + typography */
	.csg-elements {
		display: flex;
		flex-wrap: wrap;
		gap: 16px;
		align-items: center;
	}
	.csg-el {
		padding: 16px 48px 16px 16px;
		display: inline-flex;
		align-items: center;
		gap: 12px;
	}
	.csg-type-grid {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 8px 64px;
	}
	.csg-type-line {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
		gap: 24px;
		padding: 9px 44px 9px 14px;
	}
	.csg-type-line :is(h1, h2, h3, h4, h5, h6) {
		margin: 0;
	}
	.csg-spec {
		font-size: 10.5px;
		color: var(--csg-muted);
		white-space: nowrap;
		font-variant-numeric: tabular-nums;
	}
	.csg-body-block {
		padding: 10px 44px 10px 14px;
	}
	.csg-body-block p {
		margin: 0;
	}
	.csg-quote-block {
		padding: 10px 44px 10px 14px;
		margin-top: 2px;
	}
	.csg-quote-block blockquote {
		margin: 0;
	}
	.csg-lists-block {
		padding: 10px 44px 10px 14px;
		margin-top: 2px;
		display: flex;
		gap: 48px;
	}
	.csg-lists-block ul,
	.csg-lists-block ol {
		margin: 0;
		padding-left: 18px;
	}

	@media (max-width: 880px) {
		.csg {
			padding: 20px 16px 40px;
		}
		.csg-identity {
			grid-template-columns: 1fr;
			gap: 12px;
		}
		.csg-type-grid {
			grid-template-columns: 1fr;
			gap: 6px;
		}
		.csg-colors {
			grid-template-columns: repeat(3, 1fr);
		}
	}
	@media (prefers-reduced-motion: reduce) {
		.csg *,
		.csg *::before,
		.csg *::after {
			transition: none !important;
		}
	}
</style>
</head>
<body <?php body_class( 'customify-style-guide' ); ?>>

<?php
// Guide-scoped primary accent: prefer the live token, fall back to the
// saved slot so the chrome never depends on cascade health (see the
// token-rescue note in the script below).
$customify_sg_primary = get_theme_mod( 'global_styling_color_primary' );
$customify_sg_primary = ( is_string( $customify_sg_primary ) && '' !== $customify_sg_primary ) ? $customify_sg_primary : '#2271b1';
?>
<div class="csg" style="--csg-primary: var(--customify-primary, <?php echo esc_attr( $customify_sg_primary ); ?>);">

	<div class="csg-header">
		<p class="csg-title"><?php esc_html_e( 'Style Guide', 'customify' ); ?><em><?php bloginfo( 'name' ); ?></em></p>
		<button type="button" class="csg-close" aria-label="<?php esc_attr_e( 'Close style guide', 'customify' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
		</button>
	</div>

	<div class="csg-section csg-identity" style="padding-top: 0;">
		<div class="csg-id-card csg-editable">
			<?php if ( $customify_sg_logo_url ) : ?>
				<img class="csg-logo-img" src="<?php echo esc_url( $customify_sg_logo_url ); ?>" alt="">
			<?php else : ?>
				<div class="csg-logo-mark"><?php echo esc_html( strtoupper( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ) ); ?></div>
			<?php endif; ?>
			<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="custom_logo" aria-label="<?php esc_attr_e( 'Edit site logo', 'customify' ); ?>"></button>
		</div>
		<div class="csg-id-card csg-editable">
			<div class="site-title" style="margin: 0;"><?php bloginfo( 'name' ); ?></div>
			<div class="site-description" style="margin: 0;"><?php bloginfo( 'description' ); ?></div>
			<button type="button" class="csg-edit" data-focus-type="section" data-focus-id="title_tagline" aria-label="<?php esc_attr_e( 'Edit site title and tagline', 'customify' ); ?>"></button>
		</div>
		<div class="csg-id-card csg-editable">
			<div class="csg-browser-tab">
				<?php if ( $customify_sg_icon_url ) : ?>
					<img src="<?php echo esc_url( $customify_sg_icon_url ); ?>" alt="">
				<?php else : ?>
					<span class="csg-dot"><?php echo esc_html( strtoupper( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ) ); ?></span>
				<?php endif; ?>
				<?php bloginfo( 'name' ); ?>
			</div>
			<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="site_icon" aria-label="<?php esc_attr_e( 'Edit site icon', 'customify' ); ?>"></button>
		</div>
	</div>

	<div class="csg-section">
		<div class="csg-sec-label"><?php esc_html_e( 'Colors', 'customify' ); ?></div>
		<div class="csg-colors">
			<?php
			foreach ( $customify_sg_slots as $customify_sg_slot ) :
				// Inline fallback from the saved setting: not every slot
				// token is emitted by the palette engine (e.g.
				// --customify-surface), and the chip must still show.
				$customify_sg_fb = get_theme_mod( $customify_sg_slot[1] );
				$customify_sg_fb = ( is_string( $customify_sg_fb ) && '' !== $customify_sg_fb ) ? $customify_sg_fb : '';
				?>
				<div class="csg-ccard csg-editable">
					<div class="csg-chip" data-setting="<?php echo esc_attr( $customify_sg_slot[1] ); ?>" style="background: var(<?php echo esc_attr( $customify_sg_slot[2] ); ?><?php echo $customify_sg_fb ? ', ' . esc_attr( $customify_sg_fb ) : ''; ?>); box-shadow: inset 0 0 0 1px var(--csg-line);"></div>
					<div class="csg-cmeta">
						<div class="csg-cname"><?php echo esc_html( $customify_sg_slot[0] ); ?></div>
						<div class="csg-cvalue" data-token="<?php echo esc_attr( $customify_sg_slot[2] ); ?>">&nbsp;</div>
					</div>
					<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="<?php echo esc_attr( $customify_sg_slot[1] ); ?>" aria-label="<?php echo esc_attr( $customify_sg_slot[0] ); ?>"></button>
				</div>
			<?php endforeach; ?>
			<?php foreach ( $customify_sg_derived as $customify_sg_d ) : ?>
				<div class="csg-ccard">
					<div class="csg-chip" style="background: var(<?php echo esc_attr( $customify_sg_d[1] ); ?>); box-shadow: inset 0 0 0 1px var(--csg-line);"><span class="csg-auto"><?php esc_html_e( 'Auto', 'customify' ); ?></span></div>
					<div class="csg-cmeta">
						<div class="csg-cname"><?php echo esc_html( $customify_sg_d[0] ); ?></div>
						<div class="csg-cvalue" data-token="<?php echo esc_attr( $customify_sg_d[1] ); ?>">&nbsp;</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="csg-section">
		<div class="csg-sec-label"><?php esc_html_e( 'Elements', 'customify' ); ?></div>
		<div class="csg-elements">
			<div class="csg-el csg-editable">
				<a class="button" href="#" onclick="return false;"><?php esc_html_e( 'Button', 'customify' ); ?></a>
				<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="customify_button_styling" aria-label="<?php esc_attr_e( 'Edit button styling', 'customify' ); ?>"></button>
			</div>
			<div class="csg-el csg-editable">
				<input type="text" placeholder="<?php esc_attr_e( 'Text field', 'customify' ); ?>" readonly>
				<select aria-label="<?php esc_attr_e( 'Select field', 'customify' ); ?>"><option><?php esc_html_e( 'Select field', 'customify' ); ?></option></select>
				<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="customify_field_styling" aria-label="<?php esc_attr_e( 'Edit field styling', 'customify' ); ?>"></button>
			</div>
		</div>
	</div>

	<div class="csg-section">
		<div class="csg-sec-label"><?php esc_html_e( 'Typography', 'customify' ); ?></div>
		<div class="csg-type-grid">
			<div>
				<?php for ( $customify_sg_i = 1; $customify_sg_i <= 6; $customify_sg_i++ ) : ?>
					<div class="csg-type-line csg-editable">
						<?php printf( '<h%1$d>%2$s %1$d</h%1$d>', (int) $customify_sg_i, esc_html__( 'Heading', 'customify' ) ); ?>
						<span class="csg-spec" data-spec="h<?php echo (int) $customify_sg_i; ?>">&nbsp;</span>
						<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="global_typography_heading_h<?php echo (int) $customify_sg_i; ?>" aria-label="<?php esc_attr_e( 'Edit heading typography', 'customify' ); ?>"></button>
					</div>
				<?php endfor; ?>
				<div class="csg-type-line csg-editable">
					<span class="csg-spec"><?php esc_html_e( 'Heading font family & weight', 'customify' ); ?></span>
					<span class="csg-spec" data-spec-family="h2">&nbsp;</span>
					<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="global_typography_base_heading" aria-label="<?php esc_attr_e( 'Edit heading font', 'customify' ); ?>"></button>
				</div>
			</div>
			<div>
				<div class="csg-body-block csg-editable">
					<p><?php esc_html_e( 'Here is how body text will look on your website — with', 'customify' ); ?> <a href="#" onclick="return false;"><?php esc_html_e( 'text links', 'customify' ); ?></a>, <strong><?php esc_html_e( 'bold emphasis', 'customify' ); ?></strong> <?php esc_html_e( 'and', 'customify' ); ?> <em><?php esc_html_e( 'italics', 'customify' ); ?></em>. <?php esc_html_e( 'Good typography does its job quietly: comfortable line height, balanced measure, and enough contrast to read without effort.', 'customify' ); ?></p>
					<p class="csg-spec" style="margin: 8px 0 0;" data-spec="p">&nbsp;</p>
					<button type="button" class="csg-edit" data-focus-type="control" data-focus-id="global_typography_base_p" aria-label="<?php esc_attr_e( 'Edit body typography', 'customify' ); ?>"></button>
				</div>
				<div class="csg-quote-block csg-editable">
					<blockquote>
						<?php esc_html_e( 'The future belongs to those who believe in the beauty of their dreams.', 'customify' ); ?>
						<cite>Eleanor Roosevelt</cite>
					</blockquote>
					<button type="button" class="csg-edit" data-focus-type="section" data-focus-id="typography_panel" aria-label="<?php esc_attr_e( 'Edit typography', 'customify' ); ?>"></button>
				</div>
				<div class="csg-lists-block csg-editable">
					<ul>
						<li><?php esc_html_e( 'Unordered item', 'customify' ); ?></li>
						<li><?php esc_html_e( 'Unordered item', 'customify' ); ?></li>
					</ul>
					<ol>
						<li><?php esc_html_e( 'Ordered item', 'customify' ); ?></li>
						<li><?php esc_html_e( 'Ordered item', 'customify' ); ?></li>
					</ol>
					<button type="button" class="csg-edit" data-focus-type="section" data-focus-id="typography_panel" aria-label="<?php esc_attr_e( 'Edit typography', 'customify' ); ?>"></button>
				</div>
			</div>
		</div>
	</div>

</div>

<script>
( function() {
	var pencil = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
	document.querySelectorAll( '.csg-edit' ).forEach( function( btn ) {
		btn.innerHTML = pencil;
	} );

	function send( id, data ) {
		if ( window.wp && wp.customize && wp.customize.preview ) {
			wp.customize.preview.send( id, data );
		}
	}

	document.addEventListener( 'click', function( e ) {
		var btn = e.target.closest( '.csg-edit' );
		if ( btn ) {
			e.preventDefault();
			send( 'customify-style-guide-focus', {
				type: btn.getAttribute( 'data-focus-type' ),
				id: btn.getAttribute( 'data-focus-id' )
			} );
			return;
		}
		if ( e.target.closest( '.csg-close' ) ) {
			e.preventDefault();
			send( 'customify-style-guide-close' );
		}
	} );

	// ── Display-only meta: color hex labels + type specs ───────────────
	// Read from the rendered page so the labels always match what the
	// visitor would actually get; refreshed (debounced) on every
	// customize setting change that reaches the preview.
	var hexCanvas = document.createElement( 'canvas' );
	hexCanvas.width = hexCanvas.height = 1;
	var hexCtx = hexCanvas.getContext( '2d', { willReadFrequently: true } );

	// Computed backgrounds can come back in any color space (rgb(),
	// color(srgb …), oklab() from color-mix) — rasterize one pixel to
	// normalize everything to hex/rgba.
	function toHex( value ) {
		if ( ! value || ! hexCtx ) {
			return value || '';
		}
		hexCtx.clearRect( 0, 0, 1, 1 );
		hexCtx.fillStyle = '#000';
		hexCtx.fillStyle = value;
		hexCtx.fillRect( 0, 0, 1, 1 );
		var px = hexCtx.getImageData( 0, 0, 1, 1 ).data;
		if ( px[3] < 255 ) {
			return 'rgba(' + px[0] + ',' + px[1] + ',' + px[2] + ',' + ( Math.round( px[3] / 2.55 ) / 100 ) + ')';
		}
		return ( '#' + [ px[0], px[1], px[2] ].map( function( n ) {
			return ( '0' + n.toString( 16 ) ).slice( -2 );
		} ).join( '' ) ).toUpperCase();
	}

	// Cascade rescue: mid-session the palette token <style> can stop
	// participating in the cascade (its values are still in the tag's
	// text, the vars just stop resolving — observed during live palette
	// edits in the preview). Parse the token straight out of the style
	// text, substituting one level of var() references, so the chips can
	// always paint.
	function tokenFromStyleText( token, depth ) {
		var st = document.getElementById( 'customify-palette-tokens-inline-css' );
		if ( ! st || depth > 2 ) {
			return '';
		}
		var m = st.textContent.match( new RegExp( token + ':\\s*([^;}]+)' ) );
		if ( ! m ) {
			return '';
		}
		return m[1].trim().replace( /var\((--[a-z0-9-]+)\)/gi, function( _all, ref ) {
			return tokenFromStyleText( ref, ( depth || 0 ) + 1 ) || _all;
		} );
	}

	function refreshMeta() {
		document.querySelectorAll( '.csg-cvalue[data-token]' ).forEach( function( el ) {
			var chip = el.closest( '.csg-ccard' ).querySelector( '.csg-chip' );
			var bg = getComputedStyle( chip ).backgroundColor;
			if ( ( 'rgba(0, 0, 0, 0)' === bg || 'transparent' === bg ) && ! chip.hasAttribute( 'data-setting' ) ) {
				var rescued = tokenFromStyleText( el.getAttribute( 'data-token' ), 0 );
				if ( rescued ) {
					chip.style.background = rescued;
					bg = getComputedStyle( chip ).backgroundColor;
				}
			}
			el.closest( '.csg-ccard' ).style.display =
				( 'rgba(0, 0, 0, 0)' === bg || 'transparent' === bg ) ? 'none' : '';
			el.textContent = toHex( bg );
		} );
		document.querySelectorAll( '[data-spec]' ).forEach( function( el ) {
			var tag = el.getAttribute( 'data-spec' );
			var node = el.closest( '.csg-editable' ).querySelector( tag );
			if ( ! node ) {
				return;
			}
			var cs = getComputedStyle( node );
			var family = ( cs.fontFamily.split( ',' )[0] || '' ).replace( /["']/g, '' );
			el.textContent = family + ' · ' + cs.fontSize + ' · ' + cs.fontWeight;
		} );
		document.querySelectorAll( '[data-spec-family]' ).forEach( function( el ) {
			var node = document.querySelector( el.getAttribute( 'data-spec-family' ) );
			if ( ! node ) {
				return;
			}
			var cs = getComputedStyle( node );
			el.textContent = ( cs.fontFamily.split( ',' )[0] || '' ).replace( /["']/g, '' ) + ' · ' + cs.fontWeight;
		} );
	}

	var timer = null;
	function refreshSoon() {
		clearTimeout( timer );
		timer = setTimeout( refreshMeta, 250 );
	}

	document.addEventListener( 'DOMContentLoaded', refreshMeta );
	if ( document.fonts && document.fonts.ready && document.fonts.ready.then ) {
		document.fonts.ready.then( refreshMeta );
	}

	// Any setting change that reaches the preview may move colors or
	// type — recompute after the live CSS lands. This inline script runs
	// BEFORE wp_footer() loads customize-preview, so the bind must wait
	// for window load. The live engines (auto-css, palette tokens)
	// rewrite <head> style nodes asynchronously — and the token style is
	// briefly blank mid-replace — so also watch the head and add a late
	// settle pass instead of trusting one debounce window.
	// Setting values written by controls are encodeURI(JSON) strings;
	// values straight from storage are plain. Normalize to the raw color.
	function decodeSettingValue( v ) {
		if ( 'string' === typeof v ) {
			try {
				var d = JSON.parse( decodeURI( v ) );
				return 'string' === typeof d ? d : v;
			} catch ( e ) {
				return v;
			}
		}
		return v;
	}

	window.addEventListener( 'load', function() {
		refreshMeta();
		if ( window.wp && wp.customize && wp.customize.bind ) {
			wp.customize.bind( 'change', function() {
				refreshSoon();
				setTimeout( refreshMeta, 900 );
			} );

			// Editable slot chips also follow their setting directly —
			// covers tokens the palette engine doesn't emit (Surface).
			document.querySelectorAll( '.csg-chip[data-setting]' ).forEach( function( chip ) {
				wp.customize( chip.getAttribute( 'data-setting' ), function( setting ) {
					setting.bind( function( v ) {
						var val = decodeSettingValue( v );
						if ( 'string' === typeof val && val ) {
							chip.style.background = val;
						}
						refreshSoon();
					} );
				} );
			} );
		}
		if ( window.MutationObserver ) {
			new MutationObserver( refreshSoon ).observe( document.head, {
				childList: true,
				subtree: true,
				characterData: true
			} );
		}
	} );
} )();
</script>

<?php wp_footer(); ?>
</body>
</html>
