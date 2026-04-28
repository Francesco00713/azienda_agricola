<?php 
    if (!isset($_SESSION)) {
        session_start();
    }
    include "db.php"; 
    $messaggio = ""; 

    if (isset($_POST['lavora'])) {
        $input = $_POST['inputProdotto'];
        $tipoLavorazione = $_POST['tipoLavorazione'];
        $quantitaUsata = floatval($_POST['quantitaUsata']);
        $quantitaProdotta = floatval($_POST['quantitaProdotta']);
        $prezzoLavorato = $_POST['prezzoLavorato'];

        if (empty($prezzoLavorato) || $prezzoLavorato <= 0) {
            $messaggio = "<p class='error'>Inserire un prezzo valido per il prodotto lavorato.</p>";
        } elseif ($quantitaProdotta <= 0) {
            $messaggio = "<p class='error'>Inserire una quantità prodotta valida.</p>";
        } else {
            $res = $conn->query("SELECT nome, giacenza, unitaMisura FROM Prodotti WHERE idProdotto = $input");
            $prodotto = $res->fetch_assoc();

            if (!$prodotto) {
                $messaggio = "<p class='error'>Prodotto non trovato!</p>";
            } elseif ($prodotto['giacenza'] < $quantitaUsata) {
                $messaggio = "<p class='error'>Giacenza insufficiente!</p>";
            } else {
                $resTipo = $conn->query("SELECT tipo FROM Tipi WHERE idTipo = $tipoLavorazione");
                $tipoNome = $resTipo->fetch_assoc()['tipo'];
                $tipoNomeLower = strtolower($tipoNome);
                $luogo = ($tipoLavorazione == 1) ? 2 : 1;

                if ($tipoNomeLower == "produzione marmellata") {
                    $nomeOutput = "Marmellata di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif ($tipoNomeLower == "essiccazione") {
                    $nomeOutput = $prodotto['nome'] . " essiccati/e";
                    $tipoOutput = "riserva";
                } elseif ($tipoNomeLower == "fermentazione") {
                    $nomeOutput = "Sidro di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif ($tipoNomeLower == "confezionamento") {
                    $nomeOutput = "Confezione di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif ($tipoNomeLower == "spremitura") {
                    $nomeOutput = "Spremuta di " . $prodotto['nome'];
                    $tipoOutput = "confezionato";
                } elseif ($tipoNomeLower == "macinazione") {
                    $nomeOutput = $prodotto['nome'] . " macinati/e";
                    $tipoOutput = "riserva";
                } elseif ($tipoNomeLower == "salatura") {
                    $nomeOutput = $prodotto['nome'] . " salati/e";
                    $tipoOutput = "riserva";
                } elseif ($tipoNomeLower == "smallatura") {
                    $nomeOutput = $prodotto['nome'] . " smallati/e";
                    $tipoOutput = "riserva";
                }else {
                    $nomeOutput = $prodotto['nome'] . " " . $tipoNomeLower;
                    $tipoOutput = "riserva";
                }

                if (in_array($tipoNomeLower, ['produzione marmellata', 'confezionamento'])) {
                    $unitaOutput = 'pezzo';
                } elseif (in_array($tipoNomeLower, ['fermentazione', 'spremitura'])) {
                    $unitaOutput = 'l';
                } else {
                    $unitaOutput = $prodotto['unitaMisura'];
                }

                $resCheck = $conn->query("SELECT idProdotto FROM Prodotti WHERE nome = '$nomeOutput' AND tipo = '$tipoOutput'");

                if ($resCheck->num_rows > 0) {
                    $messaggio = "<div class='info-box' style='background: #e1f5fe; border: 1px solid #01579b; padding: 15px; margin: 10px 0; border-radius: 5px;'>
                                    <p style='margin: 0;'>
                                        Il prodotto <strong>$nomeOutput</strong> è già presente nel database.<br>
                                        Se desideri modificare la sua giacenza, utilizza la sezione 
                                        <a href='aggiorna_giacenza.php' style='font-weight: bold; color: #01579b;'>Aggiorna Giacenza</a>.
                                    </p>
                                  </div>";
                } else {
                    $conn->query("INSERT INTO Prodotti (nome, tipo, unitaMisura, giacenza, categoria) VALUES ('$nomeOutput', '$tipoOutput', '$unitaOutput', $quantitaProdotta, 'prodotto lavorato')");
                    $idOutput = $conn->insert_id;
                    $oggi = date('Y-m-d');
                    $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita) VALUES ($idOutput, $prezzoLavorato, '$oggi', NULL)");

                    $conn->query("INSERT INTO Lavorazioni (dataLavorazione, idLuogo, idTipo) VALUES (NOW(), $luogo, $tipoLavorazione)");
                    $idLavorazione = $conn->insert_id;
                    $idGestore = $_SESSION['idUtente'] ?? 1; 
                    
                    $conn->query("INSERT INTO compie (idGestore, idLavorazione) VALUES ($idGestore, $idLavorazione)");
                    $conn->query("INSERT INTO usa (idProdotto, idLavorazione, quantitaUsata) VALUES ($input, $idLavorazione, $quantitaUsata)");
                    $conn->query("INSERT INTO produce (idProdotto, idLavorazione, quantitaProdotta) VALUES ($idOutput, $idLavorazione, $quantitaProdotta)");
                    $conn->query("UPDATE Prodotti SET giacenza = giacenza - $quantitaUsata WHERE idProdotto = $input");
                    
                    $messaggio = "<p class='success'>Lavorazione completata con successo!</p>";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/css/style.css">
    <title>Gestione Lavorazioni</title>
</head>
<body>
    <div class="container">
        <h2>Esegui Lavorazione</h2>
        
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
                    $resT = $conn->query("SELECT * FROM Tipi WHERE tipo != 'Raccolta'");
                    while ($rowT = $resT->fetch_assoc()) {
                        echo "<option value='{$rowT['idTipo']}'>{$rowT['tipo']}</option>";
                    }
                ?>
            </select>

            <label>Indica la quantità che vuoi utilizzare:</label>
            <input type="number" step="0.01" name="quantitaUsata" placeholder="Quanto usi?" required>

            <label>Indica la quantità che vuoi produrre:</label>
            <input type="number" step="0.01" name="quantitaProdotta" placeholder="Quanto ottieni?" required>

            <label>Prezzo del prodotto lavorato (€):</label>
            <input type="number" step="0.01" name="prezzoLavorato" required>

            <button type="submit" name="lavora">Esegui lavorazione</button>
        </form>

        <?php echo $messaggio; ?>

        <hr>
        <h2>Prodotti attualmente presenti</h2>
        <table>
            <tr>
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