/**
 * Bootstrap values from PHP via wp_localize_script. Server emits a
 * `window.customifyDashboard` object before dashboard.js runs (see
 * Customify_Theme_Dashboard::enqueue_assets() — `wp_localize_script` call).
 *
 * Reads default to safe empty strings so the React app never crashes if a
 * key is missing (e.g. when running components in isolation outside the
 * admin page).
 */

const config =
	( typeof window !== 'undefined' && window.customifyDashboard ) || {};

export const PLUGIN_VERSION = config.pluginVersion || '';
export const WP_VERSION = config.wpVersion || '';
export const ADMIN_URL = config.adminUrl || '';
export const CUSTOMIZER_URL = config.customizerUrl || '';
export const BASE_URL = config.baseUrl || '';
export const PRO_ACTIVE = !! config.proActive;
export const PRO_MODULES_BOOT = Array.isArray( config.proModules )
	? config.proModules
	: [];
export const PRO_ASSETS_WRITABLE = config.proAssetsWritable !== false;
export const PRO_ASSETS_SAVE_PATH = config.proAssetsSavePath || '';

/**
 * Customify Sites Library plugin state — used by the Welcome sidebar
 * "Customify ready to import sites" card to pick the right CTA.
 *
 *   state:        'active' | 'installed' | 'not-installed'
 *   actionUrl:    where the primary button leads
 *   actionLabel:  primary button copy
 *   detailsUrl:   "Details" link target (GitHub repo)
 *   thumbnailUrl: hero image for the card (empty when missing)
 */
export const SITES_PLUGIN =
	config.sitesPlugin && typeof config.sitesPlugin === 'object'
		? config.sitesPlugin
		: { state: 'not-installed', actionUrl: '', actionLabel: '', detailsUrl: '', thumbnailUrl: '' };

/**
 * Recommended free plugins surfaced in the Welcome sidebar. Server has
 * already filtered out plugins the site already has active, so this list
 * only contains rows the user may want to install/activate.
 *
 *   { slug, name, iconUrl, state: 'installed'|'not-installed',
 *     actionUrl, actionLabel, detailsUrl }
 */
export const RECOMMEND_PLUGINS = Array.isArray( config.recommendPlugins )
	? config.recommendPlugins
	: [];

/**
 * Per-user dismissal flag for the Welcome > "Things to do" card. Persisted
 * via user_meta `customify_things_to_do_hidden`. Toggle through the
 * `set_things_to_do_hidden` AJAX task.
 */
export const THINGS_TO_DO_HIDDEN = !! config.thingsToDoHidden;

/**
 * Server-detected completion state for the Welcome > Things-to-do checklist.
 * Keys are the IDs from data/things-to-do.js; values are booleans. Missing
 * IDs default to `false` (not done). The dashboard pre-checks rows whose
 * value is true so the user can see what they've already configured.
 */
export const THINGS_TO_DO_STATUS =
	config.thingsToDoStatus && typeof config.thingsToDoStatus === 'object'
		? config.thingsToDoStatus
		: {};

/**
 * Resolve a theme-relative asset path (e.g. `build/images/admin/foo.png`)
 * to an absolute URL using the host theme/plugin base URL.
 */
export function assetUrl( relPath = '' ) {
	if ( ! BASE_URL ) {
		return relPath;
	}
	return BASE_URL + String( relPath ).replace( /^\/+/, '' );
}

/**
 * Build a Customizer URL with WP's `autofocus` deep-link parameter so a
 * Welcome card can jump straight to the right panel/section.
 *
 *     customizerLink( { panel: 'header_settings' } )
 *     customizerLink( { section: 'title_tagline' } )
 */
export function customizerLink( autofocus = {} ) {
	const url = CUSTOMIZER_URL;
	if ( ! url ) return '#';
	const params = new URLSearchParams();
	Object.entries( autofocus ).forEach( ( [ kind, target ] ) => {
		params.append( `autofocus[${ kind }]`, target );
	} );
	const sep = url.includes( '?' ) ? '&' : '?';
	return `${ url }${ sep }${ params.toString() }`;
}
