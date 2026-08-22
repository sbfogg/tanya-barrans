# Launch Readiness — Baseline Go-Live

**Status as of 2026-08-22.** Verified against the live staging site, its database, the audit log, and the repository.

This is the minimum required to put the site in front of the public without embarrassment or legal exposure. It is deliberately *not* the full vision. Anything that can safely grow after launch is listed at the bottom rather than treated as a blocker.

---

## How to read this

Each item says who has to move next. Nothing here is waiting on a decision that has already been made — if an item is blocked, the decision genuinely has not been recorded anywhere in Notion or the repository.

| Marker | Meaning |
|---|---|
| **TANYA** | Needs a decision or approved material from Tanya. Cannot be guessed. |
| **TEAM** | Needs a content or artwork package from Neil or Zea, then Tanya's approval. |
| **SEAN** | Buildable now; no outside input required. |

---

## 0. Deployment status — the site is now hosted

**The site is deployed and running on Hostinger staging:**
`https://violet-wren-104886.hostingersite.com`

Nothing is public. `tanyabarrans.com` still forwards to John L Scott, and no visitor can reach the staging address unless given the URL.

| Piece | State |
|---|---|
| Hosting | Hostinger Business (Unlimited), US Massachusetts, paid through 2030-08-13 |
| WordPress | Installed; theme, uploads and database imported |
| Theme active | Tanya Barrans Real Estate 0.3.4, current with the repository |
| Published content | 23 pages, 10 posts |
| Template overrides | 13 — see `WORKFLOW.md` |
| Active plugins | 2 — `hostinger-reach`, `wp-reviews-plugin-for-google` |
| Pages verified | Every public page returns 200, no PHP warnings |
| Desktop / mobile | Passes at 1440 px and 390 px |
| Contact form → Follow Up Boss | **Verified working from production** |
| SSH / WP-CLI | Enabled and working |
| Change history | Content revisions, plus the `tanya-audit` log for settings and plugins |

**There are two installs on this plan.** `salmon-otter-516624.hostingersite.com` was the original and is being kept deliberately — Tanya describes it as a staging copy of staging. It is stale and **must not be deployed to**. Everything below refers to violet-wren.

### Two deployment facts that must not be forgotten

**1. `siteurl` and `home` currently point at the staging URL.** They were changed in the database so staging could be tested. **At go-live both must be set back to `https://tanyabarrans.com`.** Nothing in `wp-config.php` overrides them — it is a single database change.

**2. Images on `/about/` and `/contact/` are broken on staging only.** Four images are referenced by absolute `tanyabarrans.com` URLs. The files exist and are correct; they resolve the moment DNS switches. Not a defect, do not "fix" them.

---

## 1. Legal and trust — hard blockers

These carry real risk. The site should not go live without them.

### 1.1 Privacy policy — **written and published; TANYA to read**

Replaced with real copy on 12 August 2026 and verified live on staging: ~5,600 characters across nine sections — who runs the site, what is collected and when, who it is shared with, retention, choices, security, children, changes, and contact. No WordPress boilerplate or "Suggested text:" markers remain, and the footer link is restored.

It describes what the site *actually* does: the Follow Up Boss contact form, the Flodesk newsletter, and GA4.

**Still needed from Tanya:** a read-through and confirmation it matches how she handles client data. It is accurate about the website, but only she can confirm the business practices around it. **Not a hard blocker any more** — a real, accurate policy is published.

### 1.2 Brokerage and legal disclosures — **SEAN**, verify

The footer already carries "Brokered by John L Scott Real Estate. Each office is independently owned and operated," plus a real email and phone number. Needs a final read-through against brokerage requirements before launch.

### 1.3 No placeholder or unverifiable claims — **resolved**

Both items are done, verified on the live staging site 2026-08-18:

