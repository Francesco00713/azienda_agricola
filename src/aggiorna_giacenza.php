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
    <title>Aggiorna giacenza</title>
</head>
<body>
    <div class="container">
        <h2>Aggiorna giacenza</h2>
        <form method="POST">
            <label>Seleziona prodotto:</label>
            <select name="idProdotto" required>
                <option value=""></option>
                <?php
                    $prodottiSelect = $conn->query("SELECT idProdotto, nome, tipo, unitaMisura FROM Prodotti");
                    while ($row = $prodottiSelect->fetch_assoc()) {
                        echo "<option value='{$row['idProdotto']}'>{$row['nome']} ({$row['tipo']}) - {$row['unitaMisura']}</option>";
                    }
                ?>
            </select>
            <label>Quantità da aggiungere/togliere:</label>
            <input type="number" step="0.01" name="quantita" required>
            <button type="submit" name="update">Aggiorna Giacenza</button>
        </form>
        <?php
            if (isset($_POST['update'])) {
                $idProdotto = intval($_POST['idProdotto']);
                $quantita = floatval($_POST['quantita']);

                $check = $conn->query("SELECT nome, giacenza, categoria, tipo FROM Prodotti WHERE idProdotto = $idProdotto");
                if ($check->num_rows > 0) {
                    $dati = $check->fetch_assoc();
                    $giacenzaAttuale = floatval($dati['giacenza']);
                    $nomeProdotto = $dati['nome'];
                    $categoria = $dati['categoria'];
                    $tipoProdotto = $dati['tipo'];

                    if (($giacenzaAttuale + $quantita) < 0) {
                        echo "<p class='error'>Operazione annullata: la giacenza di <strong>$nomeProdotto</strong> non può diventare negativa (attuale: $giacenzaAttuale).</p>";
                    } else {
                        $sql = "UPDATE Prodotti SET giacenza = giacenza + $quantita WHERE idProdotto = $idProdotto";
                        
                        if ($conn->query($sql)) {
                            
                            if ($quantita > 0) {
                                $idGestore = $_SESSION['idUtente'] ?? 1;
                                $idTipoTrovato = null;

                                if ($categoria !== 'prodotto lavorato') {
                                    $resT = $conn->query("SELECT idTipo FROM Tipi WHERE tipo = 'Raccolta' LIMIT 1");
                                    if ($resT->num_rows > 0) $idTipoTrovato = $resT->fetch_assoc()['idTipo'];
                                } else {
                                    $cercaTipo = $conn->query("SELECT idTipo FROM Tipi WHERE '$nomeProdotto' LIKE CONCAT('%', tipo, '%') OR tipo LIKE '%$tipoProdotto%' LIMIT 1");
                                    
                                    if ($cercaTipo->num_rows > 0) {
                                        $idTipoTrovato = $cercaTipo->fetch_assoc()['idTipo'];
                                    } else {
                                        $resT = $conn->query("SELECT idTipo FROM Tipi WHERE tipo != 'Raccolta' LIMIT 1");
                                        if ($resT->num_rows > 0) $idTipoTrovato = $resT->fetch_assoc()['idTipo'];
                                    }
                                }

                                if ($idTipoTrovato) {
                                    // Se è Raccolta (id 1), luogo Campi Aziendali (id 2)
                                    $idLuogo = ($idTipoTrovato == 1) ? 2 : 1;
                                    
                                    $conn->query("INSERT INTO Lavorazioni (dataLavorazione, idLuogo, idTipo) VALUES (NOW(), $idLuogo, $idTipoTrovato)");
                                    $idLavorazione = $conn->insert_id;
                                    $conn->query("INSERT INTO compie (idGestore, idLavorazione) VALUES ($idGestore, $idLavorazione)");
                                    $conn->query("INSERT INTO produce (idProdotto, idLavorazione, quantitaProdotta) VALUES ($idProdotto, $idLavorazione, $quantita)");
                                }
                            }
                            echo "<p class='success'>Giacenza di <strong>$nomeProdotto</strong> aggiornata e operazione registrata nello storico!</p>";
                        } else {
                            echo "<p class='error'>Errore: " . $conn->error . "</p>";
                        }
                    }
                }
            }
        ?>
        <hr>
        <h2>Lista prodotti aggiornata</h2>
        <table>
            <tr>
                <th>Nome</th>
                <th>Giacenza</th>
                <th>Unità</th>
                <th>Categoria</th>
                <th>Tipo</th>
                <th>Prezzo attuale (€)</th>
            </tr>
            <?php
                $res = $conn->query("
                    SELECT p.nome, p.giacenza, p.unitaMisura, p.categoria, p.tipo, pr.prezzo
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