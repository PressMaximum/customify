/**
 * Customify Page Settings — block editor plugin.
 *
 * Renders a PluginDocumentSettingPanel with a flat stack of controls in the
 * Document sidebar. Related toggle controls are introduced by a field-style
 * label rather than a separate tab.
 */

import './style.scss';

import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect, dispatch, select, subscribe } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	ToggleControl,
} from '@wordpress/components';

/** Config object injected by wp_localize_script in page-settings.php */
const config = window.customifyPageSettings || {};

// ---------------------------------------------------------------------------
// Layout → contentSize sync
// ---------------------------------------------------------------------------

const TWO_SIDEBAR_LAYOUTS = [
	'sidebar-content-sidebar',
	'sidebar-sidebar-content',
	'content-sidebar-sidebar',
];

// Content Layout values that disable the sidebar entirely — both visually
// (we hide the dropdown) and for contentSize purposes (force no_sidebar).
// `narrow` joins this list because narrow content is a focused-reading layout
// — combining it with a sidebar looks lopsided. PHP's
// customify_force_no_sidebar_for_full_content_layout() applies the same gate.
const NO_SIDEBAR_CONTENT_LAYOUTS = [ 'full-width', 'full-stretched', 'narrow' ];

function isNoSidebarContentLayout( contentLayout ) {
	return NO_SIDEBAR_CONTENT_LAYOUTS.includes( contentLayout );
}

const CONTENT_SIZE_STYLE_ID = 'customify-layout-content-size';

/**
 * Map a sidebar layout slug to a contentSize value.
 *
 * The map is the single source of truth in PHP (customify_get_layout_content_sizes
 * in inc/template-functions.php) — localized via wp_localize_script. Returns
 * undefined if the map is missing so callers can skip applying invalid CSS.
 */
function layoutToContentSize( layout ) {
	const map = config.contentSizeMap;
	if ( ! map ) return undefined;
	if ( TWO_SIDEBAR_LAYOUTS.includes( layout ) ) return map.two_sidebars;
	if ( layout === 'content' ) return map.no_sidebar;
	return map.one_sidebar;
}

/**
 * Inject/update a <style> tag in a given document.
 */
function injectStyle( doc, id, css ) {
	if ( ! doc || ! doc.head ) return;
	let style = doc.getElementById( id );
	if ( ! style ) {
		style = doc.createElement( 'style' );
		style.id = id;
		doc.head.appendChild( style );
	}
	if ( style.textContent !== css ) {
		style.textContent = css;
	}
}

/**
 * Build the inline CSS payload for a given size + content_layout combo.
 *
 * The `max-width: none` override for full-width / full-stretched layouts lives
 * here (not in PHP editor.php) so toggling content_layout in the metabox can
 * actually undo it. A PHP-emitted static override would stay applied even
 * after JS removes it from this style block.
 *
 * No !important on the body rule — custom-property inheritance lets the
 * body-scoped declaration override the theme.json :root baseline for
 * everything inside body without needing to win specificity. Matches the
 * frontend cascade in customify_layout_content_size_css().
 */
function buildContentSizeCss( size, contentLayout ) {
	let css = '';
	if ( size ) {
		css += `body{--wp--style--global--content-size:${ size };}`;
	}
	// full-width: block fills canvas with a 32px gap each side, mirroring
	// the frontend's `.customify-container { padding: 2em }` breathing room.
	// In the editor there is no .customify-container wrapper, so we subtract
	// the padding from the block's max-width explicitly.
	if ( contentLayout === 'full-width' ) {
		css +=
			'.editor-styles-wrapper > .is-root-container > *:not(.alignfull):not(.alignwide){max-width:calc(100% - 64px);}';
	}
	// full-stretched: block fills canvas edge-to-edge — frontend version sets
	// `.content-full-stretched > .customify-container { padding: 0 }`, so the
	// editor matches by removing the cap entirely.
	if ( contentLayout === 'full-stretched' ) {
		css +=
			'.editor-styles-wrapper > .is-root-container > *:not(.alignfull):not(.alignwide){max-width:none;}';
	}
	return css;
}

function applyContentSizeCss( css ) {
	injectStyle( document, CONTENT_SIZE_STYLE_ID, css );
	const iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
	if ( iframe && iframe.contentDocument ) {
		injectStyle( iframe.contentDocument, CONTENT_SIZE_STYLE_ID, css );
	}
}

