Ungadget
========

Transform a Gadget Specification's HTML into inline HTML

Why Ungadget?
-------------

Currently, the [Yahoo! Application Platform][yap] does not support Gadget XML
despite supporting the OpenSocial 0.8 JavaScript API. In addition, you must
include all scripts and stylesheets inline-- external resources are not yet
allowed. Ungadget wants to make porting your existing gadget to YAP v1.0 a bit
easier.

[yap]: http://wiki.opensocial.org/index.php?title=Yahoo%21 "Yahoo! on the OpenSocial Wiki"

What Ungadget Does
------------------

Ungadget extracts the HTML content from your gadget, downloads all external
scripts and stylesheets and gives you a "flattened" HTML fragment with
these resources all embedded inline.

Requirements
------------

Ungadget has been tested to work on PHP 5.2.6 and requires the cURL extension.

License
-------

Ungadget is provided under a BSD license. (See LICENSE for more information.)
