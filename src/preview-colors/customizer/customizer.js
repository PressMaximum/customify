/*
 * Customify Preview Colors — Customizer control bundle (React).
 *
 * Mounted in light DOM directly inside a WP_Customize_Control container.
 * Uses @wordpress/element (React) for declarative state management; the
 * rendered markup keeps the same class names as the SCSS source so styles
 * work unchanged.
 *
 * State is synced bidirectionally with two `option`-type Customizer settings
 * (`customify_preview_user_palettes` + `customify_preview_active_palette`)
 * via `wp.customize(id).get()` / `.set()` / `.bind()`. `transport: 'postMessage'`
 * means the preview iframe receives the changes live (handled by
 * preview-colors-preview.js — that bundle rewrites :root CSS vars).
 *
 * Skipped vs the frontend overlay: no shadow DOM, no `.sb-header`, no
 * collapse / reopen chrome, no browse-all modal — the inline Customizer
 * layout doesn't use them.
 */

import {
	createRoot,
	Fragment,
	useCallback,
	useEffect,
	useMemo,
	useRef,
	useState,
} from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

// ────────────────────────────────────────────────────────────────── helpers

function hexToRgbArray(hex) {
	let c = String(hex || '').replace('#', '');
	if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
	if (!/^[0-9A-Fa-f]{6}$/.test(c)) return null;
	return [parseInt(c.substr(0, 2), 16), parseInt(c.substr(2, 2), 16), parseInt(c.substr(4, 2), 16)];
}
function hexToRgb(hex) {
	const rgb = hexToRgbArray(hex);
	return rgb ? rgb.join(', ') : null;
}
function luminance(rgb) {
	const f = (v) => {
		v /= 255;
		return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
	};
	return 0.2126 * f(rgb[0]) + 0.7152 * f(rgb[1]) + 0.0722 * f(rgb[2]);
}
function pickOn(hex) {
	const rgb = hexToRgbArray(hex);
	if (!rgb) return '#FFFFFF';
	return luminance(rgb) > 0.45 ? '#1A1A1A' : '#FFFFFF';
}
function isLight(hex) {
	const rgb = hexToRgbArray(hex);
	if (!rgb) return true;
	return (0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2]) / 255 > 0.7;
}

// ────────────────────────────────────────────────────────────────── hooks

/**
 * Bridge a wp.customize() setting ↔ React state. The hook reads the initial
 * value via .get(), subscribes to .bind() so external changes (e.g. preview
 * iframe via postMessage) re-render, and exposes a setter that writes back
 * with .set().
 */
function useCustomizeSetting(settingId, defaultValue) {
	const [value, setValue] = useState(() => {
		const s = window.wp?.customize?.(settingId);
		const v = s && typeof s.get === 'function' ? s.get() : undefined;
		return v === undefined || v === null ? defaultValue : v;
	});

	useEffect(() => {
		const s = window.wp?.customize?.(settingId);
		if (!s || typeof s.bind !== 'function') return undefined;
		const handler = (newVal) => setValue(newVal);
		s.bind(handler);
		return () => {
			if (typeof s.unbind === 'function') s.unbind(handler);
		};
	}, [settingId]);

	const set = useCallback(
		(newVal) => {
			const s = window.wp?.customize?.(settingId);
			if (s && typeof s.set === 'function') s.set(newVal);
		},
		[settingId]
	);

	return [value, set];
}

/**
 * Mirror the active palette onto :root as the Style-Pack-aligned token set.
 * Six user-picked slots (hex + rgb triplet) plus eight auto-computed
 * companions: on-primary / on-secondary / on-surface (luminance pick),
 * text-muted / text-subtle / border-default (text @ alpha), primary-hover /
 * primary-subtle (color-mix). Rewrites every render that the active palette
 * shape changes.
 */
