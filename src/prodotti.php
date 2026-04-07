<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Gestione_prodotti</title>
</head>
<body>
    <div class="container">

        <h2>Aggiungi Prodotto</h2>

        <form method="POST">

            <label>Nome:</label>
            <input type="text" name="nome" required>

            <label>Tipo:</label>
            <select name="tipo">
                <option value="fresco">Fresco</option>
            </select>

            <label>Unità:</label>
            <select name="unita">
                <option value="kg">Kg</option>
                <option value="pezzo">Pezzo</option>
            </select>

            <label>Giacenza:</label>
            <input type="number" step="0.01" name="giacenza" required>

            <label>Categoria:</label>
            <select name="categoria" required>
                <option value="frutta">Frutta</option>
                <option value="frutta secca">Frutta secca</option>
                <option value="ortaggio">Ortaggio</option>
                <option value="agrume">Agrume</option>
                <option value="legume">Legume</option>
                <option value="spezie">Spezia</option>
            </select>

            <label>Prezzo iniziale:</label>
            <input type="number" step="0.01" name="prezzo" required>

            <button type="submit" name="add">Inserisci</button>
        </form>

        <?php
        if (isset($_POST['add'])) {

            $nome = $_POST['nome'];
            $tipo = $_POST['tipo'];
            $unita = $_POST['unita'];
            $giacenza = $_POST['giacenza'];
            $categoria = $_POST['categoria'];
            $prezzo = $_POST['prezzo'];

            $sql = "INSERT INTO Prodotti (nome, tipo, unitaMisura, giacenza, categoria)
                    VALUES ('$nome', '$tipo', '$unita', $giacenza, '$categoria')";

            if ($conn->query($sql)) {
                $idProdotto = $conn->insert_id;

                $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita)
                              VALUES ($idProdotto, $prezzo, NOW(), NULL)");

                echo "<p class='success'>Prodotto e prezzo iniziale aggiunti con successo!</p>";
            } else {
                echo "<p class='error'>Errore: " . $conn->error . "</p>";
            }
        }
        ?>

        <h2>Lista Prodotti</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Unità di misura</th>
                <th>Giacenza</th>
                <th>Categoria</th>
                <th>Prezzo attuale (€)</th>
            </tr>

        <?php
        $res = $conn->query("
            SELECT p.idProdotto, p.nome, p.tipo, p.unitaMisura, p.giacenza, p.categoria,
                pr.prezzo
            FROM Prodotti p
            LEFT JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
            ORDER BY p.idProdotto
        ");

        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $prezzo = $row['prezzo'] !== null ? "€{$row['prezzo']}" : "-";

                echo "<tr>
                        <td>{$row['idProdotto']}</td>
                        <td>{$row['nome']}</td>
                        <td>{$row['tipo']}</td>
                        <td>{$row['unitaMisura']}</td>
                        <td>{$row['giacenza']}</td>
                        <td>{$row['categoria']}</td>
                        <td>$prezzo</td>
                    </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>Nessun prodotto presente</td></tr>";
        }
        ?>
        </table>

        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>

    </div>
</body>
</html>