<?php
session_start();
session_unset();
session_destroy();

// Setelah session dihancurkan, langsung lempar ke halaman login
header("Location: login.php");
exit();
?>