- **Real testimonials are published.** Named Google reviews from Bernice Manriquez, Margo Hooper and Christian Herzberger replace the placeholder quote.
- **The unverified credibility band is gone.** "10+ years", "100%" and "5★" no longer appear anywhere on the homepage.

The Brand Bible principle held: nothing was invented to fill the gap, and the band was removed rather than guessed at.

**One placeholder line remains on each of `/buy/` and `/sell/`** — down from three. It is not a claim; it is instructional text about photography that was written into those layouts and published by accident. Tanya is clearing it as her photo library grows. Not a legal or trust issue, but it must not ship.

### 1.4 Google reviews link — **resolved**

The homepage now reads "Read Tanya's Google reviews" and links to `https://g.page/r/CTDpOcL-7SDkEAE`, which resolves to her Google Business Profile listing. Verified by following the redirect on 2026-08-18. Label and destination match.

### 1.5 Property listings — **verified real, 2026-08-22**

Three listings are published, plus a sales portfolio page. **Tanya has confirmed all are real and current.** That matters more than any SEO item here: published property detail that is stale, sold, or another brokerage's is a regulatory problem rather than a marketing one.

**Standing rule:** whenever a listing closes or expires, the page comes down or is marked closed the same week. Worth agreeing who owns that before launch, because nothing on the site will prompt it.

---

## 2. Lead capture — the site cannot convert without this

### 2.1 Contact form — **built and connected**

The contact form is live on the Contact page and files submissions into **Follow Up Boss**. The blueprint fields are all present: name, email, phone, what the enquiry is about, timing, preferred contact method, and a message. Protections are server-side — REST nonce, honeypot, validation, and allow-lists on every choice field. The API key lives in `wp-config.php` and never reaches the browser.

Verified against the live account: a test lead was created through the real API, arrived assigned to Tanya and correctly tagged, and was then deleted. Direct email, phone, and the scheduling link remain on the page for anyone who prefers them.

**Verified from production on 2026-08-13.** A live submission from the deployed staging site was accepted by Follow Up Boss — the endpoint returned success and the error log recorded neither a rejection nor a fallback. This item is closed.

**One security follow-up — SEAN:** the Follow Up Boss API key was pasted into a working transcript during deployment. It is admin-level on a CRM holding real client records. **Generate a replacement in Follow Up Boss and revoke the old one before launch.**

#### SMTP — built, not yet configured — **TANYA**, then **SEAN**

SMTP support is now built into the theme. It activates only when `TANYA_SMTP_HOST`, `TANYA_SMTP_USER` and `TANYA_SMTP_PASS` are all defined, and failures are written to the error log.

**It is not configured on production.** The constants are present but commented out, because the Gmail app password does not exist yet. Creating one requires **2-Step Verification on Tanya's Google account** — that is the blocker and it sits with Tanya.

**Consequence until then:** `wp_mail()` falls back to plain PHP mail, which Google is likely to filter silently. **WordPress password resets will not arrive.** Anyone locked out of `/wp-admin` needs a database-level reset. Worth doing before launch rather than discovering it later.

The analysis below explains why unauthenticated mail from a web server fails, and it is still accurate.

#### The email fallback is weaker than it looks — **SEAN**, decide

If Follow Up Boss cannot be reached, the form falls back to emailing Tanya with `wp_mail()` so a lead is never silently dropped. That safety net is thinner than the code implies, and it is worth understanding rather than trusting:

- **WP Engine does not run mail servers.** PHP mail is not delivered on their platform without an external service.
- **Shared hosts fare no better.** Mail sent straight from a web server usually fails SPF and DKIM checks and is filtered as spam.
- **Tanya's email is Google Workspace**, which is strict about unauthenticated senders — the most likely outcome is silent filtering rather than a bounce.

So in practice Follow Up Boss is not the primary path with a backup; it is close to the only path. That is acceptable because the CRM is the real destination and it is tested, but the fallback should not be described to Tanya as a guarantee.