function useColorVars(palette, slots) {
	useEffect(() => {
		if (!palette || !palette.colors) return;
		const root = document.documentElement;

		slots.forEach((slot) => {
			const hex = palette.colors[slot];
			if (!hex) return;
			root.style.setProperty(`--customify-color-${slot}`, hex);
			const rgb = hexToRgb(hex);
			if (rgb) root.style.setProperty(`--customify-color-${slot}-rgb`, rgb);
		});

		['primary', 'secondary', 'surface'].forEach((s) => {
			if (palette.colors[s]) {
				root.style.setProperty(`--customify-color-on-${s}`, pickOn(palette.colors[s]));
			}
		});

		const textRgb = hexToRgb(palette.colors.text);
		if (textRgb) {
			root.style.setProperty('--customify-color-text-muted', `rgba(${textRgb}, 0.55)`);
			root.style.setProperty('--customify-color-text-subtle', `rgba(${textRgb}, 0.35)`);
			root.style.setProperty('--customify-color-border-default', `rgba(${textRgb}, 0.12)`);
		}

		if (palette.colors.primary) {
			root.style.setProperty(
				'--customify-color-primary-hover',
				`color-mix(in srgb, ${palette.colors.primary}, #000 15%)`
			);
			if (palette.colors.base) {
				root.style.setProperty(
					'--customify-color-primary-subtle',
					`color-mix(in srgb, ${palette.colors.primary}, ${palette.colors.base} 92%)`
				);
			}
		}
	}, [palette, slots]);
}

// ─────────────────────────────────────────────────────────────── icons

const Icon = ({ d, size = 14, sw = 1.5 }) => (
	<svg width={size} height={size} viewBox="0 0 14 14" fill="none" stroke="currentColor" strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round"><path d={d} /></svg>
);
const IconExport = () => <Icon d="M7 12V5M4 8l3-3 3 3M2.5 2.5h9" />;
const IconImport = () => <Icon d="M7 2v7M4 6l3 3 3-3M2.5 11.5h9" />;
const IconAdd = () => <Icon d="M7 3v8M3 7h8" sw={1.6} />;
const IconReset = () => <Icon d="M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10" />;
const IconClose = ({ size = 10 }) => (
	<svg width={size} height={size} viewBox="0 0 14 14" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M3 3l8 8M11 3l-8 8" /></svg>
);
const IconGlobe = () => (
	<svg className="globe" viewBox="0 0 12 12" fill="none" stroke="currentColor" strokeWidth="1.2"><circle cx="6" cy="6" r="4.5" /><path d="M1.5 6h9M6 1.5c1.5 1.5 2.2 3 2.2 4.5S7.5 9 6 10.5M6 1.5C4.5 3 3.8 4.5 3.8 6S4.5 9 6 10.5" /></svg>
);

// ─────────────────────────────────────────────────────────── HeroDeck

const ANGLES = [-20, -12, -4, 4, 12, 20];
const OFFSETS = [-60, -36, -12, 12, 36, 60];

function HeroDeck({ palette, slots, onSlotClick }) {
	const colors = palette.colors;
	return (
		<div className="deck-wrap">
			{slots.map((slot, i) => {
				const c = colors[slot];
				if (!c) return null;
				const tx = OFFSETS[i] + 'px';
				const r = ANGLES[i] + 'deg';
				const style = {
					'--tx': tx,
					'--r': r,
					'--base-transform': `translateX(${tx}) rotate(${r})`,
					transform: `translateX(${tx}) rotate(${r})`,
					zIndex: i + 1,
					background: c,
					animationDelay: `${i * 40}ms`,
				};
				return (
					<div
						key={slot}
						className={'deck-card animating' + (!isLight(c) ? ' is-dark' : '')}
						style={style}
						title={`${slot} · ${c.toUpperCase()}`}
						onClick={(e) => { e.stopPropagation(); onSlotClick(slot); }}
					>
						<span className="slot-chip">{slot}</span>
					</div>
				);
			})}
		</div>
	);
}

// ─────────────────────────────────────────────────────────── Popover

function Popover({ slot, palette, kind, slotDesc, onChange, onClose }) {
	const hex = palette.colors[slot] || '#000000';
	return (
		<div className="popover open">
			<div className="popover-head">
				<span>{slot}</span>
				<button className="modal-close" type="button" style={{ width: 20, height: 20 }} onClick={onClose}>
					<IconClose />
				</button>
			</div>
			<label className="popover-preview" style={{ background: hex }}>
				<input type="color" value={hex} onChange={(e) => onChange(e.target.value.toUpperCase())} />
			</label>
			<div className="popover-hex">{hex.toUpperCase()}</div>
			<div className="popover-desc" dangerouslySetInnerHTML={{ __html: slotDesc[slot] || '' }} />
			{kind === 'theme' && (
				<div className="popover-copy-hint">{__('Editing creates a copy in Custom palettes', 'customify')}</div>
			)}
		</div>
	);
}

