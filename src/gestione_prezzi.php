<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Gestione Prezzi</title>
</head>
<body>
    <div class="container">
        <h2>Gestione Prezzi</h2>
        <hr>

        <h3>Aggiorna prezzo</h3>
        <form method="POST">
            <label>Prodotto:</label>
            <select name="prodottoUpdate" required>
                <?php
                $res = $conn->query("SELECT p.idProdotto, p.nome FROM Prodotti p 
                                     JOIN Prezzi pr ON p.idProdotto = pr.idProdotto 
                                     WHERE pr.dataFineValidita IS NULL");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['idProdotto']}'>{$row['nome']}</option>";
                }
                ?>
            </select><br><br>
            <label>Nuovo prezzo (€):</label>
            <input type="number" step="0.01" name="nuovoPrezzo" required><br><br>
            <button type="submit" name="updatePrezzo">Salva Nuovo Prezzo</button>
        </form>

        <?php
        if (isset($_POST['updatePrezzo'])) {
            $id = $_POST['prodottoUpdate'];
            $prezzo = $_POST['nuovoPrezzo'];
            $oggi = date('Y-m-d');

            $curr = $conn->query("SELECT * FROM Prezzi WHERE idProdotto = $id AND dataFineValidita IS NULL")->fetch_assoc();

            if ($curr) {
                if ($curr['dataInizioValidita'] == $oggi) {
                    $conn->query("UPDATE Prezzi SET prezzo = $prezzo WHERE idPrezzo = {$curr['idPrezzo']}");
                } else {
                    $ieri = date('Y-m-d', strtotime("-1 day"));
                    $conn->query("UPDATE Prezzi SET dataFineValidita = '$ieri' WHERE idPrezzo = {$curr['idPrezzo']}");
                    $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita) 
                                  VALUES ($id, $prezzo, '$oggi', NULL)");
                }
                echo "<p style='color:green'>Prezzo aggiornato!</p>";
            }
        }
        ?>

        <hr>
        <h3>Storico Variazioni Prezzi</h3>
        <table>
            <tr>
                <th>Prodotto</th>
                <th>Prezzo</th>
                <th>Inizio</th>
                <th>Fine</th>
            </tr>
            <?php
            $res = $conn->query("SELECT p.nome, pr.* FROM Prezzi pr JOIN Prodotti p ON pr.idProdotto = p.idProdotto ORDER BY p.nome, pr.dataInizioValidita DESC");
            while ($row = $res->fetch_assoc()) {
                $fine = $row['dataFineValidita'] ? date("d/m/Y", strtotime($row['dataFineValidita'])) : "Attuale";
                echo "<tr>
                        <td>{$row['nome']}</td>
                        <td>€ {$row['prezzo']}</td>
                        <td>" . date("d/m/Y", strtotime($row['dataInizioValidita'])) . "</td>
                        <td>$fine</td>
                      </tr>";
            }
            ?>
        </table>
        <br>
        <a href="index_gestore.php">⬅ Torna indietro</a>
    </div>
</body>
</html>