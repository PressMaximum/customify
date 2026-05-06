/**
 * Welcome hero — left text block + right preview slot. Both halves are
 * slot-driven so brand-specific content (greeting, title, CTA, preview)
 * flows in via props.
 */

export default function Hero( {
	greeting,
	title,
	description,
	actions,
	preview,
} ) {
	return (
		<div className="pm-hero">
			<div className="pm-hero__left">
				{ greeting && <p className="pm-hero__greeting">{ greeting }</p> }
				{ title && <h1 className="pm-hero__title">{ title }</h1> }
				{ description && (
					<p className="pm-hero__desc description">
						{ description }
					</p>
				) }
				{ actions && (
					<div className="pm-hero__actions">{ actions }</div>
				) }
			</div>
			{ preview && <div className="pm-hero__right">{ preview }</div> }
		</div>
	);
}
