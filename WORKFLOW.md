# Working on this site without breaking anyone else's work

**Written 2026-08-18.** Read this before editing templates or touching the database.

Two groups work on this site at the same time, in two different places:

- **Tanya, Neil and Zea** work inside WordPress — pages, posts, media, and sometimes templates through the Site Editor.
- **The developer** works in this repository and deploys code by uploading individual files.

Neither group can see what the other is doing until it appears on the site. This document exists so that stays harmless.

---

## Which site is the real one

> **`violet-wren-104886.hostingersite.com`** — updated 2026-08-22.

There are two WordPress installs on the hosting plan. `salmon-otter-516624.hostingersite.com` was the original and is **abandoned**: it is a stale copy, missing the area pages, property listings and sales portfolio that exist only on violet-wren.

**Deploy to violet-wren.** A theme file uploaded to salmon-otter reaches nothing and appears to do nothing, which is a very easy afternoon to lose. Check the path in every `scp` before running it.

Both are currently unreachable to search engines, but a second full copy of the site is a duplicate-content risk the moment either becomes public. Decide whether salmon-otter gets deleted before launch.

The template-override list below was measured on salmon-otter and has **not** been re-checked against violet-wren. Run the query in that section before relying on it.

---

## The rule

**The database owns everything the team can see. This repository owns code they cannot.**

That is not how the project started. Until 2026-08-14 the repo was authoritative for templates too. It no longer is, and treating it as though it were is how work gets destroyed.

| Owned in WordPress (database) | Owned here (git) |
|---|---|
| Pages and posts | `functions.php` |
| Media library | `assets/css/extra.css` |
| Templates edited in the Site Editor | `assets/js/*.js` |
| Global Styles | `patterns/*.php` |
| Navigation menus | Templates *not* listed below |

---

## Templates: the seven that ignore this repo

When someone edits a template in **Appearance → Editor**, WordPress saves a `wp_template` row in the database that **permanently shadows the theme file**. The file stays in git, keeps looking authoritative, and never renders again.

Measured on **violet-wren, 2026-08-22**. Thirteen templates are overridden:

```
archive      front-page   home         page-buy      page-covington
page-kent    page-maple-valley         page-neighborhoods
page-newcastle            page-renton  page-renton-parks-outdoors
page-sell    single
```

**Editing those files in this repo does nothing.** Change them in Appearance → Editor instead.

Six of them did not exist a week ago. The area pages, the Neighborhoods index and the parks page were all built in the Site Editor between 20 and 21 August, and `page-neighborhoods` moved out of the repo's control in the process — it used to be safe to edit here. **Assume this list grows, and re-run the query below rather than trusting it.**

These are still served from the repo and safe to edit here:

```
404.html   index.html   page.html   page-no-title.html
```

To check whether that list has grown:

```sql
SELECT post_name, post_modified FROM wp_posts
WHERE post_type = 'wp_template' ORDER BY post_modified DESC;
```

### `theme.json` is a partial case

One `wp_global_styles` row overrides `theme.json`, but only for values somebody actually changed in the Site Editor — a colour, a font size. Everything else still comes from the file.

So most `theme.json` edits work and some silently do not. **Verify on the rendered page rather than assuming either way.**

---

## Never do these

Each one destroys work that exists nowhere else.

1. **Never re-import an old SQL export.** `deploy/tanyabarrans-production.sql` is from 2026-08-12 and predates every article and template edit made since. Importing it silently erases all of them.
2. **Never push the LocalWP database upward.** It diverged on 2026-08-14 and is now badly stale.
3. **Never bulk-upload `wp-content/`.** It overwrites media the team uploaded. Upload single files.

---

## Deploying code

```bash
php -l wp-content/themes/tanya-barrans/functions.php
```

```bash
scp -P 65002 wp-content/themes/tanya-barrans/functions.php \
  u370548507@82.197.83.94:~/domains/violet-wren-104886.hostingersite.com/public_html/wp-content/themes/tanya-barrans/functions.php
```

Then load the site and confirm it returns 200.

**Lint before every upload.** A PHP error in `functions.php` white-screens the entire site while three people are mid-edit. That is the main way code work can damage content work, and it is entirely preventable.

Say something in the group chat first. Not because it corrupts anything — different layer — but so a ten-second outage doesn't send someone chasing a problem that already fixed itself.

