import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PRESETS, DEFAULT_VALUE } from './presets';

const DEVICES = [
	{ id: 'desktop', icon: 'dashicons-desktop' },
	{ id: 'tablet',  icon: 'dashicons-tablet' },
	{ id: 'mobile',  icon: 'dashicons-smartphone' },
];

function DeviceSwitcher( { device, onChange } ) {
	return (
		<div className="cb-row-layout__devices">
			{ DEVICES.map( ( d ) => (
				<button
					key={ d.id }
					type="button"
					className={ `cb-row-layout__device-btn${ device === d.id ? ' is-active' : '' }` }
					title={ d.id }
					onClick={ () => onChange( d.id ) }
				>
					<span className={ `dashicons ${ d.icon }` } />
				</button>
			) ) }
		</div>
	);
}

function LayoutSvg( { fr, stacked, count, rows } ) {
	const W   = 48;
	const H   = 30;
	const GAP = 2;

	if ( stacked ) {
		const bars      = count || 3;
		const totalGaps = GAP * ( bars - 1 );
		const barH      = ( H - 4 - totalGaps ) / bars;
		const rects     = Array.from( { length: bars }, ( _, i ) => (
			<rect key={ i } x={ 0 } y={ 2 + i * ( barH + GAP ) } width={ W } height={ Math.max( barH, 1 ) } rx={ 2 } />
		) );
		return (
			<svg width={ W } height={ H } viewBox={ `0 0 ${ W } ${ H }` } fill="currentColor" xmlns="http://www.w3.org/2000/svg">
				{ rects }
			</svg>
		);
	}

	const total   = fr.reduce( ( a, b ) => a + b, 0 );
	const totalGX = GAP * ( fr.length - 1 );
	const nRows   = Math.max( 1, parseInt( rows, 10 ) || 1 );
	const totalGY = GAP * ( nRows - 1 );
	const rowH    = ( H - 4 - totalGY ) / nRows;

	const rects = [];
	for ( let r = 0; r < nRows; r++ ) {
		let x = 0;
		const y = 2 + r * ( rowH + GAP );
		fr.forEach( ( f, i ) => {
			const w = ( f / total ) * ( W - totalGX );
			rects.push(
				<rect key={ `${ r }-${ i }` } x={ x } y={ y } width={ Math.max( w, 1 ) } height={ Math.max( rowH, 1 ) } rx={ 2 } />
			);
			x += w + GAP;
		} );
	}

	return (
		<svg width={ W } height={ H } viewBox={ `0 0 ${ W } ${ H }` } fill="currentColor" xmlns="http://www.w3.org/2000/svg">
			{ rects }
		</svg>
	);
}

function parseValue( raw ) {
	if ( ! raw ) return { ...DEFAULT_VALUE };
	try {
		const parsed = typeof raw === 'string' ? JSON.parse( raw ) : raw;
		if ( ! parsed || typeof parsed !== 'object' ) return { ...DEFAULT_VALUE };

		// Migrate old format where count was stored per-device.
		// parseInt handles both number 3 and string "3" (sanitizer turns ints to strings).
		const count = parseInt( parsed.count ?? parsed.desktop?.count ?? DEFAULT_VALUE.count, 10 ) || DEFAULT_VALUE.count;

		const globalGap     = parsed.gap     ?? 0;
		const globalPadding = parsed.padding ?? 0;

		const parseDevice = ( d, def ) => ( {
			fr:      ( d?.fr || def.fr ).map( ( v ) => parseInt( v, 10 ) || 1 ),
			gap:     parseInt( d?.gap     ?? globalGap,     10 ) || 0,
			padding: parseInt( d?.padding ?? globalPadding, 10 ) || 0,
		} );

		return {
			count,
			desktop: parseDevice( parsed.desktop, DEFAULT_VALUE.desktop ),
			tablet:  parseDevice( parsed.tablet,  DEFAULT_VALUE.tablet  ),
			mobile:  parseDevice( parsed.mobile,  DEFAULT_VALUE.mobile  ),
		};
	} catch ( e ) {
		return { ...DEFAULT_VALUE };
	}
}

