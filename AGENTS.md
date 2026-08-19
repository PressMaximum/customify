# AGENTS.md — Customify Theme

Rules and invariants for any AI coding agent working on the Customify WordPress theme. Agent-agnostic: applies equally to Claude Code, Cursor, Codex, or a human contributor.

Detailed how-it-works references live in [`docs/`](docs/). This file is **rules only** — concise, enforceable, no narrative.

---

## 1. Project at a glance

- Classic WordPress theme (not FSE). Singleton bootstrap from `inc/class-customify.php` via `Customify()` helper.
- **30,000+ live production sites.** Every change ships to that install base.
- Stack: PHP 7.4+ (theme runtime), webpack via `@wordpress/scripts` (JS/SCSS), Grunt (packaging only).
- Companion: **Customify Pro** plugin. Theme and Pro share options + filters — see [`docs/SPEC-pro-integration.md`](docs/SPEC-pro-integration.md).

---

## 2. Quick start

```bash
composer install   # PHP deps (dashboard-kit)
npm install        # JS deps
npm run build      # production bundle → build/
```

Full setup, prerequisites, troubleshooting: [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md).

---

## 3. Build & test commands

| Command | Purpose |
|---|---|
| `npm run build` | Production build — minified, RTL, asset.php sidecars |
| `npm start` | Watch mode for active dev |
| `npm run lint:js` | ESLint via wp-scripts |
| `npm run makepot` | Regenerate the `.pot` file |
| `grunt zipfile` | Packaging only — calls `npm run build` internally |

Never use Grunt for CSS/JS compilation. Grunt is packaging-only.

---

## 4. Hard rules

### 4.1 30k-site safety (the most important rule)

Any change that touches persistent data — `theme_mod` keys, `wp_options` keys, `post_meta` keys, CPT slugs, sanitize callbacks, value shapes, default values — must explicitly plan for:

1. **Backward compatibility** — read both old AND new shapes. New code accepts existing data without throwing or silently dropping fields. Always provide a sensible default.
2. **Migration** — if storage shape genuinely must change, write a one-time, version-stamped, idempotent migration. See [`docs/migration-guide.md`](docs/migration-guide.md) for the pattern.
3. **Renames are migrations.** Changing a key, option name, or sanitize signature is a data migration even if the code change looks small. Keep the old key as a read-only fallback for at least one minor version cycle.
4. **Defaults must not change silently.** Existing sites with the old value still render correctly; sites with no saved value get the new default. Never assume "users will just re-save".

When in doubt about storage, ASK before changing. A 5-minute clarification is cheaper than a support escalation across 30,000 sites.

### 4.2 Never delete or rename public functions

PHP functions, class methods, action/filter names, template tags, and Customizer field names are **public API**. Child themes, plugins, and Customify Pro may depend on them.

| Action | Rule |
|---|---|
| Delete existing function | Forbidden. Mark `@deprecated`, keep body or forward to replacement. |
| Rename existing function | Forbidden. Add the new name alongside; old becomes a `@deprecated` wrapper. |
| New function with overlapping purpose | Use a distinct name (e.g. `*_v2`). Never reuse the original. |

Deprecated wrapper pattern:

```php
/**
 * @deprecated 0.5.0 Use customify_render_header_v2() instead.
 */
function customify_render_header() {
    _deprecated_function( __FUNCTION__, '0.5.0', 'customify_render_header_v2' );
    customify_render_header_v2();
}
```

This rule applies to: standalone functions, class methods, action/filter callback names, template tags, Customizer field names.

### 4.3 Pro ↔ theme boundary

Module class names (`Customify_Pro_Module_*`), filter names, action names, REST namespaces (`/customify/v1/`, `/customify-pro/v1/`) are public API consumed by paid customers. Same deprecation discipline as §4.2.

Theme code that integrates with Pro must:

- Guard with `class_exists( 'Customify_Pro_Module_<Name>' )` — see [`docs/SPEC-pro-integration.md`](docs/SPEC-pro-integration.md) for the canonical pattern
- Never edit Pro plugin files from theme work; integrate via shared options + filters
- Honour the Pro takeover convention: when Pro module exists, theme port stays dormant (gate at `after_setup_theme:30`)