**The fix, when convenient:** route `wp_mail()` through an authenticated SMTP service. A free tier from Postmark, SendGrid, or Google Workspace SMTP is enough at this volume. **Not a launch blocker** — it only matters in the minutes when Follow Up Boss is down — but until it is done, a CRM outage means a lost enquiry.

### 2.2 Newsletter — **SEAN**, key needs re-entering on production

The Flodesk integration is built and the key works locally. **On production the key is malformed** — a newline was introduced while pasting a 232-character value into `nano`, which is valid PHP and passes `php -l` but corrupts the key.

**Effect:** newsletter signups fail silently but *gracefully* — the integration block is skipped, nothing errors, and no visitor sees a failure. Not a launch blocker, but the signup form does nothing until fixed.

**Fix:** re-enter the key as a single unbroken line — edit locally and upload, or use `nano -w`. Then submit one real test and confirm it reaches Flodesk.

### 2.3 Homebot — **SEAN**, verify

Both hubs embed the correct live Homebot experiences (buyer `hmbt.co/YgFMRD`, seller `hmbt.co/WYVKc9`) with direct links alongside. Needs a desktop and mobile pass to confirm both flows complete.

---

## 3. Content — no public path may lead somewhere empty

### 3.1 Navigation and area pages — **built**

Navigation now reads: Home, Buy, Sell, Listings, Neighborhoods, Blog, About, Contact — with **Renton, Kent, Covington, Maple Valley and Newcastle nested beneath Neighborhoods**, which is exactly the hierarchy Tanya asked for. That item is closed.

Tanya built this herself in the Site Editor between 20 and 21 August, along with `/renton-parks-outdoors/`, three property listing pages and a sales portfolio page. Six new template overrides came with it (see `WORKFLOW.md`).

### 3.2 Pages that exist but are not ready to be found — **handled in code, needs deploy**

Seven published pages have little or no content of their own:

| Page | Rendered | State |
|---|---|---|
| `/kent/` `/covington/` `/maple-valley/` `/newcastle/` | ~1,600 chars each | Honest stubs: "the full guide is still being written" |
| `/living-in-renton/` `/resources/` | ~580 chars | Empty — header and footer only |
| `/listing-template-duplicate-me/` | 3,207 chars | Internal working template, titled "LISTING TEMPLATE — Duplicate Me" |

The four area stubs are a reasonable holding page for someone following a link, and poor material for a search result — four near-identical pages differing only by place name read as thin content, and it is the local searches they will eventually win that get devalued. The listing template should never have been public.

All seven are now `noindex` and excluded from the sitemap in theme code, while staying published and reachable so work continues and links keep working. **Remove a slug from `tanya_unfinished_pages()` the moment its page has real content.**

**Committed as `aa4ac41`; not yet uploaded to violet-wren.**

### 3.2a Duplicate article — **resolved 2026-08-22**

Two published posts carried the identical title, H1 and opening copy — *Moving to Renton, WA: A Practical Guide to Planning Your Move* — each declaring itself canonical, competing for the exact query Tanya most wants locally. Caused by the `wordpress-importer` plugin creating a second copy rather than replacing the first.

Post 65 is now a draft; post 90160 remains published. `wordpress-importer` has been deactivated so it cannot recur. Two further drafts of the same article remain unpublished and harmless.

### 3.3 Rooted in Renton — **TEAM**, then **TANYA**

The blueprint states plainly: **"Do not launch this section empty."** It sets the minimum at three local business or restaurant stories, two parks or outdoor guides, one Renton weekend guide, one neighborhood-connected story, and one introductory video.

The Master Content Calendar currently holds **one** Rooted item — "LAUNCH 3 — Rooted in Renton Starts Here," a Reel, status *Not started*, waiting on Tanya, originally due 5 August.

**Both previously-missing brand assets are now resolved:**

