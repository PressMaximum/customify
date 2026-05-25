/**
 * Clickable card used inside the Customizer quick-link grid. Whole row
 * is the link surface (title + description stacked).
 *
 * Parent wraps a list of these in `.customify-dashboard-theme-grid` to
 * get the 2-col layout with internal hairline borders.
 */

export default function ThemeGridCard( { title, description, href } ) {
	return (
		<a
			className="customify-dashboard-theme-grid__item"
			href={ href || '#' }
			target="_blank"
			rel="noopener noreferrer"
		>
			<h4 className="customify-dashboard-theme-grid__title">{ title }</h4>
			{ description && (
				<p className="customify-dashboard-theme-grid__description">
					{ description }
				</p>
			) }
		</a>
	);
}
