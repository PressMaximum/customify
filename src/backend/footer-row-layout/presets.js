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
		{ fr: [ 1, 2, 2, 1 ] },
		{ stacked: true },
	],
	5: [
		{ fr: [ 1, 1, 1, 1, 1 ] },
		{ stacked: true },
	],
};

// count is global; fr, gap, padding are per-device.
export const DEFAULT_VALUE = {
	count:   4,
	desktop: { fr: [ 1, 1, 1, 1 ], gap: 0, padding: 0 },
	tablet:  { fr: [ 1, 1, 1, 1 ], gap: 0, padding: 0 },
	mobile:  { fr: [ 1 ],          gap: 0, padding: 0 },
};
