<!DOCTYPE html>
<html>
<head>
   
</head>
<body>
  <?php
    include_once("head.php");

    require_once 'lib/parsedown/Parsedown.php';
    $parsedown = new Parsedown();

    $text = file_get_contents('README.md');
    echo $parsedown->text($text);
  ?>
</body>   
</html>

