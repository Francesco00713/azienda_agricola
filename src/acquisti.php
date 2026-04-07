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
                    {$p['nome']} - €{$p['prezzo']} ({$p['giacenza']} {$p['unitaMisura']})
                  </option>";
        }
        ?>
    </select><br><br>

    <label>Quantità:</label>
    <input type="number" step="0.01" name="quantita" required><br><br>

    <button type="submit" name="aggiungi">Aggiungi al carrello</button>
</form>

<hr>

<h2>Prodotti disponibili</h2>
<table>
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

<?php
if (isset($_POST['aggiungi'])) {
    $prodotto = (int)$_POST['prodotto'];
    $quantita = (float)$_POST['quantita'];
    $res = $conn->query("SELECT giacenza FROM Prodotti WHERE idProdotto = $prodotto");

    if ($res->num_rows == 0) {
        echo "<p class='error'>Prodotto non trovato!</p>";
        exit();
    }

    $row = $res->fetch_assoc();

    if ($row['giacenza'] < $quantita) {
        echo "<p class='error'>Quantità non disponibile!</p>";
    } else {
        $check = $conn->query("
            SELECT * FROM Carrello 
            WHERE idCliente = $idCliente AND idProdotto = $prodotto
        ");

        if ($check->num_rows > 0) {
            $conn->query("
                UPDATE Carrello
                SET quantita = quantita + $quantita
                WHERE idCliente = $idCliente AND idProdotto = $prodotto
            ");
        } else {
            $conn->query("
                INSERT INTO Carrello (idCliente, idProdotto, quantita)
                VALUES ($idCliente, $prodotto, $quantita)
            ");
        }

        echo "<p class='success'>Prodotto aggiunto al carrello!</p>";
        echo "<a href='carrello.php'>Vai al carrello</a>";
    }
}
?>

<br><br>
<a href="index_cliente.php">⬅ Torna all'area clienti</a>

</div>
</body>
</html>