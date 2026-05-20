# Handoffs

Session-to-session knowledge dumps that are **transient by intent** — they exist to bridge one Claude / engineer pickup to the next, then get deleted when the work merges.

## Folder conventions

| Path | Tracked in git? | Lifetime |
|---|---|---|
| `docs/handoffs/README.md` | ✓ yes | Permanent — this file. |
| `docs/handoffs/temp/*.md` | ✗ gitignored by default | Per-session, dropped before merging to main. |

## How to share a handoff with a co-worker

The contradiction in "exclude from git but co-workers can see it" resolves through **branch-scoped sharing**:

1. Write the handoff at `docs/handoffs/temp/<date>-<topic>.md`.
2. Force-add it to the **active branch** only (NOT main):
   ```bash
   git add -f docs/handoffs/temp/2026-05-20-dashboard-v2-pro-bridge.md
   git commit -m "docs(handoff): dashboard-v2 + Pro bridge session ledger"
   git push origin <your-branch>
   ```
3. Tell the co-worker (or the next Claude session) which branch:
   ```
   git fetch origin
   git checkout <branch>
   cat docs/handoffs/temp/<file>.md
   ```
4. **Before merging the branch to main**, drop the handoff:
   ```bash
   git rm docs/handoffs/temp/<file>.md
   git commit -m "docs(handoff): drop transient handoff before merge"
   ```

The file lives on the branch's history (co-workers can `git log` / `git show` it any time), but main stays clean.

## Alternatives if branch-scoped sharing isn't enough

- **Long-lived reference**: promote the file to `docs/decisions/` or `docs/architecture/` and commit normally to main. The handoff stops being transient.
- **External link**: paste the content into Notion / a Google Doc / Slack; drop the file entirely.
- **GitHub Gist**: `gh gist create docs/handoffs/temp/<file>.md` produces a shareable URL without touching the repo.

## Why gitignore by default

Most session handoffs are stale within a week. Letting every one accumulate in main's history bloats `docs/` with one-shot notes that no future reader will care about. The gitignore + `git add -f` pattern forces a deliberate choice for each handoff — share it on a branch when needed, drop it when the work lands.
