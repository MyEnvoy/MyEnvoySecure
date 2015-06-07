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
        <link rel="stylesheet" media="screen" type="text/css" href="css/main.css">
        <link rel="stylesheet" media="screen" type="text/css" href="css/MyEnvoySecure.css">
        <link rel="icon" href="favicon.ico">
        <script src="js/1.9.1_jquery.min.js"></script>
        <script src="js/crypto-js.sha3.min.js"></script>
        <script src="js/crypto-js.aes.min.js"></script>
        <script src="js/jquery.qrcode.js"></script>
        <script src="js/favico.min.js"></script>
        <script src="js/EmojiPicker.js"></script>
        <script src="js/initEmojis.js"></script>
        <script src="js/MyEnvoySecure.js"></script>
    </head>
    <body>
        <div id="header">
            <div id="logo"><img src="img/Logos/web_hi_res_512.png" alt="MyEnvoy Logo"></div>
            <div id="title">MyEnvoy</div>
        </div>
        <?php MyEnvoySecure\MyEnvoySecure::renderMessengerForm('mes', 'secureserver.php'); ?>
    </body>
</html>