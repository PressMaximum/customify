# Customify Theme — Claude Reference

Claude-specific quick reference. **Rules are in [`AGENTS.md`](AGENTS.md). Architecture, APIs, and how-it-works are in [`docs/`](docs/).** This file only adds Claude-Code-specific workflow tips that aren't useful to other agents or human contributors.

---

## Read first

| For | Read |
|---|---|
| Hard rules (30k safety, never-rename, English-only, AJAX, CSS handle, etc.) | [`AGENTS.md`](AGENTS.md) |
| Architecture / code map | [`docs/README.md`](docs/README.md) §"How it all fits together" |
| Setup, build, troubleshooting | [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) |
| Subsystem deep-dive | [`docs/SPEC-*.md`](docs/) |
| Filter / action / template tag signature | [`docs/api-reference.md`](docs/api-reference.md) |
| Storage migration | [`docs/migration-guide.md`](docs/migration-guide.md) |
| Releasing | [`docs/release-guide.md`](docs/release-guide.md) |

---

## Task → file lookup

When the user asks you to do one of these, start at the listed file:

| Task | Start here |
|---|---|
| Add a Customizer field | `inc/customizer/configs/<area>.php` (hook `customify/customizer/config`) |
| Add a header item | `inc/customizer/configs/header/<item>.php` — model on `logo.php` or `nav-icon.php` |
| Add a footer item | `inc/customizer/configs/footer/<item>.php` |
| Add a block pattern | `patterns/<name>.php` (auto-registered by WP 6.0+) |
| Add a block style | [`inc/admin/block-styles.php`](inc/admin/block-styles.php) + SCSS in `src/frontend/scss/base/_blocks.scss` |
| Sync a Customizer value into the block editor | Append key to `$keys` in [`inc/admin/editor.php`](inc/admin/editor.php) `css()` method |
| Add a new webpack entry | [`webpack.config.js`](webpack.config.js) `entries` object + `npm run build` |

---

## Claude-specific workflow tips

### When the user reports "CSS not appearing"

Three likely causes, check in order:

1. **Inline CSS handle mismatch** — `wp_add_inline_style()` must reference `'customify-style'` exactly (see [`AGENTS.md`](AGENTS.md) §4.7)
2. **Field missing `selector` or `css_format`** — check the Customizer config item
3. **Block editor diverging from frontend** — field not in `$keys` array in [`inc/admin/editor.php`](inc/admin/editor.php) `css()`

### When the user is about to change storage shape

Stop. Read [`docs/migration-guide.md`](docs/migration-guide.md) §2 decision tree first. 30k+ sites at stake.

### When the user wants to edit a Pro module

Don't. Pro is a separate codebase. From the theme, integrate via shared options + `class_exists` guards. See [`docs/SPEC-pro-integration.md`](docs/SPEC-pro-integration.md).

### Skills that fit common tasks

- `/verify` — after frontend changes, drive the app in browser to confirm
- `/code-review` — second-pass review of larger diffs before commit
- `/security-review` — when touching AJAX, REST, or any user input path

### When the user says "release"

Read [`docs/release-guide.md`](docs/release-guide.md) — they have an automated pipeline (`grunt release`). Don't manually re-implement what's already in Gruntfile.js.

---

## PressMaximum ecosystem

PressMaximum is the umbrella brand. A session on this repo sometimes needs to read or
change a sibling product — this is the stable, machine-agnostic map.

| Product | Type | Repo |
|---|---|---|
| Blocksify | Free block plugin | https://github.com/PressMaximum/blocksify |
| Blocksify Pro | Pro plugin — extends Blocksify via its hook surface (Blocksify `docs/FOUNDATION.md` §8) | https://github.com/PressMaximum/blocksify-pro |
| Customify | Free theme ← **this repo** | https://github.com/PressMaximum/customify |
| Customify Pro | Pro companion **plugin** for the Customify theme (a plugin, not a theme) | https://github.com/PressMaximum/customify-pro |
| PressListing | Premium standalone plugin for listing sites (tours, jobs, real estate, directories, …) | https://github.com/PressMaximum/PressListing |

Shared infrastructure (not a sellable product): `@pressmaximum/dashboard-kit` — the
admin-dashboard kit consumed by the products' dashboards — https://github.com/PressMaximum/dashboard-kit

- **Branch convention: PRs target the repo's active dev branch — standard name is `dev`.**
  Not yet normalized everywhere: Customify theme uses `DEV`, Customify Pro uses `Dev`,
  PressListing develops on `main`.
- Local checkout paths + Studio dev sites are machine-specific — NEVER commit them into
  this file. Each dev keeps their own map in `~/.claude/CLAUDE.md` (user-level, per
  machine) or a gitignored `CLAUDE.local.md`.
