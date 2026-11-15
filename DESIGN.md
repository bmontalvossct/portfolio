# Portfolio design direction

Updated 4 November 2026 for the requested Meta-inspired public-site redesign. This replaces the earlier Material/Apple gadget-spec direction. The portfolio remains Britt Montalvo's own identity.

## Reference and purpose

Use [Meta's public company site](https://www.meta.com/about/) as the visual reference for clear sans-serif type, open layouts, rounded media, and short interaction transitions. Use the person's actual work, portrait, career records, and publications rather than decorative mock product specifications. No Meta logo, proprietary font, or brand affiliation is implied.

The first screen introduces Britt and the work. Projects, professional background, credentials, publications, and contact should be easy to browse without repeated promotional labels or a dashboard-like density.

## Palette and typography

| Token | Light | Dark |
| --- | --- | --- |
| Canvas | `#ffffff` | `#000000` |
| Quiet surface | `#f4f7f8` | `#0b0b0b` |
| Raised surface | `#ffffff` | `#111111` |
| Main text | `#1c2b33` | `#f5f8fa` |
| Secondary text | `#465a69` | `#adc0ce` |
| Action green | `#08683c` | `#76daa2` |
| Border | `#dce3e9` | `#303030` |

Use the shared [CSS tokens](./resources/css/app.css). Forest green was chosen for a confident, growth-oriented presentation; color associations vary by context and culture, and color alone does not establish conversion performance. One local system sans-serif stack serves headings, body, controls, and metadata. No remote font request is needed. Use regular/medium display weights and sentence case; body copy should be at least 16px. Long names and credential titles must wrap naturally.

## Layout

- A quiet, solid header with a small set of useful destinations, accessible mobile navigation, and a theme control.
- A spacious introduction with the real portrait and a clear primary action; no technical status console.
- Distinct section layouts appropriate to the material: editorial project/media features, readable service descriptions, career rows, and browsable credential records.
- Use cards when a group really needs a boundary. Do not frame every paragraph or section in identical bordered boxes.
- Rounded media may use 24–32px radii; primary actions may use pills. Connect every section with subtle, distinct background colors that blend continuously from one to the next; keep dark mode predominantly black. Avoid decorative glows, glass blur, decorative numbering, technical spec labels, and monospace metadata.
- Content and controls must work at 320px upward, with no page-wide horizontal overflow. Filter chips or media rails may scroll locally if the interaction is clear and keyboard-accessible.

## Motion

Motion expresses an action or introduces the first screen. It should not compete with the content.

| Purpose | Treatment |
| --- | --- |
| Initial hero | One brief opacity/vertical-settle sequence, up to 600ms, approximately 16–24px travel |
| Buttons and navigation | 160–240ms color/underline/opacity changes; a small arrow movement only when useful |
| Image hover | Optional small transform, never a whole-card tilt or a layout change |
| Dialog | Short opacity/scale transition with proper focus management |
| Filters | Immediate state/feedback; short transition where it preserves orientation |

Use `cubic-bezier(0.2, 0, 0, 1)` for entering/settling motion. Do not animate every section on scroll, pulse availability indicators, or count invented metrics. Preserve the existing portrait and play a single transition only when the visitor changes the theme. The full-width recognition carousel sits below the profile section, loops continuously, and pauses on hover and focus. Honor reduced-motion settings.

Respect `prefers-reduced-motion`, including preference changes while the page is open. Stop and clean up animation contexts, observers, timers, and listeners on navigation/unmount. Preserve scrolling position during ordinary modal use; restore focus to the opener when the dialog closes.

## Content and accessibility

- Preserve factual professional content and existing backend contracts. Do not add claims of verified credentials, current employment, publication status, or traffic beyond the source evidence.
- Hide the artificial live-viewer presentation from the public UI; this design task does not change the server counter implementation.
- Keep actual AI-related capabilities and credentials where relevant. Removing generic AI-looking design does not mean deleting the person's real AI work.
- Every input needs a visible label and error feedback. Guestbook email remains private and is required by the existing backend contract; browser checks must not submit real entries.
- Modals require an accessible name, Escape behavior, focus containment/return, and scroll management. Mobile navigation must be keyboard-operable.
- Green actions and secondary text must retain readable contrast in both themes. Honor focus visibility and reduced motion.

## Verification

Run the integrated Vite build and inspect the rendered result with Playwright at desktop, tablet, and mobile widths. Exercise internal navigation, themes, filters/search, modal open/close and keyboard focus, reduced-motion behavior, and relevant empty states. Check the actual screenshots, not only the build output. Do not claim exact Meta motion parity: this is a portfolio adaptation of the visual reference.
