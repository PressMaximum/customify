# Palette Switcher — QA / Verification Handoff

**Audience:** a fresh agent session with NO memory of the implementation work.
**Goal:** independently **review the code** AND **run the full test-case suite**
to confirm the new "color palette switcher" is correct and — above all —
**safe for the 30,000 live sites** running this theme.

> This is a verification handoff, not an implementation task. Do **not** rewrite
> the feature. Review it, test it, and report findings. Only make code changes if
> a test fails and the fix is small + obviously correct (and re-test after).

---

## 0. How to use this document

1. Do the **Required reading** (§1) first — especially the 30K-safety doctrine.
2. Skim **Key facts** (§3) — it is the cheat-sheet for every test below.
3. Do **Part A — Code review** (§5) — read the files, tick the invariants.
4. Do **Part B — Functional tests** (§6) — TC1…TC13, in order, on a real browser.
5. Fill in the **Results table** (§8) and report. Restore the site (§4.4) when done.

Work on branch **`claude/romantic-lamarr-d4f2fe`** (already checked out in this
worktree). The feature is committed at **`ee685a0a`**
(`feat(colors): palette switcher in Customizer Colors section`).

---

## 1. Required reading (do not skip)

| Read | Why |
|---|---|
| [`AGENTS.md`](../../AGENTS.md) | Hard rules: **30k-site safety**, never-rename keys, English-only, AJAX, CSS handle. |
| [`CLAUDE.md`](../../CLAUDE.md) | Claude-specific workflow + "when about to change storage shape, STOP". |
| [`docs/SPEC-customizer-colors.md`](../SPEC-customizer-colors.md) **§2.4** (30K doctrine), **§3.1–3.2** (slots + derived tokens), **§8.13** (this feature) | The contract this feature must honor. §8.13 is the authoritative description of what was built. |
| [`docs/migration-guide.md`](../migration-guide.md) §2 | Storage-change decision tree (this feature is additive — confirm it really is). |

**30K-safety doctrine, distilled (memorize this — it is the pass/fail spine):**

- **Additive only.** New behavior may add new `theme_mod` keys; it must **never
  rename, delete, or change the meaning** of an existing key.
- **Never change what a SAVED site renders.** A site that already saved colors
  must render byte-identical hex after this change. Only *fresh-install
  defaults* may shift, and only if documented.
- **Never silently dirty a clean Customizer.** Opening the Customizer on a saved
  site must not create "unsaved changes" or write any setting on its own.

---

## 2. What was built (one-paragraph orientation)

A card-based **palette switcher** at the top of the Customizer **Colors**
section (`customify_colors`). The user clicks a palette card (preset **Sunrise**
/ **Midnight**, or a saved custom one) and it drives the **6 existing** slot
color-pickers in one shot. Apply = `wpColorPicker('color', hex)` on each picker
— the same path the existing quick-pick already used, so there is **no new
render path and no new rendered CSS token**. Two new bookkeeping `theme_mod`
keys were added; the 6 slots keep their existing keys.

**Files in the change (commit `ee685a0a`):**

| File | Role |
|---|---|
| `inc/color-palette-switcher.php` | **NEW** — the whole feature (presets, sanitizer, settings, inline JS, inline CSS). ~860 lines. |
| `inc/class-customify.php` | One-line `includes()` entry for the new file. |
| `inc/colors-palette.php` | link-hover cascade change + `CASCADE_MAP`/`resolveCascadeValue` + preview recompute debounce. |
| `inc/customizer/configs/colors.php` | link-hover field default `#406F99` → `#235787`. |
| `docs/SPEC-customizer-colors.md` | §8.13 documentation. |

---

## 3. Key facts & invariants (cheat-sheet for all tests)

**New theme_mod keys (the ONLY storage added):**

| Key | Type | Default | Notes |
|---|---|---|---|
| `customify_active_palette` | string | `''` | id of palette the slots match. **Bookkeeping only — nothing renders from it.** |
| `customify_color_palettes` | JSON string | `'[]'` | custom palettes: `[{id,name,slots:{6}}]`. |

