<?php

$REGISTER_LTI2 = array(
"name" => "PDF PostIT",
"FontAwesome" => "fa-file-pdf-o",
"short_name" => "PDFPostIT",
"description" => "A tool to turn in a PDF file and allow it to be annotated  with PostIT notes and graded.",
    // By default, accept launch messages..
    "messages" => array("launch"),
    "privacy_level" => "name_only",  // anonymous, name_only, public
    "license" => "Apache",
    "languages" => array(
        "English",
    ),
    "source_url" => "https://github.com/tsugitools/postitator",
    // For now Tsugi tools delegate this to /lti/store
    "placements" => array(
        /*
        "course_navigation", "homework_submission",
        "course_home_submission", "editor_button",
        "link_selection", "migration_selection", "resource_selection",
        "tool_configuration", "user_navigation"
        */
    ),
    "screen_shots" => array(
        /*
        "store/screen-01.png",
        "store/screen-02.png",
        "store/screen-03.png",
        "store/screen-04.png",
        "store/screen-05.png",
        "store/screen-06.png",
        "store/screen-07.png",
         */
    )

);
