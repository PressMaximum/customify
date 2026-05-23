# Storage Migration Guide

How to safely change persistent data in Customify. **Read this before touching any `theme_mod` key, `wp_options` key, `post_meta` key, or sanitize callback.**

For the rule statement see [`../AGENTS.md`](../AGENTS.md) §4.1. For the canonical audit of every key currently in use, see [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md).

---

## 1. The 30k-sites doctrine

Customify is deployed on **30,000+ live production sites**. Every change ships to a live install base that already has saved data and that you cannot ask to re-configure.

Three load-bearing implications:

1. **Defaults are sticky.** Once a default value renders on a fresh site, sites that took that default never re-save. Changing the default later silently shifts the rendered output across those sites.
2. **Renames break trust.** A child theme that hardcodes `get_theme_mod( 'header_logo_x' )` won't survive a rename to `header_logo_x_v2`. Surface that compatibility cost before changing.
3. **Migration mistakes are loud.** A migration that fails halfway on a 50,000-row `wp_postmeta` table is a support ticket from every affected site.

---

## 2. The decision tree — do I need a migration?

```
                ┌────────────────────────────────────────────────┐
                │ Am I changing the SHAPE of saved data?         │
                │ (key name, value type, sub-field, sanitize)    │
                └────────────────┬───────────────────────────────┘
                  No             │             Yes
                  ▼              │              ▼
              No migration       │      ┌────────────────────────────┐
              needed.            │      │ Can old AND new shape       │
              Ship the change.   │      │ coexist by reading both?    │
                                 │      └──────┬──────────────────────┘
                                 │      Yes    │   No
                                 │       ▼     │    ▼
                                 │  Add a       │   Migration required.
                                 │  read-both   │   Continue to §3.
                                 │  fallback;   │
                                 │  no migration│
                                 │  needed.     │
                                 │              │
                                 ▼              ▼
                ┌────────────────────────────────────────────────┐
                │ Am I changing a DEFAULT value?                  │
                └────────────────┬───────────────────────────────┘
                                 │ Yes
                                 ▼
                Read both:
                - Sites that saved any value → render their value (no shift)
                - Sites with no saved value → get the new default

                If "render their value" requires the same code as before
                (it usually does) → no migration; just update the default
                in the config and verify scenarios A/B/C below.

                If the old default value renders DIFFERENTLY from the
                new default in any way (e.g. you also changed the
                emitted CSS for that value) → migration required.
```

**Preferred answer is always "no migration needed."** Migrations are operationally risky. If you can read both shapes and emit identical output for legacy data, do that.

---

## 3. The migration pattern

The canonical code template + step-by-step rationale lives in [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) §8 — single source of truth.

In one sentence: **idempotent migration gated by a version-stamped flag in the `customify_migrations` option, recording a timestamp per migration.**

Concrete examples already shipped:
- V1 → V2 header builder migration ([`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) §7.1)
- V1 → V2 footer builder migration ([`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) §7.2)

---

## 4. Read both shapes (the lighter-weight alternative)

For most changes, "read both shapes" beats a migration. The new code accepts old and new data transparently; old data is upgraded only when the user next saves.

```php
$value = get_theme_mod( 'foo' );

// Old shape: scalar. New shape: array with 'value' key.
if ( is_array( $value ) && isset( $value['value'] ) ) {
    $resolved = $value['value'];   // new shape
} else {
    $resolved = $value;            // old shape — scalar
}
```

Pros:

- Zero risk to existing data
- No flag tracking
- Old sites continue rendering exactly as before

Cons:

- Code carries a branch forever (or until you actually run a migration to clear out old shape)
- Other readers of the same key must also handle both shapes

Use read-both when the storage shape change is small and the read sites are few. Use migration when the new shape is fundamentally different and you have many readers.

---

## 5. Default value changes

Changing a default value is one of the most common "silent" migrations.

### 5.1 The safe pattern

The Customizer system reads defaults via `Customify()->get_setting( $key )`. When a user has saved a value, `get_setting()` returns the saved value; when they haven't, it returns the field's registered default.

| User state | Behavior on default change |
|---|---|
| Saved an explicit value (any value) | Renders their saved value — default change has no effect |
| Never opened the field, never saved | Renders the new default |

So a default change is invisible to existing users **as long as the rendered output stays the same** for any given saved value. Verify by running scenarios A / B / C below.

### 5.2 Verification scenarios

