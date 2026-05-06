/**
 * Native <select> primitive — port of .select from sample. Background arrow
 * is drawn via CSS (data: URL) so no JS needed for the chevron.
 *
 *   <Select value={v} onChange={setV} options={[
 *     { value: 'page', label: 'Per-page file' },
 *     ...
 *   ]} />
 */

export default function Select( {
	value,
	onChange,
	options,
	disabled,
	ariaLabel,
} ) {
	return (
		<select
			className="pm-select"
			value={ value }
			onChange={ ( e ) => onChange( e.target.value ) }
			disabled={ disabled }
			aria-label={ ariaLabel }
		>
			{ options.map( ( opt ) => (
				<option key={ opt.value } value={ opt.value }>
					{ opt.label }
				</option>
			) ) }
		</select>
	);
}
