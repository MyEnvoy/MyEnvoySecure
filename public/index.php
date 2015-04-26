<?php
include '../core/Index.class.php';
include '../core/MyEnvoySecure.class.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>MyEnvoy | The secure and privacy focused messenger</title>
        <meta charset="UTF-8">
        <meta name="description" content="With MyEnvoy you are able to communicate in a completely encrypted way.">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="img/logo38.ico">
        <link rel="stylesheet" media="screen" type="text/css" href="css/main.css">
        <link rel="stylesheet" media="screen" type="text/css" href="css/MyEnvoySecure.css">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/sha3.js"></script>
        <script src="http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/aes.js"></script>
        <script src="js/MyEnvoySecure_0-5.js"></script>
    </head>
    <body>
        <div id="header">
            <div id="logo"><img src="img/Logos/web_hi_res_512.png" alt="MyEnvoy Logo"></div>
            <div id="title">MyEnvoy</div>
        </div>
        <?php MyEnvoySecure\MyEnvoySecure::renderMessengerForm('mes', 'secureserver.php'); ?>
    </body>
</html>