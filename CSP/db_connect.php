<?php
// $host = 'localhost';
// $db_name = 'Ched';
// $username = 'root'; 
// $password = ''; 

// // Create connection
// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
//     // Set the PDO error mode to exception
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
// } catch(PDOException $e) {
//     die("Connection failed: " . $e->getMessage());
// }

   //Connect To Database
    $hostname='srv346.hstgr.io';
    $username='u183359785_ched2';
    $password='Syncopation@020';
    $dbname='u183359785_chedro2_db';

    try {
        $pdo = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
    

?>