For any change that touches CSS output of a saved field, simulate three site states and compare byte-equivalent CSS:

| Scenario | Setup | Expected |
|---|---|---|
| **A — Defaults** | Zero saved theme_mods (fresh install) | New code emits the same value as old code (or, for additive changes, only additive new output) |
| **B — Custom value** | All affected keys saved with custom hex/value | New code reads saved → emits same value on same selectors |
| **C — Partial save** | Only some keys saved, rest at default | Partial values preserved; the rest derived from new defaults |

The `customizer-colors` PR (#392) introduced verification helpers in `/tmp/` that automate this — see [`SPEC-customizer-colors.md`](SPEC-customizer-colors.md) §5. Adapt for any field where output correctness must be byte-equivalent.

---

## 6. Renames are migrations

Renaming a key changes the data contract even when the rename "feels" cosmetic.

| What you're doing | Migration class |
|---|---|
| Rename `theme_mod` key | Full migration — old key must remain readable for ≥1 minor cycle |
| Rename a config field's `name` (which becomes the `theme_mod` key) | Same as above |
| Change a sanitize callback signature | Full migration — old saved values must still pass the new sanitize |
| Change a sanitize callback's return type (e.g. string → array) | Full migration — old saved values are now the wrong shape |
| Rename a CPT slug | Full migration — also affects URL structure (slug is public-facing) |
| Rename a `post_meta` key | Full migration — child themes and Pro may read directly |

For renames specifically, the **read-both-then-write-new** pattern is usually right:

```php
// Read both old + new
$value = get_theme_mod( 'new_key',
    get_theme_mod( 'old_key', $default )
);

// On save (Customizer's sanitize / save flow), write new
add_filter( 'customize_save_response', function ( $response ) {
    if ( get_theme_mod( 'old_key' ) !== false ) {
        remove_theme_mod( 'old_key' );
    }
    return $response;
}, 10 );
```

Keep `old_key` as a read-only fallback for at least one minor version cycle. Document the rename in the changelog so child theme authors can update.

---

## 7. Pro ↔ theme contracts

The Pro plugin reads and writes some of the theme's keys directly. Changing those keys without coordinating with Pro = breakage on every Pro install.

Always-shared keys (theme writes, Pro reads, or vice versa):

- `customify_fa_ver` (Font Awesome version selection)
- `customify_pro_settings`
- `customify_modules`
- Builder layout keys (`header_builder_panel_v2`, `footer_builder_panel_v2`) — Pro panels may read these to extend
- All Customizer `theme_mod` keys with a Pro-provided takeover module (see `Customify_Pro_Module_*` class list in [`SPEC-pro-integration.md`](SPEC-pro-integration.md))

Before changing any shared key:

1. Open a coordination thread (Slack / issue / PR comment) with the Pro maintainer
2. Plan the rollout: theme + Pro both ship the change in the same release cycle
3. Both sides keep the read-both fallback for the same number of minor versions

---

## 8. Logging & rollback

Migrations should be visible to support staff. Two minimum-effort patterns:

```php
// 1. Log the migration when it runs (visible in error_log if WP_DEBUG_LOG is on)
error_log( '[Customify] Migrated feature_x to v2 on ' . get_site_url() );

// 2. Stash the pre-migration value for safety (delete after a stable release)
update_option( 'customify_migrations_backup_feature_x', $old );
```

Don't try to ship an automatic "undo" path. If a migration is wrong, fix the migration code and re-run with a new flag (`feature_x_v3`). The backup option above gives support a way to manually restore data when needed.

---

## 9. Checklist before changing storage

Before opening the PR, walk this list:

- [ ] What is the shape change — key name, value type, sanitize, default?
- [ ] Can I read both shapes and avoid a migration?
- [ ] If migrating: is the migration function idempotent? Have I tested running it twice?
- [ ] Have I run scenarios A / B / C and confirmed no unintended output shift?
- [ ] Is the version-stamped flag in `customify_migrations` unique to this migration?
- [ ] If this is a rename: how long do I keep the old key readable? Documented in changelog?
- [ ] If this is a shared key: have I coordinated with Pro?
- [ ] Have I added the new key to [`SPEC-data-migration-policy.md`](SPEC-data-migration-policy.md) audit?

---

## 10. When in doubt

Ask. A 5-minute clarification with a maintainer is cheaper than a support escalation across 30,000 sites.
