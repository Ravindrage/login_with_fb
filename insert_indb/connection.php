<?php 
global $pdo ; 

   // Database connection variables
$db_host = 'localhost';
$db_name = 'sociallogin';
$db_user = 'root';
$db_pass = '';
// Attempt to connect to database
try {
    // Connect to the MySQL database using PDO...
   $pdo = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name . ';charset=utf8', $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $exception) {
    // Could not connect to the MySQL database! Check db credentials...
    exit('Failed to connect to database!');
}

?>