If you find a bug that genuinely lives in Pro: (1) document it with reproduction steps, (2) open an issue / PR in the Pro repo, (3) from the theme side, work around it by guarding the affected path — never patch Pro from within a theme PR. Mixing theme + Pro changes in one PR makes either side hard to roll back, and Pro has its own release cycle.

### 4.4 Public selectors / classes / IDs

Frontend CSS classes (`.customify-header`, `.col-v2-left`, `#header-menu-sidebar`, etc.) are referenced by user custom CSS, page builders, and child theme overrides. **Treat as public API.** Changing them breaks thousands of customizations.

### 4.5 English only

All code comments, docblocks, inline notes, `.md` files, commit messages, and any other text **inside the codebase** must be English. Conversation language with the user is unconstrained — this rule is about source files only.

### 4.6 Compiled assets are off-limits

Edit `src/`, never `build/`. SCSS / JS sources live in `src/`. The `build/` folder is generated by webpack and GITIGNORED - rebuild it locally with `npm run build`; the release pipeline (see [`docs/release-guide.md`](docs/release-guide.md)) builds and packages it into the distributed zip. The `assets/` folder is legacy and is no longer updated.

After source change: `npm run build`. Always.

### 4.7 CSS handle must match `customify-style`

`wp_add_inline_style()` silently drops its CSS if the handle isn't enqueued. The main stylesheet handle is `customify-style`. Any call attaching generated CSS must use exactly that string.

```php
// CORRECT — handle matches the enqueued key
wp_enqueue_style( 'customify-style', ... );
wp_add_inline_style( 'customify-style', $css );

// WRONG — typo → inline CSS silently discarded
wp_add_inline_style( 'customify', $css );
```

If you ever rename the enqueue handle, update every `wp_add_inline_style()` call in lockstep.

### 4.8 AJAX handler template

Every `wp_ajax_*` handler must include nonce + capability + sanitized input:

```php
add_action( 'wp_ajax_my_action', function () {
    check_ajax_referer( 'my_nonce_action', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Forbidden', 403 );
    }

    $value = sanitize_text_field( wp_unslash( $_POST['value'] ?? '' ) );

    wp_send_json_success( $result );
} );
```

No exceptions. Missing any of nonce / capability / sanitize is a security defect.

### 4.9 Null-check DOM queries in JS

`getElementById()` and `querySelector()` return `null` on admin pages, widget editor, or pages where the header isn't rendered. Calling `.contains()`, `.getBoundingClientRect()`, etc. on `null` throws a TypeError that breaks the entire script.

```js
// WRONG
const sidebar = document.getElementById( 'header-menu-sidebar' );
const inside  = sidebar.contains( e.target );

// CORRECT
const sidebar = document.getElementById( 'header-menu-sidebar' );
if ( ! sidebar ) return;
const inside = sidebar.contains( e.target );
```

Known unsafe spots in `src/frontend/js/theme.js` (fix when nearby):

- ~L232: `menuSidebar.contains(e.target)` — no null guard
- ~L607: `menuSidebarInner.getBoundingClientRect()` — no null guard
- ~L682: `button.getBoundingClientRect()` — `button` can be null inside `searchFormAutoAlign`

### 4.10 Verify third-party HTML selectors against source

Do not assume class names for WP core blocks or WooCommerce elements. Verify against the actual source:

```bash
grep -r 'class=' /path/to/wordpress/wp-includes/blocks/<block-name>/
```

Known selector mistakes documented in [`docs/SPEC-block-editor.md`](docs/SPEC-block-editor.md).

### 4.11 Container width must sync to CSS custom property

When `container_width` changes in the Customizer, it must also update `--wp--style--global--wide-size` so Customizer, `theme.json`, and the block editor stay aligned. Handled by the `css_format` of the `container_width` field in [`inc/customizer/configs/layouts.php`](inc/customizer/configs/layouts.php). Do not regress this.

### 4.12 Deprecated block editor selectors (WP 6.0+)

| Old (deprecated) | Use instead |
|---|---|
| `.editor-post-title__input` | `.wp-block-post-title` |
| `.edit-post-visual-editor` | `.editor-styles-wrapper` |
| `.edit-post-layout__content` | `.editor-styles-wrapper` |
| `.wp-block[data-align="wide"]` | `.alignwide` |
| `wp-edit-post` style handle | `wp-edit-blocks` (WP 6.2+) |

