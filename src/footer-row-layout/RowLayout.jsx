import { useState, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { __experimentalUnitControl as UnitControl } from '@wordpress/block-editor';
import { PRESETS, DEFAULT_VALUE } from './presets';

const DEVICES = [
	{ id: 'desktop', icon: 'dashicons-desktop' },
	{ id: 'tablet',  icon: 'dashicons-tablet' },
	{ id: 'mobile',  icon: 'dashicons-smartphone' },
];

const PX_UNITS = [ { value: 'px', label: 'px', default: 0 } ];

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

function LayoutSvg( { fr, stacked, count } ) {
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

	const total  = fr.reduce( ( a, b ) => a + b, 0 );
	const totalG = GAP * ( fr.length - 1 );
	let x        = 0;

	const rects = fr.map( ( f, i ) => {
		const w    = ( f / total ) * ( W - totalG );
		const rect = <rect key={ i } x={ x } y={ 2 } width={ Math.max( w, 1 ) } height={ H - 4 } rx={ 2 } />;
		x += w + GAP;
		return rect;
	} );

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
	const [ device, setDevice ] = useState( 'desktop' );
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

	// count is global; fr, gap, padding are per-device.
	const count      = value.count || 1;
	const deviceData = value[ device ] || { fr: Array( count ).fill( 1 ), gap: 0, padding: 0 };
	const fr         = deviceData.fr || Array( count ).fill( 1 );
	const gap        = deviceData.gap ?? 0;
	const padding    = deviceData.padding ?? 0;
	const presets    = PRESETS[ count ] || [ { fr: Array( count ).fill( 1 ) } ];

	const commit = ( newValue ) => {
		setValue( newValue );
		isCommitting.current = true;
		window.wp?.customize?.( settingKey )?.set?.( JSON.stringify( newValue ) );
		isCommitting.current = false;
	};

	const handleCountChange = ( n ) => {
		const firstPreset = ( PRESETS[ n ] || [ { fr: Array( n ).fill( 1 ) } ] )[ 0 ];
		const newFr       = firstPreset.fr || Array( n ).fill( 1 );
		commit( { ...value, count: n, [ device ]: { ...deviceData, fr: newFr } } );
	};

	const handlePreset = ( preset ) => {
		const newFr = preset.stacked ? [ 1 ] : preset.fr;
		commit( { ...value, [ device ]: { ...deviceData, fr: newFr } } );
	};

	const handleGapChange = ( val ) => {
		commit( { ...value, [ device ]: { ...deviceData, gap: Math.max( 0, parseFloat( val ) || 0 ) } } );
	};

	const handlePaddingChange = ( val ) => {
		commit( { ...value, [ device ]: { ...deviceData, padding: Math.max( 0, parseFloat( val ) || 0 ) } } );
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
					<DeviceSwitcher device={ device } onChange={ setDevice } />
				</div>
				<div className="cb-row-layout__preset-grid">
					{ presets.map( ( preset, idx ) => {
						const isStacked = !! preset.stacked;
						const active    = isStacked
							? fr.length === 1
							: JSON.stringify( fr ) === JSON.stringify( preset.fr );
						return (
							<button
								key={ idx }
								type="button"
								className={ `cb-row-layout__preset-btn${ active ? ' is-active' : '' }` }
								title={ isStacked ? 'stacked' : preset.fr.join( ':' ) }
								onClick={ () => handlePreset( preset ) }
							>
								<LayoutSvg fr={ preset.fr || [ 1 ] } stacked={ isStacked } count={ count } />
							</button>
						);
					} ) }
				</div>
			</div>

			{ /* Gap — per-device */ }
			<div className="cb-row-layout__field">
				<div className="cb-row-layout__field-header">
					<span className="cb-row-layout__label">{ __( 'Gap', 'customify' ) }</span>
					<DeviceSwitcher device={ device } onChange={ setDevice } />
				</div>
				<UnitControl
					value={ gap ? `${ gap }px` : '' }
					onChange={ handleGapChange }
					units={ PX_UNITS }
					min={ 0 }
				/>
			</div>

			{ /* Padding — per-device */ }
			<div className="cb-row-layout__field">
				<div className="cb-row-layout__field-header">
					<span className="cb-row-layout__label">{ __( 'Padding', 'customify' ) }</span>
					<DeviceSwitcher device={ device } onChange={ setDevice } />
				</div>
				<UnitControl
					value={ padding ? `${ padding }px` : '' }
					onChange={ handlePaddingChange }
					units={ PX_UNITS }
					min={ 0 }
				/>
			</div>

		</div>
	);
}
