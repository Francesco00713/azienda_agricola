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
    <title>Storico_acquisti</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Storico acquisti</h2>
        <table>
            <thead>
                <tr>
                    <th>ID Ordine</th> 
                    <th>Data</th>
                    <th>Prodotto</th>
                    <th>Prezzo Unitario</th>
                    <th>Quantità</th>
                    <th>Unità</th>
                    <th>Totale Prodotto</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $sql = "SELECT a.idAcquisto, a.dataAcquisto, a.totale as totaleOrdine, 
                               p.nome, p.unitaMisura, d.quantita, d.prezzoUnitario
                        FROM Acquisti a
                        INNER JOIN Dettaglio_acquisto d ON a.idAcquisto = d.idAcquisto
                        INNER JOIN Prodotti p ON d.idProdotto = p.idProdotto
                        WHERE a.idCliente = $idCliente
                        ORDER BY a.idAcquisto DESC";

                $res = $conn->query($sql);

                if ($res && $res->num_rows > 0) {
                    $dati = $res->fetch_all(MYSQLI_ASSOC);
                    $totaleRighe = count($dati);

                    for ($i = 0; $i < $totaleRighe; $i++) {
                        $row = $dati[$i];
                        $prossimaRow = ($i + 1 < $totaleRighe) ? $dati[$i + 1] : null;
                        
                        $prezzoTotaleRiga = $row['quantita'] * $row['prezzoUnitario'];

                        $classeSeparatore = ($i > 0 && $row['idAcquisto'] !== $dati[$i-1]['idAcquisto']) ? "separatore-ordine" : "";

                        echo "<tr class='$classeSeparatore'>";
                        if ($i == 0 || $row['idAcquisto'] !== $dati[$i-1]['idAcquisto']) {
                            echo "<td>{$row['idAcquisto']}</td>";
                            echo "<td>" . date("d/m/Y", strtotime($row['dataAcquisto'])) . "</td>";
                        } else {
                            echo "<td></td><td></td>";
                        }
                        
                        echo "<td>{$row['nome']}</td>";
                        echo "<td>€ " . number_format($row['prezzoUnitario'], 2, ',', '.') . "</td>";
                        echo "<td>{$row['quantita']}</td>";
                        echo "<td>{$row['unitaMisura']}</td>";
                        echo "<td>€ " . number_format($prezzoTotaleRiga, 2, ',', '.') . "</td>";
                        echo "</tr>";

                        if (!$prossimaRow || $prossimaRow['idAcquisto'] !== $row['idAcquisto']) {
                            echo "<tr class='row-ordine totale-box'>";
                            echo "<td colspan='6' style='text-align:right;'>TOTALE PAGATO ORDINE {$row['idAcquisto']}:</td>";
                            echo "<td>€ " . number_format($row['totaleOrdine'], 2, ',', '.') . "</td>";
                            echo "</tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>Non hai ancora effettuato acquisti.</td></tr>";
                }
            ?>
            </tbody>
        </table>
        <br>
        <a href="index_cliente.php" class="btn">⬅ Torna all'area clienti</a>
    </div>
</body>
</html>