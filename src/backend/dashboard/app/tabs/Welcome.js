/**
 * Welcome tab — Hero, Things-to-do checklist, Theme Customizer grid, Pro
 * Modules card, sidebar with License + Resources + Products + Review.
 *
 * Local state:
 *   - which checklist rows are checked
 *   - which guide popover is open (string key or null)
 *   - whether the Things-to-do card is hidden
 */

import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { info } from '@wordpress/icons';

import {
	Card,
	Hero,
	Button,
	Icon,
	ChecklistRow,
	ThemeGridCard,
	LicenseCard,
	ResourceList,
	ReviewCard,
	WelcomeLayout,
	Dropdown,
} from '../../ui';
import GuidePopover from '../components/GuidePopover';
import ProModulesCard from '../components/ProModulesCard';
import SitesImportCard from '../components/SitesImportCard';
import RecommendPluginsCard from '../components/RecommendPluginsCard';

import { THINGS_TO_DO } from '../data/things-to-do';
import { THEME_CUSTOMIZER_ITEMS } from '../data/theme-customizer';
import { LICENSE_DATA, RESOURCES, REVIEW } from '../data/sidebar';
import {
	CUSTOMIZER_URL,
	PRO_ACTIVE,
	THINGS_TO_DO_STATUS,
	THINGS_TO_DO_HIDDEN,
} from '../config';
import { setThingsToDoHidden } from '../api/preferences';

function HeroPreview() {
	return (
		<div className="pm-hero-preview">
			<div className="pm-hero-preview__bar pm-hero-preview__bar--lg" />
			<div className="pm-hero-preview__bar pm-hero-preview__bar--md" />
			<div className="pm-hero-preview__img" />
			<div className="pm-hero-preview__bar pm-hero-preview__bar--sm" />
			<div className="pm-hero-preview__row">
				<div className="pm-hero-preview__pill" />
				<div className="pm-hero-preview__pill pm-hero-preview__pill--outline" />
			</div>
		</div>
	);
}

export default function Welcome() {
	// Seed checklist state from the server-detected completion map so items
	// the user has already configured (logo, primary color, FA version, …)
	// show up pre-checked. Local toggles afterwards override the seed.
	const [ checked, setChecked ] = useState( () => {
		const seed = {};
		THINGS_TO_DO.forEach( ( item ) => {
			seed[ item.id ] = !! THINGS_TO_DO_STATUS[ item.id ];
		} );
		return seed;
	} );
	const [ openGuide, setOpenGuide ] = useState( null );
	const [ todoHidden, setTodoHidden ] = useState( THINGS_TO_DO_HIDDEN );

	const toggleCheck = ( id ) =>
		setChecked( ( prev ) => ( { ...prev, [ id ]: ! prev[ id ] } ) );

	// Optimistic hide — flip immediately, persist in background. Revert if
	// the AJAX call fails so the user can retry instead of getting stuck
	// with an unsaved preference.
	const onHideTodo = useCallback( () => {
		setTodoHidden( true );
		setThingsToDoHidden( true ).catch( () => setTodoHidden( false ) );
	}, [] );

	const main = (
		<>
			<Hero
				greeting={ __( 'Hello admin', 'customify' ) }
				title={ __( 'Welcome to Customify', 'customify' ) }
				description={ __(
					'Customify is a fast, multipurpose WordPress theme with a WYSIWYG header & footer builder right inside the Customizer. Set up your branding, layout, typography, and start shipping pages.',
					'customify'
				) }
				actions={
					<Button
						variant="primary"
						href={ CUSTOMIZER_URL || 'customize.php' }
						size="lg"
						className={ `pm-button--lg` }
					>
						<Icon name="plus" size={ 16 } />
						{ __( 'Open Customizer', 'customify' ) }
					</Button>
				}
				preview={ <HeroPreview /> }
			/>

			{ ! todoHidden && (
				<Card
					title={ __( 'Things to do next', 'customify' ) }
					headerRight={
						<Dropdown
							triggerLabel={ __( 'More options', 'customify' ) }
							items={ [
								{
									label: __( 'Hide To Do', 'customify' ),
									icon: 'eye-off',
									onClick: onHideTodo,
								},
							] }
						/>
					}
				>
					{ THINGS_TO_DO.map( ( item ) => (
						<ChecklistRow
							key={ item.id }
							checked={ !! checked[ item.id ] }
							onToggleCheck={ () => toggleCheck( item.id ) }
							title={ item.title }
							description={ item.description }
							actions={
								<>
									<Button
										variant="unstyled"
										className="pm-info-btn"
										icon={ info }
										iconSize={ 22 }
										label={ __(
											'Learn more',
											'customify'
										) }
										onClick={ () =>
											setOpenGuide( item.guide )
										}
									/>
									<Button
										variant="primary"
										href={ item.ctaHref }
									>
										{ item.ctaLabel }
										<Icon
											name="arrow-up-right"
											size={ 13 }
										/>
									</Button>
								</>
							}
						/>
					) ) }
				</Card>
			) }

			<Card
				title={ __( 'Theme Customizer', 'customify' ) }
				headerRight={
					<a className="pm-header-link" href="customize.php">
						{ __( 'Go to Customizer', 'customify' ) }
						<Icon name="chevron-right" size={ 12 } />
					</a>
				}
			>
				<div className="pm-theme-grid">
					{ THEME_CUSTOMIZER_ITEMS.map( ( item, i ) => (
						<ThemeGridCard key={ i } { ...item } />
					) ) }
				</div>
			</Card>

			<ProModulesCard />
		</>
	);

	const sidebar = (
		<>
			{ /* Hide the upgrade pitch once the Pro plugin is active. */ }
			{ ! PRO_ACTIVE && <LicenseCard { ...LICENSE_DATA } /> }
			<SitesImportCard />
			<Card title={ __( 'Resources', 'customify' ) }>
				<ResourceList items={ RESOURCES } />
			</Card>
			<RecommendPluginsCard />
			<Card title={ __( 'Loving Customify?', 'customify' ) }>
				<ReviewCard { ...REVIEW } />
			</Card>
		</>
	);

	return (
		<>
			<WelcomeLayout main={ main } sidebar={ sidebar } />
			<GuidePopover
				guideKey={ openGuide }
				isOpen={ !! openGuide }
				onClose={ () => setOpenGuide( null ) }
			/>
		</>
	);
}
