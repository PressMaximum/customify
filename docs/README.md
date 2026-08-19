# Customify Theme — Documentation

Index for all theme documentation. Every file in this folder is either a **SPEC** (canonical reference for one subsystem), a **guide** (cross-cutting reference like API or migration), or a **handoff** (transient session note in `handoffs/`).

For agent-facing rules see [`../AGENTS.md`](../AGENTS.md). For Claude-specific tips see [`../CLAUDE.md`](../CLAUDE.md).

---

## Start here

| You are… | Read |
|---|---|
| New contributor | [`DEVELOPMENT.md`](DEVELOPMENT.md) — setup, build, daily workflow |
| About to change rules / conventions | [`../AGENTS.md`](../AGENTS.md) |
| About to change storage shape (theme_mod / option / meta) | [`migration-guide.md`](migration-guide.md) **before** anything else |
| Adding a new feature | Skim [How it all fits together](#how-it-all-fits-together) below, then the matching SPEC |
| Looking for a filter or action signature | [`api-reference.md`](api-reference.md) |
| Shipping a release | [`release-guide.md`](release-guide.md) — pre-flight, grunt commands, WP.org SVN push |

---

## Guides (cross-cutting)

| File | Scope |
|---|---|
| [`api-reference.md`](api-reference.md) | Public filters, actions, template tags — signature + file:line + example |
| [`migration-guide.md`](migration-guide.md) | 30k-site safety policy + idempotent migration patterns |
| [`release-guide.md`](release-guide.md) | Release workflow — version bump, Grunt commands, GitHub + WP.org distribution |
| [`DEVELOPMENT.md`](DEVELOPMENT.md) | Setup, build, daily workflow |

---

## How it all fits together

The big picture — request flow + which subsystem owns which path. Deep-dive each subsystem in its SPEC.

```
┌─────────────────────────────────────────────────────────────────────────┐
│  WordPress request                                                       │
│    │                                                                     │
│    ▼                                                                     │
│  functions.php  →  Customify()::get_instance()                           │
│    │                                                                     │
│    ▼                                                                     │
│  inc/class-customify.php (singleton)                                     │
│    ├── init_hooks()         theme_setup, scripts, sidebars, filters      │
│    ├── includes()           core files + customizer + builders + admin   │
│    ├── load_configs()       inc/customizer/configs/*.php (after_setup)   │
│    ├── load_compatibility() Pro / Elementor / Breadcrumb / WC            │
│    └── customizer->init()   Customify_Customizer registers everything    │
│                                                                          │
│  Frontend render path:                                                   │
│  header.php → customify_customize_render_header() → builder V2 frontend  │
│             → row → col-v2-{slot} → item->render()                       │
│  loop ────► template tags + class-post-entry / class-posts-layout       │
│  footer.php → customify_customize_render_footer() → builder V2 frontend  │
│                                                                          │
│  Customizer admin path:                                                  │
│  customize_register → Customify_Customizer::register()                   │
│                    → apply_filters('customify/customizer/config', [])   │
│                    → walks merged array → panels/sections/settings       │
│                    → Customify_Customizer_Auto_CSS::render_css()         │
│                    → wp_add_inline_style('customify-style', $css)        │
│                                                                          │
│  Block editor path:                                                      │
│  enqueue_block_editor_assets → Customify_Editor::assets()                │
│                              → css() — sync subset of Customizer values  │
│                              → wp_add_inline_style('wp-edit-blocks',…)   │
└─────────────────────────────────────────────────────────────────────────┘
```

| Concern | SPEC |
|---|---|
| Singleton, layout, element classes, template hierarchy | [`SPEC-bootstrap.md`](SPEC-bootstrap.md) |
| Context-aware content area vertical spacing | [`SPEC-content-area-spacing.md`](SPEC-content-area-spacing.md) |
| webpack entries, asset.php sidecar, CSS handle | [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) |
| Customizer config + auto-CSS + JS contexts | [`SPEC-customizer.md`](SPEC-customizer.md) |
| 6-slot color palette + `:root` token pipeline | [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) |
| Typography `--customify-typo-*` vars + legacy filter | [`SPEC-typography.md`](SPEC-typography.md) |
| Header / Footer Builder V2 (storage + render + items) | [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) |
| Transparent header conditional + Pro takeover | [`SPEC-header-transparent.md`](SPEC-header-transparent.md) |
| `theme.json`, block editor CSS bridge, patterns | [`SPEC-block-editor.md`](SPEC-block-editor.md) |
| Top-level admin dashboard SPA | [`SPEC-dashboard.md`](SPEC-dashboard.md) |
| Theme ↔ Pro contract (23 modules) | [`SPEC-pro-integration.md`](SPEC-pro-integration.md) |
| Storage key audit + migration history | [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) |
| WooCommerce / Elementor / Breadcrumb integration | [`SPEC-compat-overview.md`](SPEC-compat-overview.md) |

---

## SPECs (canonical references)

Each SPEC owns one subsystem end-to-end: storage shape, render pipeline, design decisions, hooks, troubleshooting.

| File | Subsystem |
|---|---|
| [`SPEC-bootstrap.md`](SPEC-bootstrap.md) | Singleton bootstrap, layout system, element classes, template hierarchy |
| [`SPEC-content-area-spacing.md`](SPEC-content-area-spacing.md) | Global spacing magnitude, request contexts, CPT/taxonomy inheritance |
| [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) | webpack, `index.asset.php` pattern, CSS handle, `src/` vs `build/` vs `assets/` |
| [`SPEC-customizer.md`](SPEC-customizer.md) | Customizer architecture, config-driven registration, auto-CSS, fonts, controls |
| [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) | 6-slot palette, `:root` token pipeline, `theme.json` sync, picker UI |
| [`SPEC-typography.md`](SPEC-typography.md) | Typography `:root` vars pipeline (`--customify-typo-*`), legacy escape hatch, mixin |
| [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) | Builder V2 storage, render pipeline, item registration, column settings |
| [`SPEC-header-transparent.md`](SPEC-header-transparent.md) | Transparent header conditional chain, per-page metabox, Pro handoff |
| [`SPEC-block-editor.md`](SPEC-block-editor.md) | `theme.json` sync, `Customify_Editor` CSS injection, block styles, patterns |
| [`SPEC-dashboard.md`](SPEC-dashboard.md) | Top-level admin dashboard SPA, REST, Pro bridge |
| [`SPEC-pro-integration.md`](SPEC-pro-integration.md) | Theme ↔ Pro contract — class names, options, REST namespaces |
| [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) | Storage migration patterns, key audit, version flags |
| [`SPEC-compat-overview.md`](SPEC-compat-overview.md) | WooCommerce, Elementor, Breadcrumb integration overview |

---

## Handoffs (transient)

[`handoffs/`](handoffs/) holds session-scoped notes that aren't meant to live forever — in-flight branch state, debugging logs, decision-in-progress drafts. Move stable content out into the appropriate SPEC and prune the handoff.

---

## Conventions for SPEC files

### Location
All SPECs live FLAT in `docs/`. No subfolders. Naming: `SPEC-<feature>.md`, kebab-case, no version suffix.

### Lifecycle

| Status | Meaning |
|---|---|
| **Active** | Feature in design or in-flight rollout. SPEC is the single source of truth and is updated as the feature evolves. |
| **Shipped** | Feature stable. SPEC describes current behavior; updated only when the feature changes. |
| **Superseded** | A newer SPEC owns the topic. Old file kept with a top-of-file note pointing forward (do not delete — keeps git history searchable). |

Mark status in the SPEC's opening lines if useful. Most SPECs are implicitly Shipped.

### Template
Copy [`_template.md`](_template.md) to start a new SPEC. The template encodes the section order used by every existing SPEC so readers can scan multiple SPECs without re-learning the layout.

### When to write a SPEC vs extend an existing one

- **New SPEC** when the subsystem boundary is clear (own storage keys, own render pipeline, own decisions).
- **Extend existing** when the new behavior fits inside an existing subsystem (new control type → extend [`SPEC-customizer.md`](SPEC-customizer.md); new builder item → extend [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md)).

### Storage / Render / Decisions

Every SPEC must include three load-bearing sections — these are the three angles the team cares about most:

1. **Storage shape** — exact `theme_mod` / `option` / `post_meta` keys, value shape, defaults, sanitize callback, backward-compat fallback. This is **public API** under the 30k-sites rule.
2. **Render pipeline** — flow from storage to output (CSS / HTML / JSON). Trace by file + line where possible.
3. **Design decisions** — why this shape, what alternatives were rejected and why. The "why" survives refactors that invalidate the "how".

See `_template.md` for the full section list.

### Language

English only — see [`../AGENTS.md`](../AGENTS.md).
