=== Read More WP ===
Contributors: boltonstudios
Donate link: https://ko-fi.com/boltonstudios
Tags: read more, show more, toggle text, excerpt, block
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Hide content behind an elegant "Read More" button, with a block or shortcodes.

== Description ==

Hide content behind an elegant toggle button to show more.

In the block editor, add the **Read More** block and put the content to hide inside it. Content above the block stays visible.

Or use shortcodes anywhere: insert *[start-read-more]* where hidden content begins, and *[end-read-more]* where it ends.

Target `.rmwp-button-wrap button` to style the button (optional).

View the [Demo](https://www.boltonstudios.com/read-more-wp/).

= Features (Free) =
* Read More block for the block editor.
* Easy shortcodes for the classic editor, widgets, and page builders.
* Inline breaks—great for short text such as testimonials.
* Accessible: keyboard friendly, and screen readers announce whether content is expanded.
* Lightweight: no jQuery, and scripts load only on pages that use them.
* Content stays readable when JavaScript is disabled.
* WordPress forum support.

= Plus Features (Paid) =
* Animated transitions: slide, fade, and fold.
* Accessible pop-ups (close with Escape or by clicking outside).
* Fade-out mode: show the start of long content with a fade and a "Show more" button.
* Button styles: text link or full-width button, alignment, colors, and corner radius.
* Scroll back to the button after hiding long content.
* Priority email support.
* [Get Read More WP Plus](https://www.boltonstudios.com/read-more-wp/).

= Supporting Read More WP =

If you found this free plugin helpful, you can support the developer by upgrading to *Read More WP Plus* or donating to the coffee fund:

* [Buy me a coffee](http://ko-fi.com/boltonstudios)

= Shortcode =

`[start-read-more]`
`[end-read-more]`

= Optional Shortcode Parameters =

`[start-read-more more="Read More" less="Read Less" inline=false ellipsis=true][end-read-more]`

* more..."Read More" or another button label.
* less..."Read Less" or another button label.
* inline...true or false. Default: false.
* ellipsis...true or false. Default: true.

= Plus Shortcode Parameters =

`[start-read-more mode="fade" height="200" animation="slide" speed="500" button="link" align="center" scroll=true][end-read-more]`

* animation..."none", "slide", "fade", "fold", or "pop-up".
* speed...The speed of the animation in milliseconds. Default: 500.
* mode..."fade" shows the start of the content with a fade-out. Leave empty to hide the content.
* height...In fade mode, the visible height in pixels. Default: 200.
* button..."button", "link", or "full" (full width).
* align..."left", "center", or "right".
* button_color, button_background, button_border, fade_color...Hex colors, e.g. "#0d6efd".
* scroll...true or false. Scroll back to the button after hiding the content.

== Installation ==

1. In your WordPress dashboard, go to **Plugins › Add New Plugin**, search for "Read More WP", then install and activate it.
1. Optional: adjust the default button labels in **Settings › Read More WP**.
1. Add the **Read More** block to a post or page, or wrap content in the `[start-read-more]` and `[end-read-more]` shortcodes.
1. Save Changes.

== Frequently Asked Questions ==

= How is this different from the Details block in WordPress? =

The Details block shows a summary line that expands. Read More WP lets you show the start of your content and hide the rest mid-sentence or mid-paragraph, with an ellipsis and a button that moves to the end when expanded. Plus adds animations, pop-ups, and fade-out truncation.

= How do I change the button's look? =

Target `.rmwp-button-wrap button` in your theme's CSS, or set the `--rmwp-button-color`, `--rmwp-button-background`, `--rmwp-button-border`, and `--rmwp-button-radius` CSS custom properties. Plus adds style settings without code.

= Can I nest toggles? =

Yes. Each `[end-read-more]` closes the most recent `[start-read-more]`.

== External services ==

**Freemius** handles licensing, updates, and optional usage data.

* If you opt in: sends your name, email, site address, and WordPress environment info (such as WordPress, PHP, and plugin versions). Opting in is optional.
* Plus: sends your license key and site address to activate the license and check for updates.
* [Freemius Terms](https://freemius.com/terms/), [Freemius Privacy Policy](https://freemius.com/privacy/)

The plugin connects to no other services.

== Screenshots ==

1. The shortcodes wrapping text in the block editor. Highlights added for visibility.
2. The plugin settings page with available options.
3. A block of content partially hidden with the "read more" button toggle.
4. A block of content revealed with the "read less" button toggle.

== Changelog ==

= 1.2.0 =
* Date Released: 2026-10-23
* Security: Updated Freemius SDK to version 2.13.5.
* Security: Sanitized the speed and animation shortcode attributes.
* New Feature: Read More block for the block editor.
* New Feature (Plus): Fade-out mode shows the start of long content with a fade and a "Show more" button.
* New Feature (Plus): Button styles (text link, full width), alignment, colors, and corner radius.
* New Feature (Plus): Scroll back to the button after hiding long content.
* Improvement (Plus): Pop-ups now use an accessible dialog that traps keyboard focus and closes with Escape or a click outside.
* Improvement: Rewritten without jQuery. Scripts and styles now load only on pages that use them.
* Improvement: Content stays visible when JavaScript is disabled.
* Accessibility: The toggle button now reports its expanded/collapsed state to screen readers.
* Bug fix: Button labels with apostrophes or ampersands no longer display HTML codes (e.g., "Don&#039;t").
* Bug fix: Using a pop-up toggle no longer breaks the layout of later toggles on the same page.
* Bug fix: inline=false is no longer treated as inline=true.
* Bug fix: Shortcodes on their own line in the block editor no longer produce invalid HTML.
* Bug fix: The settings page now works on sites installed in a subdirectory.
* Settings are now sanitized when saved.
* Requires WordPress 6.3 and PHP 7.4 or later.

= 1.1.7 =
* Date Released: 2026-10-18
* Security: Updated Freemius SDK to version 2.13.4.
* Minimum required PHP version is now 5.6 (required by the Freemius SDK).

= 1.1.6 =
* Date Released: 2025-02-09
* Updated Freemius SDK to the latest version.

= 1.1.5 =
* Date Released: 2023-10-21
* Implemented further changes requested by WP Plugin Review Team.

= 1.1.4 =
* Date Released: 2023-09-15
* Implemented various changes requested by WP Plugin Review Team.

= 1.1.3 =
* Date Released: 2023-07-21
* Updated Freemius SDK to the latest version.

= 1.1.2 =
* Date Released: 2023-05-21
* Bug fix: Fixed bug in Plus version that caused plugin name to echo arbitrarily.

= 1.1.1 =
* Date Released: 2023-05-21
* Bug fix: Fixed CSS bug that caused content to overflow the pop-up window in some cases.

= 1.1.0 =
* Date Released: 2023-05-21
* New Feature: Animated Pop-Ups.

= 1.0.1 =
* Date Released: 2023-05-18
* Bug fix: Fixed bug that prevented text toggling when initial Plus animation settings were empty.

= 1.0.0 =
* Date Released: 2023-05-18
* Initial Release.