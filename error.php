<?php
require_once "../config.php";
\Tsugi\Core\LTIX::getConnection();

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$OUTPUT->header();
$OUTPUT->flashMessages();
$OUTPUT->footer();
