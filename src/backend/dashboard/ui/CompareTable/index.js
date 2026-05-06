/**
 * Free vs Pro comparison table primitives.
 *
 *   <CompareTable>
 *     <CompareTable.Section title="Blocks" colLabels={['Free','Pro']} />
 *     <CompareTable.Row name="Section block" detail="..." cells={[true,true]} />
 *     <CompareTable.Row name="Per-block CSS" cells={[true,'1 / Unlimited']} />
 *     <CompareTable.CTA>...</CompareTable.CTA>
 *   </CompareTable>
 *
 * Cell value rules:
 *   true       → green check
 *   false      → gray dash
 *   string     → text-value (default tone)
 *   { value, muted } → text-value with muted modifier
 */

import Icon from '../Icon';

function CompareTable( { children, className } ) {
	const cls = [ 'pm-compare' ];
	if ( className ) {
		cls.push( className );
	}
	return <div className={ cls.join( ' ' ) }>{ children }</div>;
}

function CompareSection( { title, colLabels = [ 'Free', 'Pro' ] } ) {
	return (
		<div className="pm-compare__section">
			<div>{ title }</div>
			<div className="pm-compare__col-label">{ colLabels[ 0 ] }</div>
			<div className="pm-compare__col-label pm-compare__col-label--pro">
				{ colLabels[ 1 ] }
			</div>
		</div>
	);
}

function renderCell( value, key ) {
	if ( value === true ) {
		return (
			<div className="pm-compare__col" key={ key }>
				<span className="pm-compare__check-yes">
					<Icon name="check" size={ 12 } />
				</span>
			</div>
		);
	}
	if ( value === false ) {
		return (
			<div className="pm-compare__col" key={ key }>
				<span className="pm-compare__check-no">−</span>
			</div>
		);
	}
	const text = typeof value === 'object' ? value.value : value;
	const muted = typeof value === 'object' ? value.muted : false;
	const cls = [ 'pm-compare__text' ];
	if ( muted ) {
		cls.push( 'pm-compare__text--muted' );
	}
	return (
		<div className="pm-compare__col" key={ key }>
			<span className={ cls.join( ' ' ) }>{ text }</span>
		</div>
	);
}

function CompareRow( { name, detail, cells } ) {
	return (
		<div className="pm-compare__row">
			<div className="pm-compare__name">
				{ name }
				{ detail && <p className="description">{ detail }</p> }
			</div>
			{ cells.map( ( c, i ) => renderCell( c, i ) ) }
		</div>
	);
}

function CompareCTA( { title, description, action } ) {
	return (
		<div className="pm-compare__cta">
			<div className="pm-compare__cta-text">
				<h4>{ title }</h4>
				<p className="description">{ description }</p>
			</div>
			{ action }
		</div>
	);
}

CompareTable.Section = CompareSection;
CompareTable.Row = CompareRow;
CompareTable.CTA = CompareCTA;

export default CompareTable;
