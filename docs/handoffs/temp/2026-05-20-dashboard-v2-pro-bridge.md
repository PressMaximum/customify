# Handoff — Customify Dashboard v2 + Customify Pro bridge

**Date**: 2026-05-20
**Outgoing**: previous Claude session
**Status**: P0–P16 shipped on the theme worktree (`claude/dazzling-villani-914d75`); 10 of the PRO-N phases (PRO-1, 2, 3, 4, 8, 9, 11, 12, 14, 15) also have commits on the Pro plugin (`customify-pro-dashboard-compat`). PRO-5, 6, 7, 7.1, 10, 13 are theme-only renderer / wiring fixes — no Pro-side change. Both repos clean except for the `.claude/` worktree dirs the harness owns.

**PRs opened from this work**:
- Theme: https://github.com/PressMaximum/customify/pull/387 (base `theme-dashboard-v2`)
- Pro: https://github.com/PressMaximum/customify-pro/pull/13 (base `master`)
- Both need to merge together for the Pro extension surface to light up.

---

## 1. What this is

A new SPA admin dashboard for the **Customify Theme** (top-level slug `customify`, kit-powered React app) plus a **Customify Pro bridge plugin layer** that hooks into the kit's filter surface so Pro extends the dashboard without forking it.

The dashboard replaces the legacy PHP page (`themes.php?page=customify-legacy` — kept around in `inc/admin/dashboard.php` but the user has flagged it for full removal in this release). The new dashboard ships:

- **Welcome tab** — Hero + Get-started checklist + Customizer quick-links + Pro module grid (with live toggles when Pro is active).
- **Settings tab** — tabbed SubNav layout with composite panels: Theme settings (Font Awesome version), Module settings (Typekit fields), Customify Pro (license activation + assets-combine toggle).
- **Changelog tab** — multi-source SubNav: Customify free + Customify Pro (parsed from `readme.txt` PHP-side).

---

## 2. Required reading (in order)

Read these before touching anything:

### 2.1 Theme repo (`/Users/kientrong/Studio/customify2/wp-content/themes/customify`)

1. `AGENTS.md` / `CLAUDE.md` (root) — Customify theme conventions: existing function rule, language=English, webpack pipeline, kit-token-first SCSS, AJAX surface rules.
2. `inc/admin/dashboard-v2.php` — new dashboard PHP entry (top-level menu, render callback, boot data, asset enqueue, WP submenu sync, menu icon CSS).
3. `inc/admin/dashboard-v2-rest.php` — REST controller for the theme's General panel (extends kit `SettingsControllerBase`, SchemaBuilder declaration, legacy `customify_fa_ver` mirror).
4. `inc/admin/dashboard.php` — legacy dashboard renamed to slug `customify-legacy`. Keep code intact (back-compat for child themes hooking `customify/dashboard/*` actions) but **user has flagged this for full removal** in the release.
5. `src/backend/admin/dashboard-v2/` — full JS:
   - `index.js` — `mountDashboard()` config; defers to `DOMContentLoaded` so Pro's bridge `<script>` runs first.
   - `tabs/Welcome.jsx`, `tabs/Settings.jsx`, `tabs/Changelog.jsx` — three top-level tabs.
   - `sections/ProModulesSection.jsx` — Pro modules grid + toggle handler.
   - `sections/ProModuleSettingsPanel.jsx` — generic schema-driven Pro panel renderer (used for Typekit / Assets sections).
   - `sections/LicensePanel.jsx` — custom UI for License activation (no SaveBar; own Activate/Deactivate buttons).
   - `data/customizerLinks.js`, `data/checklist.js`, `data/proModules.js`, `data/settingsStore.js` — filterable data hooks.
   - `ui/ModuleList.jsx`, `ui/ToggleSwitch.jsx`, `ui/ThemeGridCard.jsx` — small primitives.
   - `dashboard-v2.scss` — all dashboard SCSS.
6. `git log -- src/backend/admin/dashboard-v2/ inc/admin/dashboard-v2*.php` — commit messages document every decision. Read them.

