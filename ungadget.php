<?php

require_once 'includes/Ungadget.php';

$o = new Ungadget();

if ($_GET['ver']) $o->setOpenSocialVersion($_GET['ver']);
if ($_GET['newlines']) $o->setStripNewlines(!(bool) $_GET['newlines']);

echo $o->transformFromUrl($_GET['url']);
