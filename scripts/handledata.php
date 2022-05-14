<?php 
include_once dirname(__FILE__) . "/../securize.php";
     // Send the headers
header('Content-type: text/xml');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

echo '<xml>';

// echo some dynamically generated content here
/*
<track>
    <path>song_path</path>
    <title>track_number - track_title</title>
</track>
*/

echo '</xml>';

?>