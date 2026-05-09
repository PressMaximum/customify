/**
 * Two grid card variants used in Welcome:
 *
 *   <ThemeGridCard title description href />
 *     — 2-col grid item with internal hairline borders (theme customizer)
 *
 *   <BlockCard icon name docHref />
 *     — 5-col grid item card with icon + name + meta link (free blocks)
 *
 * Both are link/click surfaces. Wrap the parent in <div class="pm-theme-grid">
 * or <div class="pm-blocks-grid"> to get the grid layout.
 */

import Icon from '../Icon';

export function ThemeGridCard( { title, description, href, onClick } ) {
	return (
		<a
			href={ href || '#' }
			className="pm-theme-grid__item"
			onClick={ onClick }
		>
			<h4>{ title }</h4>
			<p className="description">{ description }</p>
		</a>
	);
}

export function BlockCard( { icon, name, docHref, docLabel } ) {
	return (
		<div className="pm-block-card">
			<div className="pm-block-card__icon">
				<Icon name={ icon } size={ 24 } />
			</div>
			<div>
				<h4>{ name }</h4>
				<div className="pm-block-card__meta">
					<a href={ docHref || '#' }>{ docLabel }</a>
				</div>
			</div>
		</div>
	);
}
