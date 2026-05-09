/**
 * Settings field row — title + description on the left, control slot on the
 * right. Stack rows directly inside a Card; the row owns its own border-top
 * (first-of-type strips it).
 *
 *   <SettingRow
 *     title="Per-block asset loading"
 *     description="Only load CSS and JS for blocks that..."
 *     control={<ToggleSwitch checked={v} onChange={setV} />}
 *   />
 */

export default function SettingRow( { title, description, control } ) {
	return (
		<div className="pm-setting-row">
			<div className="pm-setting-row__info">
				<h4>{ title }</h4>
				{ description && (
					<p className="description">{ description }</p>
				) }
			</div>
			<div className="pm-setting-row__control">{ control }</div>
		</div>
	);
}
