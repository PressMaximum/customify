# SPEC — Data Migration Policy

Canonical reference for Customify's persistent data: a complete audit of every `theme_mod`, `wp_options`, and `post_meta` key the theme touches, the migration discipline that governs changes, and concrete examples of migrations Customify has shipped.

For the day-to-day "how do I think about this change" view, see [`migration-guide.md`](migration-guide.md).

Related references:
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) §6 — shared keys with the Pro plugin
- [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) §4 — builder storage shape
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §1.2 — 30k-site doctrine in practice

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

Customify ships on **30,000+ live production sites**. Every persistent key documented in this file is **public API** under the project's never-break-existing-sites rule. Renaming, dropping, or silently changing a key's shape is a data migration with implications across the install base.

Three things matter most:

1. **The key registry below is the authoritative list.** When you add a new key, you MUST update this file in the same PR.
2. **Migration discipline is enforced.** Every storage change goes through the decision tree in [`migration-guide.md`](migration-guide.md) §2. No shortcuts.
3. **Pro and theme share keys.** The "shared" column flags every key that Pro reads or writes — those require Pro coordination.

| Surface | Owner | Stored in |
|---|---|---|
| Customizer fields | Theme + Pro | `theme_mod` (per-theme) |
| Builder layouts (V2) | Theme | `theme_mod` (URL-encoded JSON) |
| Dashboard SPA settings | Theme | `wp_options` |
| Pro module flags & settings | Pro | `wp_options` (theme writes `customify_fa_ver` only) |
| Per-page overrides | Theme | `post_meta` (any post type) |

---

## 2. `theme_mod` keys — Customizer settings

`theme_mod` data is scoped to the active theme — switching themes hides (but does not delete) the values. All Customizer field names are `theme_mod` keys by default.

### 2.1 Layout & sidebar

| Key | Type | Default | Notes |
|---|---|---|---|
| `sidebar_layout` | string | `content` | Global default layout (`content` / `content-sidebar` / `sidebar-content` / `sidebar-content-sidebar`) |
| `blog_sidebar_layout` | string | inherits | Layout for `is_home()` / blog page |
| `single_blog_post_sidebar_layout` | string | inherits | Layout for `is_single()` |
| `archive_sidebar_layout` | string | inherits | Layout for `is_archive()` |
| `page_sidebar_layout` | string | inherits | Layout for `is_page()` |
| `{post_type}_sidebar_layout` | string | `content` | Per-CPT **single** sidebar layout (`is_singular()`). Dynamic — one key per content post type; `default` choice inherits the global `sidebar_layout` |
| `{post_type}_archive_sidebar_layout` | string | `content` | Per-CPT **archive** sidebar layout (`is_post_type_archive()`). Dynamic — only registered for types with `has_archive`; excludes WC `product` (shop owned by WC). Defaults to no-sidebar like the per-CPT single; the `Default` choice (value `default`) inherits `posts_archives_sidebar_layout` |
| `container_width` | int + unit (slider) | `1200px` | Site container max-width; MUST sync to `--wp--style--global--wide-size` |
| `site_content_padding` | responsive CSS ruler | SCSS fallback | Existing top/bottom spacing magnitude for `#main` and both sidebars; ID and output unchanged when moved to the Content Area Spacing section |
| `page_content_area_spacing` | string | `inherit` | Pages: `inherit` / `both` / `top` / `bottom` / `disabled` |
| `posts_content_area_spacing` | string | `inherit` | Blog-post singles |
| `posts_archives_content_area_spacing` | string | `inherit` | Blog home, built-in archives, and shared/unresolved taxonomies |
| `shop_content_area_spacing` | string | `inherit` | WooCommerce Shop and product taxonomy archives; assigned Shop Page legacy disable meta wins |
| `search_content_area_spacing` | string | `inherit` | Search results |
| `404_content_area_spacing` | string | `inherit` | 404 requests |
| `{post_type}_content_area_spacing` | string | `inherit` | Per-CPT single; generated from `customify_get_content_post_types()` |
| `{post_type}_archive_content_area_spacing` | string | `inherit` | Per-CPT archive when the type has a real archive owned by Customify |

