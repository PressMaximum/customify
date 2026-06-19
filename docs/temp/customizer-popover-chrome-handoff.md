# Customizer Popover Chrome — Phase 1 (Typography) done → Phase 2 (Styling) handoff

**Audience:** a fresh agent session with NO memory of this implementation work.
**Goal:** pick up **Phase 2 — convert the `styling` control to the new
trigger + popover chrome** (per-tab triggers with value previews), building on
the Phase 1 typography work that is sitting UNCOMMITTED in this worktree.

> Phase 1 is functionally complete and user-acceptance-passed ("tạm ổn").
> Everything is verified on a real site but **not committed yet** — committing
> (in grouped commits, after a final user nod) is the first task of the next
> session, BEFORE starting Phase 2 work.

---

## 0. How to use this document

1. Do the **Required reading** (§1) first.
2. Read **What was built** (§3–§5) — Phase 2 reuses all of it.
3. Internalize **Gotchas & playbook** (§6) — every one of these cost real
   debugging time this session.
4. Then execute **Next steps** (§7): commit Phase 1, then Phase 2.

Work happens in the worktree branch **`claude/eager-merkle-ab2cd0`**.
The user speaks Vietnamese in chat; ALL code/comments/docs stay English
(AGENTS.md §4.5).

---

## 1. Required reading (do not skip)

| Read | Why |
|---|---|
| [`AGENTS.md`](../../AGENTS.md) | 30k-site safety doctrine (§4.1), never-rename (§4.2), CSS handle (§4.7), LF-only (§4.14) |
| [`docs/SPEC-customizer.md`](../SPEC-customizer.md) §5–§10 | Control types catalog, the 3 JS contexts, config→control→JS bridge, repeater/modal |
| [`docs/SPEC-typography.md`](../SPEC-typography.md) | Typography pipeline: 8 foundation tokens vs leaf/literal emit, storage shape §3.1 |
| `inc/customizer/configs/typography.php` | The Phase-1 config patterns you will mirror for styling (`display_defaults`, comments) |
| `src/backend/customizer/js/typography-control.js` | The popover chrome runtime Phase 2 generalizes |
| `src/backend/customizer/js/control.js` → `customifyStyling` (~line 3050) + `customifyModal` (~line 2800) | The two ~95%-duplicate runtimes Phase 2 touches |
| Project memory (auto-loaded): `typography-control-known-bugs`, `background-tab-raf-suspended`, `prefer-static-config-over-runtime-probing`, `playground-db-rollback`, `worktree-frontend-build-workaround`, `read-first-task-comes-after` | Hard-won lessons; several directly constrain how you work |

---

## 2. Mission recap (user-approved design)

Modernize the Customizer composite controls to an **Astra-style chrome**:
a select-like **trigger row showing live value previews**, opening a
**floating popover** (overlay, not inline accordion). Strictly **chrome-level**:
setting names, value shapes, sanitize, auto-CSS emit are public API for 30k+
sites and must not change.

Three control types share the legacy popover chrome (template
`tmpl-customify-modal-settings`, `.action--edit` / `.action--reset` buttons):

| Type | Runtime | Theme fields | Pro fields |
|---|---|---|---|
| `typography` | `FontSelector` (typography-control.js) — **Phase 1, DONE** | 15 | 60 |
| `styling` | `customifyStyling` (control.js) — **Phase 2, NEXT** | 23 | 123 |
| `modal` | `customifyModal` (control.js) — Phase 3 | 7 | 15 |

Pro needs **zero code changes** — control classes + JS runtimes live in the
theme; Pro only supplies config.

---

## 3. Session state — what exists right now

### 3.1 Worktree (UNCOMMITTED — 10 modified files, LF-clean)

