<?php

error_reporting(0);

if ($_GET['url']) {

    require_once 'includes/Ungadget.php';

    $o = new Ungadget();

    if (array_key_exists('ver', $_GET)) $o->setOpenSocialVersion($_GET['ver']);

    try {
        echo $o->transformFromUrl($_GET['url']);
        die();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <title>ungadget</title>
        <style>
            #app { font-family: Helvetica, sans-serif; }
            #app h1 {
                font-size: 45px;
                letter-spacing: -3px;
                font-style: italic;
                border-bottom: 3px dotted #666;
                line-height: 40px;
            }
            span { color: #666; }
            p { padding: 5px; }
            #error { background: #f99; }
            dt { font-family: monospace; }
        </style>
    </head>
<body>
    <div id="app">
        <h1>ungadget</h1>
        <?php if ($error) : ?>
        <p id="error"><?php echo $error ?></p>
        <?php endif; ?>
        <p>Specify a gadget URL parameter like <code><?php echo $_SERVER['SCRIPT_NAME'] ?>?url=http://my.appspot.com/gadget.xml</code></p>
        <h2>Helpful GET Parameters</h2>
        <dl>
            <dt>url=http://example.com/gadget.xml</dt>
            <dd>required gadget URL</dd>
            <dt>ver=0.7</dt>
            <dd>require opensocial-0.7 instead, blank for no requirement</dd>
        </dl>
        <h2>Why not</h2>
        <p><a href="http://github.com/reid/ungadget">Grab this on GitHub</a></p>
    </div>
</body>
</html>
