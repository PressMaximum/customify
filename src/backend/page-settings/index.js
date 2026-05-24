/**
 * Customify Page Settings — block editor plugin.
 *
 * Renders a PluginDocumentSettingPanel with a flat stack of controls in the
 * Document sidebar. Sections (Layout, Disable Elements, Page Header) are
 * introduced by uppercase section labels rather than tabs.
 */

import './style.scss';

import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
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
const NO_SIDEBAR_CONTENT_LAYOUTS = [ 'full-width', 'full-stretched' ];

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
 * Inject the layout-driven --wp--style--global--content-size into both the
 * outer document and the editor canvas iframe (post-WP 6.3 layout).
 *
 * Uses `body` + `!important` because theme.json emits `body { ... }` for
 * global CSS variables — a `:root` rule loses to that within body's scope.
 */
/**
 * Build the inline CSS payload for a given size + content_layout combo.
 * Full-stretched additionally drops max-width on root blocks so they fill
 * the container, matching inc/admin/editor.php.
 */
function buildContentSizeCss( size, contentLayout ) {
	let css = '';
	if ( size ) {
		css += `body{--wp--style--global--content-size:${ size } !important;}`;
	}
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
	const size = layoutToContentSize( layout );
	const css = buildContentSizeCss( size, contentLayout );

	useEffect( () => {
		applyContentSizeCss( css );

		// Editor canvas iframe mounts asynchronously and may reload (e.g.
		// when toggling device preview). Watch for its load event so we
		// re-apply the style into the fresh iframe document.
		const iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( ! iframe ) {
			// Iframe may not exist yet — retry once after it likely mounts.
			const timer = setTimeout( () => applyContentSizeCss( css ), 500 );
			return () => clearTimeout( timer );
		}
		const onLoad = () => applyContentSizeCss( css );
		iframe.addEventListener( 'load', onLoad );
		return () => iframe.removeEventListener( 'load', onLoad );
	}, [ css ] );

	return null;
}

// ---------------------------------------------------------------------------
// Option lists
// ---------------------------------------------------------------------------

const CONTENT_LAYOUT_OPTIONS = [
	{ label: __( 'Default', 'customify' ),                 value: '' },
	{ label: __( 'Full Width', 'customify' ),               value: 'full-width' },
	{ label: __( 'Full Width – Stretched', 'customify' ),   value: 'full-stretched' },
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
			 * "Page Header" section heading is conditional now — Page Title
			 * Layout moved up next to Content Layout, so only Breadcrumb
			 * lives down here. When the user has no breadcrumb plugin
			 * active, the section would be empty and the heading would
			 * dangle.
			 */ }
			{ config.hasBreadcrumb && (
				<>
					<p className="customify-ps-section-label">
						{ __( 'Page Header', 'customify' ) }
					</p>
					<SelectControl
						label={ __( 'Breadcrumb', 'customify' ) }
						value={ get( 'breadcrumb_display' ) || 'default' }
						options={ BREADCRUMB_OPTIONS }
						onChange={ ( v ) => set( 'breadcrumb_display', v ) }
					/>
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