/**
 * Push runtime contentSize + wideSize into the block-editor settings store so
 * block toolbar dropdown hints (e.g., "None — Max 863px wide") reflect what the
 * CSS variable actually resolves to. Without this the labels read from
 * theme.json's static value (`contentSize: "863px"`) regardless of the
 * runtime override we inject via CSS, which misleads authors.
 *
 * The canonical write target is `__experimentalFeatures.layout` — that's what
 * core blocks AND third-party blocks (Blocksify Section, etc.) read when
 * rendering the alignment dropdown. Updating the top-level `layout` key alone
 * is not enough; both must move in lockstep to stay self-consistent.
 *
 * Bailing-out semantics: only write when values actually changed, to avoid
 * triggering a no-op re-render of every component that selects from settings.
 */
function syncEditorLayoutSettings( contentSize, wideSize ) {
	const store = select( 'core/block-editor' );
	if ( ! store ) return;
	const settings = store.getSettings();
	const expFeatures = settings.__experimentalFeatures || {};
	const expLayout = expFeatures.layout || {};
	const topLayout = settings.layout || {};

	const nextContent = contentSize || expLayout.contentSize;
	const nextWide = wideSize || expLayout.wideSize;

	if (
		expLayout.contentSize === nextContent &&
		expLayout.wideSize === nextWide &&
		topLayout.contentSize === nextContent &&
		topLayout.wideSize === nextWide
	) {
		return;
	}

	dispatch( 'core/block-editor' ).updateSettings( {
		layout: { ...topLayout, contentSize: nextContent, wideSize: nextWide },
		__experimentalFeatures: {
			...expFeatures,
			layout: { ...expLayout, contentSize: nextContent, wideSize: nextWide },
		},
	} );
}

/**
 * Subscribes to _customify_sidebar + _customify_content_layout meta and keeps
 * the editor's --wp--style--global--content-size in sync as the user toggles
 * the Sidebar / Content Layout dropdowns — no save/reload required.
 *
 * Registered as its own plugin (instead of nesting inside the panel) so it
 * stays mounted even when the user collapses the Page Settings panel.
 */
function ContentSizeSync() {
	const postType = useSelect(
		( select ) => select( 'core/editor' )?.getCurrentPostType(),
		[]
	);
	const [ meta ] = useEntityProp( 'postType', postType, 'meta' );
	const sidebarMeta = ( meta && meta._customify_sidebar ) || '';
	const contentLayout = ( meta && meta._customify_content_layout ) || '';

	// full-width / full-stretched override the sidebar layout — those modes
	// don't render a sidebar, so contentSize must use the no-sidebar value.
	const layout = isNoSidebarContentLayout( contentLayout )
		? 'content'
		: sidebarMeta || config.fallbackLayout || 'content-sidebar';
	const layoutSize = layoutToContentSize( layout );

	// Resolution priority (highest wins):
	//   1. Full-Width / Full-Stretched Content Layout — viewport-bound, mirrors
	//      .site-content.content-full-{width,stretched} frontend rule.
	//   2. Narrow Content Layout — uses Customizer narrow_width regardless of layout.
	//      Mirrors .site-content.content-narrow frontend rule.
	//   3. Single-post override — for post type, single_blog_post_content_width wins.
	//      Mirrors body.single-post frontend rule.
	//   4. Layout-derived size from sidebar layout map.
	let size;
	if ( contentLayout === 'full-width' ) {
		size = 'calc(100vw - 64px)';
	} else if ( contentLayout === 'full-stretched' ) {
		size = '100vw';
	} else if ( contentLayout === 'narrow' && config.narrowWidth ) {
		size = config.narrowWidth;
	} else if ( config.postType === 'post' && config.postContentSize ) {
		size = config.postContentSize;
	} else {
		size = layoutSize;
	}
	const css = buildContentSizeCss( size, contentLayout );

	// Dynamic wide-size per layout (matches PHP customify_layout_content_size_css):
	//   - Full-Width / Full-Stretched (size is calc() or 100vw) → wide = content
	//     (alignwide visually = align=none — viewport-bound layouts don't break out)
	//   - No-sidebar / Narrow (numeric px size) → size + 400 (200px breakout each side)
	//   - Sidebar layouts → wide = content (parent-constrained, no overlap)
	// Used to push into __experimentalFeatures.layout.wideSize so alignment
	// dropdown labels show the right "Max Xpx wide" hint.
	const isViewportBoundSize =
		typeof size === 'string' && /calc|vw/.test( size );
	const sizeNum =
		size && ! isViewportBoundSize ? parseInt( size, 10 ) : null;
	const isNoSidebarFamily =
		isNoSidebarContentLayout( contentLayout ) || layout === 'content';
	let wideSize;
	if ( isViewportBoundSize ) {
		wideSize = size;
	} else if ( sizeNum && isNoSidebarFamily ) {
		wideSize = `${ sizeNum + 400 }px`;
	} else {
		wideSize = size || config.wideSize;
	}

	useEffect( () => {
		applyContentSizeCss( css );

		// Keep block-editor settings.layout in lockstep with the CSS variable so
		// alignment dropdown labels (e.g., "None — Max 863px wide") reflect the
		// resolved size, not theme.json's static one. The initial dispatch can
		// be overwritten by editor bootstrap loading theme.json after our mount,
		// so subscribe and re-apply on every settings change — syncEditor-
		// LayoutSettings has internal bail-out so it's a no-op once our values
		// have stuck. Subscription is scoped to core/block-editor so we don't
		// fire on unrelated store updates.
		const applyLayoutSync = () =>
			syncEditorLayoutSettings( size, wideSize );
		applyLayoutSync();
		const unsubscribeSync = subscribe( applyLayoutSync, 'core/block-editor' );

		// Editor canvas iframe mounts asynchronously and may reload (e.g.
		// when toggling device preview). Watch for its load event so we
		// re-apply the style into the fresh iframe document.
		const iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( ! iframe ) {
			// Iframe may not exist yet — retry once after it likely mounts.
			const timer = setTimeout( () => applyContentSizeCss( css ), 500 );
			return () => {
				clearTimeout( timer );
				unsubscribeSync();
			};
		}
		const onLoad = () => applyContentSizeCss( css );
		iframe.addEventListener( 'load', onLoad );
		return () => {
			iframe.removeEventListener( 'load', onLoad );
			unsubscribeSync();
		};
	}, [ css, size, wideSize ] );

	return null;
}

