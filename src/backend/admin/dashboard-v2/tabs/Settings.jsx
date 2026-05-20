/**
 * Settings tab — schema-driven panels rendered via the kit's SchemaForm.
 * Pro extends the field-type map via `customify.dashboard.settings.field-types`
 * and the panel list via `customify.dashboard.settings.panels`.
 */

import { useSelect, useDispatch, dispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader, Icon } from '@wordpress/components';
import { check as checkIcon } from '@wordpress/icons';
import {
	SchemaForm,
	SaveBar,
	BASE_FIELD_TYPES,
	panelHeadingId,
	useBoot,
} from '@pressmaximum/dashboard-kit';

import { CUSTOMIFY_SETTINGS_STORE } from '../data/settingsStore.js';

const NOTICES_STORE = 'core/notices';

// Green-circle check glyph passed as `icon` to success snackbars so the
// "Settings saved." toast carries a positive visual cue (mirrors
// Blocksify's pattern; kit ships no opinionated snackbar icon).
const SUCCESS_GLYPH = (
	<span className="customify-dashboard-snackbar__check">
		<Icon icon={ checkIcon } size={ 14 } />
	</span>
);

export default function Settings() {
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

	const panels = applyFilters(
		'customify.dashboard.settings.panels',
		schema.panels || [],
		boot,
	);

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
		// Kit's SaveBar docstring (SPEC §5.10b) places the confirmation
		// prompt on the consumer so kit doesn't own the translated copy.
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
				err?.message ||
					__( 'Reset failed. Try again.', 'customify' ),
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

	return (
		<div className="customify-dashboard-settings">
			{ panels.map( ( panel ) => (
				<Card
					key={ panel.id }
					className="customify-dashboard-settings__panel"
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
			) ) }

			<SaveBar
				isDirty={ isDirty }
				isSaving={ isSaving }
				onSave={ handleSave }
				onReset={ handleReset }
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
	);
}