- **The circular Rooted badge** has been built in the theme, redrawn from the approved reference in the Notion blueprint — navy disc, gold rings, "ROOTED IN / RENTON" on arced text paths, serif R, EST. 1901, gold heart, cream rim. It is live in `patterns/renton-hub.php`.
- **The mustard value is `#D99F3A`**, supplied by Tanya on 2026-08-13.

What remains outstanding here is **content**, not artwork.

**This is not a launch blocker** provided Rooted stays out of public navigation, which it now is. It becomes one the moment any public CTA points at it.

### 3.4 The ten launch articles — **TEAM**, then **TANYA**

Assigned five to Neil and five to Zea, due 5 August. The Journal currently shows three older articles. These flow through the documented path: Neil and Zea deliver complete copy-and-art packages, Tanya approves, Sean formats and publishes.

The artwork half of that was blocked on image dimensions. That blocker is now cleared — see item 5.1.

### 3.5 About page — **TANYA**

Usable but not aligned with the approved About blueprint. Needs a real portrait plus approved story, working style, and community connection. No professional proof should be added until verified.

---

## 4. Technical and measurement

### 4.1 Analytics — **installed, one incident to clean up**

GA4 is installed with Tanya's measurement ID **`G-K9H4JX6HTY`**, in theme code — no plugin.

It stays off anywhere that is not the real public site, via `tanya_is_public_site()`: it requires `wp_get_environment_type()` to report `production`, then refuses to fire on hostnames containing `localhost`, `.local`, `.test`, `staging.`, `stage.`, `dev.`, `.hostingersite.com`, `.wpengine.com`, `.instawp.xyz` or `.temp.domains`.

> **⚠ It did not do this between 13 and 18 August 2026.** The marker list was written before a host had been chosen and never included `.hostingersite.com`, so the staging site reported itself as production. Every page load during deployment and testing — automated checks and manual browsing — was recorded in Tanya's live GA4 property.
>
> **Cleanup — SEAN:** in GA4, exclude **both** staging hostnames — `salmon-otter-516624.hostingersite.com`, which is where the 13–18 August pollution came from, and `violet-wren-104886.hostingersite.com`, the current one — or treat 13–18 August as invalid. The property had no real traffic yet so the damage is contained, but any pre-launch baseline drawn from those dates is wrong.
>
> Fixed in the same commit that took ownership of `robots.txt`. Verified: zero `gtag` tags across the staging site afterwards.

**At go-live:** load a page on `tanyabarrans.com`, then check **GA4 → Realtime**. The visit should appear within about 30 seconds. That is the first moment analytics is expected to work.

### 4.1a robots.txt — **SEAN**, verify on launch day

**This is the single item most capable of making the launch pointless, and it cannot be settled before DNS switches.**

The staging site serves a `robots.txt` that disallows Googlebot from the entire site:

```
User-agent: Googlebot
Disallow: /
```

There is **no `robots.txt` file in the web root** — Hostinger's CDN intercepts the path and injects this before the request reaches WordPress. Proven by comparing two URLs: `/?robots=1` (WordPress's own handler) returns the theme's rules, while `/robots.txt` returns Hostinger's. A doubled `Content-Type` header on the latter confirms the injection.

This is preview-domain protection and *should* stop applying on a real custom domain. That is an expectation, not a verified fact.

The theme now owns `robots.txt` through the `robots_txt` filter at priority 9999, so WordPress produces the right answer as soon as a request actually reaches it — closed to all crawlers on any copy of the site, and proper rules plus the sitemap on production.

**The launch-day test**, immediately after DNS switches:

```bash
curl -s https://tanyabarrans.com/robots.txt
curl -s "https://tanyabarrans.com/?robots=1"
```

**Identical output means WordPress is in control and the site is crawlable.** If they differ, the CDN is still intercepting — that is a support ticket with Hostinger, and until it is resolved the site will not be indexed by Google at all.

### 4.2 Search Console — **TANYA**, then **SEAN**

No verification tag present. Needs the property to be created on the real domain, which means it follows domain selection.

