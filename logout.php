<?php
session_start();
session_destroy();

// Delete remember me cookies
setcookie('sp_remember_email', '', time() - 3600, '/');
setcookie('sp_remember_name',  '', time() - 3600, '/');

header("Location: index.php");
exit;
?>