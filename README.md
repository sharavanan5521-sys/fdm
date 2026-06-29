# Freedom Discovery Management — Website

Marketing website for **Freedom Discovery Management Sdn. Bhd.** — a Malaysian
executive-coaching and corporate-training company.

**Live site:** https://freedomdiscovery.net
**Repository:** https://github.com/sharavanan5521-sys/fdm

---

## 1. What this is (and isn't)

This is a **plain static website** — hand-written HTML, CSS and vanilla
JavaScript. There is intentionally:

- **No build step** — no webpack/vite/npm, no `package.json`, nothing to compile.
- **No framework** — no React/Vue/jQuery.
- **No backend / database** — every page is a static `.html` file.

To work on it you just **open the `.html` files in an editor and a browser.**
What you see in the file is exactly what ships to production.

---

## 2. Tech stack

| Layer        | What we use                                                        |
|--------------|-------------------------------------------------------------------|
| Markup       | Static HTML5, one file per page                                   |
| Styling      | Plain CSS (no preprocessor). One shared stylesheet + one per page |
| Behaviour    | One shared vanilla-JS file, `js/home.js`                          |
| Fonts        | Google Fonts (Poppins + Playfair Display) via CDN                 |
| Icons        | Font Awesome 6.4 via CDN (loaded async, non-render-blocking)      |
| Hosting      | GitHub Pages, served on the custom domain `freedomdiscovery.net`  |

---

## 3. Project structure

```
fdm/
├── index.html            # Home page
├── about.html            # About / company story / founder
├── services.html         # Services & programmes
├── team.html             # Team & certified faculty
├── events.html           # Events & Trainings  (the featured event lives here)
├── testimonials.html     # Testimonials + photo gallery
├── contact.html          # Contact details & form
│
├── css/
│   ├── home.css          # SHARED — navbar, footer, buttons, hero, capsule,
│   │                     #   WhatsApp button, design tokens. Loaded on EVERY page.
│   ├── about.css         # Page-specific styles (loaded only by about.html)
│   ├── services.css      #   "
│   ├── team.css          #   "
│   ├── events.css        #   "
│   ├── testimonials.css  #   "
│   └── contact.css       #   "
│
├── js/
│   └── home.js           # SHARED — all interactivity for every page (see §6)
│
├── assets/               # Images
│   ├── logo.png, promo-poster.jpg, group-photo.jpg, ...
│   ├── gallery/          # Testimonials page photo gallery
│   ├── partners/         # Partner logos (team page)
│   └── trainners/        # Faculty headshots (team page)  [sic — keep the spelling]
│
├── sitemap.xml           # Lists every public page for search engines
├── robots.txt            # Crawler rules; points to the sitemap
└── googledf64303bd472be2f.html   # Google Search Console verification — DO NOT DELETE
```

> Every page loads **`css/home.css` first**, then its own page CSS. So shared
> things (navbar, footer, buttons) live in `home.css`; page-only styling lives
> in the matching `css/<page>.css`.

---

## 4. Running it locally

No tooling required. Either:

- **Easiest:** double-click any `.html` file to open it in your browser, **or**
- **Recommended** (so root-relative links like `/` and `/sitemap.xml` resolve
  correctly), serve the folder over a tiny local web server:

```bash
# Python 3 (built into macOS/Linux, installable on Windows)
python -m http.server 8000
# then visit http://localhost:8000

# …or with Node installed:
npx serve .

# …or just use the VS Code "Live Server" extension (Right-click → Open with Live Server)
```

---

## 5. ⚠️ Important: shared markup is duplicated across pages

The **navbar, footer, announcement capsule, WhatsApp floating button and
back-to-top button are copy-pasted into every `.html` file.** There is no
templating/includes system.

**This means: if you change one of those shared sections, you must make the
same edit in all 7 pages** (`index, about, services, team, events,
testimonials, contact`). Common gotchas:

- Adding/removing/renaming a nav link → update the navbar **and** the mobile
  menu **and** the footer "Quick Links" in **every** page.
- Changing the WhatsApp number or footer contact info → find-and-replace across
  all pages (the number `60124883300` appears many times).
- The promo poster (`assets/promo-poster.jpg`) is referenced on every page via
  the announcement capsule.

