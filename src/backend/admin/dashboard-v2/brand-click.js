/**
 * Make the dashboard brand mark + text clickable so they navigate to
 * a target hash route (Welcome by default).
 *
 * WORKAROUND for kit issue K-009 (see dashboard-kit/KIT_ISSUES.md) —
 * REMOVE when the kit lands `brand.href` support in 0.1.0 (DashboardShell
 * will then wrap the brand element in <a> automatically).
 */

const BRAND_SELECTOR = '.pmdk-dashboard__brand';

/**
 * Attach a delegated click listener on the document root that intercepts
 * clicks anywhere inside the brand element and navigates to the target
 * hash route via `window.location.hash`. Idempotent — multiple calls
 * collapse to a single listener.
 *
 * @param {string} targetHash Hash route, e.g. `'#welcome'`.
 */
export function wireBrandClick( targetHash ) {
	if ( wireBrandClick._wired ) {
		return;
	}
	wireBrandClick._wired = true;

	const handler = ( event ) => {
		const brand = event.target.closest( BRAND_SELECTOR );
		if ( ! brand ) {
			return;
		}
		event.preventDefault();
		if ( window.location.hash !== targetHash ) {
			window.location.hash = targetHash;
		}
	};

	document.addEventListener( 'click', handler );
	// Cursor affordance lives in dashboard-v2.scss so it survives
	// React remounts that would wipe an inline style.
}