| File | Changes |
|---|---|
| `inc/customizer/controls/class-control-typography.php` | Trigger anchor (`.action--edit customify-typo-trigger` + preview spans) replaces the pencil; dead `#customify-typography-panel` markup removed |
| `inc/customizer/controls/class-control-slider.php` | Multi-unit template branch (mini `<select>` + lossless unknown-unit option), per-device placeholder resolve, placeholder-derived initial unit when value empty |
| `inc/customizer/controls/class-control-base.php` | `$display_defaults` property + json; `Customify_Control_Args` strings: `inherit`, `default_label` |
| `inc/customizer/class-customizer.php` | `get_typo_fields()`: `units` config (font_size px/em/rem, line_height px/em/`-`, letter_spacing px/em) with per-unit ranges; px ranges widened 9–80 → 1–120 |
| `inc/customizer/class-customizer-auto-css.php` | `setup_slider()`: `'-'` unitless sentinel → emits bare number |
| `inc/customizer/configs/typography.php` | `display_defaults` on all 11 panel fields (lockstep comments → `_base.scss` / `_logo_site_identity.scss` / `_widgets.scss`); Widget Title moved to end of Content group |
| `src/backend/customizer/js/auto-css.js` | `setup_slider()` mirror: `'-'` sentinel |
| `src/backend/customizer/js/control.js` | `getFieldValue` slider reads `select OR :checked radio` unit; `initSlider`: handle seeding from placeholder, unit-select change → re-range + ×16 conversion + clamp, reset re-derives unit from placeholder; `refreshFromSetting` fires `customify/control/refreshed` |
| `src/backend/customizer/js/typography-control.js` | Trigger summary renderer (+ display-default fallbacks, Inherit/Default label rules, weight slot gated), popover lifecycle (open/close/position/dismiss, one-at-a-time manager, `is-above` flip), select2 `dropdownParent` (old body-attach + rAF hack DELETED) |
| `src/backend/customizer/scss/_control.scss` | Trigger styles; popover overlay (scoped to typography control only — styling/modal accordions untouched); flex value row; 136px control column; full-width Font Family; select2 pinning; 2px radius language |

Suggested commit grouping (get the user's final nod first):
1. `feat(customizer): typography trigger + floating popover chrome`
2. `feat(customizer): display-only typography defaults (trigger preview + placeholders)`
3. `feat(customizer): multi-unit typography sliders (px/em/rem, unitless line-height)`
4. `style(customizer): typography popover form polish`

### 3.2 Synced to main checkout (for the running test site)

The Studio site serves the MAIN checkout
(`/Users/kientrong/Studio/free-theme-customify/wp-content/themes/customify`),
NOT the worktree. All changed PHP + built assets are already copied there.
After merge these copies become redundant (see `worktree-frontend-build-workaround`
memory for the safe cleanup procedure).

### 3.3 Test site

- **Customify-Free** — http://customify-free.wp.local (Studio, playground
  runtime), wp-admin `admin` / see `site_list`. Customizer tab usually open at
  `?autofocus[section]=typography_panel`.
- Site is clean: no typography mods saved, no pending changesets.

---

## 4. Phase-1 architecture (what Phase 2 reuses)

### 4.1 Trigger

PHP template renders an `<a class="action--edit customify-typo-trigger">` with
`.customify-trigger--family` / `--meta` / `--arrow` spans. The legacy delegated
click handler keeps working because the `action--edit` class is preserved.
Summary is rendered ONLY by JS (`renderTypoTrigger`) — single source of truth:

- initial paint: end of `intTypos()` (controls are batch-inited at
  document.ready in control.js BEFORE `intTypos()` runs);
- live updates: delegated `change data-change` on `.customify-typography-input`;
- programmatic repaints: `customify/control/refreshed` (fired by
  `refreshFromSetting`).

Display rules: family = saved font, else `Inherit` if the field gates `font`
off, else `Default`. Meta = `size / weight`; the weight slot renders ONLY when
the field offers a weight control (h1–h6 gate it → no slot); each part falls
back to `display_defaults`.

### 4.2 Popover

`.customify-modal-settings` is absolutely positioned inside the control `li`,
class-driven (`is-open`), CSS transition 180ms. Key methods on the runtime
object: `openPopover` / `closePopover` / `positionPopover` (anchors under the
trigger, flips above via `is-above` when viewport space is short) /
`bindDismiss` / `unbindDismiss`. Dismissal: capture-phase `mousedown` outside
the li (select2 nodes excluded), capture-phase ESC (`stopPropagation` so the
Customizer doesn't also collapse the section), and **window `blur`** (clicks
inside the preview iframe never reach the controls document — the blur is the
only signal). One-popover-at-a-time via a module-scoped `activePopover` ref.

**The transition kick uses a forced synchronous reflow
(`void el.offsetWidth`) — NEVER requestAnimationFrame** (rAF does not fire in
hidden tabs; the class lands late and resurrects dismissed popovers — see
`background-tab-raf-suspended` memory).

### 4.3 Display defaults (display-only metadata)

`'display_defaults' => array( sub_field => string | {desktop,tablet,mobile} )`
on the field config → control json → placeholders + trigger fallbacks + slider
handle seeding (`seedHandle()` — programmatic `.slider("value")` fires no
`slide`, so nothing is saved). Values mirror the literal compiled SCSS
fallbacks; the lockstep comments in `typography.php` say where each came from.
**They are never written to settings and never reach the CSS generator** —
verified end-to-end (§5).

