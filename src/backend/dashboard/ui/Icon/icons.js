/**
 * Central SVG icon paths for the dashboard. Each entry is { viewBox, paths,
 * stroke?, strokeWidth?, fill? }. The <Icon name size /> component reads
 * from this map — never inline SVG in component JSX.
 *
 * Icon shapes are ported directly from samples/Dashboard.html so the built
 * dashboard matches the design source.
 */

export const ICONS = {
	'chevron-right': {
		viewBox: '0 0 12 12',
		stroke: 'currentColor',
		strokeWidth: 1.4,
		fill: 'none',
		paths: (
			<path
				d="M3 9l6-6M9 3v5M9 3H4"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
		),
	},
	plus: {
		viewBox: '0 0 16 16',
		stroke: 'currentColor',
		strokeWidth: 2,
		fill: 'none',
		paths: <path d="M8 3v10M3 8h10" />,
	},
	check: {
		viewBox: '0 0 12 12',
		stroke: 'currentColor',
		strokeWidth: 2.2,
		fill: 'none',
		paths: (
			<path
				d="M2.5 6.5l2.5 2.5 4.5-5"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
		),
	},
	'check-bold': {
		viewBox: '0 0 14 14',
		stroke: 'currentColor',
		strokeWidth: 2.2,
		fill: 'none',
		paths: (
			<path
				d="M3 7.5l2.5 2.5L11 4"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
		),
	},
	'dots-vertical': {
		viewBox: '0 0 16 16',
		fill: 'currentColor',
		paths: (
			<>
				<circle cx="8" cy="3" r="1.4" />
				<circle cx="8" cy="8" r="1.4" />
				<circle cx="8" cy="13" r="1.4" />
			</>
		),
	},
	close: {
		viewBox: '0 0 20 20',
		stroke: 'currentColor',
		strokeWidth: 1.8,
		fill: 'none',
		paths: <path d="M5 5l10 10M15 5L5 15" strokeLinecap="round" />,
	},
	info: {
		viewBox: '0 0 18 18',
		stroke: 'currentColor',
		strokeWidth: 1.6,
		fill: 'none',
		paths: (
			<>
				<circle cx="9" cy="9" r="7" />
				<path d="M9 8v4.5M9 5.5v.5" strokeLinecap="round" />
			</>
		),
	},
	warning: {
		// Triangle with exclamation mark — used by toast notices for errors
		// and warnings. Stroke + fill set by callers via CSS color so the
		// same SVG works in both yellow (warning) and red (error) variants.
		viewBox: '0 0 18 18',
		stroke: 'currentColor',
		strokeWidth: 1.5,
		fill: 'none',
		paths: (
			<>
				<path
					d="M9 2.2L16.5 15.3H1.5z"
					strokeLinecap="round"
					strokeLinejoin="round"
				/>
				<path d="M9 7v4" strokeLinecap="round" />
				<circle cx="9" cy="13" r="0.7" fill="currentColor" />
			</>
		),
	},
	'arrow-up-right': {
		viewBox: '0 0 13 13',
		stroke: 'currentColor',
		strokeWidth: 1.8,
		fill: 'none',
		paths: (
			<path
				d="M3 10L10 3M5 3h5v5"
				strokeLinecap="round"
				strokeLinejoin="round"
			/>
		),
	},
	'eye-off': {
		viewBox: '0 0 14 14',
		stroke: 'currentColor',
		strokeWidth: 1.5,
		fill: 'none',
		paths: (
			<>
				<path d="M2 7s2-4 5-4 5 4 5 4-2 4-5 4-5-4-5-4z" />
				<path d="M2 2l10 10" strokeLinecap="round" />
			</>
		),
	},
	clock: {
		viewBox: '0 0 14 14',
		stroke: 'currentColor',
		strokeWidth: 1.7,
		fill: 'none',
		paths: (
			<>
				<circle cx="7" cy="7" r="5.5" />
				<path d="M7 4v3.5l2 1.5" />
			</>
		),
	},
	star: {
		viewBox: '0 0 16 16',
		fill: 'currentColor',
		paths: (
			<path d="M8 1l2 5 5 .5-3.7 3.5L12.5 15 8 12 3.5 15l1.2-5L1 6.5 6 6z" />
		),
	},
	'star-outline': {
		viewBox: '0 0 16 16',
		stroke: 'currentColor',
		strokeWidth: 1.7,
		fill: 'none',
		paths: (
			<path
				d="M8 1l1.8 4 4.7.5-3.5 3 .8 4.5L8 11l-3.8 2 .8-4.5-3-3 4.2-.5z"
				strokeLinejoin="round"
			/>
		),
	},
	doc: {
		viewBox: '0 0 18 18',
		stroke: 'currentColor',
		strokeWidth: 1.5,
		fill: 'none',
		paths: (
			<>
				<path d="M3 2h8l4 4v10H3z" />
				<path d="M11 2v4h4" />
			</>
		),
	},
	'clock-large': {
		viewBox: '0 0 18 18',
		stroke: 'currentColor',
		strokeWidth: 1.5,
		fill: 'none',
		paths: (
			<>
				<circle cx="9" cy="9" r="6.5" />
				<path d="M9 5v4l2.5 2" />
			</>
		),
	},
	'star-large': {
		viewBox: '0 0 18 18',
		stroke: 'currentColor',
		strokeWidth: 1.5,
		fill: 'none',
		paths: (
			<path
				d="M9 1.5l2.3 5.2 5.7.5-4.3 3.8 1.3 5.6L9 13.7l-5 2.9 1.3-5.6L1 7.2l5.7-.5z"
				strokeLinejoin="round"
			/>
		),
	},
	mail: {
		viewBox: '0 0 18 18',
		stroke: 'currentColor',
		strokeWidth: 1.5,
		fill: 'none',
		paths: (
			<>
				<path d="M2 4h14v10H2z" />
				<path d="M2 4l7 5 7-5" />
			</>
		),
	},
	gauge: {
		viewBox: '0 0 16 16',
		stroke: 'currentColor',
		strokeWidth: 1.6,
		fill: 'none',
		paths: (
			<>
				<path d="M8 1l1.8 1.6 2.4-.4-.4 2.4L13.4 6.5 11.8 8l1.6 1.5L13.8 12l-2.4-.4L9.6 14 8 12.4 6.4 14 4.6 11.6 2.2 12l.4-2.5L1 8l1.6-1.5L2.2 4l2.4.4L6.4 2z" />
			</>
		),
	},
	'version-arrow': {
		viewBox: '0 0 16 16',
		stroke: 'currentColor',
		strokeWidth: 1.6,
		fill: 'none',
		paths: (
			<>
				<path d="M2 8a6 6 0 1112 0M14 8l-2-2M14 8l2-2" />
				<path d="M8 4v4l2.5 2" />
			</>
		),
	},
	'block-section': {
		viewBox: '0 0 32 32',
		stroke: 'currentColor',
		strokeWidth: 2,
		fill: 'none',
		paths: (
			<>
				<rect x="4" y="6" width="24" height="20" rx="2" />
				<path d="M4 13h24" />
			</>
		),
	},
	'block-container': {
		viewBox: '0 0 32 32',
		stroke: 'currentColor',
		strokeWidth: 2,
		fill: 'none',
		paths: (
			<>
				<rect x="4" y="4" width="11" height="11" rx="1.5" />
				<rect x="17" y="4" width="11" height="11" rx="1.5" />
				<rect x="4" y="17" width="11" height="11" rx="1.5" />
				<rect x="17" y="17" width="11" height="11" rx="1.5" />
			</>
		),
	},
	'block-heading': {
		viewBox: '0 0 32 32',
		stroke: 'currentColor',
		strokeWidth: 2.5,
		fill: 'none',
		paths: <path d="M7 6v20M25 6v20M7 16h18" />,
	},
	'block-button': {
		viewBox: '0 0 32 32',
		stroke: 'currentColor',
		strokeWidth: 2,
		fill: 'none',
		paths: (
			<>
				<rect
					x="4"
					y="10"
					width="24"
					height="12"
					rx="3"
					fill="currentColor"
					opacity=".15"
				/>
				<rect x="4" y="10" width="24" height="12" rx="3" />
			</>
		),
	},
	'block-image': {
		viewBox: '0 0 32 32',
		stroke: 'currentColor',
		strokeWidth: 2,
		fill: 'none',
		paths: (
			<>
				<rect x="4" y="4" width="24" height="24" rx="2" />
				<circle cx="11" cy="12" r="3" fill="currentColor" />
				<path d="M5 26l8-9 7 6 6-5 5 4" />
			</>
		),
	},

	/* GuidePopover hero illustrations — viewBox 240×160 (sample's POPOVERS
	 * canvas). Filled rects/paths only, no stroke at SVG level. Inner
	 * elements carry their own fill values verbatim from the sample's
	 * inline JS constants (ICON_BLOCK_GRID / ICON_DESIGN_KIT / etc.). */
	'guide-block-grid': {
		viewBox: '0 0 240 160',
		fill: 'none',
		paths: (
			<>
				<rect x="40" y="32" width="68" height="44" rx="4" fill="rgba(255,255,255,.95)" />
				<rect x="116" y="32" width="68" height="44" rx="4" fill="rgba(255,255,255,.65)" />
				<rect x="40" y="84" width="68" height="44" rx="4" fill="rgba(255,255,255,.65)" />
				<rect x="116" y="84" width="68" height="44" rx="4" fill="rgba(255,255,255,.95)" />
			</>
		),
	},
	'guide-design-kit': {
		viewBox: '0 0 240 160',
		fill: 'none',
		paths: (
			<>
				<rect x="36" y="22" width="78" height="46" rx="4" fill="rgba(255,255,255,.95)" />
				<rect x="122" y="22" width="78" height="46" rx="4" fill="rgba(255,255,255,.7)" />
				<rect x="36" y="76" width="78" height="46" rx="4" fill="rgba(255,255,255,.7)" />
				<rect x="122" y="76" width="78" height="46" rx="4" fill="rgba(255,255,255,.95)" />
				<rect x="36" y="130" width="164" height="14" rx="3" fill="rgba(255,255,255,.85)" />
			</>
		),
	},
	'guide-gauge': {
		viewBox: '0 0 240 160',
		fill: 'none',
		paths: (
			<>
				<circle cx="120" cy="92" r="44" fill="none" stroke="rgba(255,255,255,.95)" strokeWidth="6" />
				<path d="M120 80 L120 92 L138 100" stroke="rgba(255,255,255,.95)" strokeWidth="6" strokeLinecap="round" fill="none" />
				<path d="M120 24 L132 50 L120 46 L108 50 Z" fill="rgba(255,255,255,.95)" />
			</>
		),
	},
	'guide-play': {
		viewBox: '0 0 240 160',
		fill: 'none',
		paths: (
			<>
				<rect x="40" y="40" width="160" height="80" rx="6" fill="rgba(255,255,255,.92)" />
				<polygon points="100,60 100,100 140,80" fill="rgba(56,88,233,.95)" />
			</>
		),
	},
	'guide-build': {
		viewBox: '0 0 240 160',
		fill: 'none',
		paths: (
			<>
				<path d="M60 90 L60 60 L100 40 L140 60 L180 40 L180 90 L140 110 L100 90 Z" fill="rgba(255,255,255,.95)" stroke="rgba(56,88,233,.4)" strokeWidth="2" strokeLinejoin="round" />
				<path d="M100 40 L100 90 M140 60 L140 110" stroke="rgba(56,88,233,.4)" strokeWidth="2" />
			</>
		),
	},
	'guide-layers': {
		viewBox: '0 0 240 160',
		fill: 'none',
		paths: (
			<>
				<rect x="60" y="40" width="120" height="22" rx="3" fill="rgba(255,255,255,.95)" />
				<rect x="50" y="68" width="140" height="22" rx="3" fill="rgba(255,255,255,.75)" />
				<rect x="40" y="96" width="160" height="22" rx="3" fill="rgba(255,255,255,.55)" />
			</>
		),
	},
};
