/**
 * Generic SVG icon. Reads from the central ICONS map (./icons.js) so callers
 * never inline SVG markup. Add a new icon by extending the map, not by
 * adding a new component.
 */

import { ICONS } from './icons';

export default function Icon( { name, size = 16, className } ) {
	const icon = ICONS[ name ];
	if ( ! icon ) {
		return null;
	}
	const { viewBox, paths, stroke, strokeWidth, fill } = icon;
	return (
		<svg
			width={ size }
			height={ size }
			viewBox={ viewBox }
			fill={ fill || 'none' }
			stroke={ stroke }
			strokeWidth={ strokeWidth }
			className={ className }
			aria-hidden="true"
			focusable="false"
		>
			{ paths }
		</svg>
	);
}
