<?php

session_start();

// delete all session variables
$_SESSION = [];

// end the session
session_destroy();


header('Location: login.php?logout=1');
exit;