**If the change affects what visitors see, flush the CDN afterwards:** hPanel → Performance → CDN → Flush cache. Hostinger's CDN caches aggressively and will keep serving the old page otherwise.

---

## Database snapshots

```bash
ssh -p 65002 u370548507@82.197.83.94 \
  "cd ~/domains/violet-wren-104886.hostingersite.com/public_html && wp db export -" \
  > "db-backups/staging-$(date +%Y-%m-%d-%H%M).sql"
```

**A snapshot is for disaster, not for undo.** Restoring one taken two hours ago also erases every article and template edit made in those two hours. Restore only when the database is genuinely broken.

To reverse your own change, write the inverse query.

---

## When you must change the database

Occasionally a fix has to happen in content — demoting a duplicate `H1`, correcting a URL. Then:

- Always a `WHERE` clause narrow enough that you can **predict the row count before running it**
- Confirm the result matches that prediction. `2 rows affected` when you expected 2 is the check
- Prefer a moment when nobody is editing that page
- Never a bare `UPDATE wp_posts SET ...` without a filter

Worked example — the fix that demoted the duplicate `H1`s on About and Contact:

```sql
UPDATE wp_posts SET post_content =
  REPLACE(REPLACE(REPLACE(post_content,
    '"level":1,', '"level":2,'),
    '<h1 class="wp-block-heading', '<h2 class="wp-block-heading'),
    '</h1>', '</h2>')
WHERE post_name IN ('about','contact')
  AND post_status = 'publish' AND post_type = 'page';
```

Two pages, one heading each, three string replacements per page, anchored on `wp-block-heading` so it could not touch the page title. Row count verified beforehand.

---

## Finding out what changed, and who changed it

Three layers, three histories. Between them almost nothing is invisible any more.

### Content — native WordPress revisions

Pages, posts, **templates** and menus all keep revisions, with an author and a timestamp against each one. History goes back to 2026-07-06.

In the admin: open the page or template and use the Revisions panel for a visual diff.

From the database, which is faster when you do not yet know *what* changed:

```sql
SELECT r.post_date, u.display_name AS who, parent.post_type, parent.post_name,
       LENGTH(r.post_content) AS len
FROM wp_posts r
JOIN wp_posts parent ON parent.ID = r.post_parent
LEFT JOIN wp_users u ON u.ID = r.post_author
WHERE r.post_type = 'revision'
ORDER BY r.post_date DESC
LIMIT 20;
```

This is what identified who introduced the placeholder text on the Buy layout, and when, after grepping the repository for it had turned up nothing — because it never lived in the repository at all.

### Settings, plugins and theme — the audit log

WordPress keeps no history for `wp_options`; it overwrites in place. `functions.php` fills that gap for the sixteen options that can actually break something, plus plugin activation, deactivation and theme switches.

```bash
grep tanya-audit ~/.logs/error_log_*
```

Each line carries the user, what changed, and before → after:

```
[tanya-audit] tanya | option changed: siteurl | https://staging...  ->  https://tanyabarrans.com
[tanya-audit] tanya | plugin ACTIVATED | hostinger-reach/hostinger-reach.php
```

Add an option to `tanya_audited_options()` to watch it, or hook the `tanya_audited_options` filter. Resist watching everything: transients and cron writes would bury the entries that matter within a day.

**Its one blind spot: direct SQL.** An `UPDATE wp_options` bypasses `updated_option` and records nothing — which is a further reason to keep database edits rare, narrow, and mentioned to whoever else is working.

### Code — git

`git log`, as normal, for everything the repository still owns.

---

## Starting a session

The site changes between sessions without the developer touching it. **Nothing from a previous session is a verified fact.**

Before relying on any earlier finding, check the live site. Useful first look:

```sql
SELECT post_type, COUNT(*), MAX(post_modified) FROM wp_posts
WHERE post_modified > DATE_SUB(NOW(), INTERVAL 48 HOUR)
GROUP BY post_type;
```

**Read rendered body copy, not only tags and structure.** Visible placeholder text sat on the public Buy and Sell pages through two audits that checked metadata, headings, schema and contrast — and missed it, because it lived in database template overrides that no amount of grepping this repo would ever find.

---

## The decision to revisit after launch

Whether the team keeps editing templates in the Site Editor, or those seven come back under version control. Both are defensible; having it happen by accident is not.

Right now the Site Editor wins by default, which is the correct call while the site is being filled with content. Once it settles, decide deliberately — because as things stand, template work done in this repo is invisible.
