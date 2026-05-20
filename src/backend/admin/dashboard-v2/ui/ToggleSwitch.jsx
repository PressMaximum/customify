/**
 * Pill-shaped on/off switch. Controlled — parent owns state.
 *
 * Visually neutralizes the WP-admin checkbox `:checked::before` glyph
 * which would otherwise paint a misaligned tick on top of the slider.
 */

export default function ToggleSwitch( {
	checked,
	onChange,
	disabled,
	ariaLabel,
} ) {
	return (
		<label className="customify-dashboard-toggle">
			<input
				type="checkbox"
				checked={ !! checked }
				onChange={ ( event ) => onChange( event.target.checked ) }
				disabled={ !! disabled }
				aria-label={ ariaLabel }
			/>
			<span className="customify-dashboard-toggle__slider" />
		</label>
	);
}
