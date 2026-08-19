<?php
require_once "../config.php";
\Tsugi\Core\LTIX::getConnection();

use \Tsugi\Core\LTIX;
use \Tsugi\UI\MenuSet;

$LAUNCH = LTIX::requireData();

$menu = new MenuSet();
$menu->addLeft(__('Back to all students'), 'grades.php');

$OUTPUT->header();
$OUTPUT->bodyStart();
$OUTPUT->topNav($menu);
$OUTPUT->flashMessages();
$OUTPUT->footer();
