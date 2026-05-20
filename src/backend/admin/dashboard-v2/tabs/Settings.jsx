/**
 * Settings tab — SubNav layout. Each panel renders as its own sub-tab
 * (General = theme settings, then one tab per Pro module that
 * registers via `customify.dashboard.settings.panels`).
 *
 * Per-panel save:
 *   - Theme panels (proPanel !== true) share the theme settings store;
 *     the kit's SaveBar is rendered inside the active panel's CardBody
 *     so the page never shows two save clusters at once.
 *   - Pro panels are self-contained (own state + REST endpoint + Save /
 *     Discard cluster baked into ProModuleSettingsPanel).
 *
 * Single-panel case: SubNav hides; the lone panel renders full-width.
 */

import { useEffect } from '@wordpress/element';
import { useSelect, useDispatch, dispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Icon } from '@wordpress/components';
import { check as checkIcon } from '@wordpress/icons';
import {
	SchemaForm,
	SaveBar,
	SubNav,
	BASE_FIELD_TYPES,
	panelHeadingId,
	useBoot,
	navigate,
} from '@pressmaximum/dashboard-kit';

import { CUSTOMIFY_SETTINGS_STORE } from '../data/settingsStore.js';
import ProModuleSettingsPanel from '../sections/ProModuleSettingsPanel.jsx';

const NOTICES_STORE = 'core/notices';

const SUCCESS_GLYPH = (
	<span className="customify-dashboard-snackbar__check">
		<Icon icon={ checkIcon } size={ 14 } />
	</span>
);

/**
 * Render one theme-side schema panel + its own SaveBar in the body.
 * Theme panels share the dashboard-v2 settings store so they all
 * read the same `getSettings()` / dispatch the same `edit()` /
 * `save()` actions; only the visible panel's card frames the bar.
 */
function ThemePanelCard( { panel, values, fieldTypes, isDirty, isSaving, edit, onSave, onReset } ) {
	return (
		<>
			<Card
				className="customify-dashboard-settings__panel"
				data-panel-id={ panel.id }
			>
				<CardHeader>
					<h2 id={ panelHeadingId( panel.id ) }>{ panel.label }</h2>
				</CardHeader>
				<CardBody>
					{ panel.description && (
						<p className="customify-dashboard-settings__description">
							{ panel.description }
						</p>
					) }
					<SchemaForm
						panel={ panel }
						values={ values || {} }
						onFieldChange={ ( panelId, fieldId, next ) =>
							edit( `${ panelId }.${ fieldId }`, next )
						}
						fieldTypes={ fieldTypes }
					/>
				</CardBody>
			</Card>
			<div className="customify-dashboard-settings__panel-actions">
				<SaveBar
					isDirty={ isDirty }
					isSaving={ isSaving }
					onSave={ onSave }
					onReset={ onReset }
					labels={ {
						regionLabel: __( 'Settings actions', 'customify' ),
						saveLabel: __( 'Save changes', 'customify' ),
						savingLabel: __( 'Saving…', 'customify' ),
						resetLabel: __( 'Reset to defaults', 'customify' ),
						statusSaved: __( 'All changes saved', 'customify' ),
						statusDirty: __( 'Unsaved changes', 'customify' ),
						statusSaving: __( 'Saving…', 'customify' ),
					} }
				/>
			</div>
		</>
	);
}

export default function Settings( { params } ) {
	const boot = useBoot();
	const schema = boot?.settings?.schema || { panels: [] };

	const values = useSelect(
		( select ) => select( CUSTOMIFY_SETTINGS_STORE ).getSettings(),
		[],
	);
	const isDirty = useSelect(
		( select ) => select( CUSTOMIFY_SETTINGS_STORE ).isDirty(),
		[],
	);
	const isSaving = useSelect(
		( select ) => select( CUSTOMIFY_SETTINGS_STORE ).isSaving(),
		[],
	);

	const { edit, save, reset } = useDispatch( CUSTOMIFY_SETTINGS_STORE );

	const fieldTypes = applyFilters(
		'customify.dashboard.settings.field-types',
		BASE_FIELD_TYPES,
	);

	// NB: do NOT useMemo here — the panels filter list is mutated by
	// Pro / child theme bundles that load *after* this script (theme
	// bundle calls mountDashboard synchronously; Pro's bridge JS
	// registers its filters once its own <script> executes). A memoised
	// applyFilters call captured before Pro registered would never see
	// the appended panels. Recomputing per render is cheap.
	const panels = applyFilters(
		'customify.dashboard.settings.panels',
		schema.panels || [],
		boot,
	);

	const requestedPanelId = params?.panelId || null;
	const activePanel =
		panels.find( ( p ) => p.id === requestedPanelId ) || panels[ 0 ] || null;

	// Redirect bare `#settings` to the canonical multi-panel path so
	// SubNav has a resolved active row. Skipped when only one panel is
	// registered (Free + Typekit-off case for example).
	useEffect( () => {
		if ( ! requestedPanelId && panels.length > 1 && activePanel ) {
			navigate( `#settings/${ activePanel.id }` );
		}
	}, [ requestedPanelId, panels, activePanel ] );

	const handleSave = async () => {
		try {
			await save();
			dispatch( NOTICES_STORE ).createSuccessNotice(
				__( 'Settings saved.', 'customify' ),
				{
					type: 'snackbar',
					isDismissible: true,
					icon: SUCCESS_GLYPH,
				},
			);
		} catch ( err ) {
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message ||
					__( 'Saving settings failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		}
	};

	const handleReset = async () => {
		const confirmed = window.confirm(
			__(
				'Reset all settings to their defaults? This cannot be undone.',
				'customify',
			),
		);
		if ( ! confirmed ) {
			return;
		}
		try {
			await reset();
			dispatch( NOTICES_STORE ).createSuccessNotice(
				__( 'Settings reset to defaults.', 'customify' ),
				{
					type: 'snackbar',
					isDismissible: true,
					icon: SUCCESS_GLYPH,
				},
			);
		} catch ( err ) {
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message || __( 'Reset failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		}
	};

	if ( ! panels.length ) {
		return (
			<Card>
				<CardBody>
					<p>
						{ __(
							'No settings registered yet. Pro and child theme add-ons can extend this tab via the customify.dashboard.settings.panels filter.',
							'customify',
						) }
					</p>
				</CardBody>
			</Card>
		);
	}

	const renderActivePanel = () => {
		if ( ! activePanel ) {
			return null;
		}
		if ( activePanel.proPanel ) {
			return (
				<ProModuleSettingsPanel
					panel={ activePanel }
					scrollIntoView={ activePanel.id === requestedPanelId }
				/>
			);
		}
		return (
			<ThemePanelCard
				panel={ activePanel }
				values={ values }
				fieldTypes={ fieldTypes }
				isDirty={ isDirty }
				isSaving={ isSaving }
				edit={ edit }
				onSave={ handleSave }
				onReset={ handleReset }
			/>
		);
	};

	if ( panels.length < 2 ) {
		return (
			<div className="customify-dashboard-settings">
				{ renderActivePanel() }
			</div>
		);
	}

	return (
		<div className="customify-dashboard-settings customify-dashboard-settings--tabbed">
			<SubNav
				items={ panels.map( ( p ) => ( {
					id: p.id,
					label: p.label,
					hash: `#settings/${ p.id }`,
				} ) ) }
				activeId={ activePanel?.id }
				ariaLabel={ __( 'Settings panels', 'customify' ) }
			/>
			<div className="customify-dashboard-settings__pane">
				{ renderActivePanel() }
			</div>
		</div>
	);
}
