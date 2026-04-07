<?php
    session_start();
    include "db.php";
    if (!isset($_SESSION['idUtente'])) {
        header("Location: index.php");
        exit();
    }
    $idCliente = $_SESSION['idUtente'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Storico Acquisti</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="container">

    <h2>Il tuo Storico Acquisti</h2>

    <table>
        <thead>
            <tr>
                <th>Data Ordine</th>
                <th>Prodotto</th>
                <th>Quantità</th>
                <th>Prezzo Totale</th>
            </tr>
        </thead>
        <tbody>

        <?php
        $sql = "SELECT a.idAcquisto, a.dataAcquisto, a.totale, p.nome, d.quantita, pr.prezzo
                FROM Acquisti a
                JOIN Dettaglio_acquisto d ON a.idAcquisto = d.idAcquisto
                JOIN Prodotti p ON d.idProdotto = p.idProdotto
                JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                WHERE a.idCliente = $idCliente
                ORDER BY a.dataAcquisto DESC, a.idAcquisto DESC";

        $res = $conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $currentOrder = -1;

            while ($row = $res->fetch_assoc()) {

                echo "<tr>";
                echo "<td>" . ($currentOrder != $row['idAcquisto'] ? $row['dataAcquisto'] : "") . "</td>";
                echo "<td>{$row['nome']}</td>";
                echo "<td>{$row['quantita']}</td>";
                echo "<td>€" . number_format($row['quantita'] * $row['prezzo'], 2) . "</td>";
                echo "</tr>";

                $currentOrder = $row['idAcquisto'];
            }

        } else {
            echo "<tr><td colspan='4'>Non hai ancora effettuato acquisti.</td></tr>";
        }
        ?>

        </tbody>
    </table>

    <br>
    <a href="index_cliente.php">⬅ Torna all'area clienti</a>

</div>
</body>
</html>