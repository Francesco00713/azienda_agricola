<?php 
    if (!isset($_SESSION)) {
        session_start();
    }
    include "db.php"; 
    $messaggio = ""; 

    if (isset($_POST['lavora'])) {
        $input = $_POST['inputProdotto'];
        $tipoLavorazione = $_POST['tipoLavorazione'];
        $quantitaUsata = $_POST['quantitaUsata'];
        $prezzoLavorato = $_POST['prezzoLavorato'];

        if (empty($prezzoLavorato) || $prezzoLavorato <= 0) {
            $messaggio = "<p class='error'>Inserire un prezzo valido per il prodotto lavorato.</p>";
        } else {
            $luogo = 1;
            $res = $conn->query("SELECT nome, giacenza, unitaMisura FROM Prodotti WHERE idProdotto = $input");
            $prodotto = $res->fetch_assoc();

            if (!$prodotto) {
                $messaggio = "<p class='error'>Prodotto non trovato!</p>";
            } elseif ($prodotto['giacenza'] < $quantitaUsata) {
                $messaggio = "<p class='error'>Giacenza insufficiente!</p>";
            } else {
                $resTipo = $conn->query("SELECT tipo FROM Tipi WHERE idTipo = $tipoLavorazione");
                $tipoNome = $resTipo->fetch_assoc()['tipo'];

                if (strtolower($tipoNome) == "produzione marmellata") {
                    $nomeOutput = "Marmellata di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif (strtolower($tipoNome) == "essiccazione") {
                    $nomeOutput = $prodotto['nome'] . " essiccati/e";
                    $tipoOutput = "riserva";
                } elseif (strtolower($tipoNome) == "fermentazione") {
                    if (strtolower($prodotto['nome']) == "uva") $nomeOutput = "Vino";
                    elseif (strtolower($prodotto['nome']) == "mela") $nomeOutput = "Sidro";
                    else $nomeOutput = $prodotto['nome'] . " fermentati/e";
                    $tipoOutput = "confezionato";
                } elseif (strtolower($tipoNome) == "confezionamento") {
                    $nomeOutput = "Confezione di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif (strtolower($tipoNome) == "spremitura") {
                    $nomeOutput = "Spremuta di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif (strtolower($tipoNome) == "macinazione") {
                    $nomeOutput = $prodotto['nome'] . " macinati/e";
                    $tipoOutput = "riserva";
                } elseif (strtolower($tipoNome) == "salatura") {
                    $nomeOutput = $prodotto['nome'] . " salati/e";
                    $tipoOutput = "riserva";
                } elseif (strtolower($tipoNome) == "affumicatura") {
                    $nomeOutput = $prodotto['nome'] . " affumicati/e";
                    $tipoOutput = "riserva";
                } elseif (strtolower($tipoNome) == "smallatura") {
                    $nomeOutput = $prodotto['nome'] . " smallati/e";
                    $tipoOutput = "riserva";
                } else {
                    $nomeOutput = $prodotto['nome'] . " " . strtolower($tipoNome);
                    $tipoOutput = "riserva";
                }

                $unitaOutput = (in_array(strtolower($tipoNome), ['produzione marmellata', 'fermentazione', 'confezionamento', 'spremitura'])) ? 'pezzo' : $prodotto['unitaMisura'];

                $resCheck = $conn->query("SELECT idProdotto FROM Prodotti WHERE nome = '$nomeOutput' AND tipo = '$tipoOutput'");

                $prezzoErrore = false;
                if ($resCheck->num_rows > 0) {
                    $rowOutput = $resCheck->fetch_assoc();
                    $idOutput = $rowOutput['idProdotto'];
                    $resPrezzo = $conn->query("SELECT prezzo FROM Prezzi WHERE idProdotto = $idOutput AND dataFineValidita IS NULL");
                    if ($resPrezzo->num_rows > 0) {
                        $prezzoCorrente = $resPrezzo->fetch_assoc()['prezzo'];
                        if ($prezzoCorrente != $prezzoLavorato) {
                            $messaggio = "<p class='error'>Il prodotto lavorato esiste già con prezzo diverso (€$prezzoCorrente).</p>";
                            $prezzoErrore = true;
                        }
                    }
                    if (!$prezzoErrore) {
                        $conn->query("UPDATE Prodotti SET giacenza = giacenza + $quantitaUsata WHERE idProdotto = $idOutput");
                    }
                } else {
                    $conn->query("INSERT INTO Prodotti (nome, tipo, unitaMisura, giacenza, categoria) VALUES ('$nomeOutput', '$tipoOutput', '$unitaOutput', $quantitaUsata, 'prodotto lavorato')");
                    $idOutput = $conn->insert_id;
                    $oggi = date('Y-m-d');
                    $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita) VALUES ($idOutput, $prezzoLavorato, '$oggi', NULL)");
                }

                if (!$prezzoErrore) {
                    $idLavorazioneQuery = $conn->query("INSERT INTO Lavorazioni (dataLavorazione, idLuogo, idTipo) VALUES (NOW(), $luogo, $tipoLavorazione)");
                    $idLavorazione = $conn->insert_id;
                    $idGestore = $_SESSION['idUtente'] ?? 1; 
                    $conn->query("INSERT INTO compie (idGestore, idLavorazione) VALUES ($idGestore, $idLavorazione)");
                    $conn->query("INSERT INTO usa (idProdotto, idLavorazione, quantitaUsata) VALUES ($input, $idLavorazione, $quantitaUsata)");
                    $conn->query("INSERT INTO produce (idProdotto, idLavorazione, quantitaProdotta) VALUES ($idOutput, $idLavorazione, $quantitaUsata)");
                    $conn->query("UPDATE Prodotti SET giacenza = giacenza - $quantitaUsata WHERE idProdotto = $input");
                    $messaggio = "<p class='success'>Lavorazione completata con successo!</p>";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Gestione Lavorazioni</title>
</head>
<body>
    <div class="container">
        <h2>Esegui Lavorazione</h2>
        <?php echo $messaggio; ?>
        <form method="POST">
            <label>Seleziona prodotto da lavorare:</label>
            <select name="inputProdotto" required>
                <?php
                    $res = $conn->query("SELECT * FROM Prodotti WHERE giacenza > 0 AND tipo != 'confezionato'");
                    while ($row = $res->fetch_assoc()) {
                        echo "<option value='{$row['idProdotto']}'>{$row['nome']} ({$row['giacenza']} {$row['unitaMisura']})</option>";
                    }
                ?>
            </select>
            <label>Seleziona tipo di lavorazione:</label>
            <select name="tipoLavorazione" required>
                <?php
                    $resT = $conn->query("SELECT * FROM Tipi");
                    while ($rowT = $resT->fetch_assoc()) {
                        echo "<option value='{$rowT['idTipo']}'>{$rowT['tipo']}</option>";
                    }
                ?>
            </select>
            <label>Quantità da utilizzare:</label>
            <input type="number" step="0.01" name="quantitaUsata" required>
            <label>Prezzo prodotto lavorato:</label>
            <input type="number" step="0.01" name="prezzoLavorato" required>
            <button type="submit" name="lavora">Esegui lavorazione</button>
        </form>
        <hr>
        <h2>Prodotti attualmente presenti</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Giacenza</th>
                <th>Unità</th>
                <th>Categoria</th>
                <th>Prezzo (€)</th>
            </tr>
            <?php
                $resTabella = $conn->query("
                    SELECT p.idProdotto, p.nome, p.tipo, p.giacenza, p.unitaMisura, p.categoria, pr.prezzo
                    FROM Prodotti p
                    LEFT JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                    ORDER BY p.idProdotto");

                while ($rowTab = $resTabella->fetch_assoc()) {
                    $prezzo = $rowTab['prezzo'] ?? '-';
                    echo "<tr>
                            <td>{$rowTab['idProdotto']}</td>
                            <td>{$rowTab['nome']}</td>
                            <td>{$rowTab['tipo']}</td>
                            <td>{$rowTab['giacenza']}</td>
                            <td>{$rowTab['unitaMisura']}</td>
                            <td>{$rowTab['categoria']}</td>
                            <td>$prezzo</td>
                        </tr>";
                }
            ?>
        </table>
        <br>
        <a href="index_gestore.php">⬅ Torna all'area gestori</a>
    </div>
</body>
</html>