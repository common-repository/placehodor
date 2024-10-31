=== PlaceHodor ===
Contributors: xuxu.fr
Donate link: https://www.paypal.com/paypalme/kzukzu
Tags: Placeholder, Image, Thumbnail, Missing, 404
Requires at least: 4.8
Tested up to: 6.5.5
Requires PHP: 5.6
Stable tag: 1.3.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Substitute / replace missing images by a default image.

The plugin will (try to) display / generate the good size (width and height) of the image missing.
You can choose to replace the image by one picture uploaded in the Placehodor settings page.
Or you can choose to replace the images missing by images from the placeholder webservices "Placehold.co", "Placeimg.com" or "Picsum.photos".

In the 1.3.0 version, the service "Placeholder.com" was replaced by "placehold.co", and "Placeimg.com" was removed (not exists anymore).

With 1.2.0, the plugin will try to replace images broken from external source and also manage image loaded after the document was ready (by ajax or lazy load).

Since 1.1.0, you can choose to set a default thumbnail to all the posts that are not set.

You can contact me :

*   My blog: https://xuxu.fr
*   My Twitter account:  https://twitter.com/xuxu

== Installation ==

1. Extract and upload the directory `placehodor` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Check if Permalink setting is not set on "Plain"

== Upgrade Notice ==

Nothing to do right now.

== Frequently Asked Questions ==

= Not yet =

Wait for it.

== Screenshots ==

1. You have 3 modes of substitution available : Solo / Placeholder.com / Picsum.photos
2. Upload the image that will substitute the images missing
3. This image will substitute the images missing
4. Use the webservice "Placeholder.com" (options: text, text color, background color)
5. Use the webservice "Picsum.photos" (options: normal, grayscale, blur)
6. Before PlaceHodor set
7. After PlaceHodor set
8. Activate a default thumbnail for the posts
9. A default post thumbnail is set

== Changelog ==

= 1.3.2 =
* Fix select font display

= 1.3.1 =
* Update description and tags
* Clean old scripts

= 1.3.0 =
* Replace external service down (placeholder.com by placehold.co)
* Remove external service down (placeimg.com)

= 1.2.0 =
* Replace image broken from external source

= 1.1.0 =
* Can display a random post thumbnail if this option is activated
* Debug display random thumbnail
* Code Rework

= 1.0.0 =
* Hello world!
* first Release.
