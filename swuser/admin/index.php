<?php
include 'controllers/checkprov.php';
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script src="js/jquery.js"></script>
    <script src="js/jquery.json.js"></script>
    <script src="js/config.js"></script>
    <script src="js/chat.js"></script>

    </head>
<body>
这是后台
<input type="hidden" value="<?php echo $_COOKIE['muser'];  ?>" name="muser" id="muser"/>
<input type="hidden" value="<?php echo $_COOKIE['mpasswd']; ?>" name="mpasswd" id="mpasswd" />
</body>
</html>
