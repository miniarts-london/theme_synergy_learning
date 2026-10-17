# Synergy Learning theme (`theme_synergylearning`)

This is a child theme of Boost for **Moodle 5.3**. It adds:

- a branded **site home** built from the MyLearning concept (Task 1): a log in panel, a course catalogue and an about section;
- a **footer logo** setting, which falls back to `core_admin | logocompact` when no logo is uploaded;
- a **copyright** setting, shown after a static `©` in the footer, with nothing output when it is empty.

## Installation

1. Copy this folder to `public/theme/synergylearning`, or upload the zip under *Site administration > Plugins > Install plugins*.
2. Visit *Site administration > Notifications*, or run `php admin/cli/upgrade.php`, to install it.
3. Select **Synergy Learning** under *Site administration > Appearance > Themes*.
4. Make sure visitors can reach the site home. **Moodle 5.3 hides it by default.**
   - Turn on *Appearance > Navigation > Enable Home* (`enablemyhome`).
   - Turn off *General > Login > Login settings > Force users to log in* (`forcelogin`). Moodle 5.3 moved this setting out of Site security settings.

   If either is set the wrong way, the theme's settings page shows a warning that links to both settings.

## Settings

*Site administration > Appearance > Themes > Synergy Learning*

| Tab | Setting | Notes |
|---|---|---|
| General | Unneeded blocks | Kept from Boost. Moodle reads this setting from the active theme, so a child theme has to provide its own copy. |
| Footer | Footer logo | SVG, PNG, JPG, WebP or GIF. If empty, the compact logo from *Appearance > Logos* is used; if that is empty too, no logo is shown. |
| Footer | Footer content | Optional rich text, such as an address or contact details, shown in the footer, above the logo and copyright. Leave empty to show nothing. Treated as trusted admin HTML, so `tel:` links work. In the editor's HTML source view, `btn btn-primary` gives the yellow button and `btn btn-sl-cookie` the green outline Cookie settings button (see the example below). |
| Footer | Copyright text | The template adds the `©`. A `©` or `(c)` typed by the admin is stripped so it never appears twice. Multi-language tags work. Empty means no copyright markup at all. |

## Site home content

- **Catalogue.** The catalogue lists the top-level course categories the current user can see. Each card's image is chosen in this order:
  1. **An image you upload for the category.** Go to *Site administration > Courses > Manage courses and categories*, edit the category, and insert an image into its **Description** (the editor's image button). The first image in the description is used. It is shown at 72 × 72 px without cropping, and is not inverted in dark mode, so a square PNG or SVG with a transparent background works best. The image also appears in the description on the category's own page.
  2. **The theme's built-in icon**, when the category's ID number is `product`, `business-development`, `optics`, `cpd` or `business-management`.
  3. **A generic icon.**
- **Navigation.** To get the concept's navigation, add these lines to *Appearance > Advanced theme settings > Custom menu items*:
  ```
  Course catalogue|/course/index.php
  My badges|/badges/mybadges.php
  Events|/calendar/view.php?view=upcoming
  ```
- **Text.** All page text is in language strings, so a site can change it under *Language > Language customisation* without code changes.
- **Footer buttons.** Example for the Footer content setting, matching the concept:
  ```html
  <p><a class="btn btn-primary" href="tel:+441632960000"><i class="fa fa-phone" aria-hidden="true"></i> Contact us<span class="visually-hidden"> by phone</span></a></p>
  <address><strong>Nikon Optical Ltd</strong><br>3 Tanners Drive, Blakelands<br>Milton Keynes, Bucks MK14 5BU<br>United Kingdom</address>
  <p><a class="btn btn-sl-cookie" href="/admin/tool/policy/viewall.php">Cookie settings</a></p>
  ```
  Any `btn-sl-cookie` button (or an element with `data-action="sl-cookie-settings"`) opens the theme's **Cookie settings pop-up**: Essential (always on) and an Analytics switch, saved per browser in `localStorage` and confirmed with a Moodle toast. Without JavaScript the button stays a link to the site policies. If the site uses a consent plugin, point the link at that plugin instead and remove the `btn-sl-cookie` class.
- **Login page.** Moodle 5.3's split login page is restyled to match the site home: the form sits on the grey panel on the left, with underlined fields, uppercase labels and the yellow arrow button, and the MyLearning banner fills the right-hand panel instead of Boost's default photo. Moodle's own login form is kept (no template copy), so errors, sign-up, guest access and single sign-on still work. Custom login instructions (*Plugins > Authentication > Manage authentication > Instructions*) still appear over the banner.
- **Video.** No video file was supplied with the concept. Add one at `pix/frontpage/about-nikon-lenswear-uk.mp4`, or change `videourl` in `layout/frontpage.php`. Until then, pressing play shows a "Video not available" message.