**The 6 slot keys (existing — reused, NOT new):**

```
primary   → global_styling_color_primary     (legacy key)
secondary → global_styling_color_secondary    (legacy key)
accent    → customify_palette_accent
text      → customify_palette_text
surface   → customify_palette_surface
base      → customify_palette_base
```

**Presets (exact hex — used to assert “applied correctly”):**

```
Sunrise  (light)  base #ffffff  surface #ECECEC  text #2b2b2b  primary #235787  secondary #c3512f  accent #ffd042
Midnight (dark)   base #0f1217  surface #1a1e25  text #e8eaed  primary #5a8fc2  secondary #db6a44  accent #ffd042
```

Sunrise’s slot values are deliberately equal to the theme’s per-field picker
defaults → a fresh install reads as “Sunrise linked” without writing anything.

**Active-determination model (render(), three cases) — the 30K core:**

1. stored id matches current slots → that palette active + **linked**.
2. no stored id, but a preset/custom matches slots → adopt it; **persist the id
   ONLY if the customizer is already dirty** (`wp.customize.state('saved').get()===false`).
   On a clean open this never writes.
3. nothing matches → **no card active, no strip, no write** (legacy saved-custom case).

**link-hover default change:** field default `#235787`; cascade resolves
`link-hover → link → primary`. A saved link-hover override still wins.

**Value-format gotcha (you WILL hit this in probes):**
`wp.customize('global_styling_color_primary').get()` returns a **wrapped**
string like `"%22#5a8fc2%22"` after a picker write (URI-encoded JSON-quoted).
The **stored** theme_mod is **raw** hex (`#5a8fc2`). To compare, decode with
`JSON.parse(decodeURI(v))`. Both forms mean the same color.

---

## 4. Environment setup

### 4.1 Paths & URLs

| Thing | Value |
|---|---|
| Worktree (committed code lives here) | `…/wp-content/themes/customify/.claude/worktrees/romantic-lamarr-d4f2fe` |
| **Main checkout (the LIVE site loads theme from here)** | `/Users/kientrong/Studio/free-theme-customify/wp-content/themes/customify` |
| Front-end | `http://customify-free.wp.local/` |
| Customizer (Colors focused) | `http://customify-free.wp.local/wp-admin/customize.php?autofocus[section]=customify_colors` |
| wp-cli site path (`nameOrPath`) | `/Users/kientrong/Studio/free-theme-customify` |
| Colors section id | `customify_colors` |

### 4.2 Deploy the code-under-test to the live site

The browser tests the **main checkout**, not the worktree. Sync the one file
that matters (the others were already deployed) and flush:

```bash
cd /Users/kientrong/Studio/free-theme-customify/wp-content/themes/customify
cp .claude/worktrees/romantic-lamarr-d4f2fe/inc/color-palette-switcher.php inc/color-palette-switcher.php
cp .claude/worktrees/romantic-lamarr-d4f2fe/inc/colors-palette.php          inc/colors-palette.php
cp .claude/worktrees/romantic-lamarr-d4f2fe/inc/customizer/configs/colors.php inc/customizer/configs/colors.php
cp .claude/worktrees/romantic-lamarr-d4f2fe/inc/class-customify.php          inc/class-customify.php
php -l inc/color-palette-switcher.php   # expect: No syntax errors
```

> **Hygiene:** these copies are uncommitted changes on the main checkout’s
> branch (DEV). Do **not** `git commit` them on the main checkout. The source of
> truth is the worktree branch. When QA is done, restore the main checkout (§4.4).

### 4.3 Helper: set up / inspect / tear down theme_mods (wp-cli)

Use the `mcp__wordpress-studio__wp_cli` tool, `nameOrPath` =
`/Users/kientrong/Studio/free-theme-customify`.

