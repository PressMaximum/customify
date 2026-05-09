/**
 * Welcome "Theme Customizer" 2-col grid items. Each links into a panel or
 * section of the WP Customizer using `autofocus` deep-link params.
 */

import { __ } from '@wordpress/i18n';

import { customizerLink } from '../config';

export const THEME_CUSTOMIZER_ITEMS = [
	{
		title: __( 'Logo & Site Identity', 'customify' ),
		description: __( 'Upload logo, site title, tagline', 'customify' ),
		href: customizerLink( { section: 'title_tagline' } ),
	},
	{
		title: __( 'Layout Settings', 'customify' ),
		description: __( 'Container width, content layout', 'customify' ),
		href: customizerLink( { section: 'global_layout_section' } ),
	},
	{
		title: __( 'Header Builder', 'customify' ),
		description: __( 'WYSIWYG header drag-and-drop', 'customify' ),
		href: customizerLink( { panel: 'header_settings' } ),
	},
	{
		title: __( 'Footer Builder', 'customify' ),
		description: __( 'WYSIWYG footer drag-and-drop', 'customify' ),
		href: customizerLink( { panel: 'footer_settings' } ),
	},
	{
		title: __( 'Styling', 'customify' ),
		description: __( 'Brand colors & site palette', 'customify' ),
		href: customizerLink( { panel: 'styling_panel' } ),
	},
	{
		title: __( 'Typography', 'customify' ),
		description: __( 'Font family, size, line-height', 'customify' ),
		href: customizerLink( { panel: 'typography_panel' } ),
	},
	{
		title: __( 'Sidebar Settings', 'customify' ),
		description: __( 'Sidebar layout per context', 'customify' ),
		href: customizerLink( { section: 'sidebar_layout_section' } ),
	},
	{
		title: __( 'Titlebar Settings', 'customify' ),
		description: __( 'Page header title bar', 'customify' ),
		href: customizerLink( { section: 'titlebar' } ),
	},
	{
		title: __( 'Blog Posts', 'customify' ),
		description: __( 'Blog listing & single post', 'customify' ),
		href: customizerLink( { panel: 'blog_panel' } ),
	},
	{
		title: __( 'Homepage Settings', 'customify' ),
		description: __( 'Static front page setup', 'customify' ),
		href: customizerLink( { section: 'static_front_page' } ),
	},
];
