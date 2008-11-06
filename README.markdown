Ungadget
========

Transform a Gadget Specification's HTML into inline HTML

Why Ungadget?
-------------

Currently, the Yahoo! Application platform does not support Gadget XML despite
supporting the OpenSocial 0.8 JavaScript API. In addition, you must include all
scripts and stylesheets inline-- external resources are not yet allowed.
Ungadget wants to make porting your existing gadget to YAP v1.0 a bit easier.

What It Does
------------

Ungadget plucks out the HTML content from your gadget, finds all external
scripts and stylesheets and gives you a "flattened" HTML fragment with
these resources all embedded inline.
