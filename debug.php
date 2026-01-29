<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "PHP OK ✅<br>";

require_once "config/database.php";
echo "database.php loaded ✅<br>";

echo "PDO class: " . (class_exists('PDO') ? "yes" : "no") . "<br>";

$stmt = $pdo->query("SELECT 1");
echo "DB query OK ✅<br>";

echo "<pre>";
print_r($_SESSION);
echo "</pre>";
