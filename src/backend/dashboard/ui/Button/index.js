/**
 * Thin wrapper around @wordpress/components Button so the dashboard buttons
 * inherit WP admin theme color (Modern / Blue / Coffee / Light etc.) and
 * Gutenberg accessibility behaviors (focus ring, aria-disabled, etc.) for
 * free.
 *
 * Variant alias map (kept so legacy callers don't need to change):
 *   ghost → WP tertiary (text-only, no border)
 *
 * Anything else passes through verbatim, so callers can use any WP variant
 * (`primary` | `secondary` | `tertiary` | `link`) or a custom one (e.g.
 * `minimal`) which WP renders as `class="components-button is-{variant}"`
 * for downstream className-based theming.
 *
 * `href` triggers <a> rendering inside WPButton automatically.
 */

import { Button as WPButton } from '@wordpress/components';

const VARIANT_ALIAS = {
	ghost: 'tertiary',
};

export default function Button( {
	variant = 'primary',
	href,
	onClick,
	disabled,
	type = 'button',
	className,
	children,
	...rest
} ) {
	const wpVariant = VARIANT_ALIAS[ variant ] || variant;
	return (
		<WPButton
			variant={ wpVariant }
			href={ href }
			onClick={ onClick }
			disabled={ disabled }
			type={ href ? undefined : type }
			className={ className }
			{ ...rest }
		>
			{ children }
		</WPButton>
	);
}
