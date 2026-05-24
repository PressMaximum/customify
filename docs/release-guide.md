# Release Guide

How to ship a new version of Customify — version bump points, pre-flight checks, the Grunt release pipeline, distribution to GitHub Releases + WordPress.org, post-release verification, rollback.

For the rules governing what kinds of changes are safe to release, see [`../AGENTS.md`](../AGENTS.md). For storage migrations, see [`migration-guide.md`](migration-guide.md). For Pro plugin coordination, see [`SPEC-pro-integration.md`](SPEC-pro-integration.md) §11.

---

## 1. Overview

Customify ships through two distribution channels:

| Channel | How |
|---|---|
| **GitHub Releases** | Automated by `grunt release` — bumps version, builds, zips, tags, creates release with `gh release create` |
| **WordPress.org theme repository** | Manual SVN push — must update `readme.txt` `Stable tag` and upload the GitHub-produced zip |

The release pipeline is owned by [`Gruntfile.js`](../Gruntfile.js). npm scripts own the JS/SCSS/POT build (`npm run build`, `npm run makepot`); Grunt owns version bumping, header sync, staging, zipping, git tagging, and GitHub release publishing.

| Surface | Owner |
|---|---|
| JS / SCSS bundles | `npm run build` (via wp-scripts) |
| `.pot` translation template | `npm run makepot` |
| Version bumping + header sync | `grunt bumpup` + `grunt sync_style_css` (subtasks of `grunt release`) |
| Zip staging | `grunt stage:prepare` (reads `vendor/composer/installed.json` to strip non-runtime files) |
| Git tag + GitHub release | `grunt release` final steps + `gh release create` |
| WP.org SVN push | **Manual** — see §6 |

---

## 2. Release types

Four entry points, picked by what you're shipping:

| Command | Purpose | When to use |
|---|---|---|
| `grunt release [--ver=<x.y.z>]` | Production release — bump → build → zip → tag → push → `gh release create --latest` | Shipping a real version to all users |
| `grunt beta-release [--ver=<x.y.z-beta.N>]` | Pre-release — auto-bumps `-beta.N` or starts a new beta cycle; publishes with `--prerelease` (GitHub does NOT mark latest) | Testing a release candidate with select users |
| `grunt build-zip [--ver=...]` | Same build pipeline as `release` but stops after producing the zip. No preflight, no commit/tag/push, no GitHub Release | Local QA, customer hand-off, staging upload |
| `grunt zipfile` | Stage and zip whatever `build/` + `vendor/` already contain. No rebuild, no composer install | Re-zipping after a Grunt run (no need to re-build) |

All commands accept `--no-publish` to stop before publishing to GitHub (still produces the zip).

### 2.1 `--ver` behavior

- Pass `--ver=0.4.16` to set an exact version
- Pass `--ver=patch` / `--ver=minor` / `--ver=major` for semver bumps
- Omit `--ver` → release uses whatever version is in `package.json` (assumes dev bumped manually first)

For beta: omit `--ver` → auto-derives next `-beta.N` if already on a beta, otherwise patch-bumps stable and appends `-beta.1`.

---

## 3. Version bump points

When the version changes, these files must update in lockstep:

| File | Field | Auto-bumped by Grunt? |
|---|---|---|
| `style.css` | `Version:` header | ✓ (by `replace:theme_main` — canonical theme version) |
| `composer.lock` | (composer install regenerates) | ✓ (by `composer install` step) |
| `languages/customify.pot` | metadata | ✓ (by `npm run makepot`) |
| `package.json` | `"version"` | ✗ **NOT modified** — `set-version` only writes in-memory |
| **`readme.txt`** | **`Stable tag:`** | ✗ **MANUAL** — see Issue #1 |
| `build/` | (rebuilt locally; NOT committed — see note below) | ✓ (by `npm run release:assets`) |

Grunt commits this exact file list: `style.css languages/customify.pot composer.lock`.

`build/` is .gitignored (commit `c1a725ac` — "stop tracking build/ folder"). It is rebuilt locally before staging, packed into the release zip, and shipped through the GitHub Release asset — but NOT pushed to the repo.

`package.json` is intentionally left untouched. style.css is the canonical theme version (WP reads from there); the `set-version` Grunt task writes the target version to `pkg.version` in memory so style.css and the archive filename pick it up, without dirtying `package.json` on disk. If you want the npm `package.json` version to track the theme, bump it manually.

