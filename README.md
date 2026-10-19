# Synergy Learning theme (`theme_synergylearning`)

This is the optional combined task. The Task 1 page is built into the **Front page** layout of a Boost child theme called `theme_synergylearning`, and the theme also has the two required footer settings.

**Live demo:** <a href="https://moodle.miniarts.uk/" target="_blank" rel="noopener">https://moodle.miniarts.uk/</a>

It adds:

- a branded **site home** built from the MyLearning concept (Task 1): a log in panel, a course catalogue and an about section;
- a **footer logo** setting, which falls back to `core_admin | logocompact` when no logo is uploaded;
- a **copyright** setting, shown after a static `©` in the footer, with nothing output when it is empty;
- separate **footer settings** for the Contact button, the address, the footer links and the Cookie settings button (version 2).

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
| Footer | Copyright text | The template adds the `©`. A `©` or `(c)` typed by the admin is stripped so it never appears twice. Multi-language tags work. Empty means no copyright markup at all. |
| Footer | Contact button text | Text on the yellow Contact button. Defaults to "Contact us". |
| Footer | Contact button link | A web address, a page on this site starting with `/`, `mailto:` or `tel:`. A phone or envelope icon, and hidden text for screen readers ("by phone", "by email"), is added for `tel:` and `mailto:` links. Empty means no button. |
| Footer | Address | Plain text, one line per address line. The first line is bold. Empty means no address. |
| Footer | Footer links | One link per line as `Label\|URL`, the same format as Moodle's *Custom menu items*, for example `Policies\|/admin/tool/policy/viewall.php`. Lines without a label or a usable link are skipped. Empty means no links. |
| Footer | Show Cookie settings button | On by default. The green outline button opens the theme's Cookie settings pop-up. |
| Footer | Additional footer content | Optional rich text for anything the settings above don't cover, shown after the Cookie settings button. Treated as trusted admin HTML. Empty means nothing is shown. |

The footer contents are shown in that order, above the logo and copyright, and each part is left out when its setting is empty. Each one has its own setting, rather than one rich-text box, so the markup and styling stay the same whatever an admin types: the Contact button is always a button, the address is an `<address>`, the links are a list. Links typed into these settings must start with `http://`, `https://`, `/`, `#`, `mailto:` or `tel:`; anything else (a `javascript:` link, for example) is ignored.

## Site home content

- **Catalogue.** The catalogue lists the top-level course categories the current user can see. Each card's image is chosen in this order:
  1. **An image you upload for the category.** Go to *Site administration > Courses > Manage courses and categories*, edit the category, and insert an image into its **Description** (the editor's image button). The first image in the description is used. It is shown at 72 × 72 px without cropping, and is not inverted in dark mode, so a square PNG or SVG with a transparent background works best. The image also appears in the description on the category's own page.
  2. **The theme's built-in icon**, when the category's ID number is `product`, `business-development`, `optics`, `cpd` or `business-management`.
  3. **A generic icon.**
- **Top menu.** The theme adjusts Moodle's top menu through the `primary_extend` navigation hook (`db/hooks.php`, `classes/hook_callbacks.php`), only while this theme is active:
  - it adds **Course catalogue**, which goes to the catalogue section of the site home. On the site home it scrolls there smoothly and moves focus to the section heading;
  - it adds **My badges** (when badges are enabled) and **Events**, the calendar's upcoming events page. Visitors who are not logged in are asked to log in first, then taken on to the page. Each is shown as the current item on its own pages;
  - it hides **Home** and **Dashboard** from visitors who are not logged in (and guests): the site home is the log in page for them, and the logo already links to it.

  Because the theme adds these links itself, nothing needs to be typed into Moodle's **Custom menu items** setting (*Site administration > Appearance > Advanced theme settings*). If that box already has Course catalogue, My badges or Events lines, from before the theme was installed, delete them, or those links show twice in the menu.
- **Navigation bar.** 96px tall at the top of the page, with 10px/20px padding; it shrinks to 72px (logo included) once the page scrolls, and the side drawers stay attached below it. Set by `$navbar-height` in `scss/pre.scss`, `scss/navbar.scss` and `amd/src/navbar.js`. Motion is turned off for visitors who prefer reduced motion.
- **Mobile navigation bar.** Below the md breakpoint the bar shows a small logo (40px) on the left, the menu button on the right and the user menu and icons in between. The main menu drawer slides in from the right, next to the button, with its close button on the left. Boost hides the logo on phones and opens this drawer from the left, so both are overridden in `scss/navbar.scss`.
- **Footer.** The footer band is full width on every page and stays at the bottom of the window on short pages.
- **Text.** All page text is in language strings, so a site can change it under *Language > Language customisation* without code changes.
- **Cookie settings pop-up.** The Cookie settings button opens a pop-up with Essential (always on) and an Analytics switch, saved per browser in `localStorage` and confirmed with a Moodle toast. Focus moves into the pop-up and back to the button when it closes. The button is hidden until the theme's JavaScript has loaded, as it does nothing without it. It records a preference only: a real site would connect it to a consent plugin that blocks analytics until consent is given. A `btn-sl-cookie` button typed into the Additional footer content opens the pop-up too.
- **Login page.** Moodle 5.3's split login page is restyled to match the site home: the form sits on the grey panel on the left, with underlined fields, uppercase labels and the yellow arrow button, and the MyLearning banner fills the right-hand panel instead of Boost's default photo. Moodle's own login form is kept (no template copy), so errors, sign-up, guest access and single sign-on still work. Custom login instructions (*Plugins > Authentication > Manage authentication > Instructions*) still appear over the banner. The logo above the form (*Appearance > Logos > Logo*, when one is uploaded) links back to the site home.
- **Video.** No video file was supplied with the concept. Add one at `pix/frontpage/about-nikon-lenswear-uk.mp4`, or change `videourl` in `layout/frontpage.php`. Until then, pressing play shows a "Video not available" message.

## Version history

- **2.0.0** – The footer contents have their own settings: Contact button text and link, Address, Footer links and Show Cookie settings button. Before, they were all typed as HTML into one Footer content box. That box stays as **Additional footer content**, for anything else. When upgrading, move the address, links and buttons out of that box into the new settings, then clear it. If it already has a Cookie settings button, the upgrade turns the new button off so it doesn't show twice.
- **1.2.0** – The version submitted for the technical task: the branded site home, the footer logo and copyright settings, and the top menu, navigation bar, login page and footer changes described above.
