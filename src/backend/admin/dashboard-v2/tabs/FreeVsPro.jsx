/**
 * Free vs Pro tab — Customify Free upsell matrix.
 *
 * Tab visibility:
 *   - When Pro inactive: `index.js` pushes `free-vs-pro` into baseTabs +
 *     registers the `#free-vs-pro` route → this component renders.
 *   - When Pro active: `index.js` drops both → kit's HashRouter falls back
 *     to `#welcome` for any leftover deep link (no extra guard needed
 *     here).
 *
 * Layout copies Blocksify Free's pattern (consumer-side heading + tagline
 * above kit's `<CompareTable>`). Matrix data is hand-curated in
 * `../data/freeVsPro.js`; kit's CompareTable handles cell dispatch + CTA
 * banner from a single `footer` prop.
 */

import { __ } from '@wordpress/i18n';
import { CompareTable, useBoot } from '@pressmaximum/dashboard-kit';

import { buildFreeVsProMatrix } from '../data/freeVsPro.js';

export default function FreeVsPro() {
	const boot = useBoot();
	const sections = buildFreeVsProMatrix();

	const labels = {
		headFeature: __( 'Feature', 'customify' ),
		headFree: __( 'Free', 'customify' ),
		headPro: __( 'Pro', 'customify' ),
		cellYes: __( 'Included', 'customify' ),
		cellNo: __( 'Not included', 'customify' ),
	};

	return (
		<div className="customify-dashboard-free-vs-pro">
			<header className="customify-dashboard-free-vs-pro__header">
				<h2 className="customify-dashboard-free-vs-pro__title">
					{ __( 'Free vs Pro', 'customify' ) }
				</h2>
				<p className="customify-dashboard-free-vs-pro__tagline">
					{ __(
						'Customify Free covers a complete site build. Pro unlocks the modules and workflow features power-users reach for.',
						'customify',
					) }
				</p>
			</header>

			<CompareTable
				sections={ sections }
				labels={ labels }
				footer={ {
					title: __( 'Ready to unlock every module?', 'customify' ),
					// Description kept tight so kit's `.pmdk-compare__cta`
					// flex layout doesn't wrap the button to a new line
					// (`flex: 1 1 auto` on `.pmdk-compare__cta-text` —
					// long descriptions force the button down; tracked as
					// kit K-014).
					description: __(
						'Sticky headers, WooCommerce Booster, Blog Pro, custom fonts, and priority support.',
						'customify',
					),
					ctaLabel: __( 'Upgrade to Customify Pro', 'customify' ),
					ctaHref: boot?.urls?.proUpgrade || 'https://pressmaximum.com/customify/pro-upgrade/',
				} }
			/>
		</div>
	);
}
