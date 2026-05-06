/**
 * Welcome layout shell — main column + sticky sidebar. Sidebar collapses to
 * a 2-col stack at <=1100px and a 1-col stack at <=720px (CSS).
 */

export function WelcomeLayout( { main, sidebar } ) {
	return (
		<div className="pm-welcome">
			<div className="pm-welcome__main">{ main }</div>
			<div className="pm-welcome__sidebar">{ sidebar }</div>
		</div>
	);
}
