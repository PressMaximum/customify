# SPEC — <Feature name>

One-paragraph overview: what the feature does, where it lives, what surfaces it touches. Keep it scannable.

Related references:
- [`SPEC-other.md`](SPEC-other.md) — adjacent subsystem this depends on
- [`SPEC-another.md`](SPEC-another.md) — adjacent subsystem this is depended on by

This file is permanent. For transient session notes, use `docs/handoffs/`.

---

## 1. Overview

What the feature exists to do. The two or three things that matter most when reasoning about it. A one-row-per-surface table works well:

| Surface | Role |
|---|---|
| Customizer section | User-facing settings |
| `theme_mod` storage | Persistent data |
| Frontend renderer | Storage → HTML |
| JS controller | Live preview / interactivity |

---

## 2. File map

| File | Lines | Responsibility |
|---|---|---|
| [`inc/.../foo.php`](../inc/.../foo.php) | ~ | Primary class + bootstrap |
| [`src/.../foo.js`](../src/.../foo.js) | ~ | Frontend script |
| [`src/.../scss/_foo.scss`](../src/.../scss/_foo.scss) | ~ | Styling |

Bootstrap gate (if relevant — e.g. Pro guard):

```php
add_action( 'after_setup_theme', function () {
    if ( class_exists( 'Customify_Pro_Module_Foo' ) ) {
        return; // Pro takes over
    }
    Customify_Foo::get_instance();
}, 30 );
```

---

## 3. Storage shape & data contract

Public API under the 30k-sites rule. Renaming a key, dropping a key, or changing a sanitize callback signature is a data migration — see [`migration-guide.md`](migration-guide.md).

### 3.1 `theme_mod` keys

| Key | Stored type | Default | Notes |
|---|---|---|---|
| `feature_foo` | bool | `false` | Checkbox |
| `feature_bar` | array | `[]` | Per-device slider value |

### 3.2 `wp_options` keys

| Option | Stored type | Notes |
|---|---|---|
| `customify_feature_state` | array | `update_option`/`get_option` |

### 3.3 `post_meta` keys

| Meta key | Post type | Stored type | Possible values |
|---|---|---|---|
| `_customify_feature_override` | any | string | `''` / `'show'` / `'hide'` |

### 3.4 Sanitize callbacks

Name and signature of every custom sanitize callback used by the feature. Note any callbacks that accept both URL-encoded JSON and decoded arrays.

### 3.5 Defaults

Document every default value here. Changing a default silently shifts behavior on sites that never saved an explicit value — see [`migration-guide.md`](migration-guide.md).

---

## 4. Render pipeline

Trace the path from storage to output. File + line references where useful.

### 4.1 Entry points

```php
add_action( 'customify/site-start', 'customify_render_foo' );
```

### 4.2 Call chain

1. `render()` reads the `theme_mod` via `Customify()->get_setting( 'foo' )`.
2. Dispatches to per-item / per-device handler.
3. Output is bracketed by `do_action( 'customify/before-foo' )` / `do_action( 'customify/after-foo' )`.

### 4.3 Device strategy / conditional output

If the feature renders different markup per device or has a multi-tier conditional, draw a flowchart or a resolution table.

---

## 5. Design decisions

The "why" survives refactors that invalidate the "how". Document it here.

### 5.1 Decision: <decision name>

- **Chose**: <what we did>
- **Rejected**: <alternative>
- **Reason**: <constraint, deadline, prior incident, or principle that drove the call>

### 5.2 Decision: <decision name>

- **Chose**: …
- **Rejected**: …
- **Reason**: …

---

## 6. Adding / extending (recipe)

Canonical recipe for the most common extension. Step 1 / 2 / 3.

```php
// Step 1 — Create the item file
// Step 2 — Register it
// Step 3 — Done
```

---

## 7. Hooks & filters catalog

### 7.1 Registration phase

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/foo/init` | action | — | Extension point |
| `customify/foo/items` | filter | `array $items` | Modify item registry |

### 7.2 Render phase

| Hook | Type | Payload | Purpose |
|---|---|---|---|
| `customify/before-foo` | action | — | Output before render |
| `customify/after-foo` | action | — | Output after render |

---

## 8. Known issues / edge cases

Document current behavior — do not fix silently. Any change here is a data / behavior migration concern.

### Issue #1 — <name>

What it does, why it does that, and the workaround if any. Cross-reference the line in code if applicable.

### Issue #2 — <name>

…

---

## 9. Pro plugin handoff

When `Customify_Pro_Module_<Foo>` exists, the theme's port is dormant. The Pro module is expected to:

- Honour the same `theme_mod` keys (list)
- Honour the same `post_meta` keys (list)
- Apply the same body / row / element CSS classes so frontend SCSS keeps working
- Expose the same filter signatures listed in §7

This contract is **public API** under the 30k-sites rule. See [`SPEC-pro-integration.md`](SPEC-pro-integration.md) for the full contract.

---

## 10. Troubleshooting

| Symptom | Likely cause |
|---|---|
| Setting saves but no effect on frontend | … |
| Live preview doesn't update | … |

---

## 11. Quick reference — how do I…?

| I want to… | Code |
|---|---|
| Check feature state programmatically | `Customify_Foo::get_instance()->is_active()` |
| Force-disable in a specific scenario | `add_filter( 'customify/foo/is-active', '__return_false' );` |

---

## 12. Where to look next

**PHP**
- [`inc/.../foo.php`](../inc/.../foo.php) — primary class
- [`inc/.../foo-functions.php`](../inc/.../foo-functions.php) — helpers

**JavaScript**
- [`src/.../foo.js`](../src/.../foo.js) — controller

**Related specs**
- [`SPEC-other.md`](SPEC-other.md)

**Conventions**
- [`../AGENTS.md`](../AGENTS.md) — project-wide rules (English-only, no function deletions, 30k-sites policy)