**`readme.txt` is NOT in the commit list.** Currently at `Stable tag: 0.3.7` (severely stale — actual version is 0.4.15-beta.2). For WordPress.org distribution this MUST be updated to the released version BEFORE running `grunt release`. See [§9 Known issues](#9-known-issues--gotchas).

---

## 4. Pre-flight checklist

Run through this BEFORE invoking any release command. The pre-flight in `grunt release` enforces the first two; the rest are on you.

- [ ] **Clean working tree** — `git status` shows no uncommitted changes (enforced)
- [ ] **`gh auth status`** returns authenticated (enforced)
- [ ] **On the right branch** — typically `DEV` for beta, merged to `master` for production
- [ ] **`master` is up to date** with `origin/master` (for production releases)
- [ ] **Verification scenarios A / B / C pass** for any change touching CSS output or storage shape — see [`migration-guide.md`](migration-guide.md) §5.2
- [ ] **`changelog.txt` updated** — entry exists for the version being released (this file is read by the dashboard Changelog tab)
- [ ] **`readme.txt` `Stable tag` updated** — match the version being released (required for WP.org; see §6)
- [ ] **`readme.txt` `== Changelog ==` updated** — if WP.org is in scope, add matching entry
- [ ] **Pro coordination done** — if any shared key, filter, action, or REST namespace changed. See [`SPEC-pro-integration.md`](SPEC-pro-integration.md) §11
- [ ] **Storage migrations verified** — if any new migration ships, tested idempotency (running twice = no corruption)
- [ ] **`tested up to` headers refreshed** in `style.css` and `readme.txt` if WP minor version bumped
- [ ] **Pro version in lockstep** — if Pro plugin version bumped, both `customify-pro.php` header AND `readme.txt` Changelog entry exist. See [`SPEC-dashboard.md`](SPEC-dashboard.md) §10.7 — known root cause of 0.4.13 → 0.4.16 mismatch

---

## 5. The release pipeline in detail

What `grunt release [--ver=<x.y.z>]` does, step by step:

```
1. Pre-flight
   ├── Clean working tree check  (fails if dirty)
   └── gh auth status check       (fails if not authenticated)

2. Set version (only if --ver passed; otherwise uses style.css Version: as-is)
   ├── set-version:<x.y.z>        (in-memory grunt.config('pkg.version') — NO file write)
   └── replace:theme_main         (rewrites style.css "Version: <x.y.z>" header)

3. Vendor install
   └── composer install --no-dev --optimize-autoloader

4. Build assets
   └── npm run release:assets     (= npm run build + npm run makepot)
       ├── webpack production build
       │   └── emits both unminified + .min.* siblings (per EmitMinifiedAssetsPlugin)
       ├── source maps only in development mode (NOT in this run)
       └── wp i18n make-pot → languages/customify.pot

5. Stage
   ├── stage:prepare              (reads vendor/composer/installed.json,
   │                               injects copy patterns that strip each
   │                               dep down to autoload paths + LICENSE)
   └── copy theme files to release-staging/customify/
       (excluding node_modules/, src/, .git/, etc. per EXCLUDE_PATTERNS)

6. Zip
   └── release-staging/customify-<x.y.z>.zip

7. Commit + tag + push
   ├── git add style.css languages/customify.pot composer.lock
   ├── git commit -m "Release version <x.y.z>"
   ├── git tag <x.y.z>
   ├── git push origin HEAD
   └── git push origin <x.y.z>

8. GitHub release
   └── gh release create <x.y.z> release-staging/customify-<x.y.z>.zip --latest
       (uses --prerelease instead of --latest for beta-release)
```

Pass `--no-publish` to stop after step 6 (zip exists, nothing pushed).

### 5.1 Idempotency

The `git commit` in step 7 is gated — if no files changed (re-running release with the same version + no asset diff), the commit is skipped. The git tag is created unconditionally; re-running with the same version will fail at the tag step if the tag exists.

---

## 6. WordPress.org SVN distribution

The Grunt pipeline does NOT push to WP.org. Manual workflow after the GitHub release publishes:

### 6.1 Pre-push checklist

- [ ] `readme.txt` `Stable tag:` matches the release version
- [ ] `readme.txt` `== Changelog ==` section has the new version
- [ ] `Tested up to:` headers current (both `style.css` and `readme.txt`)
- [ ] GitHub release zip downloaded for upload

### 6.2 SVN push procedure

```bash
# 1. Check out the WP.org SVN repo (one-time setup)
svn co https://themes.svn.wordpress.org/customify customify-svn

# 2. Sync the new release into trunk/
cd customify-svn
rsync -av --delete \
  --exclude='.svn' \
  --exclude='.git' \
  /path/to/release-staging/customify/ trunk/

# 3. Tag the version
svn cp trunk tags/<x.y.z>

# 4. Commit
svn add trunk/* tags/<x.y.z>/*  --force
svn ci -m "Release <x.y.z>"
```

WP.org review typically takes a few hours to several days. The release isn't live to wp.org users until reviewed and approved.

### 6.3 What WP.org gates on

- License compatibility (`License: GPLv2 or later` in `readme.txt`)
- No external HTTP requests on activation
- No phone-home / analytics
- All assets bundled (no CDN deps)
- Internationalization (POT file present, strings use `__()` / `_e()`)
- Theme review automation can flag issues — fix before re-submitting

---

## 7. Post-release verification

Within 1 hour of `grunt release` completing:

- [ ] GitHub release page shows the new version as latest (or pre-release for beta)
- [ ] Release zip is attached and downloadable
- [ ] Zip filename matches `customify-<x.y.z>.zip`
- [ ] Download the zip + install on a fresh WordPress site → activates cleanly
- [ ] No PHP errors on activation (check `WP_DEBUG_LOG`)
- [ ] Customizer loads, builders load, dashboard SPA loads
- [ ] If shared keys/filters changed, install Customify Pro on the test site → no Pro errors
- [ ] Smoke-test the most recent feature shipped in this release

For production releases (not beta) additionally:

- [ ] Update the customer-facing changelog / blog post / release notes
- [ ] After WP.org review passes, verify the wp.org page shows the new version

---

## 8. Rollback procedure

If a release breaks something serious:

### 8.1 If shipped less than ~1 hour ago AND no users have downloaded yet

```bash
# Delete the GitHub release
gh release delete <x.y.z> --yes

# Delete the git tag locally + remote
git tag -d <x.y.z>
git push origin :refs/tags/<x.y.z>

# Revert the release commit
git revert HEAD                  # creates a revert commit
git push origin <branch>
```

### 8.2 If users have downloaded — ship a patch release instead

Don't try to retract; ship `<x.y.(z+1)>` with the fix. Communicate the issue + fix in:

- GitHub release notes for the patch
- `changelog.txt` entry under the patch version
- Pro repo if the issue was Pro-side

For WP.org: the existing version remains until you push a new SVN trunk + tag. WP.org does NOT support deletion of published versions.

### 8.3 Reset version files for a retry

If you ran `grunt release --ver=0.4.16` and want to roll back BEFORE pushing:

```bash
git reset --hard HEAD~1          # undo the release commit
git tag -d 0.4.16                # remove local tag (if not pushed)
# package.json, style.css, build/, customify.pot all reset to pre-release state
```

⚠️ Destructive — only use if the release commit hasn't been pushed yet.

---

## 9. Known issues / gotchas

### Issue #1 — `readme.txt` `Stable tag` drift

`readme.txt:9` is currently `Stable tag: 0.3.7`. Actual current version is **0.4.15-beta.2**. The file has not been updated through several releases.

**Impact**: WP.org SVN push would use stale `Stable tag`, breaking wp.org's automatic version detection. Users on wp.org would not see new versions in the update notification.

**Fix**: Update `readme.txt` `Stable tag` to match `style.css` Version before each production release. Consider adding the file to the Grunt `sync_style_css` task to auto-update.

### Issue #2 — `readme.txt` not in commit list

`grunt release` commits `style.css languages/customify.pot composer.lock` — **does NOT include `readme.txt`, `package.json`** (set-version is in-memory only), or `build/` (.gitignored). Manual `git add readme.txt && git commit --amend --no-edit` if the file changed in the same release. Or commit it as a separate commit before running `grunt release`.

### Issue #3 — `vendor/` is intentionally included

The `release-staging/` zip includes `vendor/` because [`functions.php`](../functions.php) loads `vendor/autoload.php` at runtime to access `pressmaximum/dashboard-kit` PHP. `stage:prepare` strips each composer package down to autoload paths + LICENSE — no per-package hand-maintenance needed.

If a new composer dep is added, run `grunt stage:prepare` (or `grunt release --no-publish`) to verify the package declares everything it needs in `autoload`. Issues here = runtime fatals on customer sites.

### Issue #4 — `.min` + unminified emitted side-by-side

`npm run release:assets` produces BOTH `*.js` and `*.min.js` (same for CSS), per the `EmitMinifiedAssetsPlugin`. PHP enqueue picks one via `Customify::get_asset_suffix()` — `.min` when `WP_DEBUG` is off, source when on. Both files ship in the release zip — increases zip size slightly but enables on-site debugging.

### Issue #5 — Source maps in production

Source maps are emitted only in dev mode (`npm start`). `npm run build` / `npm run release:assets` do NOT emit `.map` files. If a customer reports a JS error and you need source maps, rebuild locally with `npm start` against the same commit.

### Issue #6 — Beta tag confusion

`grunt beta-release` without `--ver` auto-bumps:
- Already on `0.4.16-beta.1` → bumps to `0.4.16-beta.2`
- Currently `0.4.15` (stable) → produces `0.4.16-beta.1`

Don't manually edit `package.json` to `0.4.16-beta.N` and then run `grunt beta-release` — it'll bump again to `-beta.(N+1)`. Either pass `--ver=0.4.16-beta.N` explicitly OR rely on auto-derivation from clean state.

### Issue #7 — Pro version header vs `readme.txt`

For the Customify Pro plugin: bumping the version in `customify-pro.php` header WITHOUT adding a matching `readme.txt` `== Changelog ==` entry causes the dashboard's Changelog tab to show a version that doesn't have changelog content. Root cause of 0.4.13 → 0.4.16 mismatch (fixed in commit `aed3e8c`). See [`SPEC-dashboard.md`](SPEC-dashboard.md) §10.7.

### Issue #8 — `tested up to` headers go stale

Two places to update when a WP minor release ships:

- `style.css` `Tested up to: 6.7.1`
- `readme.txt` `Tested up to: 6.7.1`

WP.org review will warn (not fail) if `Tested up to` lags by more than 2 versions. WP.org also displays a "may not work with your version" warning to users when the header is older than the current major.

---

## 10. Quick reference

| I want to… | Command |
|---|---|
| Ship a patch release | `grunt release --ver=patch` |
| Ship a minor release | `grunt release --ver=minor` |
| Ship an exact version | `grunt release --ver=0.4.16` |
| Use the version already in `package.json` | `grunt release` (no `--ver`) |
| Ship a beta | `grunt beta-release` (auto-bumps) or `grunt beta-release --ver=0.4.16-beta.3` |
| Build a zip for local QA / customer | `grunt build-zip --ver=0.4.16` |
| Re-zip after a previous Grunt run | `grunt zipfile` |
| Stop before publishing | Add `--no-publish` to any command |
| Verify staging output | Open `release-staging/customify/` after `--no-publish` |

---

## 11. Where to look next

**Pipeline files**
- [`Gruntfile.js`](../Gruntfile.js) — source of truth for the release pipeline
- [`package.json`](../package.json) — `release:assets`, `build`, `makepot` scripts
- [`composer.json`](../composer.json) — runtime PHP deps
- [`webpack.config.js`](../webpack.config.js) — production bundle config

**Version touchpoints**
- [`style.css`](../style.css) — theme header (auto-synced)
- [`readme.txt`](../readme.txt) — WP.org metadata (MANUAL update needed)
- [`changelog.txt`](../changelog.txt) — dashboard-rendered changelog
- [`languages/customify.pot`](../languages/customify.pot) — translation template (auto-regenerated)

**Related guides**
- [`migration-guide.md`](migration-guide.md) — must complete before releasing storage changes
- [`SPEC-pro-integration.md`](SPEC-pro-integration.md) §11 — Pro release coordination
- [`SPEC-dashboard.md`](SPEC-dashboard.md) §10.7 — Pro version mismatch root cause
- [`SPEC-asset-pipeline.md`](SPEC-asset-pipeline.md) — build pipeline that the release wraps

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) §4.1 — 30k-site rule (relevant for every release)
