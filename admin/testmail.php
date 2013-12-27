<?php
$to = "charmex.x@hotmail.com";
$from = "psistemas@indelpro.com";
$message = "Well hello there";
$subject = "Testing them mails";
$result = mail($to,$from,$message);
echo "sent" . $result;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
