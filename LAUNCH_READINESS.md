# Launch Readiness — Baseline Go-Live

**Status as of 2026-08-11.** Verified against the running site, the repository at commit `612321e`, and the Tanya-approved Notion sources.

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

## 1. Legal and trust — hard blockers

These carry real risk. The site should not go live without them.

### 1.1 Privacy policy — **TANYA**

The footer link was removed because `/privacy-policy/` returned a 404. The page exists in the database but is still unedited WordPress boilerplate: it contains "Suggested text:" tutorial markers and lists the local development address as the site address.

**Needed:** approved privacy policy copy. A real policy is expected once the site collects anything — the newsletter signup, and any contact form, both qualify.

**Then:** publish the page and restore the footer link (a one-line change).

### 1.2 Brokerage and legal disclosures — **SEAN**, verify

The footer already carries "Brokered by John L Scott Real Estate. Each office is independently owned and operated," plus a real email and phone number. Needs a final read-through against brokerage requirements before launch.

### 1.3 No placeholder or unverifiable claims — **TANYA**

Two items on the site are not yet substantiated:

- The homepage testimonial is explicitly labelled placeholder text in the code and needs a real, approved client quote.
- The credibility band shows "10+ years", "100%", and "5★". These are unverified. Either approve them as accurate, replace them with figures that are, or remove the band.

The Brand Bible is explicit on this point: do not invent brand facts, client results, or statistics.

### 1.4 Google reviews link — **TANYA**

The homepage link points at Google's *write-a-review* URL, so its label now reads "Worked with Tanya? Leave a Google review," which is honest but is a different CTA than intended.

**Needed:** the public Google Business profile URL, so the link can read "Read Tanya's Google reviews" and go somewhere that shows them.

---

## 2. Lead capture — the site cannot convert without this

### 2.1 Contact form — **built and connected**

The contact form is live on the Contact page and files submissions into **Follow Up Boss**. The blueprint fields are all present: name, email, phone, what the enquiry is about, timing, preferred contact method, and a message. Protections are server-side — REST nonce, honeypot, validation, and allow-lists on every choice field. The API key lives in `wp-config.php` and never reaches the browser.

Verified against the live account: a test lead was created through the real API, arrived assigned to Tanya and correctly tagged, and was then deleted. Direct email, phone, and the scheduling link remain on the page for anyone who prefers them.

**Still to do at launch:** submit the form once on the deployed site and confirm it lands in Follow Up Boss. The integration is proven against the API but has never run from production.

#### The email fallback is weaker than it looks — **SEAN**, decide

If Follow Up Boss cannot be reached, the form falls back to emailing Tanya with `wp_mail()` so a lead is never silently dropped. That safety net is thinner than the code implies, and it is worth understanding rather than trusting:

- **WP Engine does not run mail servers.** PHP mail is not delivered on their platform without an external service.
- **Shared hosts fare no better.** Mail sent straight from a web server usually fails SPF and DKIM checks and is filtered as spam.
- **Tanya's email is Google Workspace**, which is strict about unauthenticated senders — the most likely outcome is silent filtering rather than a bounce.

So in practice Follow Up Boss is not the primary path with a backup; it is close to the only path. That is acceptable because the CRM is the real destination and it is tested, but the fallback should not be described to Tanya as a guarantee.

**The fix, when convenient:** route `wp_mail()` through an authenticated SMTP service. A free tier from Postmark, SendGrid, or Google Workspace SMTP is enough at this volume. **Not a launch blocker** — it only matters in the minutes when Follow Up Boss is down — but until it is done, a CRM outage means a lost enquiry.

### 2.2 Newsletter — **SEAN**, verify

The Flodesk integration is built and the API key is configured locally. Needs one real end-to-end test submission, and confirmation the key is present in the production environment at launch.

### 2.3 Homebot — **SEAN**, verify

Both hubs embed the correct live Homebot experiences (buyer `hmbt.co/YgFMRD`, seller `hmbt.co/WYVKc9`) with direct links alongside. Needs a desktop and mobile pass to confirm both flows complete.

---

## 3. Content — no public path may lead somewhere empty

### 3.1 Navigation — done

Trimmed from eleven items to six: Home, Buy, Sell, Blog, About Tanya, Contact. Every remaining item leads to real content. Neighborhoods, Love Your Home, Rooted in Renton, and Resources remain published and reachable by URL so work can continue; they are simply withheld from public navigation until each is useful.

### 3.2 Remaining CTAs that point at empty pages — **SEAN**, pending direction

Four links in the theme still lead to near-empty destinations:

| Location | Points at | Content there |
|---|---|---|
| Homepage hero, second button | `/rooted-in-renton/` | 127 characters |
| Homepage "rooted teaser" section | `/rooted-in-renton/` | 127 characters |
| Homepage pathways section | `/love-your-home/` | 163 characters |
| Article template | `/rooted-in-renton/` | 127 characters |

The Journal's topic navigation also lists five categories, and three have no posts (Home Selling Tips, Local Life, Home & Garden).

**Recommendation:** withhold these until the destinations are real. Each is a small, reversible edit and a one-line restore later.

### 3.3 Rooted in Renton — **TEAM**, then **TANYA**

The blueprint states plainly: **"Do not launch this section empty."** It sets the minimum at three local business or restaurant stories, two parks or outdoor guides, one Renton weekend guide, one neighborhood-connected story, and one introductory video.

