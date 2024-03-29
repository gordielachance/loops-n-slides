=== Loops 'n Slides ===
Contributors: grosbouff
Donate link: http://bit.ly/gbreant
Tags: loop,shortcode,custom query,carousel,slides,owlcarousel,queries
Requires at least: 4.9
Tested up to: 6.0.1
Stable tag: trunk
License: GPLv2 or later

A simple yet powerful plugin that allows you to display posts loops based on any query, as a carousel of slides or using a custom template.

== Description ==

A simple yet powerful plugin that allows you to display posts loops based on any query, as a carousel of slides or using a custom template.

= Features =
* **Display any posts you want...** Create a loop that will display exactly the posts you want; filtered by tag, category, by post author... any Wordpress Query works !
* **...The way you want...** You can choose among several templates to display your loop, or **use your own one** !
* **...Anywhere you want !** With this simple shortcode: `[loops-n-slides id=XXX]`
* **Carousels:** Just click the *Carousel Checkbox* when editing a loop, it will be displayed in a nice, slidy way.  You can define global options for the carousels, or/and define them for each loop. (powered by [OwlCarousel](https://owlcarousel2.github.io/OwlCarousel2/))
* **Carousel galleries:** Optionally, enable carousels for your image galleries, globally or for each gallery.
* **Totally Free:** There is NO premium version for this plugin, because it is *totally free*.  Of course, [donations](http://bit.ly/gbreant) would be very appreciated.


= Shortcode =

`[loops-n-slides id=XXX]` where XXX is the ID of your loop.

= Donate! =

I made this plugin because the majority of carousels plugins had limited functionnalities or were not free.
This one IS totally free.  But if you like it, if you use it, please consider [making a donation](http://bit.ly/gbreant).
This would be very appreciated — Thanks !

= Dependencies =

* [OwlCarousel](https://owlcarousel2.github.io/OwlCarousel2/) - jQuery OwlCarousel
* [jQuery json-viewer](https://github.com/abodelot/jquery.json-viewer) - jQuery plugin for displaying JSON data

= Contributors =

Contributors [are listed here](https://github.com/gordielachance/loops-n-slides/contributors).

= Notes =

For feature request and bug reports, please use the [Github Issues Tracker](https://github.com/gordielachance/loops-n-slides/issues).

If you are a plugin developer, [we would like to hear from you](https://github.com/gordielachance/loops-n-slides). Any contribution would be very welcome.


== Installation ==

1. Upload the plugin to your blog and Activate it.
2.  Go to the settings page and setup the plugin.


== Frequently Asked Questions ==

= How can I use custom templates to render my loops? =

Create a directory `loops-n-slides` in your active theme.
Create your [custom page templates](https://developer.wordpress.org/themes/template-files-section/page-template-files/#creating-custom-page-templates-for-global-use) in that directory.  They should have a specific opening PHP tag:
`/* Loops 'n Slides Loop: My Custom Loop Template Title */`

See the files under *loops-n-slides/templates* for examples.
You can override those default files by have custom files that have the same filename in your `loops-n-slides` directory.

= How can I use the plugin to display an existing Wordpress gallery as a carousel? =

You can either enable this option by default for all your galleries (see the Loops 'n Slides Settings page); or enable it for a few galleries only, by adding the attribute `loopsns-carousel=1` to your existing shortcode.
Example : `[gallery ids="113,117" loopsns-carousel=1]`

When enabled globally, you can prevent a gallery from rendering as a carousel by adding the attribute `loopsns-carousel=0`.


== Screenshots ==

1. Loop editor metabox
2. Settings page


== Changelog ==

= 1.1.2 =
* Fix https://github.com/gordielachance/loops-n-slides/issues/1
= 1.1.1 =
* Added queries examples in the loop editor metabox.
= 1.1.0 =
* now uses [jQuery json-viewer](https://github.com/abodelot/jquery.json-viewer) - jQuery plugin for displaying JSON data

= 1.0.0 =

* First release


== Upgrade Notice ==


== Localization ==
