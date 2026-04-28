<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Storico vendite</title>
    <style>
        .row-ordine { background-color: #f2f2f2; font-weight: bold; }
        .separatore-ordine { border-top: 2px solid #333; }
        .totale-box { background-color: #e3f2fd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Storico vendite</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Acquisto</th>
                    <th>Cliente</th>
                    <th>Data</th>
                    <th>Prodotto</th>
                    <th>Quantità</th>
                    <th>Unità</th>
                    <th>Prezzo Unitario</th>
                    <th>Subtotale Prodotto</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $sql = "SELECT a.idAcquisto, u.nome AS cliente, a.dataAcquisto, 
                               p.nome AS prodotto, p.unitaMisura, da.quantita, 
                               da.prezzoUnitario, a.totale AS totaleOrdine
                        FROM Acquisti a
                        INNER JOIN Utenti u ON a.idCliente = u.idUtente
                        INNER JOIN Dettaglio_acquisto da ON a.idAcquisto = da.idAcquisto
                        INNER JOIN Prodotti p ON da.idProdotto = p.idProdotto
                        ORDER BY a.idAcquisto DESC";

                $res = $conn->query($sql);

                if ($res && $res->num_rows > 0) {
                    $dati = $res->fetch_all(MYSQLI_ASSOC);
                    $totaleRighe = count($dati);

                    for ($i = 0; $i < $totaleRighe; $i++) {
                        $row = $dati[$i];
                        $prossimaRow = ($i + 1 < $totaleRighe) ? $dati[$i + 1] : null;
                        
                        $subtotaleRiga = $row['quantita'] * $row['prezzoUnitario'];

                        $classeSeparatore = ($i > 0 && $row['idAcquisto'] !== $dati[$i-1]['idAcquisto']) ? "separatore-ordine" : "";

                        echo "<tr class='$classeSeparatore'>";
                        
                        if ($i == 0 || $row['idAcquisto'] !== $dati[$i-1]['idAcquisto']) {
                            echo "<td>{$row['idAcquisto']}</td>";
                            echo "<td>{$row['cliente']}</td>";
                            echo "<td>" . date("d/m/Y", strtotime($row['dataAcquisto'])) . "</td>";
                        } else {
                            echo "<td></td><td></td><td></td>";
                        }
                        
                        echo "<td>{$row['prodotto']}</td>";
                        echo "<td>{$row['quantita']}</td>";
                        echo "<td>{$row['unitaMisura']}</td>";
                        echo "<td>€ " . number_format($row['prezzoUnitario'], 2, ',', '.') . "</td>";
                        echo "<td>€ " . number_format($subtotaleRiga, 2, ',', '.') . "</td>";
                        echo "</tr>";

                        if (!$prossimaRow || $prossimaRow['idAcquisto'] !== $row['idAcquisto']) {
                            echo "<tr class='row-ordine totale-box'>";
                            echo "<td colspan='7' style='text-align:right;'>TOTALE INCASSATO ORDINE {$row['idAcquisto']}:</td>";
                            echo "<td>€ " . number_format($row['totaleOrdine'], 2, ',', '.') . "</td>";
                            echo "</tr>";
                        }
                    }

                } else {
                    echo "<tr><td colspan='8' style='text-align:center;'>Nessuna vendita registrata.</td></tr>";
                }
            ?>
            </tbody>
        </table>
        <br>
        <a href="index_gestore.php" class="btn">⬅ Torna all'area gestori</a>
    </div>
</body>
</html>