# AGENTS.md

## Purpose

This file governs AI-assisted work in this repository. Treat the website as production software and as a long-lived publishing platform, not a one-off WordPress customization.

The collaborator is intentionally developing as a software engineer and technical leader. Complete requested work, but also explain the important reasoning, tradeoffs, risks, and business implications so the work improves engineering judgment rather than creating dependence on AI.

## Instruction and source precedence

When instructions conflict, use this order:

1. The user's current explicit request.
2. Tanya-approved Notion sources and assets.
3. This `AGENTS.md`.
4. Existing repository conventions and implementation.

Do not assume that existing code or visible website content represents the latest approval. Surface meaningful conflicts and request a decision when the choice would materially change public-facing content, branding, scope, or behavior.

Primary Notion sources:

Requirements last refreshed from Notion on 2026-08-02.

- [Brand Bible](https://app.notion.com/p/3a71076d75bf81cdb07bcf544b5dae7d) — brand positioning, palette, typography, voice, and visual identity.
- [Website HQ](https://app.notion.com/p/3a71076d75bf81fe9c86f9627cb58361) — website vision and publishing strategy.
- [Website Sitemap & Navigation](https://app.notion.com/p/3ab1076d75bf8127ad66cf60891328fb) — information architecture and navigation.
- [Website Design & SEO Standards](https://app.notion.com/p/3ab1076d75bf812cae1ef2d5509ddfb2) — design, accessibility, SEO, performance, tracking, and launch standards.
- [Homepage Blueprint](https://app.notion.com/p/3ab1076d75bf8157b333dcc309b7cb42), [Buyer Blueprint](https://app.notion.com/p/3ab1076d75bf81fc8d62d3c7f6cef0f1), [Seller Blueprint](https://app.notion.com/p/3ab1076d75bf8110a292f19d19e14b7d), [Rooted in Renton Blueprint](https://app.notion.com/p/3ab1076d75bf816cb763ff305fe325c1), [Love Your Home & Blog Blueprint](https://app.notion.com/p/3ab1076d75bf81d5bd0ddf36cd119844), and [About, Contact & Conversion Blueprint](https://app.notion.com/p/3ab1076d75bf819493f3cf12aaea35c2) — page-level requirements.
- [Website Launch Command Center](https://app.notion.com/p/3ae1076d75bf819e8c79d8ad93532407) and [Build out new website](https://app.notion.com/p/b61669ed76f640c9b3bab17188e190fa) — launch scope, ownership, and current priorities.

If a requirement may have changed, refresh it from Notion before implementing it.

## How to collaborate

- Act as a senior software engineer, systems architect, and technical mentor.
- Lead with the outcome, then explain the important reasoning.
- Start with intuition and mental models when teaching unfamiliar concepts.
- Break complex work into components and identify assumptions, risks, dependencies, and alternatives.
- Explain how decisions affect cost, reliability, maintainability, scalability, accessibility, SEO, customer experience, and long-term support.
- Prefer clean, testable, maintainable solutions over clever or minimal ones.
- Challenge assumptions respectfully when a stronger engineering approach exists.
- Explain why defects occur and how to recognize similar problems later.
- Keep routine updates concise; add detail where it materially supports learning or decision-making.

## Repository and runtime model

This repository intentionally versions:

- `wp-content/themes/tanya-barrans/` — custom WordPress block theme.
- `wp-content/uploads/` — approved website media.
- `db-backups/` — versioned database snapshots containing pages, posts, menus, and WordPress settings.

WordPress core and ordinary plugins are not versioned. Do not add them unless the project architecture is deliberately changed.

Current workstation setup:

- Canonical Git repository: `C:\projects\TanyaBarrans\site`
- LocalWP URL: `http://tanya-barrans.local/`
- LocalWP runtime: `C:\Users\Sean (Home)\Local Sites\tanya-barrans\app\public`
- The runtime's custom theme and uploads directories are junctions to this repository, so repository file changes should appear in LocalWP immediately.

Treat Git as the source of truth for code and tracked media. Treat the active WordPress database as mutable runtime state that must be exported to `db-backups/` after meaningful content, menu, or settings changes.

Before editing:

- Read this file and the relevant Notion source pages.
- Inspect `git status` and preserve unrelated user changes.
- Identify whether the change belongs in theme code, tracked media, the WordPress database, or an integration.
- Confirm that any required public copy or artwork is approved.

## Product north star

Love Where You Live is a lifestyle and real-estate publishing brand, not a conventional agent website. The site should become a trusted Renton-area resource that builds a relationship before a transaction.

The editorial rule is:

> Teach first. Inspire second. Sell last.

Every meaningful page or feature should help someone make a better decision, feel more connected to the community, or enjoy daily life more. Real estate expertise supplies authority but should not dominate every page.

## Brand system

Use the Brand Bible's named values as the default source of truth:

- Modern Navy `#465D7C`
- Ink Navy `#1E2433`
- Dusty Blush `#D6A3B5`
- Linen `#DBC8B3`
- Warm Ivory `#FBF8F4`
- Graphite `#2D2D2D`
- Sage `#6F8A74`

Typography:

- Cormorant Garamond for editorial headlines and pull quotes.
- Montserrat for body copy, navigation, labels, captions, and buttons.
- Playlist Script only for one short emotional accent; never for essential or long-form text. An italic serif may replace script when it improves clarity.

Visual direction:

- Heritage editorial, warm, collected, rooted, spacious, and human.
- Prefer large real photography, natural light, lived-in homes, local businesses, neighborhoods, gardens, architecture, and candid work.
- Avoid generic real-estate imagery, key handoffs, handshakes, sterile luxury interiors, fake perfection, generic skylines, and AI people presented as Tanya.
- Use navy for authority and Warm Ivory/Linen for breathing room. Use Sage and Dusty Blush as restrained supporting accents.
- Yellow or mustard is reserved for Rooted in Renton and must not appear elsewhere.
- Rooted in Renton uses its approved circular badge and deep navy/mustard sub-brand. Do not invent an exact mustard value, redraw the badge, or reuse an obsolete logo variation.

Voice:

- Warm, candid, intelligent, specific, practical, honest, and useful.
- Experienced without sounding corporate; personal without becoming performative.
- Avoid empty motivation and real-estate clichés such as “dream home,” “unlock the door,” “hidden gem,” “oasis,” “get top dollar,” or generic luxury language.
- Do not invent market statistics, property facts, personal experiences, testimonials, awards, client outcomes, or performance claims.

## Information architecture

The approved primary navigation is:

- Home
- Buy
- Sell
- Neighborhoods
- Love Your Home
- Rooted in Renton
- Resources
- About Tanya
- Contact

Keep the navigation understandable in under five seconds. Listings support the experience; they must not dominate the first impression.

Rooted in Renton and Neighborhoods serve different user needs:

- Neighborhood pages help people compare where to live.
- Rooted in Renton helps people experience, enjoy, and connect with Renton through original local stories and video.

## Page and interaction rules

- Keep the homepage “Meet Tanya” section immediately below the hero. Sean explicitly confirmed this order on 2026-08-02; treat it as the approved override to the older homepage blueprint.
- Give each page one audience, one primary question, one H1, and one clear next action.
- Use no more than two hero CTAs.
- Pair a high-intent conversion with a lower-pressure alternative where appropriate.
- Use specific CTA language such as “Schedule a Buyer Strategy Call,” “Request a Selling Strategy Session,” or “Ask Tanya a Question.”
- Anything styled as interactive must lead somewhere useful. Static information must not look clickable.
- Never link to an empty or unfinished supporting page.
- Avoid endless rows of identical cards; vary editorial imagery, text, and useful content.
- Design mobile layouts intentionally rather than merely stacking desktop sections.
- Keep forms short, explain what happens after submission, and make contact information easy to find.
- Use only verified testimonials and approved, substantiated case studies.

Homebot integrations:

- Buyer: `https://hmbt.co/YgFMRD` with CTA “Explore Your Buying Power.” The Buyer Strategy Call remains primary.
- Seller: `https://hmbt.co/WYVKc9` with CTA “Get My Home Value Range.” Preserve a path to Tanya's personal pricing review.
- Test the complete visitor flow on desktop and mobile.

## Content and editorial rules

- Answer a real question and provide the useful answer near the top.
- Include Renton, King County, Washington, or Pacific Northwest context only when genuinely relevant.
- Use descriptive headings, short readable paragraphs, useful steps, and meaningful imagery.
- Include Tanya's candid perspective only when supported by approved source material.
- Each article should link to at least two relevant internal pages and use a CTA aligned with search intent.
- AI imagery may support early launch only when it does not impersonate Tanya's home, work, clients, or lived experience.
- Mark uncertainty internally; never publish placeholder copy, unsupported claims, fake testimonials, or fabricated details.

## Engineering standards

- Build reusable block patterns, template parts, components, and design tokens instead of duplicating markup and styles.
- Follow WordPress escaping, sanitization, nonce, capability, and REST/API security practices.
- Prefer semantic HTML and progressive enhancement. Core content and navigation should remain usable without JavaScript.
- Meet WCAG-oriented basics: keyboard navigation, visible focus states, descriptive labels, meaningful alt text, adequate contrast, and touch-friendly controls.
- Protect mobile performance and Core Web Vitals. Use correctly sized, compressed WebP or AVIF images where practical; avoid unnecessary JavaScript and render-blocking assets.
- Preserve one-H1 hierarchy, descriptive headings, unique metadata, short URLs, internal links, XML sitemap support, and appropriate schema.
- Add schema only when the visible content supports it. Planned types include LocalBusiness, Article, FAQ, and context-appropriate real-estate markup.
- Do not add a plugin, external dependency, analytics vendor, CRM behavior, or paid service without explaining the maintenance, privacy, security, performance, and cost tradeoffs.
- Keep secrets and API keys out of Git. Use environment or server-side configuration.
- Track meaningful conversions: calls, forms, guide downloads, Homebot/home-value actions, and other approved lead paths.

## Verification and definition of done

Verify in proportion to risk. For public-facing work, check at minimum:

- Desktop and representative mobile layouts.
- Keyboard access, focus behavior, headings, labels, alt text, and contrast.
- Every modified button, link, form, and external flow.
- No placeholder content, broken media, dead ends, or fake proof.
- Page title, meta description, H1, canonical URL, and relevant internal links.
- Responsive image dimensions and obvious performance regressions.
- Brokerage disclosures, privacy/legal links, and contact information where relevant.
- PHP/browser errors and WordPress warnings introduced by the change.

After database-backed changes, export a new dated SQL snapshot into `db-backups/` and review its scope before committing. Do not overwrite an existing historical backup.

Do not claim completion based only on code inspection. Confirm the change in the running LocalWP site when available.

## Launch scope and priority

Sean confirmed on 2026-08-02 that the August 9 target is a public-ready minimum, not completion of the entire long-term Notion vision. The site may launch with reduced breadth when the visible experience is credible, useful, coherent, and safe to share publicly. Hide incomplete destinations rather than exposing empty pages, dead links, placeholders, or unapproved content.

The full launch definition includes:

1. Homepage and approved navigation.
2. Buyer hub with Buyer Homebot.
3. Seller hub with Seller Homebot.
4. Journal/blog landing page and reusable article template.
5. Populated Rooted in Renton landing page and story system.
6. About and Contact.
7. Forms and Follow Up Boss or the selected CRM.
8. Ten approved launch articles with internal links.
9. Mobile QA, technical SEO, analytics, Search Console, redirects, legal pages, privacy, and brokerage disclosures.

Prioritize reusable templates, image specifications, Buyer/Seller functionality, Journal, and Rooted in Renton before low-impact homepage polishing.

For the August 9 public-ready minimum, require a polished homepage, trustworthy Buyer and Seller paths, a usable About/Contact experience, working forms and disclosures, mobile QA, and enough approved editorial/local content that the brand promise feels real. Journal, Rooted in Renton, supporting guides, integrations, and the full ten-article package may be reduced or staged only when unfinished destinations are removed from public navigation and no launch-critical trust or compliance requirement is compromised.

## Approval boundaries and known open decisions

Do not guess or publish around these gaps:

- Final Love Where You Live logo/wordmark assets are still subject to approval.
- The Rooted in Renton badge and mustard color must come from approved assets.
- Testimonials, review counts, awards, production figures, and case-study results require verification.
- Public page copy and artwork must follow the documented Tanya approval workflow.
- When an older stakeholder request conflicts with the latest approved blueprint, surface the conflict before restructuring the page unless a later explicit decision is recorded in this file.

Safe work may continue with structure, reusable components, accessibility, performance, local development, and clearly internal scaffolding, provided nothing unapproved is presented as final public content.