A tip: use your editor's "Find in Files" (search the whole project) rather than
editing a single file.

---

## 6. JavaScript (`js/home.js`)

One small vanilla-JS file, loaded at the bottom of every page, wrapped in an
IIFE. It progressively enhances the page (the site still works with JS off).
It handles:

- Footer copyright **year** auto-fill (`#currentYear`)
- **Sticky navbar** state on scroll
- **Mobile hamburger menu** open/close
- Hero headline **accent-word highlighting** (driven by the `data-accent`
  attribute on each page's `<h1 id="heroTitle">`)
- Scroll-triggered **reveal animations** and **count-up stats** (IntersectionObserver)
- Timeline, card-tilt and magnetic-button hover effects
- The **announcement capsule** (the floating "Latest Announcements" pill) and
  its **poster lightbox**

Because it's shared, it checks whether each element exists before using it, so
it's safe on pages that don't have a given component.

---

## 7. Common content edits (how-to)

| I want to…                                   | Where to go                                                                 |
|----------------------------------------------|-----------------------------------------------------------------------------|
| **Replace the promo poster**                 | Overwrite `assets/promo-poster.jpg` keeping the **same filename** — it updates everywhere automatically. See the big comment block in `events.html`. |
| **Change the featured event** (title/date/venue) | `events.html` → the `★ EDIT THE CURRENT EVENT HERE ★` section, **and** keep the `Schema.org JSON-LD` in that page's `<head>` in sync. |
| **Update the announcement capsule** text     | The `<div class="cap ...">` block near the bottom of each page (duplicated — see §5). |
| **Add a team member / faculty**              | `team.html`; headshots go in `assets/trainners/`.                          |
| **Add gallery photos**                       | `testimonials.html`; images go in `assets/gallery/`. Add `loading="lazy"`. |
| **Change phone / email / address**           | Footer of every page + `contact.html` (see §5).                            |
| **Edit colours, fonts, button styles**       | `css/home.css` (shared design tokens / components).                        |

---

## 8. SEO files — keep these in sync

- **`sitemap.xml`** — lists every public page with a `<lastmod>` date. When you
  add/remove a page, update this file (and bump `<lastmod>` when content
  changes meaningfully).
- **`robots.txt`** — allows all crawlers and points to the sitemap.
- **`googledf64303bd472be2f.html`** — Google Search Console domain-verification
  file. **Do not delete or rename it** or Search Console access breaks.
- Each page has its own `<title>`, `<meta name="description">`, Open Graph /
  Twitter tags, and a `<link rel="canonical">`. Keep these accurate per page.

---

## 9. Performance notes / conventions

- Below-the-fold images use `loading="lazy" decoding="async"`. **Above-the-fold
  images (navbar logo, first hero/content image) are intentionally left eager**
  — don't lazy-load them or you hurt the Largest-Contentful-Paint metric.
- Font Awesome is loaded asynchronously (`media="print" onload="this.media='all'"`)
  with a `<noscript>` fallback, so icons don't block the first paint.
- ⚠️ **Image sizes:** several source images are large (e.g. `group-photo.jpg`
  ~1.9 MB, `g1.jpg` ~1.3 MB). Before adding new photos, **compress them**
  (and prefer WebP where possible). This is the biggest remaining perf win.

---

## 10. Deployment

The site is hosted on **GitHub Pages** and served on the custom domain
**`freedomdiscovery.net`**.

- **Pushing to the `main` branch publishes the site** (GitHub Pages builds from
  `main`). Allow a minute or two for changes to go live.
- The custom domain is configured in the repo's **Settings → Pages**. If you
  add a `CNAME` file or change the domain, do it there.
- Always test locally (see §4) before pushing.

> The repo currently has several stale feature branches. **`main` is the source
> of truth / production branch.** Prefer making changes on a short-lived branch
> and merging into `main` via PR.

---

## 11. Quick start for a new developer

1. Clone: `git clone https://github.com/sharavanan5521-sys/fdm.git`
2. Open the folder in your editor (VS Code recommended).
3. Run a local server (§4) and open `http://localhost:8000`.
4. Read §5 — **remember shared sections are duplicated across all pages.**
5. Make your edit, test in the browser, commit, and push to `main` (or open a PR).