```
# INSPECT current state
eval 'foreach (["global_styling_color_primary","global_styling_color_secondary","global_styling_color_link","global_styling_color_link_hover","customify_palette_text","customify_palette_surface","customify_palette_base","customify_palette_accent","customify_active_palette","customify_color_palettes"] as $k){ $v=get_theme_mod($k); echo $k."=".(is_string($v)?$v:json_encode($v))."\n"; }'

# SET a legacy "saved-custom" site (saved colors, NEVER used palettes) — used by TC2/TC4/TC12
eval 'set_theme_mod("global_styling_color_primary","#0066cc"); set_theme_mod("global_styling_color_secondary","#ff6600"); set_theme_mod("global_styling_color_link","#0066cc"); set_theme_mod("customify_palette_text","#222222"); remove_theme_mod("customify_active_palette"); remove_theme_mod("customify_color_palettes"); echo "saved-custom set\n";'

# RESET to fresh-default (all keys removed) — used by TC1
eval 'foreach (["global_styling_color_primary","global_styling_color_secondary","global_styling_color_link","global_styling_color_link_hover","customify_palette_text","customify_palette_surface","customify_palette_base","customify_palette_accent","customify_active_palette","customify_color_palettes"] as $k){ remove_theme_mod($k); } echo "reset to fresh default\n";'

# CLEAR customizer changesets + cache (do this between cases that leave the customizer dirty)
eval 'global $wpdb; $ids=$wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type=\"customize_changeset\""); foreach($ids as $id){ wp_delete_post($id,true); } echo "deleted=".count($ids)."\n"; wp_cache_flush();'
```

Always run `cache flush` (or the changeset-clear snippet) **after** changing
theme_mods via wp-cli and **before** loading the customizer, or you’ll read
stale state.

### 4.4 Teardown when QA is finished

1. Reset the site to fresh-default (the RESET snippet above) + clear changesets.
2. Restore the main checkout to clean **only if asked** (the owner is keeping the
   feature deployed for live review):
   `cd …/themes/customify && git checkout -- inc/ && rm -f inc/color-palette-switcher.php`
   (the file is untracked on DEV; the 4 edits revert via checkout).

---

## 5. Part A — Code review checklist

Read each file and confirm the invariant. Mark ✅/❌ + note file:line for any ❌.

### 5.1 `inc/color-palette-switcher.php`

- [ ] **Additive storage.** Only `customify_active_palette` + `customify_color_palettes`
      are registered (`add_setting`). No existing key is renamed or written with a
      new meaning. The 6 slots are driven via the existing picker, not re-stored.
- [ ] **Sanitizer `customify_color_sanitize_palettes()`** rejects malicious import:
      `name` → `sanitize_text_field` + length cap; **every** slot hex →
      `customify_color_normalize_hex(...,'')` and the entry is dropped if any slot
      fails to normalize; array hard-capped (100); non-array → `'[]'`.
- [ ] **Custom id can’t collide with a preset id** (`in_array($id,$preset_ids,true)`
      → prefixes `user-`).
- [ ] **XSS:** every user-controlled value rendered into `innerHTML` passes through
      the `esc()` helper (palette names, ids, hex in inline `style`, export
      textarea). Rename uses `input.value=` (property, not HTML) — fine.
- [ ] **render() three-case model** (see §3) — confirm case 3 (no match) yields
      no active card / no strip / no write, and case 2’s `setActive` is gated on
      `wp.customize.state('saved').get()===false`.
- [ ] **`applyPalette` releases its `applying` guard in a `finally`** (so a picker
      throw can’t leave it stuck and disable `maybeSyncCustom` for the session).
- [ ] **No direct persistence:** the file never calls `set_theme_mod` /
      `remove_theme_mod` / `previewer.save()`. (grep it.)
- [ ] **NOWDOC** inline JS uses `<<<'JS'` (single-quoted) → no PHP `$var` interpolation.

### 5.2 `inc/colors-palette.php`

- [ ] `$link_hover_default = $slots['primary'];` and the static `:root` emits
      `--customify-link-hover: var(--customify-link, …)` (pure var() chain, no
      color-mix) — emitted **only when** `! $ov_link_hover` (saved override wins).