The Master Content Calendar currently holds **one** Rooted item — "LAUNCH 3 — Rooted in Renton Starts Here," a Reel, status *Not started*, waiting on Tanya, originally due 5 August.

Two brand assets are also missing, and neither can be invented:

- **The circular Rooted badge.** The Brand Bible's "Assets Still to Finalize" checklist has *Primary logo suite* unchecked.
- **The exact mustard value.** The approved Love Where You Live palette contains no mustard at all — the Rooted section is specified as navy-and-mustard, but that colour exists only inside an approved reference image, not as a recorded hex code.

**This is not a launch blocker** provided Rooted stays out of public navigation, which it now is. It becomes one the moment any public CTA points at it.

### 3.4 The ten launch articles — **TEAM**, then **TANYA**

Assigned five to Neil and five to Zea, due 5 August. The Journal currently shows three older articles. These flow through the documented path: Neil and Zea deliver complete copy-and-art packages, Tanya approves, Sean formats and publishes.

The artwork half of that was blocked on image dimensions. That blocker is now cleared — see item 5.1.

### 3.5 About page — **TANYA**

Usable but not aligned with the approved About blueprint. Needs a real portrait plus approved story, working style, and community connection. No professional proof should be added until verified.

---

## 4. Technical and measurement

### 4.1 Analytics — **TANYA**, then **SEAN**

Not installed. No tracking code of any kind is present. Needs a decision on provider (GA4 or a privacy-friendly alternative such as Plausible) and on the consent approach, since that interacts with the privacy policy above.

### 4.2 Search Console — **TANYA**, then **SEAN**

No verification tag present. Needs the property to be created on the real domain, which means it follows domain selection.

### 4.3 Domain and hosting — **decided, pending purchase**

**Hosting recommendation: WP Engine.** The reasoning is specific to this build rather than general reviews.

WordPress.com is only viable on the **Business** plan, around $300/yr. Every tier below it forbids uploading a custom theme, and this site is a custom theme, so those plans cannot run it at all.

Of the rest, WP Engine costs roughly $150/yr more than GoDaddy or Hostinger and earns it twice over here:

- **LocalWP pushes to it in one click** — files, database and URL rewriting together. Everywhere else, each deploy is the manual export-upload-import-replace sequence in `deploy/DEPLOY.md`. Against 5–10 hours a week, that difference compounds.
- **Staging is included and reports itself as staging.** Google Analytics only fires when the environment reports `production`, so on a generic host a staging copy would look like production and put test traffic into Tanya's real reports.

This site is unusually light — 8 MB of files, a 232 KB database, and **zero plugins**, since the contact form, newsletter, analytics and SEO are all theme code. Much of what managed hosting charges for is plugin maintenance we will never need, so raw performance tiers are not the deciding factor. Workflow is.

GoDaddy would work, but its cheap price is a first-term promotion that typically doubles on renewal, narrowing the gap while keeping the manual deploy. Having the domain there is not a reason — domain and hosting are independent, and pointing GoDaddy DNS at WP Engine takes minutes. If budget is the deciding factor, Hostinger is the better cheap option.

One requirement for any host: the theme makes outbound API calls to Follow Up Boss and Flodesk, so unrestricted outbound HTTP is needed.

---

The domain is **`tanyabarrans.com`**, registered at GoDaddy with DNS managed there. It currently 301-forwards to `tanyab.johnlscott.com`. That forward is Tanya's own setting — John L Scott has no relationship with the domain, so switching it off needs no approval from the brokerage and is not a blocker.

Tanya does not pay for GoDaddy hosting, only the domain renewal, so there was nowhere to deploy. **The decision is WP Engine**, pending purchase of the plan. That also supplies the WP Engine ID that LocalWP needs, which resolves the Live Link error seen on 12 August.

Sequence: buy the plan, create an install, push from LocalWP, verify on the temporary WP Engine address, then point GoDaddy DNS at WP Engine and disable the forward. Going live is that last step and nothing before it is public.

No redirect map is needed. Nothing currently lives on `tanyabarrans.com` to preserve — it has only ever forwarded.

### 4.4 Metadata and canonical — done

The Journal index had no canonical URL; it now has one, and paged views point at themselves. Buy, Sell, About, and Contact have intent-specific titles and unique descriptions. One H1 per page across all changed surfaces.

### 4.5 Structured data — **SEAN**, after content

Add only where visible content substantiates it. Not appropriate until the claims above are verified.

---

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
- [ ] Focus states, labels, heading order, alt text, colour contrast, reduced motion
- [ ] No horizontal overflow *(currently 3 px on mobile from the newsletter input — minor, outstanding)*
- [ ] Brokerage disclosure, contact details, and legal links present
- [ ] No placeholder copy anywhere

---

## 7. Safe to grow after launch

Deliberately excluded from the baseline so they do not delay it: Rooted in Renton, Neighborhoods, Love Your Home, Resources, IDX and live listings, remaining Journal categories, downloadable resources, testimonials beyond the first approved one, and structured data.

---

## The short version for Tanya

Six decisions unblock nearly everything:

1. **Privacy policy copy** — hard legal blocker.
2. **Where contact form submissions should go** — the site cannot capture leads without it.
3. **The stats and testimonial** — approve, correct, or remove.
4. **The public Google reviews URL.**
5. **Analytics provider and the production domain.**
6. **Rooted in Renton** — confirm it is deliberately a post-launch section, so the homepage links pointing at it can be withheld for now.

Items 1 and 2 are the only true hard blockers. Everything else either has a safe interim state already in place, or can grow after launch.
