<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Gestione_prezzi</title>
</head>
<body>
    <div class="container">
        <h2>Gestione Prezzi Prodotti</h2>
        <hr>

        <h3>Aggiorna Prezzo Prodotto</h3>
        <form method="POST">
            Seleziona prodotto:
            <select name="prodottoUpdate" required>
                <?php
                $resProd = $conn->query("
                    SELECT p.idProdotto, p.nome 
                    FROM Prodotti p
                    LEFT JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                    WHERE pr.idPrezzo IS NOT NULL
                ");
                while ($rowP = $resProd->fetch_assoc()) {
                    echo "<option value='{$rowP['idProdotto']}'>{$rowP['nome']}</option>";
                }
                ?>
            </select><br><br>
            Nuovo prezzo: <input type="number" step="0.01" name="nuovoPrezzo" required><br><br>
            <button type="submit" name="updatePrezzo">Aggiorna Prezzo</button>
        </form>

        <?php
        if (isset($_POST['updatePrezzo'])) {
            $idProdotto = $_POST['prodottoUpdate'];
            $nuovoPrezzo = $_POST['nuovoPrezzo'];
            $oggi = date('Y-m-d');

            $resCurr = $conn->query("SELECT * FROM Prezzi WHERE idProdotto = $idProdotto AND dataFineValidita IS NULL");
            if ($resCurr->num_rows > 0) {
                $rowCurr = $resCurr->fetch_assoc();
                $dataFine = $oggi;

                $conn->query("UPDATE Prezzi SET dataFineValidita = '$dataFine' WHERE idPrezzo = {$rowCurr['idPrezzo']}");

                $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita)
                            VALUES ($idProdotto, $nuovoPrezzo, '$dataFine', NULL)");

                echo "<p>Prezzo aggiornato correttamente!</p>";
            } else {
                echo "<p>Nessun prezzo attuale trovato per questo prodotto.</p>";
            }
        }
        ?>
        <hr>
        <h3>Storico Prezzi Prodotti</h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Prodotto</th>
                <th>Prezzo (€)</th>
                <th>Data Inizio Validità</th>
                <th>Data Fine Validità</th>
            </tr>
            <?php
            $res = $conn->query("
                SELECT p.nome, pr.prezzo, pr.dataInizioValidita, pr.dataFineValidita
                FROM Prodotti p
                LEFT JOIN Prezzi pr ON p.idProdotto = pr.idProdotto
                ORDER BY p.idProdotto, pr.dataInizioValidita
            ");

            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $dataFine = $row['dataFineValidita'] ? $row['dataFineValidita'] : '-';
                    echo "<tr>
                            <td>{$row['nome']}</td>
                            <td>€{$row['prezzo']}</td>
                            <td>{$row['dataInizioValidita']}</td>
                            <td>$dataFine</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Nessun prezzo registrato</td></tr>";
            }
            ?>
        </table>

        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>
    </div>
</body>
</html>