### 4.4 Multi-unit sliders

`units` in the field config opts a slider into the mini unit `<select>`
(legacy single-px radio markup is byte-identical when `units` is absent —
container_width etc. untouched). Read path accepts select OR checked radio.
Saved units always round-trip losslessly (unknown units render as their own
selected option). Switching units converts the number (px↔em/rem/`-` at ×16),
rounds (px integer, others 2dp), clamps to the unit's range. Empty value +
placeholder → the template/reset derive the INITIAL unit from the placeholder
suffix (`2.42em` → em, bare `1.216` → `-`), so the seeded handle sits on the
right scale. `'-'` is the unitless sentinel: `setup_slider()` (PHP **and** the
auto-css.js mirror — keep in lockstep) maps it to `''` at emit.

### 4.5 Select2 font picker pinning (3-layer fix — don't regress)

1. `dropdownParent` = the font row's `.customify-field-settings-inner`
   (body-attached popups drift; the old rAF reposition hack was deleted).
2. That box needs `display: flow-root` (it holds a floated widget; collapsed
   height made `top:100%` meaningless) — for the font row it's full-width.
3. The popup wrapper (`> .select2-container--open:last-child` — safe selector:
   the visible widget is never `:last-child` while open) is pinned with
   `position:absolute; top:100%; left:0; right:0 !important`, and the inner
   `.select2-dropdown` is forced `position:static; width:100% !important`
   (beats Select2's inline coords AND the legacy
   `width:200px !important` rule in `customizer.scss`). The legacy arrow skin
   in `customizer.scss` paints `background:#fff url(16px chevron)` — its
   background-COLOR leaks through shorthand; the popover scope neutralizes it.

### 4.6 Layout language (popover form)

Flex value row `[slider ~10px gap~ input 68][unit 34][reset 26]` = a 136px
control column; native selects match it (width 136, height 28, 2px radius,
8px SVG caret, labels `calc(100% - 142px)` so rows never wrap). Font Family
stacks label-above + full-width picker + full-width dropdown. Labels 500/12px.
Everything (trigger included) uses **2px border-radius**.

---

## 5. Data-safety doctrine (proven; repeat for Phase 2)

What was PROVEN this session, with the method to reuse:

1. **Baseline md5**: seed a value via UI, publish, capture
   `md5(serialize(get_theme_mod(...)))` via WP-CLI; after unrelated operations
   the md5 must be byte-identical.
2. **Emit checks**: `curl` the frontend and grep the emitted CSS
   (`--customify-typo-h1-font-size: 1.5em`, unitless `1.4`, untouched `30px`).
3. **Empty values save as `{unit, value:""}` and emit nothing** (pre-existing
   shape; display defaults never leak into it — assert via the hidden input
   JSON after a forced `get()`).
4. Sanitize (`sanitize_slider` → `sanitize_text_field`) passes `em/rem/-`
   unchanged; typography object goes through `sanitize_text_field_deep`.
5. The only `remove_theme_mod` caller in the theme is the section-reset AJAX.

⚠️ Mid-session a "published data vanished" scare consumed an hour of
forensics — the cause was **the user clicking the section reset (⟲) while
testing in parallel** (`customify__reset_section` removes every section mod
server-side instantly, no publish involved). Check for that FIRST next time
(see `playground-db-rollback` memory). The user tests in the same browser/
site while you work — expect concurrent state changes.

---

## 6. Gotchas & verification playbook

- **Measure, don't eyeball.** The user explicitly demanded careful visual
  checks after a misaligned-by-eye approval. Assert geometry with
  `getBoundingClientRect` deltas (right-edge alignment ≤1px, same-line top
  diff 0, gap widths). Screenshots are scaled and the shared browser's zoom
  CHANGES between turns — absolute pixel values are not comparable across
  calls; only intra-call relative numbers are.
- **Cache busting**: assets enqueue with a static `?ver=` — after every sync,
  in-page run
  `fetch('<asset url>', {cache:'reload'}).then(()=>location.reload())`, and
  first `jQuery(window).off('beforeunload'); window.onbeforeunload=null;`
  (the Customizer blocks navigation when dirty; the chrome extension can't
  dismiss the native dialog).
- **Build (worktree)**: full `npm run build` dies on dashboard-kit. Use a
  throwaway config spreading `webpack.config.js` with ONLY the needed entries
  and **`output: {clean:false}`** (MANDATORY — see memory), e.g. entries
  `backend/customizer/control`, `backend/customizer/customizer` (CSS),
  `backend/customizer/auto-css`. Then copy changed PHP + `build/js/backend/customizer/*`
  + `build/css/backend/customizer/customizer{,-rtl}.css` into the main checkout.
