/**
 * License upgrade card — sidebar Pro CTA. Slot-driven so brand-specific
 * pricing/features flow in via props. The CTA renders as a WP Button so it
 * inherits the user's admin color scheme + standard button affordances.
 */

import Icon from '../Icon';
import Button from '../Button';

export default function LicenseCard( {
	title,
	tagline,
	features,
	price,
	priceUnit,
	priceFootnote,
	ctaLabel,
	ctaHref,
	onCtaClick,
} ) {
	return (
		<div className="pm-license">
			<h4 className="pm-license__title">
				<Icon name="star-outline" size={ 16 } />
				{ title }
			</h4>
			{ tagline && (
				<p className="pm-license__tagline description">
					{ tagline }
				</p>
			) }
			<ul className="pm-license__features">
				{ features.map( ( feat, i ) => (
					<li key={ i }>
						<Icon name="check-bold" size={ 14 } />
						<span>{ feat }</span>
					</li>
				) ) }
			</ul>
			<div className="pm-license__price-line">
				<span className="pm-license__price">{ price }</span>
				<span className="pm-license__price-unit">{ priceUnit }</span>
			</div>
			{ priceFootnote && (
				<p className="pm-license__footnote description">
					{ priceFootnote }
				</p>
			) }
			<Button variant="primary" className={`pm-button--lg`} href={ ctaHref } onClick={ onCtaClick }>
				{ ctaLabel }
			</Button>
		</div>
	);
}