### 2.2 Customify Pro (`/Users/kientrong/Studio/customify2/wp-content/plugins/customify-pro`)

1. `customify-pro.php` — bootstrap. `init_admin()` loads the bridge file. Theme-name notice carries a `customify-dev` WORKAROUND clause (REMOVE when the symlink drops).
2. `inc/admin/class-dashboard-v2-bridge.php` — the entire bridge. PHP filter for `customify_dashboard_pro_active`, REST routes under `/customify-pro/v1/`, module snapshot, changelog snapshot, settings_panels composite shape, license updater wrapper.
3. `assets/js/admin/dashboard-v2-bridge.js` — JS filters that hook into `customify.dashboard.{pro.modules, pro.toggle, settings.panels, changelog.sources}`.
4. `inc/admin/class-dashboard.php` — legacy Pro dashboard hooks (`customify/dashboard/main`, `box_modules`, `box_assets`, AJAX `wp_ajax_customify_pro_module`). Bridge layers on top; legacy stays intact.
5. `inc/class-module-base.php` — `get_settings()` / `set_key_value()` / `save()` storage helpers (option key `customify_pro_settings`).
6. `inc/updater/updater.php` — `Customify_Pro_Updater::active($key)` / `deactivate_license()` / `get_save_data()`. EDD API at `pressmaximum.com`. **NB**: activation method is `active()` NOT `activate_license()` — the `activate_license` string elsewhere in the file is the EDD action argument. Calling the wrong name fataled at first; fixed in PRO-14 (`fd02553`).
7. `git log customify-pro-dashboard-compat` — commit messages.

### 2.3 Dashboard kit (`/Users/kientrong/Studio/dashboard-kit`)

1. `docs/SPEC.md` §5 (public API), §8 (bootstrap), §9 (filter contracts), §16 (theming).
2. `KIT_ISSUES.md` — Open + Closed. K-008 (option-shape silent coerce) + K-009 (brand.href accepted but ignored) were filed during this work; both shipped. K-001..K-007 already closed before this session.
3. `src/core/DashboardShell.jsx`, `src/core/mountDashboard.jsx`, `src/core/HashRouter.js` — read these to understand how `applyFilters` is called and when.
4. `src/settings/SchemaForm.jsx`, `src/settings/SaveBar.jsx`, `src/settings/createSettingsStore.js` — the kit's settings primitives we wrap.
5. `src/welcome/Hero.jsx`, `src/welcome/Checklist.jsx` — Welcome page primitives.
6. `src/changelog/ReleaseBlock.jsx`, `src/layouts/SubNav.jsx` — Changelog tab primitives.
7. `includes/Admin/AssetEnqueue.php`, `includes/REST/SettingsControllerBase.php`, `includes/Schema/SchemaBuilder.php` — kit's PHP utilities.

### 2.4 Cross-reference

- Blocksify Free's worktree at `/Users/kientrong/Studio/customify/wp-content/plugins/blocksify/.claude/worktrees/eloquent-haibt-02de76` — multi-source Changelog SubNav reference, snackbar success glyph pattern (`SUCCESS_GLYPH` const), CardHeader-less Checklist composition. The pattern this dashboard mostly mirrors.

### 2.5 Memory rules

`~/.claude/projects/-Users-kientrong-Studio-customify2-wp-content-themes-customify/memory/MEMORY.md` indexes:
- `feedback_kit_vs_theme_fix_split.md` — **CRITICAL**: kit defects go into `KIT_ISSUES.md`, NEVER edit kit source. Theme defects fix inline.

---

## 3. Architecture quick map

