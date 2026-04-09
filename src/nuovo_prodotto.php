<?php
session_start();
include "db.php";
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Gestione prodotti</title>
</head>
<body>
    <div class="container">
        <h2>Aggiungi prodotto</h2>
        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" required>
            <label>Tipo:</label>
            <select name="tipo">
                <option value="fresco">Fresco</option>
                <option value="riserva">Riserva</option>
                <option value="confezionato">Confezionato</option>
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
                $nome = $conn->real_escape_string($_POST['nome']);
                $tipo = $conn->real_escape_string($_POST['tipo']);
                $unita = $conn->real_escape_string($_POST['unita']);
                $giacenza = floatval($_POST['giacenza']);
                $categoria = $conn->real_escape_string($_POST['categoria']);
                $prezzoNuovo = floatval($_POST['prezzo']);

                $idGestore = $_SESSION['idUtente'] ?? 1; 

                $checkProd = $conn->query("SELECT idProdotto, categoria FROM Prodotti WHERE nome = '$nome' AND tipo = '$tipo' AND unitaMisura = '$unita'");

                if ($checkProd->num_rows > 0) {
                    $rowProd = $checkProd->fetch_assoc();
                    $idEsistente = $rowProd['idProdotto'];
                    $categoriaEsistente = $rowProd['categoria'];

                    $checkPrezzo = $conn->query("SELECT prezzo FROM Prezzi WHERE idProdotto = $idEsistente AND dataFineValidita IS NULL");
                    
                    $prezzoAttuale = 0;
                    if ($checkPrezzo->num_rows > 0) {
                        $prezzoAttuale = floatval($checkPrezzo->fetch_assoc()['prezzo']);
                    }

                    if ($categoria != $categoriaEsistente) {
                        echo "<p class='error'>Attenzione: Il prodotto è già presente ma appartiene a una categoria diversa ($categoriaEsistente). <br> 
                            Impossibile aggiungere la giacenza: le caratteristiche non corrispondono.</p>";
                    } elseif ($prezzoNuovo != $prezzoAttuale) {
                        echo "<p class='error'>Attenzione: Il prodotto è già presente ma con un prezzo diverso (€" . number_format($prezzoAttuale, 2) . "). <br> 
                            Modifica prima il prezzo del prodotto dalla gestione prezzi e poi riprova ad aggiungere la giacenza.</p>";
                    } else {
                        if ($conn->query("UPDATE Prodotti SET giacenza = giacenza + $giacenza WHERE idProdotto = $idEsistente")) {
                            echo "<p class='success'>Prodotto già presente: giacenza aggiornata con successo!</p>";
                        } else {
                            echo "<p class='error'>Errore nell'aggiornamento: " . $conn->error . "</p>";
                        }
                    }
                } else {
                    $sql = "INSERT INTO Prodotti (nome, giacenza, unitaMisura, categoria, tipo) 
                            VALUES ('$nome', $giacenza, '$unita', '$categoria', '$tipo')";

                    if ($conn->query($sql)) {
                        $idProdotto = $conn->insert_id;
                        $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita) 
                                    VALUES ($idProdotto, $prezzoNuovo, NOW(), NULL)");
                        $conn->query("INSERT INTO aggiuge (idGestore, idProdotto) VALUES ($idGestore, $idProdotto)");

                        echo "<p class='success'>Nuovo prodotto e prezzo aggiunti con successo!</p>";
                    } else {
                        echo "<p class='error'>Errore: " . $conn->error . "</p>";
                    }
                }
            }
        ?>
        <h2>Lista prodotti</h2>
        <table>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Giacenza</th>
            <th>Unità di misura</th>
            <th>Categoria</th>
            <th>Tipo</th>
            <th>Prezzo attuale (€)</th>
        </tr>
        <?php
            $res = $conn->query("
                SELECT p.idProdotto, p.nome, p.giacenza, p.unitaMisura, p.categoria, p.tipo, pr.prezzo
                FROM Prodotti p
                LEFT JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                ORDER BY p.idProdotto
            ");

            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $prezzo = $row['prezzo'] !== null ? "€" . number_format($row['prezzo'], 2) : "-";
                    echo "<tr>
                            <td>{$row['idProdotto']}</td>
                            <td>{$row['nome']}</td>
                            <td>{$row['giacenza']}</td>
                            <td>{$row['unitaMisura']}</td>
                            <td>{$row['categoria']}</td>
                            <td>{$row['tipo']}</td>
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