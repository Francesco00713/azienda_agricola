<?php
$conn = new mysqli("db", "myuser", "mypassword", "azienda_agricola");

if ($conn->connect_error) {
    die("Errore connessione: " . $conn->connect_error);
}
?>