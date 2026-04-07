<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Elenco Clienti</title>
</head>
<body>
    <div class="container">

        <h2>Elenco Clienti</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Telefono</th>
                <th>Email</th>
            </tr>

        <?php
        $res = $conn->query("SELECT * FROM Utenti WHERE ruolo = 'cliente' ORDER BY idUtente ASC");

        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['idUtente']}</td>
                        <td>{$row['nome']}</td>
                        <td>{$row['telefono']}</td>
                        <td>{$row['email']}</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Nessun cliente presente.</td></tr>";
        }
        ?>

        </table>

        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>

    </div>
</body>
</html>