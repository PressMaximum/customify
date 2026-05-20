/**
 * Welcome tab — single-page scroll with Hero + Checklist + Customizer
 * quick-links + Pro module grid. Composes kit primitives; no list-page
 * or DataViews machinery (lightweight theme shape per SPEC §10.1).
 */

import { applyFilters } from '@wordpress/hooks';
import { Card, CardBody, CardHeader, Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import { Hero, Checklist, useBoot } from '@pressmaximum/dashboard-kit';

import { useCustomizerLinks } from '../data/customizerLinks.js';
import { useChecklist } from '../data/checklist.js';
import { useProModules } from '../data/proModules.js';

export default function Welcome() {
	const boot = useBoot();
	const links = useCustomizerLinks( boot );
	const checklistItems = useChecklist( boot );
	const proModules = useProModules();

	const greeting = boot?.user?.displayName
		? sprintf(
			// translators: %s is the current user's display name.
			__( 'Welcome, %s', 'customify' ),
			boot.user.displayName,
		)
		: __( 'Welcome to Customify', 'customify' );

	const tagline = __(
		'Lightweight, SEO-optimized, multipurpose WordPress theme. Set up your site identity, header, footer, and styling — all from the Customizer.',
		'customify',
	);

	/**
	 * Allow Pro / child themes to append sections below the checklist.
	 *
	 * Each section is rendered as `<section.render({ boot })>`.
	 */
	const extraSections = applyFilters(
		'customify.dashboard.welcome.sections',
		[],
		boot,
	);

	return (
		<div className="customify-dashboard-welcome">
			<Hero
				greeting={ greeting }
				tagline={ tagline }
				primaryCta={ {
					label: __( 'Open the Customizer', 'customify' ),
					href: boot?.urls?.customize || '#',
				} }
			/>

			<Card className="customify-dashboard-welcome__card">
				<CardHeader>
					<h2>{ __( 'Get started', 'customify' ) }</h2>
				</CardHeader>
				<CardBody>
					<Checklist
						items={ checklistItems }
						ariaLabel={ __( 'Customify onboarding checklist', 'customify' ) }
						itemLabels={ {
							checking: __( 'Checking…', 'customify' ),
							completed: __( 'Completed', 'customify' ),
							pending: __( 'Pending', 'customify' ),
						} }
					/>
				</CardBody>
			</Card>

			<Card className="customify-dashboard-welcome__card">
				<CardHeader>
					<h2>{ __( 'Customizer quick links', 'customify' ) }</h2>
				</CardHeader>
				<CardBody>
					<ul className="customify-dashboard-welcome__links">
						{ links.map( ( link ) => (
							<li key={ link.id }>
								<Button
									variant="tertiary"
									href={ link.href }
									target="_blank"
									rel="noopener noreferrer"
								>
									{ link.label }
								</Button>
							</li>
						) ) }
					</ul>
				</CardBody>
			</Card>

			{ extraSections.map( ( section ) => {
				const Render = section.render;
				return Render ? (
					<Render key={ section.id } boot={ boot } />
				) : null;
			} ) }

			<Card className="customify-dashboard-welcome__card customify-dashboard-welcome__pro">
				<CardHeader>
					<h2>{ __( 'Customify Pro modules', 'customify' ) }</h2>
					<Button
						variant="primary"
						href={ boot?.urls?.proUpgrade || '#' }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'Upgrade now', 'customify' ) } &rarr;
					</Button>
				</CardHeader>
				<CardBody>
					<ul className="customify-dashboard-welcome__pro-grid">
						{ proModules.map( ( m ) => (
							<li
								key={ m.id }
								className={
									'customify-dashboard-welcome__pro-item' +
									( m.sub ? ' is-sub' : '' )
								}
							>
								<h3>{ m.name }</h3>
								{ m.desc && <p>{ m.desc }</p> }
							</li>
						) ) }
					</ul>
				</CardBody>
			</Card>
		</div>
	);
}
