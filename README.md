# Tanya Barrans Real Estate — WordPress Site

Custom WordPress block theme for Tanya Barrans, Puget Sound real estate agent (John L Scott).
Built locally with LocalWP; will migrate to live hosting at launch.

## What's versioned here
- `wp-content/themes/tanya-barrans/` — the custom block theme (design, templates, patterns)
- `db-backups/` — database exports (pages, posts, settings)

## Restoring
1. Create a fresh site in LocalWP
2. Copy the theme into `wp-content/themes/` and activate it
3. Import the newest dump from `db-backups/` (LocalWP: right-click site > Open Site Shell > `wp db import <file>`)
4. Update URLs if the local domain differs: `wp search-replace 'tanya-barrans.local' 'new-domain.local'`
