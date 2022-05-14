
<?php
//<a href="download.php?file=testing.mp3">Download MP3</a>
//<a href="download.php?file=testing2.mp3">Download MP3</a>
$file = $_GET['file'];  

header('Content-type: audio/mpeg');

header('Content-Disposition: attachment; filename="'.$file.'"');

?>
