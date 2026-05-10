/**
 * Inline install/activate flow for the Recommend Plugins card — JS-only,
 * no server-side helpers added in the theme. Both URLs are normal
 * wp-admin pages with a valid `_wpnonce`:
 *
 *   install:  /wp-admin/update.php?action=install-plugin&plugin={slug}&_wpnonce=…
 *   activate: /wp-admin/plugins.php?action=activate&plugin={file}&_wpnonce=…
 *
 * The card fetches each URL with `credentials: 'same-origin'` so the user's
 * cookies authenticate the request, parses the HTML response for known
 * outcome markers, and updates the React state. No tab switch, no redirect.
 *
 * Server already returns the install URL as the initial `actionUrl`. After
 * a successful install the response HTML carries an "Activate Plugin"
 * anchor that points at the activate URL — we extract it from the response
 * and stash it for the next click.
 */

/**
 * Fetch a wp-admin URL as an authenticated browser request and return the
 * raw HTML body. Throws on HTTP error so callers can branch on outcome.
 *
 * @param {string} url Full or relative wp-admin URL.
 * @returns {Promise<string>} Response HTML.
 */
async function fetchAdminPage( url ) {
	const resp = await fetch( url, {
		method: 'GET',
		credentials: 'same-origin',
		// Prevent browser/cdn from serving a cached copy that could mask a
		// just-completed state change.
		cache: 'no-store',
		headers: { 'X-Requested-With': 'XMLHttpRequest' },
	} );
	if ( ! resp.ok ) {
		throw new Error( `HTTP ${ resp.status } fetching ${ url }` );
	}
	return resp.text();
}

/**
 * Look for a plain-text fragment inside the HTML body. Forgiving of
 * surrounding markup: strips tags before matching.
 */
function bodyContains( html, needles ) {
	const text = String( html )
		.replace( /<script[\s\S]*?<\/script>/gi, '' )
		.replace( /<style[\s\S]*?<\/style>/gi, '' )
		.replace( /<[^>]+>/g, ' ' )
		.replace( /\s+/g, ' ' );
	return needles.some( ( n ) => text.includes( n ) );
}

/**
 * Extract the activate URL from a successful install response. WP renders:
 *
 *   <a href="…/wp-admin/plugins.php?action=activate&amp;plugin=…&amp;_wpnonce=…">Activate Plugin</a>
 *
 * We pull the href, decode &amp; entities back to & so the URL is usable
 * with fetch() / location, and return it.
 *
 * @param {string} html Install-response body.
 * @returns {string} Activate URL or '' when not present.
 */
function extractActivateUrl( html ) {
	const match = html.match(
		/href=["']([^"']*plugins\.php\?action=activate[^"']*)["']/i
	);
	if ( ! match ) return '';
	return match[ 1 ]
		.replace( /&amp;/g, '&' )
		.replace( /&#038;/g, '&' );
}

/**
 * Run the install URL and resolve { activateUrl } on success. Throws on
 * any error WP prints in the response body.
 *
 * @param {string} installUrl WP install URL with valid nonce.
 * @param {string} pluginName Human label for error messages.
 * @returns {Promise<{ activateUrl: string }>}
 */
export async function runInstall( installUrl, pluginName ) {
	const html = await fetchAdminPage( installUrl );

	if ( bodyContains( html, [ 'destination directory already exists' ] ) ) {
		throw Object.assign(
			new Error(
				`A folder for "${ pluginName }" already exists. Delete it from wp-content/plugins/ and try again.`
			),
			{ code: 'destination_exists' }
		);
	}
	if (
		bodyContains( html, [
			'Sorry, you are not allowed',
			'You do not have sufficient permissions',
		] )
	) {
		throw Object.assign(
			new Error(
				`You don't have permission to install "${ pluginName }".`
			),
			{ code: 'forbidden' }
		);
	}
	if ( bodyContains( html, [ 'Plugin install failed', 'Installation failed' ] ) ) {
		throw new Error( `Installation failed for "${ pluginName }".` );
	}

	const activateUrl = extractActivateUrl( html );
	if ( ! activateUrl && ! bodyContains( html, [ 'Successfully installed', 'Plugin already installed' ] ) ) {
		// Neither the success marker nor an activate link is present —
		// treat it as an unknown failure so the UI can surface it.
		throw new Error( `Could not confirm "${ pluginName }" installed.` );
	}
	return { activateUrl };
}

/**
 * Run the activate URL. Resolves on success, throws when the response
 * body contains a known WP failure marker.
 *
 * @param {string} activateUrl WP activate URL with valid nonce.
 * @param {string} pluginName  Human label for error messages.
 * @returns {Promise<void>}
 */
export async function runActivate( activateUrl, pluginName ) {
	if ( ! activateUrl ) {
		throw new Error(
			`Activate link missing for "${ pluginName }". Reload the page and retry.`
		);
	}
	const html = await fetchAdminPage( activateUrl );
	if (
		bodyContains( html, [
			'Plugin could not be activated',
			'caused a fatal error',
			'Sorry, you are not allowed',
		] )
	) {
		throw new Error( `Could not activate "${ pluginName }".` );
	}
	// On success WP redirects (browser follows it transparently) and the
	// final page is plugins.php with `?activate=true`. Either we land on
	// that listing or on a "Plugin activated" notice — both fine.
}
