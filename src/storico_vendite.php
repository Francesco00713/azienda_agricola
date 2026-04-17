<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Storico vendite</title>
</head>
<body>
    <div class="container">
        <h2>Storico vendite</h2>
        <table>
            <tr>
                <th>ID acquisto</th>
                <th>Cliente</th>
                <th>Data</th>
                <th>Prodotto</th>
                <th>Quantità</th>
                <th>Unità</th>
                <th>Prezzo unitario (€)</th>
                <th>Subtotale (€)</th>
                <th>Totale ordine (€)</th>
            </tr>
        <?php
            $sql = "
                SELECT Acquisti.idAcquisto,
                    Utenti.nome AS cliente,
                    Acquisti.dataAcquisto,
                    Prodotti.nome AS prodotto,
                    Prodotti.unitaMisura,
                    Dettaglio_acquisto.quantita,
                    Prezzi.prezzo,
                    (Dettaglio_acquisto.quantita * Prezzi.prezzo) AS subtotale,
                    Acquisti.totale
                FROM Acquisti
                INNER JOIN Utenti ON Acquisti.idCliente = Utenti.idUtente
                INNER JOIN Dettaglio_acquisto ON Acquisti.idAcquisto = Dettaglio_acquisto.idAcquisto
                INNER JOIN Prodotti ON Dettaglio_acquisto.idProdotto = Prodotti.idProdotto
                INNER JOIN Prezzi ON Prodotti.idProdotto = Prezzi.idProdotto AND Prezzi.dataFineValidita IS NULL
                ORDER BY Acquisti.idAcquisto ASC, Dettaglio_acquisto.idDettaglio ASC";

            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                
                while ($row = $res->fetch_assoc()) {
                    $prezzo_formattato = number_format($row['prezzo'], 2, '.', '');
                    $subtotale_formattato = number_format($row['subtotale'], 2, '.', '');
                    $totale_formattato = number_format($row['totale'], 2, '.', '');

                    echo "<tr>
                            <td>{$row['idAcquisto']}</td>
                            <td>{$row['cliente']}</td>
                            <td>{$row['dataAcquisto']}</td>
                            <td>{$row['prodotto']}</td>
                            <td>{$row['quantita']}</td>
                            <td>{$row['unitaMisura']}</td>
                            <td>{$prezzo_formattato}</td>
                            <td>{$subtotale_formattato}</td>
                            <td>{$totale_formattato}</td>
                        </tr>";
                }

            } else {
                echo "<tr><td colspan='9'>Nessuna vendita registrata.</td></tr>";
            }
        ?>
        </table>
        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>
    </div>
</body>
</html>