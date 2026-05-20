/**
 * Welcome tab — single-page scroll with Hero + Checklist + Customizer
 * quick-link grid + Pro module grid. Composes kit primitives + small
 * theme-owned UI components (`ModuleList`, `ThemeGridCard`, `ToggleSwitch`).
 */

import { applyFilters } from '@wordpress/hooks';
import { Card, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Hero, Checklist, useBoot } from '@pressmaximum/dashboard-kit';

import { useCustomizerLinks } from '../data/customizerLinks.js';
import { useChecklist } from '../data/checklist.js';
import ThemeGridCard from '../ui/ThemeGridCard.jsx';
import ProModulesSection from '../sections/ProModulesSection.jsx';

export default function Welcome() {
	const boot = useBoot();
	const links = useCustomizerLinks( boot );
	const checklistItems = useChecklist( boot );

	const greeting = __( 'Welcome to Customify', 'customify' );

	const tagline = __(
		'Lightweight, SEO-optimized, multipurpose WordPress theme. Set up your site identity, header, footer, and styling — all from the Customizer.',
		'customify',
	);

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

			<Card className="customify-dashboard-welcome__checklist">
				<CardHeader>
					<h2 className="customify-dashboard-welcome__checklist-title">
						{ __( 'Get started', 'customify' ) }
					</h2>
				</CardHeader>
				{ /* Checklist renders directly inside Card (no CardBody) so
				     item dividers span edge-to-edge — kit's
				     pmdk-checklist__item carries its own padding. */ }
				<Checklist
					items={ checklistItems }
					ariaLabel={ __( 'Customify onboarding checklist', 'customify' ) }
					itemLabels={ {
						checking: __( 'Checking…', 'customify' ),
						completed: __( 'Completed', 'customify' ),
						pending: __( 'Pending', 'customify' ),
					} }
				/>
			</Card>

			<Card className="customify-dashboard-welcome__theme-customizer">
				<CardHeader>
					<h2 className="customify-dashboard-welcome__checklist-title">
						{ __( 'Customizer quick links', 'customify' ) }
					</h2>
					<a
						className="customify-dashboard-header-link"
						href={ boot?.urls?.customize || '#' }
						target="_blank"
						rel="noopener noreferrer"
					>
						{ __( 'Go to Customizer', 'customify' ) }
					</a>
				</CardHeader>
				<div className="customify-dashboard-theme-grid">
					{ links.map( ( link ) => (
						<ThemeGridCard
							key={ link.id }
							title={ link.title }
							description={ link.description }
							href={ link.href }
						/>
					) ) }
				</div>
			</Card>

			{ extraSections.map( ( section ) => {
				const Render = section.render;
				return Render ? (
					<Render key={ section.id } boot={ boot } />
				) : null;
			} ) }

			<ProModulesSection boot={ boot } />
		</div>
	);
}
