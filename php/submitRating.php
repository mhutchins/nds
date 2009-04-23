<?php
  header('Content-type: text/plain');
  print "Thank you for rating the content ".$_GET['ratingID']." with the value ".$_GET['value'];
?>
