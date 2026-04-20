/**
 * Customify Page Settings — block editor plugin.
 *
 * Renders a PluginDocumentSettingPanel with a TabPanel UI so the content
 * fits compactly in the Document sidebar without excessive padding.
 *
 * Tabs: Layout | Page Header  (+ Breadcrumb merged into Page Header when active)
 * Disable Elements uses ToggleControl for a cleaner on/off UX.
 */

import './style.scss';

import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	TabPanel,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';

/** Config object injected by wp_localize_script in page-settings.php */
const config = window.customifyPageSettings || {};

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
	{ label: __( 'Default', 'customify' ),                 value: 'normal' },
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

/** "Layout" tab content. */
function LayoutTab( { meta, setMeta } ) {
	const get = ( key ) => meta[ `_customify_${ key }` ] ?? '';
	const set = ( key, v ) => setMeta( { [ `_customify_${ key }` ]: v } );

	return (
		<div className="customify-ps-tab-content">
			<SelectControl
				label={ __( 'Content Layout', 'customify' ) }
				value={ get( 'content_layout' ) }
				options={ CONTENT_LAYOUT_OPTIONS }
				onChange={ ( v ) => set( 'content_layout', v ) }
			/>

			<SelectControl
				label={ __( 'Sidebar', 'customify' ) }
				value={ get( 'sidebar' ) }
				options={ SIDEBAR_OPTIONS }
				onChange={ ( v ) => set( 'sidebar', v ) }
			/>

			<p className="customify-ps-section-label">
				{ __( 'Disable Elements', 'customify' ) }
			</p>

			<MetaToggle label={ __( 'Header', 'customify' ) }        metaKey="disable_header"        meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Page Title', 'customify' ) }    metaKey="disable_page_title"    meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Header Top', 'customify' ) }    metaKey="disable_header_top"    meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Header Main', 'customify' ) }   metaKey="disable_header_main"   meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Header Bottom', 'customify' ) } metaKey="disable_header_bottom" meta={ meta } setMeta={ setMeta } />
			{ config.hasProFeatures && (
				<MetaToggle label={ __( 'Footer Top', 'customify' ) } metaKey="disable_footer_top"   meta={ meta } setMeta={ setMeta } />
			) }
			<MetaToggle label={ __( 'Footer Main', 'customify' ) }   metaKey="disable_footer_main"   meta={ meta } setMeta={ setMeta } />
			<MetaToggle label={ __( 'Footer Bottom', 'customify' ) } metaKey="disable_footer_bottom" meta={ meta } setMeta={ setMeta } />
		</div>
	);
}

/** "Page Header" tab content — also contains Breadcrumb when the plugin is active. */
function PageHeaderTab( { meta, setMeta } ) {
	const get = ( key ) => meta[ `_customify_${ key }` ] ?? '';
	const set = ( key, v ) => setMeta( { [ `_customify_${ key }` ]: v } );

	return (
		<div className="customify-ps-tab-content">
			<SelectControl
				label={ __( 'Display', 'customify' ) }
				value={ get( 'page_header_display' ) || 'default' }
				options={ PAGE_HEADER_OPTIONS }
				onChange={ ( v ) => set( 'page_header_display', v ) }
			/>

			{ config.hasBreadcrumb && (
				<SelectControl
					label={ __( 'Breadcrumb', 'customify' ) }
					value={ get( 'breadcrumb_display' ) || 'default' }
					options={ BREADCRUMB_OPTIONS }
					onChange={ ( v ) => set( 'breadcrumb_display', v ) }
				/>
			) }
		</div>
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

	const tabs = [
		{
			name:  'layout',
			title: __( 'Layout', 'customify' ),
		},
		{
			name:  'page-header',
			title: __( 'Page Header', 'customify' ),
		},
	];

	return (
		<TabPanel
			className="customify-ps-tabs"
			tabs={ tabs }
		>
			{ ( tab ) => {
				if ( tab.name === 'layout' ) {
					return <LayoutTab meta={ meta } setMeta={ setMeta } />;
				}
				return <PageHeaderTab meta={ meta } setMeta={ setMeta } />;
			} }
		</TabPanel>
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
			icon="admin-appearance"
		>
			<CustomifyPageSettings />
		</PluginDocumentSettingPanel>
	),
} );