// ─────────────────────────────────────────────────────────── PresetCard / Grid

function PresetCard({ palette, kind, isActive, slots, onPick, onDelete, onRename }) {
	const c = palette.colors;
	const isUser = kind === 'user';
	const [editing, setEditing] = useState(false);
	const [draft, setDraft] = useState(palette.name);
	const inputRef = useRef(null);

	useEffect(() => {
		if (editing && inputRef.current) inputRef.current.select();
	}, [editing]);

	// Reset draft if the upstream name changes (e.g. import overwrites).
	useEffect(() => {
		if (!editing) setDraft(palette.name);
	}, [palette.name, editing]);

	const startEdit = (e) => {
		if (!isUser || !onRename) return;
		e.stopPropagation();
		setDraft(palette.name);
		setEditing(true);
	};

	const commit = () => {
		setEditing(false);
		const trimmed = draft.trim();
		if (trimmed && trimmed !== palette.name && onRename) onRename(palette.id, trimmed);
	};

	const cancel = () => {
		setDraft(palette.name);
		setEditing(false);
	};

	const className = 'preset-card' + (isActive ? ' active' : '') + (isUser ? ' is-user' : '');
	return (
		<div className={className} onClick={(e) => {
			if (e.target.closest('.del')) return;
			if (e.target.closest('.preset-name-edit')) return;
			onPick(palette.id);
		}}>
			<p className="preset-name">
				{editing ? (
					<input
						ref={inputRef}
						className="preset-name-edit"
						type="text"
						value={draft}
						maxLength={60}
						onChange={(e) => setDraft(e.target.value)}
						onBlur={commit}
						onKeyDown={(e) => {
							if (e.key === 'Enter') { e.preventDefault(); e.target.blur(); }
							else if (e.key === 'Escape') { e.preventDefault(); cancel(); }
						}}
						onClick={(e) => e.stopPropagation()}
					/>
				) : (
					<span
						className={isUser && onRename ? 'preset-name-text editable' : 'preset-name-text'}
						title={isUser && onRename ? __('Click to rename', 'customify') : undefined}
						onClick={startEdit}
					>
						{palette.name}
					</span>
				)}
				<span className="slot-count">{slots.length}</span>
			</p>
			<div className="palette-strip">
				{slots.map((s) => (
					<div key={s} className="chip" style={{ background: c[s] }} title={s} />
				))}
			</div>
			{isUser && (
				<button
					className="del"
					type="button"
					aria-label={__('Delete palette', 'customify')}
					title={__('Delete', 'customify')}
					onClick={(e) => { e.stopPropagation(); onDelete(palette.id); }}
				>
					<IconClose />
				</button>
			)}
		</div>
	);
}

function PresetGrid({ palettes, kind, activeId, slots, onPick, onDelete, onRename }) {
	return (
		<div className="preset-grid">
			{palettes.map((p) => (
				<PresetCard
					key={p.id}
					palette={p}
					kind={kind}
					isActive={p.id === activeId}
					slots={slots}
					onPick={onPick}
					onDelete={onDelete}
					onRename={onRename}
				/>
			))}
		</div>
	);
}

// ─────────────────────────────────────────────────────────── AddForm

function AddForm({ allPalettes, themePresets, defaultExtendId, userCount, onConfirm, onCancel }) {
	const [name, setName] = useState('');
	const [extendFrom, setExtendFrom] = useState(defaultExtendId);
	const inputRef = useRef(null);
	useEffect(() => {
		setTimeout(() => inputRef.current?.focus(), 100);
	}, []);
	const submit = () => {
		// translators: %d is the next sequential number for the palette
		const finalName = name.trim() || sprintf(__('Palette %d', 'customify'), userCount + 1);
		onConfirm(finalName, extendFrom);
	};
	return (
		<div className="add-form open">
			<div className="form-title">{__('Create new palette', 'customify')}</div>
			<div className="form-field">
				<label>{__('Palette title', 'customify')}</label>
				<input
					ref={inputRef}
					type="text"
					value={name}
					placeholder={__('e.g. My brand', 'customify')}
					autoComplete="off"
					onChange={(e) => setName(e.target.value)}
					onKeyDown={(e) => { if (e.key === 'Enter') submit(); }}
				/>
			</div>
			<div className="form-field">
				<label>{__('Extend from', 'customify')}</label>
				<select value={extendFrom} onChange={(e) => setExtendFrom(e.target.value)}>
					{allPalettes.map((p) => {
						const isTheme = themePresets.some((t) => t.id === p.id);
						const tag = isTheme ? __('theme', 'customify') : __('user', 'customify');
						return <option key={p.id} value={p.id}>{`${p.name} (${tag})`}</option>;
					})}
				</select>
			</div>
			<div className="form-actions">
				<button className="btn-cancel" type="button" onClick={onCancel}>{__('Cancel', 'customify')}</button>
				<button className="btn-add" type="button" onClick={submit}>
					<IconAdd /> {__('Add', 'customify')}
				</button>
			</div>
		</div>
	);
}