### 4.13 `alignwide` / `alignfull` — modern approach only

Use CSS custom properties from `theme.json`. Do NOT use the `transform: translateX(-50%)` hack — it conflicts with `margin: auto` on `.entry-content > *`.

```scss
// CORRECT
.entry-content > .alignwide {
    max-width: var(--wp--style--global--wide-size, 1200px);
    width: 100%;
}
.entry-content > .alignfull {
    width: 100vw;
    margin-left:  calc(50% - 50vw) !important;
    margin-right: calc(50% - 50vw) !important;
}
```

### 4.14 LF-only line endings

**Every file created or modified in this repo — regardless of type or purpose — must use LF (`\n`) line endings only.** Never commit CRLF, CR, or mixed CR/LF + LF in the same file. Applies to: `*.php`, `*.js`, `*.jsx`, `*.ts`, `*.tsx`, `*.scss`, `*.css`, `*.json`, `*.md`, `*.yml`, `*.txt`, dotfiles — everything. `.editorconfig` already declares `end_of_line = lf`; keep your editor honouring it and avoid pasting from sources that inject CRLF (Notepad, some Windows shells, certain copy-paste paths from RDP/terminals).

Mixed endings cause real downstream breakage:

- `webpack` preserves whatever endings the input file used — a single CRLF SCSS partial or JS module (or upstream npm package, e.g. `@dnd-kit/utilities` ESM build) poisons the bundled `build/**` output.
- Diff tools, code review surfaces, `git blame`, and SVN all treat a CRLF↔LF flip as a full-file change, hiding the real edit.
- Some PHP `heredoc`/regex paths behave differently across `\n` vs `\r\n`.

`webpack.config.js` ships a `NormalizeLineEndingsPlugin` (PROCESS_ASSETS_STAGE_REPORT) that rewrites every emitted text asset (`.js`, `.css`, `.map`, `.asset.php`, etc.) to LF before write — keep it in place. It's the only thing standing between CRLF-shipping deps and the WP.org SVN reviewer.

Audit and fix:

```bash
# audit the working tree (returns nothing when clean)
find . -type f \
  -not -path "./node_modules/*" -not -path "./vendor/*" \
  -not -path "./release-staging/*" -not -path "./.git/*" \
  -exec file {} \; | grep -i "crlf\|cr line"

# convert a single file in place (macOS / Linux)
perl -i -pe 's/\r\n/\n/g' path/to/file
```

After fixing sources, `npm run build` so `build/` is regenerated from clean inputs.

---

## 5. Storage key registry (high-level)

Full audit in [`docs/SPEC-data-migration-policy.md`](docs/SPEC-data-migration-policy.md). Cliff notes:

| Key family | Stored in | Public? |
|---|---|---|
| Customizer settings (`global_styling_*`, `header_*`, `footer_*`, `single_blog_post_*`, etc.) | `theme_mod` | YES — never rename |
| Builder layouts (`header_builder_panel_v2`, `footer_builder_panel_v2`) | `theme_mod` (URL-encoded JSON) | YES |
| `customify_fa_ver`, `customify_dashboard_v2_settings`, `customify_modules` | `wp_options` | YES |
| `_customify_content_layout`, `_customify_sidebar`, `_customify_header_transparent_display`, `_customify_page_header_*`, etc. | `post_meta` (any post type) | YES |
| `header_builder_panel`, `footer_builder_panel` (V1) | `theme_mod` | LEGACY — readable for migration only |

---

## 6. When in doubt

- Storage shape question → read [`docs/migration-guide.md`](docs/migration-guide.md), then ask if still unclear
- Pro plugin question → read [`docs/SPEC-pro-integration.md`](docs/SPEC-pro-integration.md)
- Filter/action lookup → [`docs/api-reference.md`](docs/api-reference.md)
- Architecture/code map → [`docs/README.md`](docs/README.md) §"How it all fits together"
- Subsystem deep-dive → matching `docs/SPEC-*.md`

If a rule above conflicts with something you observe in the code, the rule wins until a maintainer says otherwise. Surface the conflict — don't silently work around it.