// ---------------------------------------------------------------------------
// Option lists
// ---------------------------------------------------------------------------

const CONTENT_LAYOUT_OPTIONS = [
	{ label: __( 'Default', 'customify' ),                 value: '' },
	{ label: __( 'Full Width', 'customify' ),               value: 'full-width' },
	{ label: __( 'Full Width – Stretched', 'customify' ),   value: 'full-stretched' },
	{ label: __( 'Narrow', 'customify' ),                   value: 'narrow' },
];

const SIDEBAR_OPTIONS = [
	{ label: __( 'Inherit from Customizer', 'customify' ), value: '' },
	...Object.entries( config.sidebarLayouts || {} ).map( ( [ value, label ] ) => ( { label, value } ) ),
];

const PAGE_HEADER_OPTIONS = [
	{ label: __( 'Inherit from Customizer', 'customify' ), value: 'default' },
	{ label: __( 'Default - inside main content', 'customify' ), value: 'normal' },
	{ label: __( 'Cover', 'customify' ),                   value: 'cover' },
	{ label: __( 'Titlebar', 'customify' ),                value: 'titlebar' },
	{ label: __( 'Hide', 'customify' ),                    value: 'none' },
];

const TRANSPARENT_HEADER_OPTIONS = [
	{ label: __( 'Default', 'customify' ), value: 'default' },
	{ label: __( 'Enable', 'customify' ), value: 'show' },
	{ label: __( 'Disable', 'customify' ), value: 'hide' },
];

const BREADCRUMB_OPTIONS = [
	{ label: __( 'Inherit from Customizer', 'customify' ), value: 'default' },
	{ label: __( 'Hide', 'customify' ),                    value: 'hide' },
	{ label: __( 'Show', 'customify' ),                    value: 'show' },
];

// ---------------------------------------------------------------------------
// Sub-components
// ---------------------------------------------------------------------------

/** A single ToggleControl bound to a _customify_* meta key ('1' / ''). */
function MetaToggle( { label, metaKey, meta, setMeta } ) {
	return (
		<ToggleControl
			label={ label }
			checked={ meta[ `_customify_${ metaKey }` ] === '1' }
			onChange={ ( on ) =>
				setMeta( { [ `_customify_${ metaKey }` ]: on ? '1' : '' } )
			}
		/>
	);
}

// ---------------------------------------------------------------------------
// Main component
// ---------------------------------------------------------------------------