// ─────────────────────────────────────────────────────────── ImportForm

const EXAMPLE_JSON = JSON.stringify(
	[
		{ name: 'Sunset', colors: { base: '#FFF4E6', text: '#2B1810', primary: '#E85D04', secondary: '#3D1F0A', accent: '#FFD56B', surface: '#FFFFFF' } },
		{ name: 'Frost', colors: { base: '#F0F7FA', text: '#0A2540', primary: '#0284C7', secondary: '#0F3E5C', accent: '#BAE6FD', surface: '#FFFFFF' } },
	],
	null,
	2
);

function ImportForm({ slots, onConfirm, onCancel }) {
	const [text, setText] = useState('');
	const [error, setError] = useState('');
	const inputRef = useRef(null);
	useEffect(() => {
		setTimeout(() => inputRef.current?.focus(), 100);
	}, []);
	const submit = () => {
		const raw = text.trim();
		if (!raw) { setError(__('Paste JSON code first.', 'customify')); return; }
		let parsed;
		try { parsed = JSON.parse(raw); } catch (err) {
			setError(sprintf(__('Invalid JSON: %s', 'customify'), err.message));
			return;
		}
		const items = Array.isArray(parsed) ? parsed : [parsed];
		const validated = [];
		for (let i = 0; i < items.length; i++) {
			const it = items[i];
			if (!it || typeof it !== 'object') {
				return setError(sprintf(__('Item %d: must be an object.', 'customify'), i + 1));
			}
			if (!it.name || typeof it.name !== 'string') {
				return setError(sprintf(__('Item %d: missing "name" field.', 'customify'), i + 1));
			}
			if (!it.colors || typeof it.colors !== 'object') {
				return setError(sprintf(__('Item %d: missing "colors" object.', 'customify'), i + 1));
			}
			const colors = {};
			for (const slot of slots) {
				const v = it.colors[slot];
				if (!v || typeof v !== 'string' || !/^#([0-9A-F]{3}|[0-9A-F]{6})$/i.test(v.trim())) {
					return setError(sprintf(
						/* translators: 1: item index, 2: palette name, 3: slot key */
						__('Item %1$d ("%2$s"): slot "%3$s" missing or not a valid hex.', 'customify'),
						i + 1, it.name, slot
					));
				}
				colors[slot] = v.trim().toUpperCase();
			}
			validated.push({ name: it.name.trim(), colors });
		}
		onConfirm(validated);
	};
	return (
		<div className="add-form open">
			<div className="form-title">
				{__('Import palette(s) from JSON', 'customify')}
				<button className="paste-example" type="button" onClick={() => { setText(EXAMPLE_JSON); setError(''); }}>{__('Use example', 'customify')}</button>
			</div>
			<div className="form-field">
				<label>{__('Paste JSON', 'customify')}</label>
				<textarea
					ref={inputRef}
					value={text}
					spellCheck={false}
					onChange={(e) => setText(e.target.value)}
				/>
				<p className="form-hint">{__('Accepts a single palette object or an array. Requires "name" + all 6 slots.', 'customify')}</p>
				{error && <div className="form-error show">{error}</div>}
			</div>
			<div className="form-actions">
				<button className="btn-cancel" type="button" onClick={onCancel}>{__('Cancel', 'customify')}</button>
				<button className="btn-add" type="button" onClick={submit}>
					<IconImport /> {__('Import', 'customify')}
				</button>
			</div>
		</div>
	);
}

// ─────────────────────────────────────────────────────────── ExportForm

