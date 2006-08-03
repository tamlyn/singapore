Modern - singapore template v1.3b (www.sgal.org)
by Ross Howard (www.abitcloser.com/singapore)	

Modern is a lightweight xHTML/CSS template for singapore.

It includes gallery, album and image pages, and also has
a built-in slideshow, and full-size image pop-up ability.

It uses common naming conventions (albeit with an sg prefix)
and a clean div structure to allow you to easily and simply customise 
it to suit your needs.

It supports both fixed-width, and scaling page sizes, and is intended to
be web standard compliant and render correctly on a wide range of browsers.
These include Mozilla/Firefox 1.5, Safari, Opera and IE6.

Modern comes standard with a default 'black on black' colour scheme, but also 
includes CSS files for 'white on black' and 'white on white'.

By default, Modern uses the 'Arial, Helvetica, sans-serif;' font family, as 
declared for the body CSS. Simply changing this one line will effect the
entire template.

If you make any changes to template.ini please make sure to check the CSS file
too, in case you need to adjust page or thumbnail block widths.

TODO - Future Versions

Add Javascript UI library
Add AJAX Slideshow

CHANGELOG

1.3 - FIX CSS image urls
1.3 - FIX Slideshow rollovers wrong size with resized images
1.3 - FIX slideshow links for galleries with no images
1.3 - Rewrote CSS to separate structure from colour

1.2 - Added 'Loading' GIF
1.2 - Added Play/Pause for Slideshow
1.2 - All text now utilised translator
1.2 - Added ability to use old image map in image page (not in slideshow) but have set it as off in template.ini which overwrites singapore.ini
1.2 - Rewrite URL coding to work without mod_rewrite and handles variable stack detection in URL
1.2 - Set colour scheme to 'black on black' by default, can be set via template.ini
1.2 - Added ability to float galleries next to each other via template.ini
1.2 - Added simple HTML slideshow that can be enabled via template.ini
1.2 - Added link to full size image when current image is resized, can enabled via template.ini
1.2 - Added rollover behaviour for image navigation which replaces imagemap

1.1 - Added support for external.php and prefixed selectors with sg

1.0 - Added IE conditional comments

0.9 - Original working build