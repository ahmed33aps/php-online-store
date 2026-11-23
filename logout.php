<?php
require_once __DIR__ . "/security.php";
session_unset();
session_destroy();
header("Location: login.php");
exit();