function ExportForm({ palettes, slots, onClose }) {
	const [selected, setSelected] = useState(() => new Set(palettes.map((p) => p.id)));
	const [copied, setCopied] = useState(false);

	const json = useMemo(() => {
		const chosen = palettes.filter((p) => selected.has(p.id));
		if (!chosen.length) return null;
		const payload = chosen.map((p) => ({ name: p.name, colors: { ...p.colors } }));
		return JSON.stringify(payload.length === 1 ? payload[0] : payload, null, 2);
	}, [palettes, selected]);

	const toggle = (id) => {
		setSelected((prev) => {
			const next = new Set(prev);
			if (next.has(id)) next.delete(id); else next.add(id);
			return next;
		});
	};
	const toggleAll = (checked) => {
		setSelected(checked ? new Set(palettes.map((p) => p.id)) : new Set());
	};

	const doCopy = () => {
		if (!json) return;
		const fallback = () => {
			const ta = document.createElement('textarea');
			ta.value = json; ta.style.position = 'fixed'; ta.style.opacity = '0';
			document.body.appendChild(ta); ta.select();
			try { document.execCommand('copy'); } catch (e) {}
			document.body.removeChild(ta);
		};
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(json).then(() => null).catch(fallback);
		} else { fallback(); }
		setCopied(true);
		setTimeout(() => setCopied(false), 1600);
	};

	const doDownload = () => {
		if (!json) return;
		const chosen = palettes.filter((p) => selected.has(p.id));
		const filename = chosen.length === 1
			? `customify-palette-${chosen[0].name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.json`
			: `customify-palettes-${chosen.length}.json`;
		const blob = new Blob([json], { type: 'application/json' });
		const url = URL.createObjectURL(blob);
		const a = document.createElement('a');
		a.href = url; a.download = filename;
		document.body.appendChild(a); a.click(); document.body.removeChild(a);
		setTimeout(() => URL.revokeObjectURL(url), 1000);
	};

	if (palettes.length === 0) {
		return (
			<div className="add-form open">
				<div className="form-title">{__('Export custom palettes', 'customify')}</div>
				<div className="form-field">
					<label>{__('Select palettes', 'customify')}</label>
					<div className="export-empty">{__('No custom palettes to export yet. Create or import one first.', 'customify')}</div>
				</div>
				<div className="form-actions">
					<button className="btn-cancel" type="button" onClick={onClose}>{__('Close', 'customify')}</button>
				</div>
			</div>
		);
	}

	const allChecked = selected.size === palettes.length;
	const someChecked = selected.size > 0 && selected.size < palettes.length;

	return (
		<div className="add-form open">
			<div className="form-title">{__('Export custom palettes', 'customify')}</div>
			<div className="form-field">
				<label>{__('Select palettes', 'customify')}</label>
				<div className="export-list">
					<div className="select-all-row">
						<input
							type="checkbox"
							checked={allChecked}
							ref={(el) => { if (el) el.indeterminate = someChecked; }}
							onChange={(e) => toggleAll(e.target.checked)}
						/>
						<span>{__('All custom palettes', 'customify')}</span>
					</div>
					<div>
						{palettes.map((p) => (
							<label key={p.id} className="export-item">
								<input type="checkbox" checked={selected.has(p.id)} onChange={() => toggle(p.id)} />
								<span className="ex-name">{p.name}</span>
								<span className="mini-strip">
									{slots.map((s) => <span key={s} style={{ background: p.colors[s] }} />)}
								</span>
							</label>
						))}
					</div>
				</div>
			</div>
			<div className="form-field">
				<label>{__('JSON output', 'customify')}</label>
				<div className="export-output">{json || __('//  No palettes selected', 'customify')}</div>
				<p className="form-hint">{__('Re-importable via the ↓ button above or on another site.', 'customify')}</p>
			</div>
			<div className="form-actions">
				<span className={'copied-flash' + (copied ? ' show' : '')}>{__('✓ Copied', 'customify')}</span>
				<button className="btn-cancel" type="button" onClick={onClose}>{__('Close', 'customify')}</button>
				<button className="btn-ghost" type="button" disabled={!json} onClick={doDownload}>
					<IconExport /> {__('Download', 'customify')}
				</button>
				<button className="btn-add" type="button" disabled={!json} onClick={doCopy}>{__('Copy', 'customify')}</button>
			</div>
		</div>
	);
}

// ─────────────────────────────────────────────────────────── SettingsRows