### 4.3 Domain and hosting — **decided and purchased: Hostinger**

> **This section's original recommendation (WP Engine) was superseded on 2026-08-13.** Hostinger Business was purchased and the site is deployed. The reasoning below is kept because it still explains what was traded away.
>
> **Why the decision changed:** the WP Engine case rested on one-click LocalWP deploys and a staging environment that reports itself as staging. Weighed against roughly $150/yr more on a site that is essentially a low-traffic blog, maintained by one developer who fixes his own bugs, that convenience did not justify the cost. The analytics concern turned out to be a non-issue: WordPress defaults `wp_get_environment_type()` to `production`, and the theme's own hostname check keeps GA4 off on `.hostingersite.com` addresses regardless.
>
> **What was accepted in exchange:** every deploy is the manual export-upload-import sequence in `deploy/DEPLOY.md`, and there is no push-button staging. Both proved workable — see the deployment notes below.

**Lessons from the actual deployment**, worth knowing before the next one:

- **Hostinger's CDN caches aggressively and independently of the origin.** After the database import the site served the old fresh-install pages for over an hour. Nothing on the server fixed it — not `wp cache flush`, not deleting cache directories, not the object-cache drop-in. The fix is **hPanel → Performance → CDN → Flush cache**. Expect to need this after any large content change.
- **The hPanel file editor does not load reliably.** It returned an empty stub every time. Use SSH.
- **`wp-config.php` is not in git**, so API keys never travel with a deployment and must be added by hand on the server each time.
- **Do not paste long API keys into `nano` without `-w`.** It inserts a real newline mid-string, which is valid PHP and passes `php -l`, but silently corrupts the key.

The original WP Engine reasoning follows.

WordPress.com is only viable on the **Business** plan, around $300/yr. Every tier below it forbids uploading a custom theme, and this site is a custom theme, so those plans cannot run it at all.

Of the rest, WP Engine costs roughly $150/yr more than GoDaddy or Hostinger and earns it twice over here:

- **LocalWP pushes to it in one click** — files, database and URL rewriting together. Everywhere else, each deploy is the manual export-upload-import-replace sequence in `deploy/DEPLOY.md`. Against 5–10 hours a week, that difference compounds.
- **Staging is included and reports itself as staging.** Google Analytics only fires when the environment reports `production`, so on a generic host a staging copy would look like production and put test traffic into Tanya's real reports.

This site is unusually light — 8 MB of files and a 232 KB database — and **nothing user-facing is powered by a plugin**: the contact form, newsletter, analytics and SEO are all theme code. Much of what managed hosting charges for is plugin maintenance we largely do not need, so raw performance tiers are not the deciding factor. Workflow is.

> **Correction, 2026-08-18.** This section previously claimed "zero plugins" outright, and that was repeated in the client-facing summary. It is not literally true and has not been for some time. Plugin state has changed twice in a single day without the developer acting:
>
> - `wpforms-lite` was active this morning — a leftover from an early contact form attempt, rendering nothing — and has since been deactivated by someone else.
> - **`hostinger-reach` is now active** and enqueues a stylesheet and script on *every* page for a subscription block that is not used anywhere. See 4.6.
>
> The architectural point stands: no plugin powers anything a visitor uses. The absolute claim does not. Check `active_plugins` before repeating it.

GoDaddy would work, but its cheap price is a first-term promotion that typically doubles on renewal, narrowing the gap while keeping the manual deploy. Having the domain there is not a reason — domain and hosting are independent, and pointing GoDaddy DNS at WP Engine takes minutes. If budget is the deciding factor, Hostinger is the better cheap option.

One requirement for any host: the theme makes outbound API calls to Follow Up Boss and Flodesk, so unrestricted outbound HTTP is needed.

---

