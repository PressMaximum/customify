/**
 * Column layout presets for footer rows.
 * fr: flex-fraction values for grid-template-columns.
 * layout: optional mixed-row identifier for five-column grids.
 * rowCols: visual row column counts used by the preset icon only.
 */
export const PRESETS = {
	1: [
		{ fr: [ 1 ] },
	],
	2: [
		// Balanced, moderate splits, strong splits, then stacked.
		{ fr: [ 1, 1 ] },
		{ fr: [ 2, 3 ] },
		{ fr: [ 3, 2 ] },
		{ fr: [ 1, 2 ] },
		{ fr: [ 2, 1 ] },
		{ fr: [ 1, 3 ] },
		{ fr: [ 3, 1 ] },
		{ stacked: true },
	],
	3: [
		// Balanced, mirrored edge emphasis, center/group emphasis, stacked.
		{ fr: [ 1, 1, 1 ] },
		{ fr: [ 2, 1, 1 ] },
		{ fr: [ 1, 1, 2 ] },
		{ fr: [ 3, 1, 1 ] },
		{ fr: [ 1, 1, 3 ] },
		{ fr: [ 1, 2, 1 ] },
		{ fr: [ 1, 3, 1 ] },
		{ fr: [ 2, 2, 1 ] },
		{ fr: [ 1, 2, 2 ] },
		{ fr: [ 2, 1, 2 ] },
		{ stacked: true },
	],
	4: [
		// Balanced, single-column emphasis, symmetric emphasis, wrapped, stacked.
		{ fr: [ 1, 1, 1, 1 ] },
		{ fr: [ 2, 1, 1, 1 ] },
		{ fr: [ 1, 1, 1, 2 ] },
		{ fr: [ 1, 2, 1, 1 ] },
		{ fr: [ 1, 1, 2, 1 ] },
		{ fr: [ 2, 2, 1, 1 ] },
		{ fr: [ 1, 2, 2, 1 ] },
		{ fr: [ 1, 1, 2, 2 ] },
		{ fr: [ 2, 1, 1, 2 ] },
		// fr shorter than count → grid items wrap to a new row.
		{ fr: [ 1, 1 ], rows: 2 },
		{ fr: [ 1, 2 ], rows: 2 },
		{ fr: [ 2, 1 ], rows: 2 },
		{ stacked: true },
	],
	5: [
		// Balanced, emphasis variants, mixed-row grids, then stacked.
		{ fr: [ 1, 1, 1, 1, 1 ] },
		{ fr: [ 2, 1, 1, 1, 1 ] },
		{ fr: [ 1, 1, 1, 1, 2 ] },
		{ fr: [ 1, 2, 1, 1, 1 ] },
		{ fr: [ 1, 1, 1, 2, 1 ] },
		{ fr: [ 1, 1, 2, 1, 1 ] },
		{ fr: [ 2, 2, 1, 1, 1 ] },
		{ fr: [ 1, 2, 2, 1, 1 ] },
		{ fr: [ 1, 1, 2, 2, 1 ] },
		{ fr: [ 1, 1, 1, 2, 2 ] },
		{ fr: [ 2, 1, 1, 1, 2 ] },
		{ fr: [ 1, 1 ], layout: '2-3', rowCols: [ 2, 3 ] },
		{ fr: [ 1, 1, 1 ], layout: '3-2', rowCols: [ 3, 3 ] },
		{ fr: [ 1, 1 ], layout: '2-2-1', rowCols: [ 2, 2, 2 ] },
		{ stacked: true },
	],
};

// count is global; fr, gap, padding are per-device.
// Per-device defaults sync with PHP defaults in
// inc/customizer/configs/config-default.php — keep both files in step so
// the React Builder UI's initial render matches what PHP emits on the
// frontend when no col_layout is saved yet (tablet collapses to 2 cols,
// mobile stacks to 1 col — sensible defaults for footer-style content).
export const DEFAULT_VALUE = {
	count:   4,
	desktop: { fr: [ 1, 1, 1, 1 ], gap: 0, padding: 0 },
	tablet:  { fr: [ 1, 1 ],       gap: 0, padding: 0 },
	mobile:  { fr: [ 1 ],          gap: 0, padding: 0 },
};