```
Customify Theme (slug `customify`, top-level admin menu)
├── React SPA (entry: src/backend/admin/dashboard-v2/index.js)
│   ├── mountDashboard({ brand, baseTabs, baseRoutes, helpItems, … })
│   │     ↓ kit applies filters per §9: customify.dashboard.*
│   └── routes:
│       ├── #welcome → tabs/Welcome.jsx
│       ├── #settings + #settings/:panelId → tabs/Settings.jsx
│       │   └── SubNav of panels (filtered via customify.dashboard.settings.panels):
│       │       ├── Theme settings — kit ThemePanelCard + SchemaForm
│       │       └── (Pro-only) Module settings, Customify Pro — composite panels:
│       │           └── each section.kind=schema → ProModuleSettingsPanel
│       │           └── each section.kind=license → LicensePanel
│       └── #changelog + #changelog/:sourceId → tabs/Changelog.jsx
│           └── customify.dashboard.changelog.sources filter
└── REST: /customify/v1/settings (theme General panel)

Customify Pro (plugin, branch customify-pro-dashboard-compat)
├── customify-pro.php → loads class-dashboard-v2-bridge.php in init_admin()
├── Bridge PHP:
│   ├── apply_filter(customify_dashboard_pro_active) = true
│   ├── apply_filter(customify_dashboard_localize) injects boot.proVersion
│   ├── REST under /customify-pro/v1/:
│   │   ├── /module/typekit (GET, POST)  → Typekit settings via module base
│   │   ├── /settings/assets (GET, POST) → customify_pro_assets_compress option
│   │   ├── /license + /license/activate + /license/deactivate → updater->active()/deactivate_license()
│   └── localised data → assets/js/admin/dashboard-v2-bridge.js
└── Bridge JS:
    ├── addFilter customify.dashboard.pro.modules → augment theme list with runtime state
    ├── addFilter customify.dashboard.pro.toggle → POSTs to wp_ajax_customify_pro_module
    ├── addFilter customify.dashboard.changelog.sources → Pro readme.txt changelog
    └── addFilter customify.dashboard.settings.panels → composite panels (modules + customify-pro)
```

---

## 4. What's done (full ledger)

| Phase | Branch | Summary |
|---|---|---|
| P0–P3 | theme | Kit dep wire-up, top-level menu, Welcome tab with Hero/Checklist, Settings + Changelog scaffolds, brand polish, regression tests. |
| P4–P5 | theme | Customify logo SVG (header brand + WP menu icon), flush layout (admin chrome zeroed), brand-icon sizing. |
| P6 | theme | Brand-click → `#welcome` via K-009 workaround (removed in P16 when kit landed `brand.href`). |
| P7–P8 | theme | Save success snackbar with green check glyph; Welcome checklist edge-to-edge dividers. |
| P9–P10 | theme | Customizer quick-links 3-col grid + Pro modules 2-col grid + sub-modules, help panel, WP sidebar submenu sync. |
| P11–P14 | theme | WP menu icon 18px, Settings tab Reset-to-defaults fix, Changelog multi-source, 5-fix Welcome polish, drop Sidebar/Header Transparent. |
| P15 | theme | Multi-source Changelog tab with Pro source. |
| P16 | theme | Kit CSS refresh + drop K-009 workaround (kit landed brand.href natively). |
| PRO-1 | Pro | Bridge skeleton: theme-name `customify-dev` notice, module snapshot + toggle handler. |
| PRO-2 | both | Module toast glyph + Pro readme.txt changelog source. |
| PRO-3 | both | Pro version label in header (boot.proVersion injection). |
| PRO-4 | both | Typekit settings panel + `#settings/:panelId` route. |
| PRO-5 | theme | Settings tab → SubNav layout, one save UI per panel. |
| PRO-6 | theme | Unify save UI (kit SaveBar) across theme + Pro panels. |
| PRO-7 | theme | SaveBar moved outside the panel card. |
| PRO-8 | Pro | Decode HTML entities in panel field labels. |
| PRO-9 | Pro | Pro Settings (assets combine) + License key panels. |
| PRO-10 | theme | applyFilters timing bug — `DOMContentLoaded` gate + drop stale `useMemo` caches. |
| PRO-11 | both | LicensePanel with full EDD activate/deactivate flow. |
| PRO-12 | both | Rename General → "Theme settings", Typekit → "Typekit (module)". |
| PRO-13 | theme | LicensePanel uses TextControl + left-aligned actions. |
| PRO-14 | both | Fix license `active()` method name + sanitize REST error messages. |
| PRO-15 | both | Composite panels (Module settings + Customify Pro grouping); version anchor → `#changelog/customify-pro` when Pro active. |

