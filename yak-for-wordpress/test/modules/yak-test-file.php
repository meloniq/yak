<?php
error_reporting(E_ALL ^ E_DEPRECATED);

$filename = $_GET['file'];

$content = file_get_contents($filename);
$rowcount = count(explode("\n", $content));
?>
<html>
<body>
    <p>Filename: <span id="filename"><?php echo $filename ?></span></p>
    <p>Rows: <span id="rowcount"><?php echo $rowcount ?></span></p>

    <pre id="content"><?php echo $content ?></pre>
</body>
</html>


