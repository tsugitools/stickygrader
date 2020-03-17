<?php 
require_once "../config.php";

use \Tsugi\Util\U;
use \Tsugi\Core\LTIX;
use \Tsugi\Blob\Access;
use \Tsugi\Blob\BlobUtil;

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

$next = U::safe_href(U::get($_GET, 'next', 'edit.php'));
$next_text = __('Info');
if ( $next != 'edit.php' ) $next_text = __('Back');

$user_id = U::safe_href(U::get($_GET, 'user_id'));
if ( $user_id && ! $LAUNCH->user->instructor ) {
    http_response_code(403);
    die('Not authorized');
}

if ( ! $user_id ) $user_id = $LAUNCH->user->id;

$file_id = $LAUNCH->result->getJsonKeyForUser('file_id', false, $user_id);
if ( ! $file_id ) {
    header("Location: ".addSession($next));
    return;
}

$url = BlobUtil::getAccessUrlForBlob($file_id);

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>PDF StickyGrader</title>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <meta name="viewport" content="width=device-width, initial-scale=1">

<style id="compiled-css" type="text/css">
  .pdf-page {
    border: 1px solid black;
    direction: ltr;
  }
</style>

    <link rel='stylesheet' href='<?= $CFG->staticroot ?>/js/stickygrader/stickygrader.css'>


<script type="text/javascript">//<![CDATA[

// https://stackoverflow.com/a/19099203/1994792
window.onload=function(){

    // If absolute URL from the remote server is provided, configure the CORS
    // header on that server.
    var url = '<?= addSession($url) ?>';

    // Loaded via <script> tag, create shortcut to access PDF.js exports.
    var pdfjsLib = window['pdfjs-dist/build/pdf'];

    // The workerSrc property shall be specified.
    // pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://unpkg.com/pdfjs-dist@2.2.228/build/pdf.worker.js';

    // https://www.sitepoint.com/custom-pdf-rendering/
    function handlePages(page)
    {
        //This gives us the page's dimensions at full scale
        var viewport = page.getViewport({ scale: 1.5, rotation: 0 });

        //We'll create a canvas for each page to draw it on
        var canvas = document.createElement( "canvas" );
        canvas.style.display = "block";
        var context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        canvas.setAttribute('class', 'pdf-page');

        //Draw it on the canvas
        page.render({canvasContext: context, viewport: viewport});

        //Add it to the web page
        document.body.appendChild( canvas );

        //Move to next page
        currPage++;
        if ( thePDF !== null && currPage <= numPages )
        {
            thePDF.getPage( currPage ).then( handlePages );
        }
    }


    // Asynchronous download of PDF
    var currPage = 1; //Pages are 1-based not 0-based
    var numPages = 0;
    var thePDF = null;

    var loadingTask = pdfjsLib.getDocument(url);

    loadingTask.promise.then(function(pdf) {
        console.log('PDF loaded');

        //Set PDFJS global object (so we can easily access in our page functions
        thePDF = pdf;

        //How many pages it has
        numPages = pdf.numPages;

        //Start with first page
        pdf.getPage( 1 ).then( handlePages );

    }, function (reason) {
        // PDF loading error
        console.error(reason);
    });

}

//]]></script>

</head>
    <body>
    <div id="button_div">
    <a href="javascript:;" class="button" id="prev_note">&lt;</a>
    <a href="javascript:;" class="button" id="add_new">+</a>
    <a href="javascript:;" class="button" id="next_note">&gt;</a>
    <a href="edit.php" class="button">?</a>

    </div>
    <div id="board" style="position: absolute; top: 1; left: 1; width: 100%;">
    </div>
    <script src="https://unpkg.com/pdfjs-dist@2.2.228/build/pdf.js"></script>

<script src="<?= $CFG->staticroot ?>/js/jquery-1.11.3.js"></script>
<script src="<?= $CFG->staticroot ?>/js/jquery-ui-1.11.4/jquery-ui.min.js"></script>
<script src='<?= $CFG->staticroot ?>/js/stickygrader/stickygrader.js'></script>

<script>
$(document).ready(function () {

    $("#board").height($(document).height());

    $("#prev_note").click(StickyGrader.prevNote);
    $("#add_new").click(StickyGrader.newNote);
    $("#next_note").click(StickyGrader.nextNote);

    $('.remove').click(StickyGrader.deleteNote);
    // StickyGrader.newNote();

    // onDelete : function(id, top, left, text)
    StickyGrader.options.onDelete = StickyGrader.onDelete;
    // onChange : function(id, top, left, text)
    StickyGrader.options.onChange = StickyGrader.onChange;

    StickyGrader.options.service = 'store/';
    StickyGrader.loadNotes();

    return false;
});

function clearAnnotations() {
    StickyGrader.deleteAll();
}
//# sourceURL=pen.js
</script>
</body>
