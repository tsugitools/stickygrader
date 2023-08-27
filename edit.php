<?php
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Util\Net;
use \Tsugi\Util\LTI13;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tsugi\UI\SettingsForm;
use \Tsugi\Blob\BlobUtil;
use \CloudConvert\Models\Task;

require_once "strlen.php";

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$upload_max_size_bytes = BlobUtil::maxUploadBytes();
$upload_max_size = U::displaySize($upload_max_size_bytes);

// If settings were updated
if ( SettingsForm::handleSettingsPost() ) {
    header( 'Location: '.addSession('edit.php') ) ;
    return;
}

$next = U::safe_href(U::get($_GET, 'next', 'edit.php'));
$user_id = U::safe_href(U::get($_GET, 'user_id'));
if ( $user_id && ! $LAUNCH->user->instructor ) {
    http_response_code(404);
    die('Not authorized');
}
if ( ! $user_id ) $user_id = $LAUNCH->user->id;

// Load and parse the old JSON
$json = $LAUNCH->result->getJsonForUser($user_id);
$json = json_decode($json);
if ( $json == null ) $json = new \stdClass();
$lock = isset($json->lock) && $json->lock;
$annotations = isset($json->annotations) ? $json->annotations : array();
if ( is_object($annotations) ) $annotations = (array) $annotations;

$inst_note = $LAUNCH->result->getNote($user_id );

if( U::get($_POST, 'resetAnnotations') ) {
    $json->annotations = array();
    $_SESSION['success'] = 'Annotations reset';
    $json = json_encode($json);
    $LAUNCH->result->setJson($json);
    header( 'Location: '.addSession('edit.php') ) ;
    return;
}

// Sanity check input
if ($_SERVER['REQUEST_METHOD'] == 'POST' && count($_POST) < 1 ) {
    $_SESSION['error'] = 'File upload size exceeded, please re-upload a smaller file';
    error_log("Upload size exceeded");
    header('Location: '.addSession('edit.php'));
    return;
}

if ( count($_FILES) > 1 ) {
    $_SESSION['error'] = 'Only one file allowed';
    header( 'Location: '.addSession('edit.php') ) ;
    return;
}

// Check all files to be within our size limit
$thefdes = null;
foreach($_FILES as $fdes) {
    if ( $fdes['size'] > $upload_max_size_bytes ) {
        $_SESSION['error'] = 'Error - '.$fdes['name'].' has a size of '.$fdes['size'].' (' . $upload_max_size . ' max size per file)';
        header( 'Location: '.addSession('edit.php') ) ;
        return;
    }
    $thefdes = $fdes;
}

if ( $thefdes ) {
    $file_id = BlobUtil::uploadToBlob($thefdes);
    $LAUNCH->result->setJsonKey('file_id', $file_id);
    // Notify LMS of a submission for LTI13
    if ( $LAUNCH->isLTI13() ) {
        $grade = null;
        $row = false;
        $debug_log = false;
        $extra13 = array(
            LTI13::ACTIVITY_PROGRESS => LTI13::ACTIVITY_PROGRESS_SUBMITTED,
            LTI13::GRADING_PROGRESS => LTI13::GRADING_PROGRESS_PENDINGMANUAL,
        );
        $LAUNCH->result->gradeSend($grade, $row, $debug_log, $extra13);
    }
    header( 'Location: '.addSession('index.php') ) ;
    return;
}

$file_id = $LAUNCH->result->getJsonKey('file_id');

$menu = new \Tsugi\UI\MenuSet();
if ( $file_id ) {
    $menu->addLeft(__('View'), 'index.php');
} else {
    $menu->addLeft(__('Please upload your file'), false);
}

