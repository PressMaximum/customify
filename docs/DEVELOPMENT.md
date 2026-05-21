# Development setup

Working on the Customify theme. For end-user install see [`../README.md`](../README.md). For the dashboard internals see [`SPEC-dashboard.md`](SPEC-dashboard.md); Customizer / Header-Footer builder have their own SPEC files.

---

## 1. Prerequisites

| Tool | Min version | What for |
|---|---|---|
| Node.js | 18+ | npm + webpack |
| PHP | 7.4+ | match theme runtime |
| Composer | 2.x | pull `pressmaximum/dashboard-kit` PHP code |
| Git + SSH key on GitHub | — | composer clones the kit via SSH (private repo) |
| Local WP environment | — | [Studio](https://developer.wordpress.com/studio/) or similar |

---

## 2. First-time setup (clone fresh)

```bash
git clone git@github.com:PressMaximum/customify.git
cd customify
git checkout DEV          # active development branch

composer install          # pulls dashboard-kit into vendor/
npm install               # pulls JS deps into node_modules/
npm run build             # production build → build/
```

SSH key setup (1-time, required for `composer install` because the kit repo is private):

```bash
ssh-keygen -t ed25519 -C "you@your-email"
# paste ~/.ssh/id_ed25519.pub into https://github.com/settings/keys
ssh-keyscan -t rsa,ed25519 github.com >> ~/.ssh/known_hosts
ssh -T git@github.com   # should print "Hi <username>!"
```

Activate the `customify` theme in WP admin → done.

---

## 3. Daily workflow

```bash
git pull                  # sync DEV
composer install          # only if composer.lock changed (kit bumped)
npm install               # only if package-lock.json changed
npm run build             # rebuild bundle
# OR: npm start          # watch mode for active dev (rebuilds on save)
```

Smoke-test the dashboard at `http://your-site.local/wp-admin/admin.php?page=customify`.

---

## 4. Tools at a glance

| Tool | Manifest | Output | Purpose |
|---|---|---|---|
| Composer | `composer.json` + `composer.lock` | `vendor/` (gitignored) | PHP deps — currently just `pressmaximum/dashboard-kit` |
| npm + webpack | `package.json` + `package-lock.json` | `build/` (tracked) | JS + SCSS — the dashboard SPA + admin scripts |
| Grunt | `Gruntfile.js` | release zip + POT file | Packaging only (`grunt zipfile`); not part of dev loop |

`vendor/` and `node_modules/` are gitignored — every machine rebuilds them locally.
`build/` IS committed so production deploys don't need a build step.

---

## 5. Common tasks

**Bump dashboard-kit to a newer version**:
```bash
composer update pressmaximum/dashboard-kit
git add composer.lock && git commit -m "chore: bump dashboard-kit"
```

**Add a new PHP dep**:
```bash
composer require some-vendor/package
git add composer.json composer.lock
```

**Add a new JS dep**:
```bash
npm install some-package
git add package.json package-lock.json
```

**Add a new webpack entry**: edit `entries` map in [`webpack.config.js`](../webpack.config.js), then `npm run build`.

---

## 6. Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `admin.php?page=customify` shows "Sorry, you are not allowed" | `vendor/` missing → kit's `AssetEnqueue` class doesn't exist → menu not registered | `composer install` |
| `composer install` fails with `Host key verification failed` | github.com not in known_hosts | `ssh-keyscan -t rsa,ed25519 github.com >> ~/.ssh/known_hosts` |
| `composer install` fails with `Could not read from remote repository` | SSH key not registered with GitHub | Add `~/.ssh/id_ed25519.pub` at https://github.com/settings/keys |
| Dashboard CSS broken / old | Stale build/ | `npm run build` + hard-reload browser |
| `npm start` produced files but missing `.min.css` | Dev mode skips minification | Use `npm run build` (production) instead |

---

## 7. Where things live

```
wp-content/themes/customify/
├── inc/                  Theme PHP — bootstrap, Customizer, admin pages
│   ├── admin/dashboard-v2.php        Top-level Customify admin page
│   └── customizer/                    Customizer config + auto-CSS
├── src/                  JS / SCSS source (compiles to build/)
│   ├── backend/admin/dashboard-v2/   New dashboard SPA
│   ├── backend/customizer/            Customizer controls
│   └── frontend/                      Front-end theme JS + SCSS
├── build/                Compiled output (committed)
├── docs/                 SPECs (this file + SPEC-*.md)
├── composer.json         PHP deps
├── package.json          JS deps + scripts
└── webpack.config.js     Bundle entry config
```

---

## 8. References

- [`SPEC-dashboard.md`](SPEC-dashboard.md) — new dashboard SPA reference
- [`SPEC-customizer.md`](SPEC-customizer.md) — Customizer settings + auto-CSS pipeline
- [`SPEC-header-footer-builder.md`](SPEC-header-footer-builder.md) — Header / Footer Builder
- [`handoffs/`](handoffs/) — transient session notes (`.gitignored` by default; force-add to share)
