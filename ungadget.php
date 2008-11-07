<?php

if (!$_GET['url']) {
    echo "<h1>ungadget</h1>\n";
    echo "<p>Specify a gadget URL like <pre>?url=http://my.appspot.com/gadget.xml</pre></p>\n";
    echo "<p>Helpful params: <ul><li>newlines=1 &mdash; don't strip newlines</li><li>ver=0.7 &mdash; allow another opensocial version</li></ul>\n";
    echo "<p><a href=\"http://github.com/reid/ungadget\">Grab this on GitHub</a></p>\n";
    die();
}

require_once 'includes/Ungadget.php';

$o = new Ungadget();

if ($_GET['ver']) $o->setOpenSocialVersion($_GET['ver']);
if ($_GET['newlines']) $o->setStripNewlines(!(bool) $_GET['newlines']);

echo $o->transformFromUrl($_GET['url']);