if ( $LAUNCH->user->instructor ) {
    $submenu = new \Tsugi\UI\Menu();
    $submenu->addLink(__('Student Data'), 'grades');
    $submenu->addLink(__('Settings'), '#', /* push */ false, SettingsForm::attr());
    if ( $CFG->launchactivity ) {
        $submenu->addLink(__('Analytics'), 'analytics');
    }
    if ( U__strlen($inst_note) > 0 ) $menu->addRight(__('Note'), '#', /* push */ false, 'data-toggle="modal" data-target="#noteModal"');
    $menu->addRight(__('Help'), '#', /* push */ false, 'data-toggle="modal" data-target="#helpModal"');
    $menu->addRight(__('Instructor'), $submenu, /* push */ false);
} else {
    if ( U__strlen($inst_note) > 0 ) $menu->addRight(__('Note'), '#', /* push */ false, 'data-toggle="modal" data-target="#noteModal"');
    $menu->addRight(__('Help'), '#', /* push */ false, 'data-toggle="modal" data-target="#helpModal"');
    $menu->addRight(__('Settings'), '#', /* push */ false, SettingsForm::attr());
}


// Render view
$OUTPUT->header();
// https://github.com/jitbit/HtmlSanitizer

$OUTPUT->bodyStart();
$OUTPUT->topNav($menu);
$OUTPUT->flashMessages();

SettingsForm::start();
?>
<p>
This program makes use of the following technologies:
<ul>
<li>JavaScript PDF Rendering library from Mozilla
<a href="https://mozilla.github.io/pdf.js/" target="_blank">PDF.JS</a> </li>
<li>JavaScript sticky grader
<a href="https://github.com/csev/stickygrader" target="_blank">Sticky Grader</a></li>
</ul>
</p>
<?php

SettingsForm::done();
SettingsForm::end();

$OUTPUT->helpModal("PDF Annotation Tool",
    "You can upload a PDF document with this tool.  You and your teacher can annotate your document with colored notes.");

if ( U__strlen($inst_note) > 0 ) {
    echo($OUTPUT->modalString(__("Instructor Note"), htmlentities($inst_note), "noteModal"));
}

if ( $file_id ) {
    echo("<p>Your file has been uploaded.</p>\n");
    if ( count($annotations) > 0 ) {
        echo("<p>".__('Annotations:').' '.count($annotations)."</p>\n");
?>
<p>
<form method="post" if="upload_form">
<input type="submit" id="submit" name="resetAnnotations" class="btn btn-warning" 
onclick="return confirm('Are you sure you want to reset the annotations?');"
value="<?= __('Reset Annotations') ?>">
</form>
</p>
<?php
    }

    $OUTPUT->footer();
    return;
}

?>
<span class="fa fa-file-pdf-o fa-3x" style="color: var(--primary); float:right;"></span>
<!-- https://stackoverflow.com/a/8667695 -->
<form action="<?= addSession('edit.php') ?>" method="post" enctype="multipart/form-data">
<p>
<label class="btn btn-default">
    <input type="file" data-max-size="<?= $upload_max_size_bytes ?>" 
        data-max-text="Error: {0} is > <?= $upload_max_size ?>"
        accept="application/pdf" name="result_<?= $LAUNCH->result->id ?>">
</label>
</p>
<p>
    <input type="submit" id="submit" class="btn btn-primary" value="Submit"> (Max size <?= $upload_max_size ?>) 
    <span id="spinner" style="display:none;"><img src="<?= $OUTPUT->getSpinnerUrl() ?>"/></span>
</p>
</form>
<p>
Please select a PDF file to upload.  
<?php
$OUTPUT->footerStart();
?>
<script>
console.debug("In Edit...");
// Set up the checking of uploaded file size in the browser
// Call this before adding more submit() events to these forms so the file check event runs first

// https://stackoverflow.com/questions/8212041/is-it-possible-to-validate-the-size-and-type-of-input-file-in-html5
// https://stackoverflow.com/a/11799218
tsugiCheckFileMaxSize();

$("#upload_form").submit(function(e) {
        $("#spinner").show();
        $("#submit").attr("disabled", true);
});

</script>
<?php
$OUTPUT->footerEnd();

