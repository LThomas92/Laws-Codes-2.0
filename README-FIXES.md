# Laws & Codes 2.0 — Bug Fixes & Plugin Integration

## Bugs Fixed

### 1. JS bundle was empty (`src/index.js`)
**Root cause:** `src/index.js` contained only a comment — it never imported any modules,
so Webpack compiled an empty bundle (~42 lines, no actual code).

**Fix:** Added two imports to `src/index.js`:
```js
import './modules/main.js';
import './modules/quiz.js';
```
`dist/js/main.js` has been manually rebuilt from the module sources. After installing
node_modules, run `npm run build` to get a properly minified production bundle.

---

### 2. Wrong CSS filename in `functions.php`
**Root cause:** `functions.php` enqueued `dist/css/main.css` but `webpack.config.js`
(via `MiniCssExtractPlugin`) outputs `dist/css/style.css`.

**Fix:** Replaced hardcoded path with `lc_asset()` helper that reads `dist/assets.json`
(written by `assets-webpack-plugin`) for the correct filename automatically. Falls back
to `dist/css/style.css` when the manifest is absent.

---

### 3. Quiz & switcher JS pointing at non-existent `/assets/js/` folder
**Root cause:** `functions.php` registered `lc-quiz` and `lc-switcher` scripts pointing
to `LC_URI . '/assets/js/quiz.js'` and `/assets/js/project-switcher.js`. Those paths
don't exist — both modules live in `src/modules/` and are bundled into `dist/js/main.js`.

**Fix:** Removed both separate enqueue calls. `LC_Projects` is now localised to the
`lc-main` handle. Everything loads from the single `dist/js/main.js` bundle.

---

## Gravity Forms Integration

`page-contact.php` now checks `class_exists( 'GFForms' )`:
- **GF active:** renders `[gravityforms id="1" ajax="true"]` (AJAX submission, no page reload)
- **GF absent:** falls back to the original hand-rolled AJAX form (no broken state)

To change the form ID, add this to your child theme or a plugin:
```php
add_filter( 'lc_gf_contact_form_id', fn() => 2 ); // use form ID 2
```

**Create the form in WordPress:**
1. Go to **Forms → New Form**
2. Add fields: First Name, Last Name, Email, Business Name, Service (Drop Down),
   Project Details (Paragraph Text), Submit Button
3. In **Form Settings → Confirmations**: set a thank-you message
4. In **Form Settings → Notifications**: send to `hello@lawscodes.com`

**Gravity Forms also gets these theme hooks (added to `functions.php`):**
- Scripts moved to footer (`gform_init_scripts_footer`)
- Theme's `.form-submit` class applied to the GF submit button
- `scss/pages/_contact.scss` contains a full GF skin that maps GF markup onto
  the theme's existing form-input / form-label / form-submit styles

---

## ACF Integration

`functions.php` now includes:
- **Local JSON sync** — ACF field groups are saved/loaded from `acf-json/` (already in the
  theme). Commit this folder to keep field groups in version control.
- **`lc_project_field()` helper** — used throughout `lc_get_featured_projects()`. When ACF
  is active it calls `get_field()` for a cleaner API; when ACF is absent it falls back to
  `get_post_meta()` automatically.

**ACF field group setup:**
1. Go to **Custom Fields → Add New**
2. Name it **Project Details**, assign it to post type `lc_project`
3. Add fields with names matching the existing meta keys (minus leading underscore):
   - `lc_client_name`, `lc_industry`, `lc_location`, `lc_year`, `lc_timeline`
   - `lc_services`, `lc_tech_stack`
   - `lc_kpi_1_num`, `lc_kpi_1_label`, `lc_kpi_2_num`, `lc_kpi_2_label`
   - `lc_kpi_3_num`, `lc_kpi_3_label`, `lc_kpi_4_num`, `lc_kpi_4_label`
   - `lc_github_url`, `lc_live_url`, `lc_bg_color` (Color Picker)
   - `lc_category_label`, `lc_result_teaser`
   - `lc_challenge`, `lc_solution`, `lc_results` (WYSIWYG)
   - `lc_featured` (True/False), `lc_switcher_order` (Number)
4. Save — ACF will write a JSON file to `acf-json/` automatically

---

## After uploading

```bash
# In the theme folder on your local dev machine:
npm install
npm run build      # production build
# — or —
npm run dev        # watch mode for development
```

The `dist/js/main.js` included in this zip is a concatenated dev build. Running
`npm run build` will replace it with a properly minified, Babel-transpiled bundle.