---

## 5. Open work / pending decisions

### 5.1 Deferred features (user-mentioned, not yet implemented)

- **Regenerate Assets button** on the Customify Pro tab's Assets section. Legacy Pro had a "Regenerate Assets" button beside the Combine toggle. Needs a custom action slot in `ProModuleSettingsPanel` — likely a `section.actions[]` array of `{label, endpoint, method, confirm?}` rendered as buttons inside the panel body. Or extend the section schema with an `extraActions` render slot.
- **Legacy dashboard removal**. User confirmed in this session: "legacy settings sẽ được remove ở release này chứ không giữ lại đâu". `inc/admin/dashboard.php` (the `Customify_Dashboard` class) + the legacy hooks `customify/dashboard/main`, `customify/dashboard/sidebar`, `customify/dashboard/changelog/before`, `customify_dashboard_settings` AJAX should all go away in this release. Pro's `inc/admin/class-dashboard.php` legacy box_modules / box_assets / changelog_tabs hooks can come out too once the legacy page is dead.
- **Smoke-test symlink cleanup**. `/Users/kientrong/Studio/customify2/wp-content/themes/customify-dev` is a symlink → this worktree. When the worktree branch merges into the canonical theme, drop the symlink. The Pro `customify-pro.php` notice WORKAROUND can be reverted then.

### 5.2 Out-of-scope reminders

- **DOMContentLoaded gate** in `index.js` is load-bearing. Pro registers JS filters AFTER theme script load. If anyone tries to mount synchronously again, the Pro panels will disappear at first paint. The `NB:` comments at every `applyFilters` call explain why no useMemo.
- **License activation method**: it's `Customify_Pro_Updater::active($key)`, not `activate_license($key)`. The string `activate_license` elsewhere is the EDD action argument.
- **EDD activation needs a real key** against `https://pressmaximum.com/`. Bogus keys come back as `status: "invalid"`, which is correct behaviour, not a bug.

### 5.3 Kit issues filed in this session

- **K-008** (closed): `SchemaBuilder::selectField/radioField` silently accepted bad option shape → labels rendered as `"Array"`. Kit fix shipped.
- **K-009** (closed): `brand.href` accepted by `mountDashboard` but unused. Kit landed native `<a class="pmdk-dashboard__brand-link">` wrapping. Theme dropped its consumer DOM-delegation workaround in P16.

No open kit issues from this work.

---

## 6. How to build / test

### 6.1 Theme

```bash
# From the worktree root
cd /Users/kientrong/Studio/customify2/wp-content/themes/customify/.claude/worktrees/dazzling-villani-914d75
npm install          # only first time
npm run build        # full build
npm start            # watch mode
```

Build outputs to `build/`. Webpack entries declared in `webpack.config.js` — the dashboard one is `backend/admin/dashboard-v2`.

### 6.2 Pro plugin

```bash
cd /Users/kientrong/Studio/customify2/wp-content/plugins/customify-pro
# Pure PHP + plain JS — no JSX. Grunt for asset compile if you touch
# any .scss / non-bridge JS. The bridge file
# assets/js/admin/dashboard-v2-bridge.js is plain JS without Grunt
# processing.
grunt    # if SCSS / non-bridge JS changes
```

### 6.3 Smoke site

```bash
# Studio site at /Users/kientrong/Studio/customify2 (URL http://customify2.wp.local)
# Theme: customify-dev (symlink to this worktree)
# Pro: customify-pro plugin (activate via WP-CLI)
studio wp --path /Users/kientrong/Studio/customify2 theme list 2>&1 | grep customify
studio wp --path /Users/kientrong/Studio/customify2 plugin list 2>&1 | grep customify-pro
```