function SettingsRows({ palette, settings, defaults, onSlotChange }) {
	// Per-row reset reverts each slot in the row to its `defaults` value
	// (typically the first theme preset's colour for that slot — the
	// canonical default supplied by App).
	const resetRow = (slotsToReset) => {
		slotsToReset.forEach((slot) => {
			const defaultHex = defaults[slot];
			if (!defaultHex) return;
			if (palette.colors[slot] === defaultHex) return;
			onSlotChange(slot, defaultHex);
		});
	};
	return (
		<div>
			{settings.map((cfg, i) => {
				const slotsArr = cfg.slots || [];
				const isAtDefault = slotsArr.every((s) => palette.colors[s] === defaults[s]);
				return (
					<div key={i} className="setting-row">
						<div className="label-wrap">
							<span className="label">{cfg.label}</span>
							<span className="sublabel">{cfg.sublabel}</span>
						</div>
						<div className="right">
							<button
								className="reset-mini"
								type="button"
								onClick={() => resetRow(slotsArr)}
								title={__('Reset to default', 'customify')}
								aria-label={__('Reset to default', 'customify')}
								disabled={isAtDefault}
							>
								<Icon d="M2 5.5V2m0 3.5h3.5M2 5.5A5 5 0 1 1 3 10" size={12} />
							</button>
							{slotsArr.map((slotName) => {
								const col = palette.colors[slotName];
								if (!col) return null;
								return (
									// Click anywhere on the dot opens the browser's
									// native colour picker (the input is layered on
									// top, fully transparent). The label keeps the
									// existing dot visual + globe icon.
									<label
										key={slotName}
										className={'color-dot' + (isLight(col) ? ' light' : '')}
										style={{ background: col }}
										title={`${slotName} · ${col.toUpperCase()}`}
									>
										<input
											type="color"
											value={col}
											onInput={(e) => onSlotChange(slotName, e.target.value.toUpperCase())}
											onClick={(e) => e.stopPropagation()}
										/>
										<IconGlobe />
									</label>
								);
							})}
						</div>
					</div>
				);
			})}
		</div>
	);
}

// ─────────────────────────────────────────────────────────── AutoComputed

// Labels are wrapped in __() so the strings get extracted for translation
// even though the section is currently hidden in App() — re-enabling the
// render is just an uncomment.
const AUTO_COMPUTED_ROWS = [
	{ slot: 'on-primary',     label: __('Text on primary', 'customify') },
	{ slot: 'on-secondary',   label: __('Text on secondary', 'customify') },
	{ slot: 'on-surface',     label: __('Text on surface', 'customify') },
	{ slot: 'text-muted',     label: __('Muted text', 'customify') },
	{ slot: 'text-subtle',    label: __('Subtle text', 'customify') },
	{ slot: 'border-default', label: __('Default border', 'customify') },
	{ slot: 'primary-hover',  label: __('Primary hover', 'customify') },
	{ slot: 'primary-subtle', label: __('Primary subtle', 'customify') },
];

function AutoComputed({ trigger }) {
	// Read computed values from :root each time `trigger` (the active palette
	// signature) changes — useColorVars has already updated those vars in a
	// sibling effect.
	const rows = useMemo(() => {
		const rs = getComputedStyle(document.documentElement);
		return AUTO_COMPUTED_ROWS.map((row) => ({
			...row,
			value: rs.getPropertyValue(`--customify-color-${row.slot}`).trim() || '—',
		}));
	}, [trigger]);
	return (
		<Fragment>
			<div className="auto-computed-hint">{__('Theme generates these from the 6 slots above.', 'customify')}</div>
			<div>
				{rows.map((r) => (
					<div key={r.slot} className="auto-computed-row" title={`${r.slot} · ${r.value}`}>
						<span className="auto-computed-swatch" style={{ background: r.value }} />
						<span className="auto-computed-label">{r.label}</span>
						<span className="auto-computed-slot">{r.slot}</span>
					</div>
				))}
			</div>
		</Fragment>
	);
}

// ─────────────────────────────────────────────────────────── App

