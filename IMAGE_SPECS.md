# Website Image Specifications

**Source of truth:** the specifications Sean posted to the team on 2026-08-06, in a comment on the Notion task *Confirm website templates + exact image dimensions*. This file mirrors that comment so the repository and the team are working from the same numbers.

If these ever disagree with the Notion comment again, the Notion comment wins and this file should be corrected.

## The two things that always matter

1. **Width in pixels** — listed per image type below.
2. **File size** — keep every image **under 500 KB**, saved as **JPEG** at **70–80% quality**.

When unsure how to resize or compress, drop the file into [TinyPNG](https://tinypng.com/) or [Squoosh](https://squoosh.app/).

## Required exports

| Asset | Dimensions | Aspect ratio | Notes |
|---|---|---|---|
| Hero, desktop | 2500 × 1200 px | ~21:10 (wide) | Large banner at the top of a page. |
| Hero, mobile | 1200 × 1500 px | 4:5 (tall) | See the caveat below before producing these. |
| Inline article image | 1200 px wide | 3:2 or 16:9 | Keep the ratio consistent within a single article. Height follows the ratio. |
| House / listing photo | 1200–1600 px wide | 3:2 | Buyers zoom in, so quality matters most here. |
| Blog card / thumbnail | 600 × 400 px | 3:2 | Clickable preview images. |
| Social preview (Open Graph) | 1200 × 630 px | 1.91:1 | Shown when a page is shared to Facebook, LinkedIn, iMessage. Keep important content centred. |
| Portrait photo of Tanya | 1200 px on the long edge | 4:5 or 3:4 | Vertical editorial features. |

### Why two hero versions

Desktop heroes are wide. On a phone a wide image becomes a thin sliver, so a taller crop reads far better. If only one version can be produced, supply the desktop version and it will be cropped for mobile.

> **Caveat — the mobile hero cannot be used yet.** No template currently supports art-directed responsive images (`<picture>` or a media-query `srcset`), so a separate 1200 × 1500 mobile crop has nowhere to go. Either that template support gets built, or the team should treat hero artwork as desktop-only for now. Until this is resolved, producing mobile crops is wasted effort.

## Reusable templates

**Article template:** hero (2500 × 1200) → inline images (1200 px wide, consistent ratio) → thumbnail (600 × 400)

**Neighborhood page template:** hero (2500 × 1200) → three to five listing or area photos (1200–1600 px) → optional map image (1200 px wide)

## Repository notes

- Commit only web-ready exports. Keep the high-resolution original in Canva or the approved shared asset library.
- Give every image meaningful alt text at the point of use, not in the filename.
- Existing site photography predates this specification: current images are 1536 × 1024 and the homepage hero is 1720 × 914, with at least one file over the 500 KB ceiling. These should be re-exported when the artwork pass happens.
