<?php
    $conn = new mysqli("db", "myuser", "mypassword", "azienda_agricola");

    if ($conn->connect_error) {
        die("Errore connessione: " . $conn->connect_error);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database</title>
</head>
<body>
</body>
</html>