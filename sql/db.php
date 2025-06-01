<?php 
$host="localhost";
$username="root";
$password="";
$database="mypetakom";

try {
    // Create PDO connection instead of MySQLi
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}



$conn=new mysqli($host,$username,$password,$database);
if($conn->connect_error){
    die("Cannot Establish Conection".$conn->connect_error);

}
?>