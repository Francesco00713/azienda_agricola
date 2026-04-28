<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Storico lavorazioni</title>
</head>
<body>
    <div class="storico">
        <h2>Storico lavorazioni</h2>
        <table>
            <tr>
                <th>Data</th>
                <th>Gestore</th>
                <th>Tipo lavorazione</th>
                <th>Luogo</th>
                <th>Prodotto realizzato</th>
                <th>Quantità prodotta</th>
                <th>Unità di misura</th>
                <th>Prodotto usato</th>
                <th>Quantità usata</th>
                <th>Unità di misura</th>
            </tr>
        <?php
            $sql = "
            SELECT 
                Lavorazioni.dataLavorazione,
                Utenti.nome AS gestore,
                Tipi.tipo AS tipo_lavorazione,
                Luoghi.nome AS luogo,
                P1.nome AS prodotto_prodotto,
                produce.quantitaProdotta,
                P1.unitaMisura AS um_prodotta,
                P2.nome AS prodotto_usato,
                usa.quantitaUsata,
                P2.unitaMisura AS um_usata
            FROM Lavorazioni

            LEFT JOIN compie ON Lavorazioni.idLavorazione = compie.idLavorazione
            LEFT JOIN Utenti ON compie.idGestore = Utenti.idUtente

            LEFT JOIN Tipi ON Lavorazioni.idTipo = Tipi.idTipo
            LEFT JOIN Luoghi ON Lavorazioni.idLuogo = Luoghi.idLuogo

            LEFT JOIN produce ON Lavorazioni.idLavorazione = produce.idLavorazione
            LEFT JOIN Prodotti P1 ON produce.idProdotto = P1.idProdotto

            LEFT JOIN usa ON Lavorazioni.idLavorazione = usa.idLavorazione
            LEFT JOIN Prodotti P2 ON usa.idProdotto = P2.idProdotto

            ORDER BY Lavorazioni.idLavorazione ASC";

            $res = $conn->query($sql);

            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {

                    $quantitaProdotta = isset($row['quantitaProdotta']) ? number_format($row['quantitaProdotta'], 2, '.', '') : '';
                    $quantitaUsata = isset($row['quantitaUsata']) ? number_format($row['quantitaUsata'], 2, '.', '') : '';

                    echo "<tr>
                            <td>{$row['dataLavorazione']}</td>
                            <td>{$row['gestore']}</td>
                            <td>{$row['tipo_lavorazione']}</td>
                            <td>{$row['luogo']}</td>
                            <td>{$row['prodotto_prodotto']}</td>
                            <td>{$quantitaProdotta}</td>
                            <td>{$row['um_prodotta']}</td>
                            <td>{$row['prodotto_usato']}</td>
                            <td>{$quantitaUsata}</td>
                            <td>{$row['um_usata']}</td>
                        </tr>";
                }

            } else {
                echo "<tr><td colspan='11'>Nessuna lavorazione registrata.</td></tr>";
            }
        ?>
        </table>
        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>
    </div>
</body>
</html>