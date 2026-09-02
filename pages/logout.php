<?php
session_start();
session_destroy();
header("Location: /SmritiMitra/pages/login.php");
exit;
?>
