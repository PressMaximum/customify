/**
 * Small pill / badge. Variants ported from the sample (default neutral, free
 * green). Brand-specific tints can be added by extending the variant map.
 */

export default function Pill( { children, variant, className } ) {
	const cls = [ 'pm-pill' ];
	if ( variant ) {
		cls.push( `pm-pill--${ variant }` );
	}
	if ( className ) {
		cls.push( className );
	}
	return <span className={ cls.join( ' ' ) }>{ children }</span>;
}
