/**
 * Settings tab — schema-driven panels rendered via the kit's SchemaForm.
 * Pro extends the field-type map via `customify.dashboard.settings.field-types`
 * and the panel list via `customify.dashboard.settings.panels`.
 */

import { useSelect, useDispatch, dispatch } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import {
	SchemaForm,
	SaveBar,
	BASE_FIELD_TYPES,
	panelHeadingId,
	useBoot,
} from '@pressmaximum/dashboard-kit';

import { CUSTOMIFY_SETTINGS_STORE } from '../data/settingsStore.js';

const NOTICES_STORE = 'core/notices';

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

	const { edit, save, clearDirty } = useDispatch( CUSTOMIFY_SETTINGS_STORE );

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
				{ type: 'snackbar', isDismissible: true },
			);
		} catch ( err ) {
			dispatch( NOTICES_STORE ).createErrorNotice(
				err?.message ||
					__( 'Saving settings failed. Try again.', 'customify' ),
				{ type: 'snackbar', isDismissible: true },
			);
		}
	};

	const handleReset = () => {
		clearDirty();
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
					saveButton: __( 'Save changes', 'customify' ),
					resetButton: __( 'Discard', 'customify' ),
					unsavedNotice: __( 'You have unsaved changes.', 'customify' ),
					savingNotice: __( 'Saving…', 'customify' ),
				} }
			/>
		</div>
	);
}
