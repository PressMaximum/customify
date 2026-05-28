/**
 * Column layout presets for footer rows.
 * fr: flex-fraction values for grid-template-columns.
 */
export const PRESETS = {
	1: [
		{ fr: [ 1 ] },
	],
	2: [
		{ fr: [ 1, 1 ] },
		{ fr: [ 1, 2 ] },
		{ fr: [ 2, 1 ] },
		{ fr: [ 1, 3 ] },
	],
	3: [
		{ fr: [ 1, 1, 1 ] },
		{ fr: [ 1, 2, 1 ] },
		{ fr: [ 2, 1, 1 ] },
		{ fr: [ 1, 1, 2 ] },
		{ fr: [ 1, 3, 1 ] },
		{ fr: [ 3, 1, 1 ] },
		{ fr: [ 1, 1, 3 ] },
		{ stacked: true },
	],
	4: [
		{ fr: [ 1, 1, 1, 1 ] },
		{ fr: [ 2, 1, 1, 1 ] },
		{ fr: [ 1, 2, 2, 1 ] },
		// fr shorter than count → grid items wrap to a new row.
		// fr=[1,1] with 4 items renders a 2×2 grid (50/50 on each row).
		{ fr: [ 1, 1 ], rows: 2 },
		{ stacked: true },
	],
	5: [
		{ fr: [ 1, 1, 1, 1, 1 ] },
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
