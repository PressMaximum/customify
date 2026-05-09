/**
 * Review prompt card body — 5 stars + tagline + WP Button CTA. Wrap in a
 * <Card title> for the full sidebar look.
 */

import Icon from '../Icon';
import Button from '../Button';

export default function ReviewCard( {
	rating = 5,
	message,
	ctaLabel,
	ctaHref,
	onCtaClick,
} ) {
	return (
		<div className="pm-review">
			<div className="pm-review__stars">
				{ Array.from( { length: rating } ).map( ( _, i ) => (
					<Icon key={ i } name="star" size={ 16 } />
				) ) }
			</div>
			<p className="description">{ message }</p>
			<Button
				variant="secondary"
				href={ ctaHref }
				onClick={ onCtaClick }
				className={'pm-fullwidth'}
			>
				{ ctaLabel }
			</Button>
		</div>
	);
}
