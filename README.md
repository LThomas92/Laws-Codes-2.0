# Laws & Codes — Custom WordPress Theme

A fully custom-built WordPress theme powering [lawscodes.com](https://lawscodes.com), a boutique web development studio. Built from scratch with no page builders or pre-made frameworks, no theme boilerplate beyond the initial project scaffold.

## Overview

This theme supports a full client-facing marketing site, including:

- Custom page templates for services, process, work, about, and contact
- A custom post type and single template for case studies (`single-lc_case_study.php`)
- Advanced Custom Fields (ACF) integration with version-controlled field group configs via `acf-json`, so field structure stays in sync across environments without manual re-entry
- A dedicated payment page template
- Custom archive templates for filtering and displaying project work

## Stack

- **PHP** — custom WordPress template hierarchy, no page builder dependency
- **SCSS** — component-based stylesheet architecture, compiled via a Webpack build pipeline
- **JavaScript** — custom interactive behavior, bundled through `webpack.config.js`
- **ACF Pro** — structured content fields, synced via `acf-json` for version control

## Build

```bash
npm install
npm run build   # or the equivalent script defined in package.json
```

## Structure

- `template-parts/` — reusable template partials
- `inc/` — theme setup, custom functions, and helpers
- `acf-json/` — version-controlled ACF field group definitions
- `scss/` / `js/` — source styles and scripts, compiled to `dist/`

---

Built and maintained by [Lawrence Thomas](https://lawscodes.com).