function App({ cfg }) {
	const [userPalettes, setUserPalettes] = useCustomizeSetting(cfg.settingIds.palettes, []);
	const [activeId, setActiveId] = useCustomizeSetting(cfg.settingIds.active, '');
	const [editSlot, setEditSlot] = useState(null);
	const [openForm, setOpenForm] = useState(null); // 'add' | 'import' | 'export' | null

	const slots = cfg.slots;
	const themePresets = cfg.themePresets || [];
	const userArr = Array.isArray(userPalettes) ? userPalettes : [];

	const allPalettes = useMemo(() => [...themePresets, ...userArr], [themePresets, userArr]);

	const activePalette = useMemo(() => {
		return allPalettes.find((p) => p.id === activeId) || allPalettes[0] || null;
	}, [allPalettes, activeId]);

	const activeKind = useMemo(
		() => (themePresets.some((p) => p.id === activePalette?.id) ? 'theme' : 'user'),
		[themePresets, activePalette]
	);

	useColorVars(activePalette, slots);

	useEffect(() => {
		if (activePalette) {
			console.log('[Customify Preview Colors] Current palette:', {
				name: activePalette.name,
				colors: { ...activePalette.colors },
			});
		}
	}, [activePalette]);

	// Esc closes popover / open form.
	useEffect(() => {
		const handler = (e) => {
			if (e.key === 'Escape') {
				setEditSlot(null);
				setOpenForm(null);
			}
		};
		document.addEventListener('keydown', handler);
		return () => document.removeEventListener('keydown', handler);
	}, []);

	// Click outside popover closes it.
	useEffect(() => {
		if (!editSlot) return undefined;
		const handler = (e) => {
			const path = e.composedPath ? e.composedPath() : [];
			for (const n of path) {
				if (!n || !n.classList) continue;
				if (n.classList.contains('popover')) return;
				if (n.classList.contains('deck-card')) return;
				if (n.classList.contains('color-dot')) return;
			}
			setEditSlot(null);
		};
		document.addEventListener('click', handler);
		return () => document.removeEventListener('click', handler);
	}, [editSlot]);

	const editSlotColor = useCallback(
		(slot, newHex) => {
			if (!activePalette) return;
			if (activeKind === 'theme') {
				const id = 'user_' + Date.now();
				const copy = {
					id,
					name: activePalette.name + ' (copy)',
					colors: { ...activePalette.colors, [slot]: newHex },
				};
				setUserPalettes([...userArr, copy]);
				setActiveId(id);
			} else {
				const updated = userArr.map((p) =>
					p.id === activePalette.id
						? { ...p, colors: { ...p.colors, [slot]: newHex } }
						: p
				);
				setUserPalettes(updated);
			}
		},
		[activePalette, activeKind, userArr, setUserPalettes, setActiveId]
	);

	const addPalette = useCallback(
		(name, sourceId) => {
			const source = allPalettes.find((p) => p.id === sourceId);
			if (!source) return;
			const id = 'user_' + Date.now();
			setUserPalettes([
				...userArr,
				{ id, name, colors: { ...source.colors } },
			]);
			setActiveId(id);
			setOpenForm(null);
		},
		[allPalettes, userArr, setUserPalettes, setActiveId]
	);

	const deletePalette = useCallback(
		(id) => {
			setUserPalettes(userArr.filter((p) => p.id !== id));
			if (activeId === id) {
				setActiveId(themePresets[0]?.id || '');
			}
		},
		[userArr, setUserPalettes, activeId, setActiveId, themePresets]
	);

	const renamePalette = useCallback(
		(id, newName) => {
			setUserPalettes(userArr.map((p) => (p.id === id ? { ...p, name: newName } : p)));
		},
		[userArr, setUserPalettes]
	);

	const importPalettes = useCallback(
		(items) => {
			const validated = items.map((item, i) => ({
				id: 'user_' + Date.now() + '_' + i,
				name: item.name,
				colors: { ...item.colors },
			}));
			setUserPalettes([...userArr, ...validated]);
			setActiveId(validated[0].id);
			setOpenForm(null);
		},
		[userArr, setUserPalettes, setActiveId]
	);

	if (!activePalette) return null;

	const triggerKey = activePalette.id + '|' + slots.map((s) => activePalette.colors[s]).join(',');

	return (
		<div className="sidebar">
			<div className="section">
				<div className="sec-row"><h3>{__('Current palette', 'customify')}</h3></div>
				<div className="hero-card">
					<HeroDeck
						key={activePalette.id /* re-mount to retrigger animation on switch */}
						palette={activePalette}
						slots={slots}
						onSlotClick={setEditSlot}
					/>
					<div className="deck-footer">
						<span className="deck-name">
							<span>{activePalette.name}</span>
							<span className={'tag-theme' + (activeKind === 'user' ? ' tag-user' : '')}>
								{activeKind === 'theme' ? __('theme', 'customify') : __('user', 'customify')}
							</span>
						</span>
						<span className="deck-sub">
							{sprintf(
								/* translators: %d is the number of color slots in a palette */
								__('%d slots', 'customify'),
								slots.length
							)}
						</span>
					</div>
					<div className="deck-hint">{__('Click a card to edit that slot', 'customify')}</div>
					{editSlot && (
						<Popover
							slot={editSlot}
							palette={activePalette}
							kind={activeKind}
							slotDesc={cfg.slotDesc || {}}
							onChange={(hex) => editSlotColor(editSlot, hex)}
							onClose={() => setEditSlot(null)}
						/>
					)}
				</div>
			</div>

			<div className="section">
				<div className="sec-row"><h3>{__('Theme presets', 'customify')}</h3></div>
				<PresetGrid palettes={themePresets} kind="theme" activeId={activeId} slots={slots} onPick={setActiveId} />
			</div>

			<div className="section">
				<div className="sec-row">
					<h3>{__('Custom palettes', 'customify')} <span className="badge-count">{userArr.length}</span></h3>
					<div style={{ display: 'flex', gap: 4 }}>
						<button className="icon-btn" type="button" aria-label={__('Export palettes', 'customify')} title={__('Export JSON', 'customify')} onClick={() => setOpenForm(openForm === 'export' ? null : 'export')}><IconExport /></button>
						<button className="icon-btn" type="button" aria-label={__('Import palette', 'customify')} title={__('Import JSON', 'customify')} onClick={() => setOpenForm(openForm === 'import' ? null : 'import')}><IconImport /></button>
						<button className="icon-btn" type="button" aria-label={__('Add palette', 'customify')} title={__('Add new palette', 'customify')} onClick={() => setOpenForm(openForm === 'add' ? null : 'add')}><IconAdd /></button>
					</div>
				</div>
				{userArr.length === 0 ? (
					<div className="empty-user">
						{__('No custom palettes yet.', 'customify')}<br />
						{__('Click + to create one, or ↓ to import JSON.', 'customify')}
					</div>
				) : (
					<PresetGrid
						palettes={userArr}
						kind="user"
						activeId={activeId}
						slots={slots}
						onPick={setActiveId}
						onDelete={deletePalette}
						onRename={renamePalette}
					/>
				)}
				{openForm === 'add' && (
					<AddForm
						allPalettes={allPalettes}
						themePresets={themePresets}
						defaultExtendId={activeId}
						userCount={userArr.length}
						onConfirm={addPalette}
						onCancel={() => setOpenForm(null)}
					/>
				)}
				{openForm === 'import' && (
					<ImportForm slots={slots} onConfirm={importPalettes} onCancel={() => setOpenForm(null)} />
				)}
				{openForm === 'export' && (
					<ExportForm palettes={userArr} slots={slots} onClose={() => setOpenForm(null)} />
				)}
			</div>

			<div className="section">
				<div className="group-title"><h4>{__('Theme color', 'customify')}</h4></div>
				<SettingsRows
					palette={activePalette}
					settings={cfg.settingsRows || []}
					defaults={themePresets[0]?.colors || {}}
					onSlotChange={editSlotColor}
				/>
			</div>

			{/*
			Auto-computed section hidden — companion vars (on-*, text-muted,
			primary-hover, …) are still emitted on :root by useColorVars; this
			block only reflected them for debugging. Re-enable by uncommenting.

			<div className="section">
				<div className="group-title"><h4>{__('Auto-computed', 'customify')}</h4></div>
				<AutoComputed trigger={triggerKey} />
			</div>
			*/}
		</div>
	);
}

// ─────────────────────────────────────────────────────────── Mount

(function () {
	const cfg = window.CustomifyPreviewColors;
	if (!cfg) return;

	let mounted = false;
	function tryMount() {
		if (mounted) return true;
		const host = document.getElementById(cfg.rootId);
		if (!host) return false;
		mounted = true;
		const root = createRoot(host);
		root.render(<App cfg={cfg} />);
		return true;
	}

	if (!tryMount() && typeof MutationObserver === 'function') {
		const observer = new MutationObserver(() => {
			if (tryMount()) observer.disconnect();
		});
		observer.observe(document.body || document.documentElement, {
			childList: true,
			subtree: true,
		});
	}
})();
