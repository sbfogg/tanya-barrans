# Website Image Specifications

These specifications are the launch standard for Love Where You Live artwork. They unblock Buyer, Seller, Journal, Rooted in Renton, and social-preview production while keeping image behavior consistent across responsive templates.

## Required exports

| Asset | Source dimensions | Ratio | Target delivered size | Notes |
|---|---:|---:|---:|---|
| Landing-page hero | 2400 x 1600 px | 3:2 | 350 KB or less | Keep important subjects within the center 60%; allow room for text and mobile cropping. |
| Article hero | 2000 x 1125 px | 16:9 | 300 KB or less | Used at the top of Journal and Rooted story pages. |
| Inline editorial image | 1600 x 1067 px | 3:2 | 250 KB or less | Use for article and long-form page sections. |
| Editorial card | 1200 x 900 px | 4:3 | 180 KB or less | Used for Journal, resource, neighborhood, and Rooted story cards. |
| Portrait/card image | 1200 x 1500 px | 4:5 | 220 KB or less | Used for Tanya portraits and vertical editorial features. |
| Social preview | 1200 x 630 px | 1.91:1 | 300 KB or less | Open Graph and link-preview image; keep text inside a generous safe area. |
| Video thumbnail | 1280 x 720 px | 16:9 | 220 KB or less | Rooted in Renton and other YouTube/video cards. |

## Export rules

- Export photographs as WebP at approximately 75–85 quality. Use AVIF only when the publishing workflow provides a reliable fallback.
- Preserve a high-resolution original in Canva or the approved shared asset library; commit only web-ready exports to the repository.
- Do not upscale a low-resolution source to meet these dimensions.
- Remove unnecessary metadata before publishing.
- Use descriptive lowercase filenames with hyphens, such as `renton-coffee-shop-interior.webp`.
- Do not bake page headings, buttons, or essential information into images.
- Provide concise alt text with every handoff. Describe the image's purpose and visible subject; do not stuff keywords.
- Record the source, usage rights, approval status, and any restrictions with the asset handoff.

## Composition safe areas

- Landing heroes must support both wide desktop and narrow mobile crops. Avoid placing faces, logos, or key objects against the outer 20% of either side.
- Social previews should keep important text and subjects inside the center 80% horizontally and 75% vertically.
- Card images should remain understandable when center-cropped.
- Rooted in Renton artwork may use the approved badge and mustard accent. Other website images should not introduce mustard or unapproved logo treatments.

## Implementation expectations

- WordPress should generate responsive `srcset` variants for media-library images.
- Templates must reserve image space with explicit dimensions or `aspect-ratio` to reduce layout shift.
- Do not serve a 2400px hero as a small card thumbnail.
- Verify appearance at desktop, tablet, and approximately 390px mobile width before approval.
