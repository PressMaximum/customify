/**
 * Resource list — vertical stack of side-row items inside a Card. Each row
 * is a link with leading icon, label, and trailing external-arrow icon.
 *
 *   <ResourceList items={[{ icon, label, href }]} />
 */

import Icon from '../Icon';

export default function ResourceList( { items } ) {
	return (
		<div className="pm-resource-list">
			{ items.map( ( item, i ) => (
				<a
					key={ i }
					className="pm-resource-list__row"
					href={ item.href || '#' }
				>
					<span className="pm-resource-list__icon">
						<Icon name={ item.icon } size={ 18 } />
					</span>
					<span className="pm-resource-list__label">
						{ item.label }
					</span>
					<span className="pm-resource-list__ext">
						<Icon name="chevron-right" size={ 12 } />
					</span>
				</a>
			) ) }
		</div>
	);
}