### 2.2 Builder layouts

| Key | Type | Default | Notes |
|---|---|---|---|
| `header_builder_version` | string | `v2` | Active header builder version (`v1` / `v2`) |
| `header_builder_panel` | string (JSON) | `''` | **V1 LEGACY** — kept readable for migration only |
| `header_builder_panel_v2` | string (URL-encoded JSON) | default tree | Active header layout tree |
| `footer_builder_panel` | string (JSON) | `''` | **V1 LEGACY** |
| `footer_builder_panel_v2` | string (URL-encoded JSON) | default tree | Active footer layout tree |
| `footer_v1_to_v2_migrated` | bool / string | absent | One-shot migration flag — set after V1→V2 footer migration runs |
| `hide_header_builder_switcher` | string (`yes` / `''`) | `''` | Hide V1↔V2 switcher UI |

Builder storage shape: see [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) §4.

### 2.3 Colors

See [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §3 for the full color contract. Cliff notes:

**6 palette slots:**

| Key | Type | Default | Status |
|---|---|---|---|
| `global_styling_color_primary` | hex | `#235787` | REUSED from pre-Phase 1 |
| `global_styling_color_secondary` | hex | `#c3512f` | REUSED |
| `customify_palette_accent` | hex | `#FFD042` | NEW Phase 1 |
| `customify_palette_text` | hex | `#2b2b2b` | NEW Phase 1 |
| `customify_palette_surface` | hex | `#ECECEC` | NEW Phase 1 (default changed in 2.4) |
| `customify_palette_base` | hex | `#FFFFFF` | NEW Phase 1 |

**7 legacy overrides** (still emit literal hex; explicit overrides over computed defaults):

`global_styling_color_text`, `global_styling_color_link`, `global_styling_color_link_hover`, `global_styling_color_border`, `global_styling_color_meta`, `global_styling_color_heading`, `global_styling_color_w_title`

**3 background composites:**

`background`, `site_content_styling`, `content_background` (all composite JSON, `bg_color` subfield + image options)

### 2.4 Typography

| Key (representative) | Type | Notes |
|---|---|---|
| `global_typography_base` | composite | Body font family + weight + size + line-height + letter-spacing |
| `global_typography_base_heading` | composite | Heading defaults |
| `global_typography_heading_h1` … `_h6` | composite | Per-level heading typography |

All typography fields are `typography` composite controls. See [`SPEC-customizer.md`](SPEC-customizer.md) §5.3.

### 2.5 Header builder per-item fields

Naming is inconsistent across items — historical accident, see [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) §7. Representative examples:

| Key | Item | Notes |
|---|---|---|
| `custom_logo` | Logo | Standard WP attachment ID |
| `logo_max_width` | Logo | Slider (bare prefix) |
| `header_logo_retina` | Logo | Image control (prefixed) |
| `header_logo_tran` | Logo | Object `{ id, mime, url }` — Customify's `image` control shape |
| `header_logo_tran_retina` | Logo | Same shape |
| `logo_tran_max_width` | Logo | Per-device slider |
| `primary_menu_style` | Primary Menu | Bare prefix |
| `primary_menu_style_border_h` | Primary Menu | Bare prefix |
| `nav_icon_*` | Nav Icon | Bare prefix |
| `search_icon_*` | Search Icon | Bare prefix |

### 2.6 Header Transparent

| Key | Type | Default | Notes |
|---|---|---|---|
| `header_top_transparent` | bool | `false` | Per-row toggle |
| `header_main_transparent` | bool | `false` | Per-row toggle |
| `header_bottom_transparent` | bool | `false` | Per-row toggle |
| `header_top_transparent_styling` | styling array \| `""` | `""` | Per-row styling composite |
| `header_main_transparent_styling` | styling array \| `""` | `""` | |
| `header_bottom_transparent_styling` | styling array \| `""` | `""` | |
| `header_transparent_display_pages` | modal array (`display` tab) | `{}` | Page-type exclusion map |

See [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §10 for full details.

### 2.7 Other Customizer fields

Hundreds more under `inc/customizer/configs/`. The discovery rule: grep for `'name' =>` in config files to find every registered field. Or read `apply_filters( 'customify/customizer/config', [] )` runtime output.

### 2.8 Stability promise

Every `theme_mod` key listed above (and every key registered in `inc/customizer/configs/*.php`) is **public API**. Renaming requires a migration; see §7 below.

---

## 3. `wp_options` keys

Options are site-wide (not theme-scoped). Removing the theme leaves these in place.

| Option | Owner | Read by | Written by | Purpose |
|---|---|---|---|---|
| `customify_fa_ver` | Theme | Theme icon loader, Pro dashboard | Theme dashboard-v2 REST, Pro dashboard | Font Awesome version (`v4` / `v6` / `v456`) |
| `customify_dashboard_v2_settings` | Theme | Theme + Pro bridge | Theme dashboard-v2 REST | Dashboard panel settings |
| `customify_modules` | Pro | Pro | Pro | Pro module on/off flags |
| `customify_pro_settings` | Pro | Pro | Pro | Pro-only settings — theme should not touch |
| `customify_migrations` | Theme | Theme migration code | Theme migration code | Version-stamped migration flags (e.g. `feature_x_v2 => timestamp`) |
| `elementor_load_fa4_shim` | Elementor | Elementor compat | Theme (conditionally) | FA4 compat flag |
| `page_on_front` | WP core | Theme template logic | WP core | Static homepage ID |
| `page_for_posts` | WP core | Theme template logic | WP core | Blog page ID |
| `thread_comments` | WP core | Theme enqueue logic | WP core | Comment thread depth toggle |

### 3.1 Shared option coordination

Renaming `customify_fa_ver`, `customify_dashboard_v2_settings`, or `customify_modules` requires Pro coordination — those are read/written by both codebases. See [`SPEC-pro-integration.md`](SPEC-pro-integration.md) §6 and [`migration-guide.md`](migration-guide.md) §7.

---

## 4. `post_meta` keys

Per-post overrides. All Customify-managed meta keys use the `_customify_` prefix (the leading underscore is WP convention for "hide from the standard custom-fields UI").

### 4.1 Layout / display overrides

| Meta key | Stored type | Possible values | File |
|---|---|---|---|
| `_customify_content_layout` | string | `''` / `'default'` / `'full-width'` / `'full-stretched'` | [`inc/template-functions.php:224`](../inc/template-functions.php) |
| `_customify_sidebar` | string | `''` / `'default'` / `'content'` / `'content-sidebar'` / `'sidebar-content'` / `'sidebar-content-sidebar'` | [`inc/template-functions.php:171`](../inc/template-functions.php) |
| `_customify_disable_header` | bool | `1` / `''` | [`inc/template-functions.php:312`](../inc/template-functions.php) |
| `_customify_disable_{builder_id}` | bool | `1` / `''` | [`inc/template-functions.php:368`](../inc/template-functions.php) — `builder_id` is `header` / `footer` |
| `_customify_disable_page_title` | bool | `1` / `''` | [`inc/template-functions.php:385`](../inc/template-functions.php) |
| `_customify_disable_content_vertical_padding` | bool | `1` / `''` | Legacy singular override; remains highest precedence in [`inc/content-area-spacing.php`](../inc/content-area-spacing.php) |

### 4.2 Page header / cover overrides

| Meta key | Stored type | Possible values | File |
|---|---|---|---|
| `_customify_page_header_display` | string | varies | [`inc/customizer/configs/page-header.php:865`](../inc/customizer/configs/page-header.php) |
| `_customify_page_header_title` | string | text | same `:875` |
| `_customify_page_header_tagline` | string | text | same `:881` |
| `_customify_page_header_image` | int | attachment ID | same `:887` |
| `_customify_page_header_shortcode` | string | shortcode source | same `:896` |

### 4.3 Feature overrides

| Meta key | Stored type | Possible values | File |
|---|---|---|---|
| `_customify_breadcrumb_display` | bool | `1` / `''` | [`inc/compatibility/breadcrumb.php:320`](../inc/compatibility/breadcrumb.php) |
| `_customify_header_transparent_display` | string | `''` / `'default'` / `'show'` / `'hide'` | [`inc/customizer/configs/header/transparent.php:341`](../inc/customizer/configs/header/transparent.php) |
| `_customify_related_posts` | array | post IDs | [`inc/blog/class-related-posts.php:72`](../inc/blog/class-related-posts.php) |

### 4.4 WooCommerce-specific

| Meta key | Post type | Notes |
|---|---|---|
| `_customify_wc_show_page_title` | `page` (shop assignment) | Show WC shop page title |
| `woocommerce_catalog_tablet_columns` | WC settings | WC standard meta (theme reads only) |

---

## 5. Custom Post Types

| Slug | Owner | Purpose |
|---|---|---|
| `customify_hook` | Pro (Hooks module) | User-authored HTML/PHP snippets injected at WP action hooks |

Theme does NOT register any CPTs. `customify_hook` posts on sites with Pro Hooks module = paying customer data. Pro never renames the slug — see Pro's own migration policy.

---

## 6. Sanitize callbacks (canonical list)

When a Customizer field uses a custom sanitize callback, the callback signature is also public API (it determines what shape the saved value takes).

| Callback | Used by | Accepts | Returns |
|---|---|---|---|
| `customify_sanitize_columns_settings` | `columns_settings` control type | URL-encoded JSON string OR decoded array | Decoded array (strips unknown sub-keys) |
| `customify_sanitize_typography` | `typography` control | array | array (validates sub-keys) |
| `customify_sanitize_image` | `image` control | array OR int (attachment ID) | array `{ id, mime, url }` |
| (more) | various controls | — | — |

Changing a sanitize callback's return shape = data migration (every saved value passes through the new shape on next save). See [`migration-guide.md`](migration-guide.md) §6.

---

## 7. Migration history (concrete examples)

### 7.1 V1 → V2 header builder migration

**Trigger**: User has `header_builder_panel` (V1) but no `header_builder_panel_v2` (V2) set.

**Where**: [`inc/panel-builder/builder-functions.php:15-24`](../inc/panel-builder/builder-functions.php).

**Logic**:
1. Read `header_builder_version` — if `v2` AND `header_builder_panel_v2` already set, skip.
2. Read V1 data from `header_builder_panel`.
3. Transform V1 row/column shape to V2 device → row → slot shape.
4. Write V2 data to `header_builder_panel_v2`.
5. Set `header_builder_version = 'v2'`.

**Idempotency**: Migration only runs when V2 data is absent. Running twice is a no-op.

**Backward compat**: V1 data is NOT deleted. Theme still reads V1 if `header_builder_version === 'v1'`.

### 7.2 V1 → V2 footer builder migration

**Trigger**: User has `footer_builder_panel` (V1) but no `footer_v1_to_v2_migrated` flag.

**Where**: [`inc/panel-builder/builder-functions.php:71-134`](../inc/panel-builder/builder-functions.php).

**Logic**:
1. Check `footer_v1_to_v2_migrated` flag — if set, skip.
2. Read V1 data from `footer_builder_panel`.
3. Iterate V1 rows (`top` / `main` / `bottom`), transform to V2 JSON structure.
4. Write to `footer_builder_panel_v2`.
5. Set `footer_v1_to_v2_migrated = true`.

**Idempotency**: Version flag prevents re-run.

**Backward compat**: V1 data preserved.

### 7.3 Colors phase 1 — additive only

**Trigger**: New install OR existing install opens the Colors section.

**Where**: [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) — no migration code, all additive.

**Logic**: Add 4 new slot keys (`customify_palette_*`) + emit a `:root` token block that derives from saved legacy keys. Legacy 9 color keys keep rendering on the same selectors unchanged.

**Idempotency**: No state change on the existing 9 keys.

**Backward compat**: Verified byte-equivalent against three site states (Defaults / Custom brand / Partial save). See [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §5.

### 7.4 Colors phases 2.1-2.7 — var() refactor with cascade fallbacks

**Trigger**: Theme update — no per-site migration.

**Where**: Auto-CSS pipeline rewrites — see [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §8.2-8.7.

**Logic**: Each CSS rule that emits a saved value gets a `var(--customify-XXX, {{value}})` fallback. The `:root` token block emits a cascade decl (`--customify-XXX: var(--customify-YYY, hex)`) when no override is saved.

**Idempotency**: No state change.

**Backward compat**: Verified byte-equivalent against A/B/C scenarios after every phase.

---

## 8. The canonical migration pattern

For any new migration, follow this template. (For when to migrate vs read-both-shapes, see [`migration-guide.md`](migration-guide.md) §2 decision tree.)

```php
add_action( 'init', function () {

    // 1. Idempotent guard
    $migrated = get_option( 'customify_migrations', array() );
    if ( isset( $migrated['<feature>_v<N>'] ) ) {
        return;
    }

    // 2. Migrate
    $old = get_option( 'customify_<feature>', array() );
    if ( ! empty( $old ) ) {
        $new = customify_migrate_<feature>_v<N>( $old );
        update_option( 'customify_<feature>', $new );
    }

    // 3. Stamp the flag
    $migrated['<feature>_v<N>'] = time();
    update_option( 'customify_migrations', $migrated );

    // 4. Optional: log + backup
    error_log( '[Customify] Migrated <feature> to v<N> on ' . get_site_url() );
    update_option( 'customify_migrations_backup_<feature>', $old );
} );
```

Key requirements:

- **Idempotent migration function** (`customify_migrate_<feature>_v<N>`) — safe to run twice without corruption
- **Unique flag key** — never reuse a flag from a previous migration
- **Timestamp value** — so support can audit when each site migrated
- **Optional backup** — stash the pre-migration value for at least one release

---

## 9. Design decisions

### 9.1 `theme_mod` over `wp_options` by default

- **Chose**: Customizer fields stored as `theme_mod`
- **Rejected**: All-options storage
- **Reason**: Switching themes hides theme_mod data — keeps the next theme's state clean. Use `wp_options` only when the value semantically belongs to the site (Font Awesome version), not the theme.

### 9.2 URL-encoded JSON for builder layouts

- **Chose**: `theme_mod 'header_builder_panel_v2'` stores `urlencode(json_encode($tree))`
- **Rejected**: Native PHP array via `set_theme_mod`
- **Reason**: Customizer's JS state bridge passes strings cleanly across the iframe boundary. Native arrays got mangled in some setups. URL-encoded JSON survives every transport.

### 9.3 V1 data preserved indefinitely

- **Chose**: V1 keys (`header_builder_panel`, `footer_builder_panel`) stay readable forever
- **Rejected**: Delete V1 after migration
- **Reason**: 30k sites; an aborted migration leaves V2 corrupt and V1 the only intact data. Deleting V1 = data loss on the failure path. Storage cost of one extra theme_mod row is negligible.

### 9.4 Migration flag in a single option, not per-flag option

- **Chose**: `customify_migrations` array stores all flags
- **Rejected**: One option per flag (`customify_migration_feature_x_v2`)
- **Reason**: Single option = one DB query for "what migrations have run on this site". Per-flag options = N queries. For supporting customers with 20+ migrations over time, single option is the obvious win.

### 9.5 Migration flag stores timestamp, not boolean

- **Chose**: `$migrated['feature_x_v2'] = time();`
- **Rejected**: `$migrated['feature_x_v2'] = true;`
- **Reason**: Audit log. Support can ask "when did this migration run?" and see the exact timestamp. Boolean drops that info.

### 9.6 Default value changes don't trigger migrations

- **Chose**: Update the field's `default` in config; sites that saved any value keep their value
- **Rejected**: Migration that writes the new default to every site
- **Reason**: Writing defaults explicitly = converting "site at default" to "site with explicit save", which is a semantic change. Next time the default changes, those sites won't pick it up. Read-time default resolution preserves the "I never touched it, use the latest theme default" semantic.

---

## 10. Known issues / edge cases

### Issue #1 — V1 builders still appear in switcher UI

The V1 builder UI is still functional in the Customizer. Sites that downgrade `header_builder_version` from `v2` to `v1` will start reading the V1 data again. This is intentional (no-regret upgrade path) but means V1 code can't be deleted without breaking that downgrade flow.

### Issue #2 — Bare-prefix builder item field names

Some header/footer builder items use bare-prefix names (`logo_max_width`, `nav_icon_*`) instead of namespaced (`header_logo_*`). Historical accident — these are public API now, renaming requires a migration. New items must use the prefixed convention.

### Issue #3 — `image` control value shape varies

Customify's `image` control stores `{ id, mime, url }` object — NOT a plain attachment ID like core WP's `custom_logo`. Reading code must use `Customify()->get_media( $value, $size )` to resolve either shape.

### Issue #4 — Composite control sub-key drift

The `styling` composite control's enabled sub-fields change between feature contexts (e.g. `header_transparent_styling` disables `padding`, `margin`, `link_color`, etc.). Saved values from a context that had sub-fields A+B+C should not break when re-read in a context that only allows A+B. Sanitize accepts wider shape than emits.

### Issue #5 — Customizer `get_setting()` returns registered default, not `null`

`get_theme_mod( 'foo', null )` returns `null` if the user hasn't saved. But `Customify()->get_setting( 'foo' )` returns the field's registered default. For "did the user save anything?" checks, use `get_theme_mods()` (saved-only array) — critical pattern documented in [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §8.3.

### Issue #6 — Post meta on attachments

Attachment post type passes `is_single()`, so `_customify_sidebar` meta on attachments works the same as on posts. If you want to exclude attachments from per-post overrides, check `'attachment' === get_post_type()` explicitly.

---

## 11. Adding a new key — checklist

Before opening a PR that adds any persistent key:

- [ ] Is this a `theme_mod`, `wp_options`, or `post_meta`?
- [ ] Does the name follow the convention (Customify prefix, underscores)?
- [ ] Is there a sanitize callback? Is its signature documented?
- [ ] Is the default value documented + sensible?
- [ ] Will Pro need to read/write this? If yes, coordinate with Pro maintainer
- [ ] Have I added the key to the registry tables above?
- [ ] Have I added an example to [`api-reference.md`](api-reference.md) if it's filter-shaped?

---

## 12. Removing or renaming a key — checklist

Before opening a PR that touches an existing key:

- [ ] Is this a rename or a remove? Both are migrations
- [ ] Have I followed the decision tree in [`migration-guide.md`](migration-guide.md) §2?
- [ ] Is there a read-both-shapes fallback for at least one minor cycle?
- [ ] Have I added a migration with a unique version flag?
- [ ] Is the migration idempotent? Tested?
- [ ] Have I coordinated with Pro if this is a shared key?
- [ ] Have I added the migration to §7 above (Migration history)?
- [ ] Have I updated the key registry tables to mark the old key as DEPRECATED?

---

## 13. Where to look next

**Theme files**
- [`inc/class-customify.php`](../inc/class-customify.php) — `get_setting()` resolver
- [`inc/template-functions.php`](../inc/template-functions.php) — post meta consumers
- [`inc/panel-builder/builder-functions.php`](../inc/panel-builder/builder-functions.php) — V1→V2 migration examples
- [`inc/admin/dashboard-v2-rest.php`](../inc/admin/dashboard-v2-rest.php) — option-mirror pattern (`customify_fa_ver`)

**Related specs**
- [`migration-guide.md`](migration-guide.md) — how-to-think view of migrations
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) §6 — shared-option coordination
- [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) §4 — builder storage shape
- [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §5 — byte-equivalent verification
- [`SPEC-header-transparent.md`](SPEC-header-transparent.md) §10 — feature-level key list

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.1 — 30k-site rule
- [`../AGENTS.md`](../AGENTS.md) §4.2 — never-rename rule
