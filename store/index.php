<?php

use \Tsugi\Util\U;

require_once 'U.php';

session_start();
$pieces = U::rest_path();

$_SESSION['count'] = U::get($_SESSION, 'count', 0) + 1;
error_log('Count '.$_SESSION['count']);

if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
    $annotations = U::get($_SESSION, 'annotations', array());
    header('Content-Type: application/json; charset=utf-8');
    echo(json_encode(array_values($annotations), JSON_PRETTY_PRINT));
    return;
}

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    $input = file_get_contents('php://input');
    $json = json_decode($input);
    $annotations = U::get($_SESSION, 'annotations', array());
    $annotations[$json->id] = $json;
    $_SESSION['annotations'] = $annotations;
    return;
}

if ( $_SERVER['REQUEST_METHOD'] === 'DELETE' ) {
    $annotations = U::get($_SESSION, 'annotations', array());

    if ( isset($pieces->extra) && strlen($pieces->extra) > 0 ) {
        $id = $pieces->extra;
        unset($annotations[$id]);
    } else {
        error_log('Resetting annotations');
        $annotations = array();
    }
    $_SESSION['annotations'] = $annotations;

    http_response_code(204);
    return;
}

echo("<pre>\n");
var_dump($pieces);
http_response_code(405);
die("Working on the rest...");


/*
 object(stdClass)#7 (8) {
  ["parent"]=>
  string(23) "/py4e/mod/ckpaper/store"
  ["base_url"]=>
  string(21) "http://localhost:8888"
  ["controller"]=>
  string(17) "92992929292929292"
  ["extra"]=>
  string(3) "zap"
  ["action"]=>
  string(3) "zap"
  ["parameters"]=>
  array(0) {
  }
  ["current"]=>
  string(41) "/py4e/mod/ckpaper/store/92992929292929292"
  ["full"]=>
  string(45) "/py4e/mod/ckpaper/store/92992929292929292/zap"
}
 */

/* Annotation format

    http://docs.annotatorjs.org/en/v1.2.x/annotation-format.html#annotation-format
 
{
  "id": "39fc339cf058bd22176771b3e3187329",  # unique id (added by backend)
  "annotator_schema_version": "v1.0",        # schema version: default v1.0
  "created": "2011-05-24T18:52:08.036814",   # created datetime in iso8601 format (added by backend)
  "updated": "2011-05-26T12:17:05.012544",   # updated datetime in iso8601 format (added by backend)
  "text": "A note I wrote",                  # content of annotation
  "quote": "the text that was annotated",    # the annotated text (added by frontend)
  "uri": "http://example.com",               # URI of annotated document (added by frontend)
  "ranges": [                                # list of ranges covered by annotation (usually only one entry)
    {
      "start": "/p[69]/span/span",           # (relative) XPath to start element
      "end": "/p[70]/span/span",             # (relative) XPath to end element
      "startOffset": 0,                      # character offset within start element
      "endOffset": 120                       # character offset within end element
    }
  ],
  "user": "alice",                           # user id of annotation owner (can also be an object with an 'id' property)
  "consumer": "annotateit",                  # consumer key of backend
  "tags": [ "review", "error" ],             # list of tags (from Tags plugin)
  "permissions": {                           # annotation permissions (from Permissions/AnnotateItPermissions plugin)
    "read": ["group:__world__"],
    "admin": [],
    "update": [],
    "delete": []
  }
}

 */