- [ ] `CASCADE_MAP` has `global_styling_color_link_hover → global_styling_color_link`;
      `FIELD_DEFAULTS` has the matching `#235787`; `resolveCascadeValue()` recurses
      and is **acyclic** (link_hover→link→primary; primary is not a key).
- [ ] Preview `CASCADE_FALLBACK['--customify-link-hover'] === 'var(--customify-link)'`.
- [ ] `recomputeDerivedDebounced` coalesces the six-slot switch (~24ms) and is what
      the SOURCE_SLOTS bind to.

### 5.3 `inc/customizer/configs/colors.php`

- [ ] link-hover `default` and `placeholder` are `#235787`; description “same as Link color.”

### 5.4 `inc/class-customify.php`

- [ ] `'/inc/color-palette-switcher.php'` added to `includes()` **after**
      `colors-palette.php` (it depends on helpers there).

---

## 6. Part B — Functional test cases

Drive a real browser (Claude-in-Chrome). Before each case: set theme_mods (§4.3),
clear changesets + flush, then load the customizer URL and **wait ~7s** for boot.
Use the **state probe** (§7) to read machine-checkable state, and a screenshot to
confirm visuals. After any case that dirties the customizer, **do not Publish** —
clear changesets to discard.

### TC1 — Fresh-default site: Sunrise linked, no dirt
- **Pre:** RESET to fresh-default; clear changesets; flush.
- **Steps:** open customizer Colors.
- **Expect:** `active_setting=""`, `saved=true`. Exactly one card shows
  active+linked = **Sunrise** (slots equal field defaults). No “modified” strip.
- **Front-end** (`/`): `--customify-primary/link/link-hover = #235787`,
  `text=#2b2b2b`, `base=#ffffff`.

