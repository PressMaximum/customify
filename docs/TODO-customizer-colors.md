# Customizer Colors — TODO (post-PR #396)

Follow-up items deferred from PR #396 (Phase 2.10–2.13). Each item
is scoped to be a separate PR.

Last updated: 2026-05-24 after PR #396 merge candidate.

---

## 1. Wire `--customify-border-strong` to form input CSS

**Status**: token emitted + in picker, but no SCSS rule consumes it yet.

**Why**: Spec §2 mandates form input borders need WCAG 1.4.11 ≥3:1
contrast vs canvas. Current `_base.scss` form rules use `$color_border`
(the decorative ~1.35:1 variant). Project owner explicitly deferred
this wire ("tạo color đã chưa sửa css border input") so the token
ships first, then CSS updates separately.

**Affected selectors** (from `_base.scss`):
- `input[type="text|email|url|password|tel|search|number|date|color"]`
- `textarea`
- `select` / `.select2-selection`
- `.select2-dropdown` (border)

**Implementation**:
```scss
// Replace:
border: 1px solid $color_border;
// With:
border: 1px solid var(--customify-border-strong, #{$color_border});
```

Fallback to `$color_border` preserves 30K legacy rendering when
`--customify-border-strong` is not emitted (palette not opted in).

**Effort**: 15-30 min (1 SCSS edit + build + visual verify).

---

## 2. Refactor header/footer/blog/page-header configs to consume slot tokens

**Status**: SPEC §13 has the full refactor matrix (Phase 2 §8.11 followup).

**Why**: ~50 color/styling sub-fields across these configs still emit
literal hex. Should switch to `var(--customify-X, {{value}})` so saved
values still emit identically (byte-equivalent for 30K) AND a slot
edit cascades to the whole site.

**Recommended batch order** (from SPEC §13.5):

| Batch | Items | Effort | Risk |
|---|---|---|---|
| 1 — Quick wins | A.3 (nav-icon) + A.8 (logo) + D.1 (page-header) + A.1 (button) + C.2 (read-more) — ~16 fields | half-day | LOW |
| 2 — Visual wins | A.9 (social-icons) + B.1 (footer-bg) + A.4 (search-box) + A.5 (search-icon) — ~18 fields | day | LOW-MED |
| 3 — Careful | A.2 (menus) + C.1 (post-entry) + A.6 (header-row) — ~15 fields | day | MED (test per skin) |
| 4 — Defer | D.2 (cover overlay) + A.7 (transparent header) | — | needs design direction |

Each batch should run scenarios A/B/C byte-equivalence + a real-upgrade
visual test (per SPEC §5) before commit.

**Effort**: 3-4 separate PRs across ~3-4 days.

---

## 3. Spike: shadow-slug investigation (legacy slug `--wp--preset--color--*` without class-rule collision)

**Status**: documented in SPEC §8.12 as Phase 3 candidate.

**Why**: Phase 2.13 chose the back-compat shim approach (SCSS rules
without `!important`) to preserve 30K rendering. Reviewer suggested
an alternative: inject legacy slugs via `wp_theme_json_data_default`
(default layer, not theme layer) so WP emits `--wp--preset--color--*`
vars WITHOUT generating `.has-{slug}-color { color: var(...) !important }`
class rules.

**Hypothesis**: WP generates class rules only from theme/user palette
layers, not core defaults. Needs verification.

**Verification steps** (~30 min spike):
1. Add filter callback that injects 6 legacy slugs into default-layer palette.
2. Inspect rendered `global-styles-inline-css` for `.has-text-color`
   class rule presence.
3. Test block with `has-primary-color has-text-color` — confirm primary
   wins (no clobber from default-layer slug).
4. Compare DX vs current shim approach.

**Decision criteria**: If shadow-slug works, it's cleaner (no SCSS
shim maintenance). If not, current shim is fine and this spike is
informational only.

**Effort**: 30-60 min spike + decide.

---

## 4. Iris picker UX overhaul

**Status**: design backlog. Project owner wants modern picker layout.

**Why**: Current Iris layout uses vertical strips. Owner wants
full-width saturation box + hue + alpha strips stacked vertically.

**Constraints**:
- Iris doesn't use jQuery UI slider widget — has its own drag math
  reading inline `offsetTop`. CSS `transform: rotate()` trick breaks
  the drag handler.
- Iris's initial colorful state is grayscale when current value is
  grayscale (hue undefined). Owner wants colorful initial state.

**Options**:
- Patch Iris source (fragile, breaks on WP core update)
- Replace with custom React/vanilla widget (large scope)
- Accept Iris vertical strips as-is

Defer until UX direction is decided.

**Effort**: large (multi-day if custom widget).

---

## 5. Container in dark Palette — documented limitation, optional fix

**Status**: spec-documented as "light-base only" (color-token-derivation
spec §"Honest limits").

**Why**: Container formula `P = clamp((TARGET_L - L_base) / (L_source - L_base), 0.02, 0.98)`
clamps to 0.98 when base is dark (because target L=0.93 isn't reachable
from a dark base + any source). Result: containers in dark Palette
mode ≈ pure brand color (no soft tint feel).

**Possible fix**: adaptive TARGET_CONTAINER_L based on base luminance:
- Light base → target L=0.93 (current behavior)
- Dark base → target L=0.20 or similar (creates "softly tinted dark"
  container that complements dark canvas)

**Open question**: dark-mode design semantics differ from light-mode.
Need design direction before implementing.

**Effort**: 1-2 days (formula + spec update + dark-Palette visual
verification across all container use cases).

---

## 6. Background composite ↔ slot two-way sync

**Status**: spec-documented as Phase 2 remaining.

**Why**: When user edits `bg_color` subfield of `background` composite,
the `customify_palette_base` slot doesn't update (and vice versa).
Should propagate so the slot/composite are conceptually the same value.

**Effort**: 30-60 min (PHP filter + Customizer JS sync).

---

## 7. `content_background` 7th slot?

**Status**: spec-documented as Phase 2 remaining.

**Why**: If `content_background` composite is used in practice, add a
7th slot or a deeper-content surface token. Currently has no slot
equivalent.

**Decision needed first**: is `content_background` actually being used
by 30K sites? Survey before deciding.

---

## 8. SCSS `_vars.scss` cleanup — drop dead `$color_*` legacy

**Status**: opportunistic cleanup, not blocking.

**Why**: A few `$color_*` SCSS vars in `_vars.scss` are no longer
referenced after the Phase 2 var()-based refactor. Audit + delete
dead code.

**Effort**: 15 min (grep + delete).

---

## Done (no longer in scope)

These items WERE in earlier TODOs but are now CLOSED:

- ✅ Phase 2.8 WCAG --on-* live preview (PR #392)
- ✅ Phase 2.10 Surface adaptive wiring (PR #396)
- ✅ Phase 2.11 Picker palette injection + slug refactor (PR #396)
- ✅ Phase 2.12 Implement color-token-derivation spec (PR #396)
- ✅ Phase 2.13 Auto-wire + rgba composite + opt-in gate drop (PR #396)
- ✅ Legacy slug back-compat shim (PR #396)
- ✅ Comprehensive Color System Test page (customify2 page id 184)

---

## Priority recommendation

If anh có 1 day cho follow-up work, ưu tiên:

1. **Item 1** (border-strong wire) — 30 min, high user value (form inputs are everywhere)
2. **Item 3** (shadow-slug spike) — 1 hour, may close item 6 of SPEC §13 alternative
3. **Item 2 Batch 1** (16 fields quick wins) — half-day, visible cascade improvement

Items 4-7 cần design direction trước.