The domain is **`tanyabarrans.com`**, registered at GoDaddy with DNS managed there. It currently 301-forwards to `tanyab.johnlscott.com`. That forward is Tanya's own setting — John L Scott has no relationship with the domain, so switching it off needs no approval from the brokerage and is not a blocker.

Tanya does not pay for GoDaddy hosting, only the domain renewal. **Hostinger Business was purchased on 2026-08-13** and the site is deployed to its staging address.

Remaining sequence: finish the items below, set `siteurl`/`home` back to `https://tanyabarrans.com`, add `tanyabarrans.com` to the Hostinger site, point GoDaddy DNS at Hostinger, and **turn off Domain Forwarding** — that last switch is what makes the site public. Nothing before it is visible to anyone.

No redirect map is needed. Nothing currently lives on `tanyabarrans.com` to preserve — it has only ever forwarded.

### 4.4 Metadata and canonical — done

The Journal index had no canonical URL; it now has one, and paged views point at themselves. Buy, Sell, About, and Contact have intent-specific titles and unique descriptions. One H1 per page across all changed surfaces.

### 4.5 Structured data and social sharing — **done**

An SEO audit on 18 August found both missing from the theme entirely. Both are now live and verified on staging.

**Structured data.** A `RealEstateAgent` node carrying Tanya's name, phone, email, logo, both social profiles, `memberOf` John L Scott, and the four areas served — linked to `WebSite`, and to `BlogPosting` on single posts, through a shared `@id` graph so Google reads one entity rather than several unrelated ones. Validated by parsing the emitted JSON.

Two deliberate omissions, on the same principle as everywhere else in this document: **no rating or review markup**, which must come from a verified source rather than being hand-written, and **no postal address**, because none is published on the site and a fabricated one is actively harmful in local results.

**Open Graph and Twitter cards.** Previously every share to Facebook, Instagram, or Messages rendered as a bare grey link. Now 8 Open Graph and 4 Twitter tags per page, `summary_large_image`, with articles using their own featured image and everything else falling back to the Love Where You Live hero.

**Also fixed in the same pass:** four meta descriptions ran past Google's truncation point and two pages (the Renton hub and Neighborhoods) shared an identical 23-character fallback — all nine pages now carry unique descriptions of 104–124 characters. The About and Contact pages each had two `H1`s; the duplicates are demoted to `H2` with no visual change, since both already carried an explicit font-size class. The author archive, a contentless duplicate of the Journal index, is now `noindex` and dropped from the sitemap.

---

### 4.6 Hostinger Reach overlaps Flodesk — **TANYA** to decide

The `hostinger-reach` plugin is active and loads a stylesheet and script on every page of the site, for a subscription block that is not placed anywhere. Dead weight on every request.

The larger question is not performance. **The site now has two email marketing systems.** The newsletter form visitors actually use posts to `tanya/v1/subscribe` and files subscribers into **Flodesk**, which is the approved tool and holds the existing list. Hostinger Reach is a second, parallel system that came with the hosting plan.

Running both means subscribers end up split across two lists with no single view of the audience — the kind of problem that is cheap to avoid now and expensive to reconcile later.

**Needed:** a decision from Tanya on which one is the newsletter platform. If it stays Flodesk, deactivate the plugin. If Reach replaces Flodesk, that is a larger change to the theme's newsletter endpoint and should be scoped separately, not slipped in before launch.

Not a launch blocker either way. It is a launch *decision*.

## 5. Open conflicts to resolve

### 5.1 Two different image specifications — **TANYA** or **SEAN** to reconcile

Two sets of numbers are in circulation and they disagree:

| Asset | Notion comment, 6 Aug (what the team received) | `IMAGE_SPECS.md`, 2 Aug (in the repo) |
|---|---|---|
| Hero, desktop | 2500 × 1200 | 2400 × 1600 |
| Hero, mobile | 1200 × 1500 | not specified |
| Inline image | 1200 wide | 1600 × 1067 |
| Card / thumbnail | 600 × 400 | 1200 × 900 |
| Social preview | 1200 × 630 | 1200 × 630 (only match) |
| Format | JPEG, 70–80% | WebP, 75–85 |
| File size | under 500 KB | tiered, 180–350 KB |

