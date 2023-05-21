=== Read More WP ===
Contributors: boltonstudios
Donate link: https://ko-fi.com/boltonstudios
Tags: read more, show more, word limit, word count, toggle text, excerpt, ellipsis
Requires at least: 4.0.0
Tested up to: 6.2.2
Requires PHP: 5.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create excerpts and hide text with an elegant toggle button to show more.

== Description ==

Use *Read More WP* to create excerpts and hide text with an elegant toggle button to show more.

Truncate your long text by inserting the *[start-read-more]* shortcode were you want to hide words, 
followed by the *[end-read-more]* shortcode where you want the button to show more.

Use the CSS selector `.rmwp-button-wrap button` to style the button.

View the [Demo](https://www.boltonstudios.com/read-more-wp/).

= Features (Free) =
* Easy *[start-read-more]* and *[end-read-more]* shortcodes.
* Support for inline breaks (great for truncating short text such as testimonials and review quotes).
* WordPress forum support.

= Premium Features (One-Time Payment) =
* Animated transitions.
* Priority email support.
* [Get Read More WP Plus](https://www.boltonstudios.com/read-more-wp/).

= Supporting Read More WP =

If you found this free plugin helpful, you can support the developer by upgrading to Read More WP Plus or making a donation:

* [Buy me a coffee](http://ko-fi.com/boltonstudios)

= Shortcode =

    [start-read-more]
    [end-read-more]

= Optional Shortcode Parameters =

    [start-read-more more="Read More" less="Read Less" inline=false ellipsis=true][end-read-more]

* more..."Read More" or another button label.
* less..."Read Less" or another button label.
* inline...true or false. Default: false.
* ellipsis...true or false. Default: true.
* animation..."none", "slide", "fade", "fold", "pop-up".
* speed...The speed of the animation in milliseconds. Default: 500.

== Installation ==

1. Upload `read-more-wp.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the `[start-read-more]` and `[end-read-more]` shortcodes to your page or post.
1. Save Changes.

== Screenshots ==

1. The shortcodes wrapping text in the Gutenberg block editor. Highlights added for visibility.
2. The plugin settings pages with avalable options.
3. A block of content partially hidden with the "read more" button toggle.
4. A block of content revealed with the "read less" button toggle.

== Changelog ==

= 1.1.0 =
* Date Released: 2023-06-01
* New Feature: Animated Pop-Ups.

= 1.0.1 =
* Date Released: 2023-05-18
* Bug fix: Fixed bug that prevented text toggling when initial Plus animation settings were empty.

= 1.0.0 =
* Date Released: 2023-05-18
* Initial Release