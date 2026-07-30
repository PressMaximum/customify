# SPEC — Content Area Spacing

Context-aware control of Customify's existing vertical spacing around `#main`
and the primary/secondary sidebars. This is a theme-level layout feature: it
uses the real WordPress request context and has no page-builder or plugin-
template coupling.

---

## 1. Storage shape

`site_content_padding` remains the responsive `css_ruler` that owns the actual
top/bottom spacing magnitude. Its theme-mod ID, value shape, selector and CSS
output are unchanged; only its Customizer section moved to
`Layouts → Content Area Spacing`.

Context controls store one of `inherit`, `both`, `top`, `bottom`, or `disabled`:

| Context | Theme mod |
|---|---|
| Pages | `page_content_area_spacing` |
| Blog posts | `posts_content_area_spacing` |
| Blog archives | `posts_archives_content_area_spacing` |
| Search | `search_content_area_spacing` |
| 404 | `404_content_area_spacing` |
| CPT single | `{post_type}_content_area_spacing` |
| CPT archive | `{post_type}_archive_content_area_spacing` |

Every new setting defaults to `inherit`. No migration is required: an existing
site with no saved context mod gets the same global CSS and body classes as
before this feature.

The existing singular post meta
`_customify_disable_content_vertical_padding=1` remains supported and has
absolute precedence over the context setting.

---

## 2. Customizer UI

The `Content Area Spacing` section lives under the existing `Layouts` panel.
The `General` group contains the unchanged global spacing magnitude followed by
Pages, Blog Posts, Blog Archives, Search and 404 modes.

`Post Type Settings` is a heading control above dynamically generated CPT
controls. The CPT source is `customify_get_content_post_types()`, so internal
utility types and plugin-specific exclusions remain centralized. A CPT archive
control is added only when the type has a real archive and Customify owns that
archive. WooCommerce's Product archive and product-only taxonomies keep their
established ownership, receive no context setting, and leave the global spacing
unchanged. Their assigned Shop Page's legacy
`_customify_disable_content_vertical_padding` override is still honored through
the WooCommerce integration.

---

## 3. Request resolution

`customify_get_content_area_spacing_context()` maps the real request:

1. Search and 404.
2. Blog home.
3. Page or blog-post single.
4. Supported CPT archive.
5. Custom taxonomy owned by exactly one supported archive CPT.
6. Archive/taxonomy owned by an excluded integration → no context setting.
7. Built-in/shared/unresolved archive → Blog Archives.
8. Supported CPT single.

`customify_resolve_content_area_spacing_mode()` then applies this precedence:

1. Existing singular disable meta → `disabled`.
2. Context theme mod.
3. Invalid/empty value → `inherit`.

Taxonomies shared by multiple post types intentionally fall back to Blog
Archives. This avoids guessing ownership and matches the sidebar resolver.

---

## 4. Render pipeline

`inherit` and `both` add no body class; the existing `site_content_padding` CSS
continues to provide both sides. Other modes emit only the class needed to zero
one or both components:

| Mode | Body class | Effect |
|---|---|---|
| `top` | `content-area-spacing-top-only` | zero bottom |
| `bottom` | `content-area-spacing-bottom-only` | zero top |
| `disabled` | `disable-content-vertical-padding` | zero both |

The disabled mode deliberately reuses the legacy public class. Static rules
live in `src/frontend/scss/layouts/_layouts.scss`; the global spacing value is
not duplicated or replaced.

---

## 5. Extension hooks

| Filter | Purpose |
|---|---|
| `customify/content_area_spacing/archive_post_types` | Change CPT archives owned by the spacing UI/resolver. |
| `customify/content_area_spacing/context` | Map an unusual route to a core context or direct setting name. |
| `customify/content_area_spacing/mode` | Filter a non-legacy resolved mode. |
| `customify/content_area_spacing/components` | Filter final `top` / `bottom` booleans. |
| `customify/content_area_spacing/body_classes` | Extend the minimal spacing body classes. |

The legacy singular disable meta returns before the mode/components filters so
its existing precedence cannot be weakened accidentally.

---

## 6. Verification

Focused resolver coverage lives in `tests/content-area-spacing-test.php` and
includes page, blog post, CPT single, CPT archive, owning taxonomy, shared
taxonomy, search, 404 and legacy meta precedence. Run:

```bash
php tests/content-area-spacing-test.php
```
