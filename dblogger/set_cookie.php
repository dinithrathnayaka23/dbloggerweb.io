<?php
//Set cookie for 1 year
setcookie("cookieAccepted", "true", time() + (365 * 24 * 60 * 60), "/");

//Send a simple success response
http_response_code(200);
echo "Cookie set successfully";
?>
