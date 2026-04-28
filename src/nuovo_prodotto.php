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

                $checkProd = $conn->query("SELECT idProdotto FROM Prodotti WHERE nome = '$nome' AND tipo = '$tipo' AND unitaMisura = '$unita'");

                if ($checkProd->num_rows > 0) {
                    echo "<div class='info-box' style='background: #e1f5fe; border: 1px solid #01579b; padding: 15px; margin: 10px 0; border-radius: 5px;'>
                            <p style='margin: 0;'>
                                Il prodotto <strong>$nome</strong> è già presente nel database.<br>
                                Se desideri modificare la sua giacenza, utilizza la sezione 
                                <a href='aggiorna_giacenza.php' style='font-weight: bold; color: #01579b;'>Aggiorna Giacenza</a>.
                            </p>
                          </div>";
                } else {
                    $sql = "INSERT INTO Prodotti (nome, giacenza, unitaMisura, categoria, tipo) 
                            VALUES ('$nome', $giacenza, '$unita', '$categoria', '$tipo')";

                    if ($conn->query($sql)) {
                        $idProdotto = $conn->insert_id;
                        
                        $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita) 
                                    VALUES ($idProdotto, $prezzoNuovo, NOW(), NULL)");
                        
                        $conn->query("INSERT INTO aggiuge (idGestore, idProdotto) VALUES ($idGestore, $idProdotto)");

                        if ($giacenza > 0) {
                            $resTipo = $conn->query("SELECT idTipo FROM Tipi WHERE tipo = 'Raccolta' LIMIT 1");
                            if ($resTipo->num_rows > 0) {
                                $idTipoRaccolta = $resTipo->fetch_assoc()['idTipo'];
                                // Se è Raccolta (idTipo 1), il luogo è Campi Aziendali (id 2)
                                $idLuogo = ($idTipoRaccolta == 1) ? 2 : 1;

                                $conn->query("INSERT INTO Lavorazioni (dataLavorazione, idLuogo, idTipo) VALUES (NOW(), $idLuogo, $idTipoRaccolta)");
                                $idLavorazione = $conn->insert_id;

                                $conn->query("INSERT INTO compie (idGestore, idLavorazione) VALUES ($idGestore, $idLavorazione)");
                                $conn->query("INSERT INTO produce (idProdotto, idLavorazione, quantitaProdotta) VALUES ($idProdotto, $idLavorazione, $giacenza)");
                            }
                        }
                        echo "<p class='success'>Nuovo prodotto inserito e operazione di raccolta registrata!</p>";
                    } else {
                        echo "<p class='error'>Errore: " . $conn->error . "</p>";
                    }
                }
            }
        ?>
        <h2>Lista prodotti</h2>
        <table>
        <tr>
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
                            <td>{$row['nome']}</td>
                            <td>{$row['giacenza']}</td>
                            <td>{$row['unitaMisura']}</td>
                            <td>{$row['categoria']}</td>
                            <td>{$row['tipo']}</td>
                            <td>$prezzo</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Nessun prodotto presente</td></tr>";
            }
        ?>
        </table>
        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>
    </div>
</body>
</html>