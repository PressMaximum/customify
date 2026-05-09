/**
 * Dashboard App — layout shell. Header + active tab pane + footer, with
 * URL hash routing. Each tab component is responsible for its own content;
 * App only owns the chrome and the active-tab state.
 */

import AppHeader from './AppHeader';
import AppFooter from './AppFooter';
import Notices from './components/Notices';
import { TABS } from './routes';
import useHashRoute from './useHashRoute';

import Welcome from './tabs/Welcome';
import Settings from './tabs/Settings';
import FreeVsPro from './tabs/FreeVsPro';
import Changelog from './tabs/Changelog';

const PANES = {
	welcome: Welcome,
	settings: Settings,
	'free-vs-pro': FreeVsPro,
	changelog: Changelog,
};

export default function App() {
	const [ tab, navigate ] = useHashRoute();
	const Pane = PANES[ tab ] || Welcome;

	return (
		<>
			<AppHeader
				tabs={ TABS }
				activeTab={ tab }
				onTabChange={ navigate }
			/>
			<div className="pm-tab-pane">
				<Pane />
			</div>
			<AppFooter />
			<Notices />
		</>
	);
}
