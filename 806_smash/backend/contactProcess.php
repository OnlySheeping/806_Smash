<?php
mail($_POST['cEmail'],$_POST[cName],$_POST['cMessage']);
header('location: ../fontEnd/contact.php')
?>
