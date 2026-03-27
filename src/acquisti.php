<?php
session_start();
include "db.php";

if (!isset($_SESSION['carrello'])) {
    $_SESSION['carrello'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Acquisti</title>
</head>
<body>
    <div class="container">

        <h2>Carrello Prodotti</h2>

        <form method="POST">
            <label>Seleziona cliente:</label><br>
            <select name="cliente" required>
                <?php
                $resClienti = $conn->query("SELECT * FROM Clienti ORDER BY nome");
                while ($c = $resClienti->fetch_assoc()) {
                    echo "<option value='{$c['idCliente']}'>{$c['nome']}</option>";
                }
                ?>
            </select><br><br>

            <label>Prodotto:</label><br>
            <select name="prodotto" required>
                <?php
                $res = $conn->query("
                    SELECT p.idProdotto, p.nome, p.giacenza, p.unitaMisura, pr.prezzo
                    FROM Prodotti p
                    JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                    WHERE p.giacenza > 0
                ");
                while ($p = $res->fetch_assoc()) {
                    echo "<option value='{$p['idProdotto']}'>
                            {$p['nome']} - €{$p['prezzo']} ({$p['giacenza']} {$p['unitaMisura']})
                        </option>";
                }
                ?>
            </select><br><br>

            Quantità: <input type="number" step="0.01" name="quantita" required><br><br>

            <button type="submit" name="aggiungi">Aggiungi al carrello</button>
        </form>

        <hr>

        <?php
        // AGGIUNTA AL CARRELLO
        if (isset($_POST['aggiungi'])) {
            $idProdotto = $_POST['prodotto'];
            $quantita = $_POST['quantita'];
            $_SESSION['cliente'] = $_POST['cliente'];

            $res = $conn->query("
                SELECT p.nome, p.giacenza, p.unitaMisura, pr.prezzo
                FROM Prodotti p
                JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                WHERE p.idProdotto = $idProdotto
            ");
            $p = $res->fetch_assoc();

            if ($p['giacenza'] < $quantita) {
                echo "Quantità non disponibile!";
            } else {
                $_SESSION['carrello'][] = [
                    "id" => $idProdotto,
                    "nome" => $p['nome'],
                    "prezzo" => $p['prezzo'],
                    "quantita" => $quantita,
                    "unita" => $p['unitaMisura']
                ];
                echo "Prodotto aggiunto al carrello!";
            }
        }
        ?>

        <h3>Carrello</h3>

        <table border="1" cellpadding="5">
        <tr>
            <th>Prodotto</th>
            <th>Quantità</th>
            <th>Prezzo</th>
            <th>Totale</th>
            <th>Azione</th>
        </tr>

        <?php
        $totaleGenerale = 0;

        foreach ($_SESSION['carrello'] as $index => $item) {
            $tot = $item['prezzo'] * $item['quantita'];
            $totaleGenerale += $tot;

            echo "<tr>
                    <td>{$item['nome']}</td>
                    <td>{$item['quantita']} {$item['unita']}</td>
                    <td>€{$item['prezzo']}</td>
                    <td>€$tot</td>
                    <td>
                        <a href='?rimuovi=$index'></a>
                    </td>
                </tr>";
        }

        echo "<tr><td colspan='3'><b>Totale</b></td><td colspan='2'><b>€$totaleGenerale</b></td></tr>";
        ?>
        </table>

        <?php
        // RIMOZIONE DAL CARRELLO
        if (isset($_GET['rimuovi'])) {
            unset($_SESSION['carrello'][$_GET['rimuovi']]);
            $_SESSION['carrello'] = array_values($_SESSION['carrello']);
            header("Location: acquisti.php");
        }

        // ACQUISTO FINALE
        if (isset($_POST['acquista']) && !empty($_SESSION['carrello'])) {

            $idCliente = $_SESSION['cliente'];

            $conn->query("
                INSERT INTO Acquisti (idCliente, dataAcquisto, totScontato, note)
                VALUES ($idCliente, NOW(), $totaleGenerale, '')
            ");

            $idAcquisto = $conn->insert_id;

            echo "<hr><h2>Scontrino</h2>";

            foreach ($_SESSION['carrello'] as $item) {

                $conn->query("
                    INSERT INTO Dettaglio_acquisto (idAcquisto, idProdotto, quantita)
                    VALUES ($idAcquisto, {$item['id']}, {$item['quantita']})
                ");

                $conn->query("
                    UPDATE Prodotti
                    SET giacenza = giacenza - {$item['quantita']}
                    WHERE idProdotto = {$item['id']}
                ");

                // elimina se giacenza 0
                $conn->query("
                    DELETE FROM Prodotti
                    WHERE idProdotto = {$item['id']} AND giacenza <= 0
                ");

                echo "<p>{$item['nome']} - {$item['quantita']} x €{$item['prezzo']}</p>";
            }

            echo "<b>Totale: €$totaleGenerale</b>";

            // svuota carrello
            $_SESSION['carrello'] = [];
        }
        ?>

        <form method="POST">
            <button type="submit" name="acquista">Conferma Acquisto</button>
        </form>

        <hr>

        <h2>Prodotti disponibili</h2>

        <table border="1" cellpadding="5">
        <tr>
            <th>Nome</th>
            <th>Giacenza</th>
            <th>Unità</th>
            <th>Prezzo</th>
        </tr>

        <?php
        $res = $conn->query("
            SELECT p.nome, p.giacenza, p.unitaMisura, pr.prezzo
            FROM Prodotti p
            JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
            WHERE p.giacenza > 0
        ");

        while ($p = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$p['nome']}</td>
                    <td>{$p['giacenza']}</td>
                    <td>{$p['unitaMisura']}</td>
                    <td>€{$p['prezzo']}</td>
                </tr>";
        }
        ?>
        </table>

        <br>
        <a href="index_cliente.php">Torna alla home cliente</a>
    </div>
</body>
</html>