### TC2 — Legacy saved-custom site, clean open ⭐ (THE critical 30K case)
- **Pre:** SET saved-custom (primary #0066cc, secondary #ff6600, link #0066cc,
  text #222222; palette keys removed); clear changesets; flush.
- **Steps:** open customizer Colors. **Touch nothing.**
- **Expect (all must hold):** `active_setting=""`; `saved=true`;
  `activeCardNames=[]` (**no** card active); **no** modified strip; slots read the
  saved values (primary `#0066cc`, secondary `#ff6600`, text `#222222`).
- **Fail = 30K regression.** If a card shows active, or `saved=false` on open, or
  `active_setting` got written → STOP and report as a blocker.

### TC3 — Apply a preset (Midnight) on the fresh site
- **Pre:** TC1 state.
- **Steps:** click the **Midnight** card.
- **Expect:** the 6 slots become Midnight hex (decode the wrapped values);
  `active_setting="midnight"`; `saved=false`; Midnight card `is-active is-linked`;
  preview background turns dark; **zero console errors**. Header stays light
  (Skin Mode is intentionally NOT touched).
- **Teardown:** clear changesets (discard).

### TC4 — Apply a preset ON TOP of saved-custom, then discard
- **Pre:** TC2 saved-custom state.
- **Steps:** click **Sunrise**, observe, then **discard** (clear changesets, do not publish).
- **Expect:** apply works (slots → Sunrise, dirty), and after discard +
  re-inspect via wp-cli the **live theme_mods are still the saved-custom values**
  (nothing persisted without Publish).

### TC5 — Section reset reconciliation (was a known bug)
- **Pre:** TC3 (Midnight applied, dirty) OR any non-default state.
- **Steps:** click the section **Reset** control (the ↺ at the top of the Colors
  section — it is the theme’s pre-existing `customify__reset_section`; it removes
  the section’s theme_mods and reloads).
- **Expect:** after reload, slots = field defaults → the UI shows **Sunrise
  active (inferred)**, **no** “Midnight + Modified” limbo, no spurious dirty.
- **Note:** this reset is destructive by design (immediate `remove_theme_mod`,
  confirm-gated). It is NOT part of the palette feature — just confirm the
  feature *reconciles* cleanly to it.

### TC6 — Custom palette: create / rename / delete
- **Steps:** edit a couple of slots to a unique combo → click **＋ (add)** /
  “create from current”. Then rename it. Then delete it.
- **Expect:** new card appears with a `user-…` id; it’s active+linked; rename
  persists; **Space inside the rename input does not close it**; delete removes
  the card and does **not** recolor the slots (slots stay as last applied).
- Verify `customify_color_palettes` in wp-cli holds the JSON while it exists.

### TC7 — Import / export (round-trip + malicious input) ⭐ security
- **Export:** open export, pick a palette / “all”, confirm valid JSON in the textarea.
- **Round-trip:** import that JSON → palettes reappear identically.
- **Malicious import:** paste JSON with `name:"<img src=x onerror=alert(1)>"` and a
  slot value `"javascript:alert(1)"` / `"not-a-color"`.
  - **Expect:** the bad-hex entry is **dropped** (slot fails `normalize_hex`); the
    name is stored escaped/sanitized; **no script executes**; the card renders the
    name as inert text. Confirm via DOM that the name is text, not parsed HTML.

### TC8 — link-hover cascade + saved-override-wins
- **Default path:** on TC2 saved-custom (link `#0066cc`, link-hover unsaved),
  front-end `--customify-link-hover` resolves to `#0066cc` (= link). The picker
  swatch for “Link hover color” shows the cascade-resolved color.
- **Override path:** `set_theme_mod("global_styling_color_link_hover","#abcdef")`,
  flush, reload front-end → `--customify-link-hover = #abcdef` (saved value wins,
  cascade suppressed). Remove it afterward.

### TC9 — Per-slot reset baseline follows active palette (syncSlotDefaults)
- **Pre:** apply Midnight (TC3).
- **Steps:** observe the six slot rows.
- **Expect:** after applying Midnight, the per-slot reset arrows do **not** all
  light up (baseline re-anchored to Midnight). Editing one slot then shows a reset
  arrow that returns to the **Midnight** value, not the theme field default.

### TC10 — Edge cases
- Empty `customify_color_palettes` (`'[]'`) → only the 2 presets show, no JS error.
- Import a custom with `id:"sunrise"` → stored id becomes `user-sunrise` (no collision).
- Delete the **active** custom palette → active marker clears, slots unchanged, no error.

### TC11 — Persistence across save + reload (case 1)
- **Steps:** apply Midnight → **Publish** → reopen the customizer.
- **Expect:** `customify_active_palette` saved `"midnight"`; on reopen Midnight is
  active+linked (case 1, stored id matches slots), `saved=true`, no strip.
- **Teardown:** RESET to fresh-default + clear changesets (don’t leave Midnight published).

### TC12 — Front-end 30K render unchanged ⭐
- **Pre:** TC2 saved-custom; clear changesets; flush.
- **Steps:** load `/` (front-end, not customizer).
- **Expect:** `--customify-primary = #0066cc`, `--customify-link = #0066cc`,
  `--customify-link-hover = #0066cc`, `--customify-text = #222222`. Content links
  render the saved blue; the header BUTTON renders the saved secondary `#ff6600`.
  **No visual difference vs. before the feature** except link-hover now following
  link (documented default shift).

### TC13 — No fatal / no front-end overhead
- `php -l` all 5 files (expect clean).
- Load `/` and confirm the switcher adds **nothing** to the front-end (its
  customize_* hooks only fire in the customizer; front-end is just function defs).
- Console on both front-end and customizer: **zero** errors mentioning
  `cps|palette|customify`.

---

## 7. Reusable browser state-probe

Run via the Chrome `javascript_tool` in the **customizer** tab (after ~7s boot).
Returns machine-checkable JSON for the assertions above:

```js
(function(){
  var out={}, get=function(id){try{var s=wp.customize(id);return s?s.get():'__no__';}catch(e){return '__err__';}};
  var dec=function(v){try{return JSON.parse(decodeURI(v));}catch(e){return v;}};
  out.slots={primary:dec(get('global_styling_color_primary')),secondary:dec(get('global_styling_color_secondary')),
    text:dec(get('customify_palette_text')),surface:dec(get('customify_palette_surface')),
    base:dec(get('customify_palette_base')),accent:dec(get('customify_palette_accent')),
    link:dec(get('global_styling_color_link')),link_hover:dec(get('global_styling_color_link_hover'))};
  out.active_setting=get('customify_active_palette');
  out.palettes_setting=get('customify_color_palettes');
  out.saved=(function(){try{return wp.customize.state('saved').get();}catch(e){return '__err__';}})();
  var cards=document.querySelectorAll('.cps-card');
  out.cardNames=[].map.call(cards,function(c){var n=c.querySelector('.cps-name');return n?n.textContent.trim():'?';});
  out.activeCardNames=[].filter.call(cards,function(c){return /is-active/.test(c.className);})
    .map(function(c){var n=c.querySelector('.cps-name');return n?n.textContent.trim():c.className;});
  out.cardClasses=[].map.call(cards,function(c){return c.className;});
  var strip=document.querySelector('[class*="cps-modified"],[class*="cps-dirty"],[class*="cps-strip"]');
  out.modifiedStrip=strip?(strip.offsetParent!==null):'none-in-dom';
  return JSON.stringify(out);
})()
```

**Front-end root-var probe** (run on `/`):

```js
(function(){var cs=getComputedStyle(document.documentElement);
  return JSON.stringify({primary:cs.getPropertyValue('--customify-primary').trim(),
    link:cs.getPropertyValue('--customify-link').trim(),
    linkHover:cs.getPropertyValue('--customify-link-hover').trim(),
    text:cs.getPropertyValue('--customify-text').trim(),
    base:cs.getPropertyValue('--customify-base').trim()});})()
```

### Expected baselines actually observed during implementation (compare against these)

- **TC2 clean open (saved-custom):**
  `{"active_setting":"","saved":true,"activeCardNames":[],"modifiedStrip":"none-in-dom"}`
  with slots primary `#0066cc`, secondary `#ff6600`, text `#222222`.
- **TC3 after Midnight:** slots decode to Midnight hex;
  `{"active_setting":"midnight","saved":false,"activeCardNames":["Midnight"]}`,
  Midnight class `cps-card is-active is-linked`.
- **TC12 front-end (saved-custom):**
  `{"primary":"#0066cc","link":"#0066cc","linkHover":"#0066cc","text":"#222222"}`.
- **TC1 front-end (fresh default):**
  `{"primary":"#235787","link":"#235787","linkHover":"#235787","text":"#2b2b2b","base":"#ffffff"}`.

---

## 8. Results & reporting

Fill this in and report. Any ❌ on TC2 / TC4 / TC12 (the saved-site cases) or a
storage/XSS finding in Part A is a **blocker**.

| ID | Case | Pass? | Notes |
|----|------|-------|-------|
| A | Code review (§5, all boxes) | | |
| TC1 | Fresh default = Sunrise linked | | |
| TC2 | ⭐ Saved-custom clean open | | |
| TC3 | Apply Midnight | | |
| TC4 | Apply over saved-custom + discard | | |
| TC5 | Section-reset reconciliation | | |
| TC6 | Custom palette CRUD | | |
| TC7 | ⭐ Import/export + malicious | | |
| TC8 | link-hover cascade + override | | |
| TC9 | Per-slot reset baseline | | |
| TC10 | Edge cases | | |
| TC11 | Persist across save+reload | | |
| TC12 | ⭐ Front-end 30K render | | |
| TC13 | No fatal / no FE overhead | | |

**Report should state:** overall verdict (SHIP / FIX-FIRST / BLOCK), every ❌ with
file:line or repro steps, and confirmation that the site + main checkout were
restored (§4.4). Prior implementation review verdict was **SHIP**; treat this as
an independent second pass — try to *break* it, don’t just confirm.