function CustomifyPageSettings() {
	const postType = useSelect(
		( select ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);

	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	if ( ! meta ) return null;

	const get = ( key ) => meta[ `_customify_${ key }` ] ?? '';
	const set = ( key, v ) => setMeta( { [ `_customify_${ key }` ]: v } );

	const contentLayout = get( 'content_layout' );
	const sidebarHidden = isNoSidebarContentLayout( contentLayout );

	return (
		<div className="customify-ps-body">
			<SelectControl
				label={ __( 'Content Layout', 'customify' ) }
				value={ contentLayout }
				options={ CONTENT_LAYOUT_OPTIONS }
				onChange={ ( v ) => set( 'content_layout', v ) }
			/>

			{ /*
			 * Page Title Layout used to live under a separate "Page Header"
			 * section heading below "Disable Elements". Moved up next to
			 * Content Layout because it's a primary layout decision — same
			 * cognitive group, and most users were missing it buried under
			 * the toggle stack. Label renamed from "Display" → "Page Title
			 * Layout" so the dropdown describes what it controls (where the
			 * page title renders: inside main content, as a cover hero, as
			 * a titlebar strip, or hidden).
			 */ }
			<SelectControl
				label={ __( 'Page Title Layout', 'customify' ) }
				value={ get( 'page_header_display' ) || 'default' }
				options={ PAGE_HEADER_OPTIONS }
				onChange={ ( v ) => set( 'page_header_display', v ) }
			/>

			{ ! sidebarHidden && (
				<SelectControl
					label={ __( 'Sidebar', 'customify' ) }
					value={ get( 'sidebar' ) }
					options={ SIDEBAR_OPTIONS }
					onChange={ ( v ) => set( 'sidebar', v ) }
				/>
			) }

			<p className="customify-ps-section-label">
				{ __( 'Disable Elements', 'customify' ) }
			</p>

			<MetaToggle label={ __( 'Header', 'customify' ) }        metaKey="disable_header"        meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Header Top', 'customify' ) }    metaKey="disable_header_top"    meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Header Main', 'customify' ) }   metaKey="disable_header_main"   meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Header Bottom', 'customify' ) } metaKey="disable_header_bottom" meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Page Title', 'customify' ) }    metaKey="disable_page_title"    meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Content Vertical Padding', 'customify' ) } metaKey="disable_content_vertical_padding" meta={ meta } setMeta={ setMeta } />
			{ config.hasProFeatures && (
				<MetaToggle label={ __( 'Footer Top', 'customify' ) } metaKey="disable_footer_top"   meta={ meta } setMeta={ setMeta } />
			) }
			<MetaToggle label={ __( 'Footer Main', 'customify' ) }   metaKey="disable_footer_main"   meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Footer Bottom', 'customify' ) } metaKey="disable_footer_bottom" meta={ meta } setMeta={ setMeta } />

			{ /*
			 * Both controls depend on optional features. Transparent Header
			 * follows the native/Pro module gate localized by PHP; Breadcrumb
			 * follows its plugin compatibility gate.
			 */ }
			{ ( config.hasHeaderTransparent || config.hasBreadcrumb ) && (
				<>
					{ config.hasHeaderTransparent && (
						<SelectControl
							label={ __( 'Transparent Header', 'customify' ) }
							value={
								get( 'header_transparent_display' ) || 'default'
							}
							options={ TRANSPARENT_HEADER_OPTIONS }
							onChange={ ( v ) =>
								set( 'header_transparent_display', v )
							}
						/>
					) }
					{ config.hasBreadcrumb && (
						<SelectControl
							label={ __( 'Breadcrumb', 'customify' ) }
							value={ get( 'breadcrumb_display' ) || 'default' }
							options={ BREADCRUMB_OPTIONS }
							onChange={ ( v ) => set( 'breadcrumb_display', v ) }
						/>
					) }
				</>
			) }
		</div>
	);
}

// ---------------------------------------------------------------------------
// Register plugin
// ---------------------------------------------------------------------------

registerPlugin( 'customify-page-settings', {
	render: () => (
		<PluginDocumentSettingPanel
			name="customify-page-settings-panel"
			title={ __( 'Customify Page Settings', 'customify' ) }
			className="customify-page-settings-panel"
		>
			<CustomifyPageSettings />
		</PluginDocumentSettingPanel>
	),
} );

// Registered as a separate plugin so it stays mounted regardless of whether
// the Page Settings panel above is expanded or collapsed.
registerPlugin( 'customify-content-size-sync', {
	render: () => <ContentSizeSync />,
} );