Cookie + WP-REST testing — Studio CLI doesn't pass the right user context for nonce-gated REST. Use the browser DevTools console with `wp.apiFetch({ path: '…' })` for authed REST calls in dev.

### 6.4 Live verification pattern (Chrome MCP)

```js
// In Chrome devtools console (or via mcp__Claude_in_Chrome__javascript_tool):
JSON.stringify({
    proActive: window.customifyDashboard?.proActive,
    proVersion: window.customifyDashboard?.proVersion,
    bridgeModuleCount: window.customifyProBridge?.modules?.length,
    bridgePanelCount: window.customifyProBridge?.settingsPanels?.length,
    subNavLabels: Array.from(document.querySelectorAll('.customify-dashboard-settings .pmdk-subnav__item')).map(a => a.textContent.trim()),
})
```

---

## 7. Conventions to keep

1. **Kit vs theme split** — see memory rule. Kit bugs → `dashboard-kit/KIT_ISSUES.md`, never edit kit source.
2. **No useMemo around applyFilters** when the filter is mutated by a later-loading script. Comments at each call site explain. Adding one re-introduces the timing bug PRO-10 fixed.
3. **WORKAROUND / REMOVE comments** for any consumer-side workaround tied to a kit issue or a transient setup quirk. Greppable so reverts are mechanical.
4. **English only** in code comments and strings (per CLAUDE.md).
5. **One save UI per panel** principle. Theme panels use the kit's `SaveBar`. Pro schema sections use `ProModuleSettingsPanel`'s built-in `SaveBar` (rendered outside the card). License section runs independently — no SaveBar.
6. **Composite panels** for grouping sections under one SubNav row. New section kinds extend `Settings.jsx`'s `renderSection()` branch.
7. **Filter namespace** — every consumer-side hook lives under `customify.dashboard.*` (JS) or `customify_dashboard_*` (PHP). Customify Pro hooks the same names with priority 10.

---

## 8. Quick start for the next session

```bash
# 1. Read this file end-to-end.
# 2. Read AGENTS.md + the relevant SPEC.md sections.
# 3. Check git state both repos:
git -C /Users/kientrong/Studio/customify2/wp-content/themes/customify/.claude/worktrees/dazzling-villani-914d75 log --oneline -15
git -C /Users/kientrong/Studio/customify2/wp-content/plugins/customify-pro log customify-pro-dashboard-compat --oneline -15
# 4. Verify the smoke site is up + Pro is active:
studio wp --path /Users/kientrong/Studio/customify2 plugin list | grep customify-pro
# 5. Browse to http://customify2.wp.local/wp-admin/admin.php?page=customify
#    Confirm: Welcome shows greeting + 20 module rows; Settings SubNav reads
#    [Theme settings, Module settings, Customify Pro]; Changelog SubNav reads
#    [Customify, Customify Pro]; version anchor href is #changelog/customify-pro.
```

If anything diverges from that smoke-state, the worktree got out of sync with what this handoff describes.

**Heads at handoff time**:
- Theme `claude/dazzling-villani-914d75`: `8cf862c1` last code commit (PRO-15); `522dada9` adds this handoff + the `docs/handoffs/` convention on top.
- Pro `customify-pro-dashboard-compat`: `8d60c57` (PRO-15).

A clean pick-up should see those as the tip (or one or two further if the next session committed more before reading this). If you see materially different SHAs, scan the new commits for context before changing anything.

---

## 9. Contact / questions

User's preferred language: Vietnamese (with English fine for code/strings).
User's review style: surgical, screenshot-driven. Expect screenshots with very specific feedback (`spacing left`, `border radius bottom`, etc.).
User's instinct on layout: prefers WP-native components (`TextControl`, `Button`) over raw HTML, prefers save chrome outside cards, prefers one save UI per panel.

Ping back if any of the required reading is missing or if `git log` shows commits past `8cf862c1` / `8d60c57` that this handoff didn't anticipate.
