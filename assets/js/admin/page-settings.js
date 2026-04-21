/* global wp, customifyPageSettings */
( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.element ) {
		return;
	}

	var el               = wp.element.createElement;
	var Fragment         = wp.element.Fragment;
	var useSelect        = wp.data.useSelect;
	var useEntityProp    = wp.coreData && wp.coreData.useEntityProp;
	var registerPlugin   = wp.plugins.registerPlugin;
	var __               = wp.i18n.__;

	// PluginDocumentSettingPanel lives in wp.editor (WP 6.6+) or wp.editPost (older).
	var PluginDocumentSettingPanel =
		( wp.editor && wp.editor.PluginDocumentSettingPanel ) ||
		( wp.editPost && wp.editPost.PluginDocumentSettingPanel );

	var SelectControl   = wp.components.SelectControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var PanelBody       = wp.components.PanelBody;
	var PanelRow        = wp.components.PanelRow;

	if ( ! PluginDocumentSettingPanel || ! useEntityProp ) {
		return;
	}

	// ---------------------------------------------------------------------------
	// Option lists
	// ---------------------------------------------------------------------------

	var contentLayoutOptions = [
		{ label: __( 'Default', 'customify' ),               value: '' },
		{ label: __( 'Full Width', 'customify' ),             value: 'full-width' },
		{ label: __( 'Full Width - Stretched', 'customify' ), value: 'full-stretched' },
	];

	var sidebarOptions = [ { label: __( 'Inherit from Customizer', 'customify' ), value: '' } ].concat(
		Object.keys( config.sidebarLayouts || {} ).map( function ( key ) {
			return { label: config.sidebarLayouts[ key ], value: key };
		} )
	);

	var pageHeaderOptions = [
		{ label: __( 'Inherit from Customizer', 'customify' ), value: 'default' },
		{ label: __( 'Default', 'customify' ),                 value: 'normal' },
		{ label: __( 'Cover', 'customify' ),                   value: 'cover' },
		{ label: __( 'Titlebar', 'customify' ),                value: 'titlebar' },
		{ label: __( 'Hide', 'customify' ),                    value: 'none' },
	];

	var breadcrumbOptions = [
		{ label: __( 'Inherit from Customizer', 'customify' ), value: 'default' },
		{ label: __( 'Hide', 'customify' ),                    value: 'hide' },
		{ label: __( 'Show', 'customify' ),                    value: 'show' },
	];

	// ---------------------------------------------------------------------------
	// Main component
	// ---------------------------------------------------------------------------

	function CustomifySettings() {
		var postType = useSelect( function ( select ) {
			return select( 'core/editor' ).getCurrentPostType();
		}, [] );

		var entityProps  = useEntityProp( 'postType', postType, 'meta' );
		var meta         = entityProps[ 0 ];
		var updateMeta   = entityProps[ 1 ];

		if ( ! meta ) {
			return null;
		}

		/** Read a single _customify_* meta value. */
		function get( key ) {
			var val = meta[ '_customify_' + key ];
			return ( val === undefined || val === null ) ? '' : val;
		}

		/** Write a single _customify_* meta value. */
		function set( key, value ) {
			var patch = {};
			patch[ '_customify_' + key ] = value;
			updateMeta( patch );
		}

		return el(
			Fragment,
			null,

			// ── Layout ──────────────────────────────────────────────────────
			el(
				PanelBody,
				{ title: __( 'Layout', 'customify' ), initialOpen: true },

				el( SelectControl, {
					label:    __( 'Content Layout', 'customify' ),
					value:    get( 'content_layout' ),
					options:  contentLayoutOptions,
					onChange: function ( v ) { set( 'content_layout', v ); },
				} ),

				el( SelectControl, {
					label:    __( 'Sidebar', 'customify' ),
					value:    get( 'sidebar' ),
					options:  sidebarOptions,
					onChange: function ( v ) { set( 'sidebar', v ); },
				} ),

				el( 'p', { className: 'customify-ps-group-label' },
					__( 'Disable Elements', 'customify' )
				),

				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Header', 'customify' ),
						checked:  get( 'disable_header' ) === '1',
						onChange: function ( v ) { set( 'disable_header', v ? '1' : '' ); },
					} )
				),
				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Page Title', 'customify' ),
						checked:  get( 'disable_page_title' ) === '1',
						onChange: function ( v ) { set( 'disable_page_title', v ? '1' : '' ); },
					} )
				),
				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Header Top', 'customify' ),
						checked:  get( 'disable_header_top' ) === '1',
						onChange: function ( v ) { set( 'disable_header_top', v ? '1' : '' ); },
					} )
				),
				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Header Main', 'customify' ),
						checked:  get( 'disable_header_main' ) === '1',
						onChange: function ( v ) { set( 'disable_header_main', v ? '1' : '' ); },
					} )
				),
				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Header Bottom', 'customify' ),
						checked:  get( 'disable_header_bottom' ) === '1',
						onChange: function ( v ) { set( 'disable_header_bottom', v ? '1' : '' ); },
					} )
				),
				config.hasProFeatures ? el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Footer Top', 'customify' ),
						checked:  get( 'disable_footer_top' ) === '1',
						onChange: function ( v ) { set( 'disable_footer_top', v ? '1' : '' ); },
					} )
				) : null,
				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Footer Main', 'customify' ),
						checked:  get( 'disable_footer_main' ) === '1',
						onChange: function ( v ) { set( 'disable_footer_main', v ? '1' : '' ); },
					} )
				),
				el( PanelRow, null,
					el( CheckboxControl, {
						label:    __( 'Footer Bottom', 'customify' ),
						checked:  get( 'disable_footer_bottom' ) === '1',
						onChange: function ( v ) { set( 'disable_footer_bottom', v ? '1' : '' ); },
					} )
				)
			),

			// ── Page Header ─────────────────────────────────────────────────
			el(
				PanelBody,
				{ title: __( 'Page Header', 'customify' ), initialOpen: false },

				el( SelectControl, {
					label:    __( 'Display', 'customify' ),
					value:    get( 'page_header_display' ) || 'default',
					options:  pageHeaderOptions,
					onChange: function ( v ) { set( 'page_header_display', v ); },
				} )
			),

			// ── Breadcrumb (conditional) ─────────────────────────────────────
			config.hasBreadcrumb ? el(
				PanelBody,
				{ title: __( 'Breadcrumb', 'customify' ), initialOpen: false },

				el( SelectControl, {
					label:    __( 'Breadcrumb', 'customify' ),
					value:    get( 'breadcrumb_display' ) || 'default',
					options:  breadcrumbOptions,
					onChange: function ( v ) { set( 'breadcrumb_display', v ); },
				} )
			) : null
		);
	}

	// ---------------------------------------------------------------------------
	// Register plugin
	// ---------------------------------------------------------------------------

	registerPlugin( 'customify-page-settings', {
		render: function () {
			return el(
				PluginDocumentSettingPanel,
				{
					name:      'customify-page-settings-panel',
					title:     __( 'Customify Page Settings', 'customify' ),
					className: 'customify-page-settings-panel',
					icon:      'admin-appearance',
				},
				el( CustomifySettings, null )
			);
		},
	} );

} )( window.wp, window.customifyPageSettings || {} );
