/**
 * Pill-shaped on/off switch — port of .toggle from sample. Controlled:
 * parent owns `checked` + `onChange`.
 */

export default function ToggleSwitch( {
	checked,
	onChange,
	disabled,
	ariaLabel,
} ) {
	return (
		<label className="pm-toggle">
			<input
				type="checkbox"
				checked={ !! checked }
				onChange={ ( e ) => onChange( e.target.checked ) }
				disabled={ disabled }
				aria-label={ ariaLabel }
			/>
			<span className="pm-toggle__slider" />
		</label>
	);
}
