/**
 * Settings tab layout — left sub-nav (240px) + right content. Collapses to
 * a horizontal scrollable nav above the content at <=1100px.
 */

export default function SettingsLayout( { nav, children } ) {
	return (
		<div className="pm-settings">
			<div className="pm-settings__nav">{ nav }</div>
			<div className="pm-settings__content">{ children }</div>
		</div>
	);
}