- **First click after reload often no-ops** (controls not wired yet) — wait
  ~14s after `location.reload()` before interacting, and re-click if state
  didn't change.
- **Native `<select>` dropdowns** don't respond to synthesized arrow keys via
  the extension — drive unit/select changes with
  `jQuery(el).val(x).trigger('change')` (same event path as a user change).
- **Known deferred bugs — do NOT fix without an exact repro** (user's
  explicit instruction; see `typography-control-known-bugs` memory): Reset
  duplicates the font list (`optionHtml +=` accumulation), fast first click
  can leave the family dropdown empty, per-open catalogue re-fetch perf gap.
- **No hidden DOM probes / runtime measurement for display metadata** — the
  user rejected that approach; declare static config + lockstep comment
  (`prefer-static-config-over-runtime-probing` memory).
- JS assertion snippets that worked well: open popover via
  `jQuery('.customify-typo-trigger', li).trigger('click')`; read saved JSON via
  `JSON.parse(li.querySelector('.customify-typography-input').value)`; check
  `wp.customize.state('saved').get()` after clicking Publish (button top-left).

---

## 7. Next steps

### 7.0 Commit Phase 1 (first!)

Confirm with the user, commit the 10 files in the groups from §3.1 (English
messages, `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`), then
update `docs/SPEC-typography.md` (§3.1 unit values incl. `-` sentinel, the
`display_defaults` key, the chrome) + `docs/SPEC-customizer.md` §5.3 — the
user agreed docs can land once, covering all phases, but don't let them rot
past Phase 2.

### 7.1 Phase 2 — styling control (agreed design)

Convert `styling` (and structurally prepare `modal`) to the chrome:

1. **One trigger row per TAB** — e.g. Button Styling renders two rows:
   `Normal` and `Hover`. Tab keys are NOT fixed: theme/Pro configs use
   `normal/hover`, `default/hover`, `display/advanced`, `titles/taglines`,
   and single-tab `array('normal' => '_')` (render ONE trigger; label falls
   back to the control title). Iterate `tabs` generically.
2. **Trigger preview**: color swatches (text/link/bg/border colors from that
   tab's saved values) + a compact value summary; reuse the typography trigger
   DOM/classes (`customify-trigger--*`) and the `customify/control/refreshed`
   + hidden-input change re-render wiring (`.customify-hidden-modal-input`).
3. **Popover per tab**: clicking a tab's trigger opens the shared popover
   showing ONLY that tab's fields (`{key}_fields`); the in-popover tab bar is
   removed. Storage unchanged: one hidden input, full JSON
   `{normal:{...}, hover:{...}}` — `get()` must still serialize ALL tabs even
   when only one is rendered… **decide carefully**: either render all tab
   contents (hidden) so the existing `get()` works untouched, or rework
   `get()` to merge the open tab into the last-known JSON. Prefer the first
   (zero value-plumbing risk).
4. **Unify `customifyModal` + `customifyStyling`** (~95% duplicate) into one
   parameterized runtime IF it stays low-risk; otherwise duplicate the chrome
   first and unify in Phase 3. The popover lifecycle methods in
   typography-control.js are the template — consider extracting them into a
   tiny shared factory (`popover-chrome.js`) rather than copy #3.
5. **Scope CSS carefully**: Phase-1 popover CSS is scoped to
   `.customize-control-customify-typography` precisely so styling/modal
   accordions still work. Phase 2 extends the scope to
   `.customize-control-customify-styling` (and later `-modal`) only when that
   control actually gets the new chrome.
6. **Verify like Phase 1**: geometry assertions, md5 data baselines on a
   `customify_button_styling`-style mod, Pro field smoke test (e.g. transparent
   header / sticky header styling fields), legacy controls untouched check.

### 7.2 Parked / later

- Phase 3: `modal` control + runtime unification cleanup.
- Deferred typography bugs (need exact repro first).
- `docs/SPEC-*` updates if not done in §7.0.
- Possible follow-up: trigger styles for `control--bg` variant controls
  (only relevant once styling controls adopt the chrome — they use it).

---

## 8. Working agreement with the user (important)

- Vietnamese in chat; English in every file.
- "Đọc X để <goal>" = read + report, WAIT for the scoped task.
- Propose → get the nod → implement; for UI, verify with NUMBERS and a
  screenshot before claiming done.
- Data saved by users is sacred — prove safety, don't assert it.
- The user tests in parallel in the same browser/site; coordinate around it.