The Notion comment is what Neil and Zea are working from, so it should win. The repository file should be updated to match.

**One practical catch:** the mobile hero has nowhere to go. No template currently supports art-directed responsive images, so a separate 1200 × 1500 mobile crop cannot be used without a template change. Either add that support or tell the team desktop-only for now.

### 5.2 Existing photography does not match either spec — **SEAN**

Current site images are 1536 × 1024; the hero is 1720 × 914. One file exceeds the 500 KB ceiling.

---

## 6. Pre-launch QA — **SEAN**

Run against the real domain once the above is settled:

- [ ] Desktop and 390 px mobile pass on every public page
- [ ] Mobile menu works by keyboard and touch
- [ ] Every link and CTA resolves to real content
- [ ] Both Homebot flows complete on desktop and mobile
- [ ] Contact and newsletter submissions arrive at the correct destination
- [ ] No console errors or PHP warnings
- [x] Focus states, labels, heading order, alt text, colour contrast, reduced motion — **accessibility pass completed 2026-08-13.** A WCAG 2.1 AA review found only two real defects, both fixed: eyebrow text failing contrast on light backgrounds, and footer links failing contrast against navy. A visible `:focus-visible` outline was added across all interactive elements. Re-run on the real domain to confirm nothing regressed.
- [ ] No horizontal overflow *(currently 3 px on mobile from the newsletter input — minor, outstanding)*
- [ ] Brokerage disclosure, contact details, and legal links present
- [ ] No placeholder copy anywhere

---

## 7. Safe to grow after launch

Deliberately excluded from the baseline so they do not delay it: Rooted in Renton, Neighborhoods, Love Your Home, Resources, IDX and live listings, remaining Journal categories, downloadable resources, testimonials beyond the first approved one, and structured data.

---

## The short version — updated 2026-08-22

**Every original hard blocker is cleared**, and the site is substantially bigger than it was a week ago. Tanya has built the Neighborhoods hierarchy with five area pages beneath it, a Renton parks guide, three property listings and a sales portfolio — and published real Google reviews.

**Nothing on this list is urgent, because nothing is public.** These are all "before DNS switches", not "today".

### Tanya

1. **Flodesk or Hostinger Reach** for the newsletter — running both splits the subscriber list in two.
2. **2-Step Verification** on her Google account, so an app password exists and password resets can send.
3. **The last placeholder line** on Buy and Sell.

### Sean

4. **Deploy `functions.php` to violet-wren** — carries the noindex rules for the seven unfinished pages and the description fixes (`aa4ac41`).
5. **Re-enter the Flodesk key** — it is malformed, so signups go nowhere.
6. **Rotate the Follow Up Boss key** — it passed through a working transcript.
7. **Take the WordPress 7.1 update** before launch, not after.

### Launch day

Revert `siteurl`/`home` → add the domain → switch GoDaddy DNS and kill the forward → **verify `/robots.txt` matches `/?robots=1`** → SSL and full retest → GA4 Realtime, and exclude **both** staging hostnames → submit the sitemap.

### A standing rule worth agreeing now

**Property listings go stale.** When one closes, its page comes down or is marked closed that week. Nothing on the site will prompt anyone, and a live page for a sold home is a compliance problem rather than an SEO one.

### A note on this document

It drifted badly between 13 and 18 August, and again between 18 and 22 August — the second time because the site moved to a different install (`violet-wren`) while every document still described the old one. Deploying to the abandoned copy would have looked like a no-op with no error.

**Re-verify against the live site before acting on any section.** `WORKFLOW.md` explains why the site changes without the developer touching it, and the three change histories — content revisions, the `tanya-audit` log, and git — now make "what changed since I last looked" answerable in about a minute.