export default function RowLayout( { settingKey } ) {
	// Seed the device tab from the customizer's previewedDevice so opening
	// the control while the preview is in tablet/mobile mode reflects that.
	const [ device, setDevice ] = useState( () => {
		const d = window.wp?.customize?.previewedDevice?.get?.();
		return d === 'tablet' || d === 'mobile' ? d : 'desktop';
	} );
	const [ value, setValue ]   = useState( () => {
		const raw = window.wp?.customize?.( settingKey )?.get?.();
		return parseValue( raw );
	} );

	const isCommitting = useRef( false );

	useEffect( () => {
		const setting = window.wp?.customize?.( settingKey );
		if ( ! setting ) return;
		// Re-sync after mount in case the setting value loads asynchronously.
		const raw = setting.get?.();
		if ( raw ) setValue( parseValue( raw ) );
		// Sync on external changes (e.g. undo/redo), but not our own commits.
		const onChange = ( newRaw ) => {
			if ( ! isCommitting.current ) setValue( parseValue( newRaw ) );
		};
		setting.bind( onChange );
		return () => setting.unbind( onChange );
	}, [ settingKey ] );

	// Mirror the customizer's previewedDevice so clicking the footer-toolbar
	// device buttons (or another row-layout control) keeps this tab in sync.
	useEffect( () => {
		const preview = window.wp?.customize?.previewedDevice;
		if ( ! preview?.bind ) return;
		const onPreviewDevice = ( d ) => {
			if ( d === 'desktop' || d === 'tablet' || d === 'mobile' ) {
				setDevice( d );
			}
		};
		preview.bind( onPreviewDevice );
		return () => preview.unbind( onPreviewDevice );
	}, [] );

	// Clicking a row-layout device button drives the preview iframe to the
	// matching breakpoint so the user sees the layout they're editing.
	const handleDeviceChange = ( d ) => {
		setDevice( d );
		window.wp?.customize?.previewedDevice?.set?.( d );
	};

	// count is global; fr is per-device.
	const count      = value.count || 1;
	const deviceData = value[ device ] || { fr: Array( count ).fill( 1 ) };
	const fr         = deviceData.fr || Array( count ).fill( 1 );
	const presets    = PRESETS[ count ] || [ { fr: Array( count ).fill( 1 ) } ];

	const commit = ( newValue ) => {
		setValue( newValue );
		isCommitting.current = true;
		window.wp?.customize?.( settingKey )?.set?.( JSON.stringify( newValue ) );
		isCommitting.current = false;
	};

	// Truncate or pad an fr array to a target length, preserving leading
	// proportions. Used when column count changes so every device's fr
	// stays in sync with `count`. Without this, switching count on one
	// device tab leaves the other devices with a stale fr length, which
	// the renderer then emits as extra/empty grid tracks on those
	// breakpoints (the "col4 still renders after 4→3" bug).
	const resizeFr = ( oldFr, newCount ) => {
		const arr = Array.isArray( oldFr ) ? oldFr.slice( 0, newCount ) : [];
		while ( arr.length < newCount ) arr.push( 1 );
		return arr;
	};

	const handleCountChange = ( n ) => {
		const firstPreset = ( PRESETS[ n ] || [ { fr: Array( n ).fill( 1 ) } ] )[ 0 ];
		const newFr       = firstPreset.fr || Array( n ).fill( 1 );

		// Active device gets the preset; inactive devices preserve their
		// own proportions but get resized to the new count. Mobile is
		// special: when the active tab isn't mobile we leave mobile.fr
		// alone so it keeps falling back to stacked (1fr) until the user
		// picks a horizontal preset on the mobile tab themselves.
		const next = { ...value, count: n };
		[ 'desktop', 'tablet', 'mobile' ].forEach( ( dev ) => {
			const cur = value[ dev ] || { fr: [], gap: 0, padding: 0 };
			if ( dev === 'mobile' && dev !== device ) {
				next[ dev ] = cur;
				return;
			}
			next[ dev ] = {
				...cur,
				fr: dev === device ? newFr : resizeFr( cur.fr, n ),
			};
		} );

		commit( next );
	};

	const handlePreset = ( preset ) => {
		const newFr = preset.stacked ? [ 1 ] : preset.fr;
		commit( { ...value, [ device ]: { ...deviceData, fr: newFr } } );
	};

	return (
		<div className="cb-row-layout">

			{ /* Columns — global */ }
			<div className="cb-row-layout__count-section">
				<span className="cb-row-layout__label">
					{ __( 'Columns', 'customify' ) }
				</span>
				<div className="cb-row-layout__count-buttons">
					{ [ 1, 2, 3, 4, 5 ].map( ( n ) => (
						<button
							key={ n }
							type="button"
							className={ `cb-row-layout__count-btn${ count === n ? ' is-active' : '' }` }
							onClick={ () => handleCountChange( n ) }
						>
							{ n }
						</button>
					) ) }
				</div>
			</div>

			{ /* Layout — per-device fr presets */ }
			<div className="cb-row-layout__field">
				<div className="cb-row-layout__field-header">
					<span className="cb-row-layout__label">{ __( 'Layout', 'customify' ) }</span>
					<DeviceSwitcher device={ device } onChange={ handleDeviceChange } />
				</div>
				<div className="cb-row-layout__preset-grid">
					{ presets.map( ( preset, idx ) => {
						const isStacked = !! preset.stacked;
						const active    = isStacked
							? fr.length === 1
							: JSON.stringify( fr ) === JSON.stringify( preset.fr );
						const title     = isStacked
							? 'stacked'
							: preset.rows
								? `${ preset.rows }×${ preset.fr.length } (${ preset.fr.join( ':' ) })`
								: preset.fr.join( ':' );
						return (
							<button
								key={ idx }
								type="button"
								className={ `cb-row-layout__preset-btn${ active ? ' is-active' : '' }` }
								title={ title }
								onClick={ () => handlePreset( preset ) }
							>
								<LayoutSvg
									fr={ preset.fr || [ 1 ] }
									stacked={ isStacked }
									count={ count }
									rows={ preset.rows }
								/>
							</button>
						);
					} ) }
				</div>
			</div>

		</div>
	);
}
