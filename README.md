# Tanya Barrans Real Estate — WordPress Site

Custom WordPress block theme for Tanya Barrans, Puget Sound real estate agent (John L Scott).
Built locally with LocalWP; will migrate to live hosting at launch.

## What's versioned here
- `wp-content/themes/tanya-barrans/` — the custom block theme (design, templates, patterns)
- `wp-content/uploads/` — real media (Tanya's photos, logos, sold-listing photos)
- `db-backups/` — database exports (pages, posts, settings) — re-exported after each work session

## Setting up on a new computer
1. Install [LocalWP](https://localwp.com) if it's not already installed.
2. Create a new site named `tanya-barrans` (matching the name keeps the local domain identical: `tanya-barrans.local`).
3. Start the new site once so LocalWP finishes the base WordPress install, then stop it.
4. Open a terminal in the new site's `app/public` folder and pull this repo in:
   ```
   git init -b main
   git remote add origin https://github.com/sbfogg/tanya-barrans.git
   git fetch origin
   git checkout -f main
   ```
   This overlays the theme, uploads, and db-backups on top of the fresh WordPress install without touching WordPress core/plugins (those aren't tracked, on purpose).
5. Activate the theme and import the latest database dump. From LocalWP: right-click the site → **Open Site Shell**, then:
   ```
   wp theme activate tanya-barrans
   wp db import db-backups/<newest-file>.sql
   ```
6. If the local domain differs from `tanya-barrans.local` for any reason, run:
   ```
   wp search-replace 'tanya-barrans.local' 'new-domain.local'
   ```
7. Restart the site in LocalWP and it should look exactly like it does here.

Note: Claude's memory of this project's history and decisions lives on the original machine, not in this repo. On a new computer, a fresh Claude Code session won't have that context automatically — point it at this README and the git log to catch up, or continue working from the original machine/session if that's easier.
