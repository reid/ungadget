<?php

if (!$_GET['url']) {
    echo <<<EOHTML
<html>
<head><title>ungadget</title></head>
<body>
<h1>ungadget</h1>
<p>Specify a gadget URL like <pre>?url=http://my.appspot.com/gadget.xml</pre></p>
<h2>Helpful GET Paramaters</h2>
<dl>
    <dt>newlines=0</dt>
    <dd>strip newlines</dd>
    <dt>ver=0.7</dt>
    <dd>allow another opensocial version</dd>
</dl>
<h2>Why not</h2>
<p><a href=\"http://github.com/reid/ungadget\">Grab this on GitHub</a></p>
</body>
</html>
EOHTML;
    die();
}

require_once 'includes/Ungadget.php';

$o = new Ungadget();

if ($_GET['ver']) $o->setOpenSocialVersion($_GET['ver']);
if ($_GET['newlines']) $o->setStripNewlines(!(bool) $_GET['newlines']);

echo $o->transformFromUrl($_GET['url']);
