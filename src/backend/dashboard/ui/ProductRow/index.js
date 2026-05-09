/**
 * Cross-promo product row — gradient initials avatar + name + meta + install
 * link. Used inside Cards to advertise sister products.
 */

import Icon from '../Icon';

export default function ProductRow( {
	initials,
	gradient = 'blue',
	name,
	meta,
	ctaLabel,
	ctaHref,
	onCtaClick,
} ) {
	return (
		<div className="pm-product-row">
			<div
				className={ `pm-product-row__pic pm-product-row__pic--${ gradient }` }
			>
				{ initials }
			</div>
			<div className="pm-product-row__info">
				<h5>{ name }</h5>
				<p className="description">{ meta }</p>
				<a
					className="pm-product-row__cta"
					href={ ctaHref || '#' }
					onClick={ onCtaClick }
				>
					{ ctaLabel }
					<Icon name="chevron-right" size={ 12 } />
				</a>
			</div>
		</div>
	);
}
