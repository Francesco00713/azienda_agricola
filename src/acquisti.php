<?php
    session_start();
    include "db.php";
    if (!isset($_SESSION['idUtente'])) {
        die("Devi effettuare il login!");
    }
    $idCliente = $_SESSION['idUtente'];
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acquisti</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
    <h2>Nuova Vendita</h2>
    <form method="POST">
        <label>Seleziona prodotto:</label><br>
        <select name="prodotto" required>
            <?php
            $resProd = $conn->query("
                SELECT p.idProdotto, p.nome, p.giacenza, p.unitaMisura, pr.prezzo
                FROM Prodotti p
                JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                WHERE p.giacenza > 0
            ");

            while ($p = $resProd->fetch_assoc()) {
                echo "<option value='{$p['idProdotto']}'>
                        {$p['nome']} - €" . number_format($p['prezzo'], 2, ',', '.') . " ({$p['giacenza']} {$p['unitaMisura']})
                    </option>";
            }
            ?>
        </select><br><br>
        <label>Quantità:</label>
        <input type="number" step="0.01" name="quantita" required><br><br>
        <button type="submit" name="aggiungi">Aggiungi al carrello</button>
    </form>

    <?php
    if (isset($_POST['aggiungi'])) {
        $prodotto = (int)$_POST['prodotto'];
        $quantita = (float)$_POST['quantita'];
        
        $res = $conn->query("SELECT giacenza FROM Prodotti WHERE idProdotto = $prodotto");
        $row = $res->fetch_assoc();

        if (!$row || $row['giacenza'] < $quantita) {
            echo "<p class='error'>Quantità non disponibile o prodotto inesistente!</p>";
        } else {
            $check = $conn->query("SELECT * FROM Carrello WHERE idCliente = $idCliente AND idProdotto = $prodotto");

            if ($check->num_rows > 0) {
                $conn->query("UPDATE Carrello SET quantita = quantita + $quantita WHERE idCliente = $idCliente AND idProdotto = $prodotto");
            } else {
                $conn->query("INSERT INTO Carrello (idCliente, idProdotto, quantita) VALUES ($idCliente, $prodotto, $quantita)");
            }

            echo "<p class='success'>Prodotto aggiunto al carrello! <a href='carrello.php'>Vai al carrello</a></p>";
        }
    }
    ?>

    <hr>
    <h2>Prodotti disponibili</h2>
    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Giacenza</th>
                <th>Unità</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Prezzo Attuale</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $res = $conn->query("
                SELECT p.nome, p.giacenza, p.unitaMisura, p.categoria, p.tipo, pr.prezzo
                FROM Prodotti p
                JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                WHERE p.giacenza > 0
            ");

            while ($p = $res->fetch_assoc()) {
                echo "<tr>
                        <td>{$p['nome']}</td>
                        <td>{$p['giacenza']}</td>
                        <td>{$p['unitaMisura']}</td>
                        <td>{$p['categoria']}</td>
                        <td>{$p['tipo']}</td>
                        <td>€" . number_format($p['prezzo'], 2, ',', '.') . "</td>
                    </tr>";
            }
        ?>
        </tbody>
    </table>
    
    <br><br>
    <a href="index_cliente.php" class="btn">⬅ Torna all'area clienti</a>
    </div>
</body>
</html>