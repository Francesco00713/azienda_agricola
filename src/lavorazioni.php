<?php include "db.php"; ?>

<h2>Esegui Lavorazione</h2>

<form method="POST">
    <label>Seleziona prodotto da lavorare:</label><br>
    <select name="inputProdotto" required>
        <?php
        $res = $conn->query("SELECT * FROM Prodotti WHERE giacenza > 0 AND tipo != 'confezionato'");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='{$row['idProdotto']}'>{$row['nome']} ({$row['giacenza']} {$row['unitaMisura']})</option>";
        }
        ?>
    </select><br><br>

    <label>Seleziona tipo di lavorazione:</label><br>
    <select name="tipoLavorazione" required>
        <?php
        $res = $conn->query("SELECT * FROM Tipi");
        while ($row = $res->fetch_assoc()) {
            echo "<option value='{$row['idTipo']}'>{$row['tipo']}</option>";
        }
        ?>
    </select><br><br>

    Quantità da utilizzare: <input type="number" step="0.01" name="quantitaUsata" required><br><br>

    Prezzo prodotto lavorato: <input type="number" step="0.01" name="prezzoLavorato" required><br><br>

    <button type="submit" name="lavora">Esegui lavorazione</button>
</form>

<hr>

<h2>Prodotti attualmente presenti</h2>

<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>ID</th>
    <th>Nome</th>
    <th>Tipo</th>
    <th>Giacenza</th>
    <th>Unità di misura</th>
    <th>Categoria</th>
    <th>Prezzo attuale (€)</th>
</tr>

<?php
$res = $conn->query("
    SELECT p.idProdotto, p.nome, p.tipo, p.giacenza, p.unitaMisura, p.categoria, pr.prezzo
    FROM Prodotti p
    LEFT JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
    ORDER BY p.idProdotto
");

while ($row = $res->fetch_assoc()) {
    $prezzo = $row['prezzo'] ?? '-';
    echo "<tr>
            <td>{$row['idProdotto']}</td>
            <td>{$row['nome']}</td>
            <td>{$row['tipo']}</td>
            <td>{$row['giacenza']}</td>
            <td>{$row['unitaMisura']}</td>
            <td>{$row['categoria']}</td>
            <td>$prezzo</td>
          </tr>";
}
?>
</table>

<?php
if (isset($_POST['lavora'])) {

    $input = $_POST['inputProdotto'];
    $tipoLavorazione = $_POST['tipoLavorazione'];
    $quantitaUsata = $_POST['quantitaUsata'];
    $prezzoLavorato = $_POST['prezzoLavorato'];

    if (empty($prezzoLavorato) || $prezzoLavorato <= 0) {
        die("Inserire un prezzo valido per il prodotto lavorato.");
    }

    $luogo = 1; // Sempre azienda

    // Recupera prodotto originale
    $res = $conn->query("SELECT nome, giacenza, unitaMisura FROM Prodotti WHERE idProdotto = $input");
    $prodotto = $res->fetch_assoc();
    if (!$prodotto) die("Prodotto non trovato!");
    if ($prodotto['giacenza'] < $quantitaUsata) die("Giacenza insufficiente!");

    // Recupera nome della lavorazione
    $resTipo = $conn->query("SELECT tipo FROM Tipi WHERE idTipo = $tipoLavorazione");
    $tipoNome = $resTipo->fetch_assoc()['tipo'];

    // Logica nomi e tipo prodotto lavorato
    if (strtolower($tipoNome) == "produzione marmellata") {
        $nomeOutput = "Marmellata di " . $prodotto['nome'];
        $tipoOutput = "confezionato";
    } elseif (strtolower($tipoNome) == "essiccazione") {
        $nomeOutput = $prodotto['nome'] . " essiccate";
        $tipoOutput = "riserva";
    } elseif (strtolower($tipoNome) == "fermentazione") {
        if (strtolower($prodotto['nome']) == "uva") $nomeOutput = "Vino";
        elseif (strtolower($prodotto['nome']) == "mela") $nomeOutput = "Sidro";
        else $nomeOutput = $prodotto['nome'] . " fermentato";
        $tipoOutput = "confezionato";
    } elseif (strtolower($tipoNome) == "confezionamento") {
        $nomeOutput = $prodotto['nome'] . " confezionato";
        $tipoOutput = "confezionato";
    } else {
        $nomeOutput = $prodotto['nome'] . " " . strtolower($tipoNome);
        $tipoOutput = "riserva";
    }

    // Controlla se il prodotto lavorato esiste già
    $resCheck = $conn->query("SELECT idProdotto, giacenza FROM Prodotti WHERE nome = '$nomeOutput' AND tipo = '$tipoOutput'");
    if ($resCheck->num_rows > 0) {
        $rowOutput = $resCheck->fetch_assoc();
        $idOutput = $rowOutput['idProdotto'];

        // Controlla se il prezzo esistente è diverso
        $resPrezzo = $conn->query("SELECT prezzo FROM Prezzi WHERE idProdotto = $idOutput AND dataFineValidita IS NULL");
        if ($resPrezzo->num_rows > 0) {
            $prezzoCorrente = $resPrezzo->fetch_assoc()['prezzo'];
            if ($prezzoCorrente != $prezzoLavorato) {
                die("Il prodotto lavorato esiste già con un prezzo diverso (€$prezzoCorrente). Modificare prima il prezzo nella sezione apposita.");
            }
        }

        // Aggiorna giacenza
        $conn->query("UPDATE Prodotti SET giacenza = giacenza + $quantitaUsata WHERE idProdotto = $idOutput");
    } else {
        // Inserisci nuovo prodotto lavorato
        $conn->query("
            INSERT INTO Prodotti (nome, tipo, unitaMisura, giacenza, categoria)
            VALUES ('$nomeOutput', '$tipoOutput', '{$prodotto['unitaMisura']}', $quantitaUsata, 'prodotto lavorato')
        ");
        $idOutput = $conn->insert_id;

        // Inserisci prezzo iniziale
        $oggi = date('Y-m-d');
        $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita)
                      VALUES ($idOutput, $prezzoLavorato, '$oggi', NULL)");
    }

    // Crea lavorazione
    $conn->query("INSERT INTO Lavorazioni (dataLavorazione, note, idLuogo, idTipo)
                  VALUES (NOW(), 'Lavorazione automatica', $luogo, $tipoLavorazione)");
    $idLavorazione = $conn->insert_id;

    // Registra uso prodotto
    $conn->query("INSERT INTO usa (idProdotto, idLavorazione, quantitaUsata)
                  VALUES ($input, $idLavorazione, $quantitaUsata)");

    // Registra produzione
    $conn->query("INSERT INTO produce (idProdotto, idLavorazione, quantitaProdotta)
                  VALUES ($idOutput, $idLavorazione, $quantitaUsata)");

    // Aggiorna giacenza prodotto originale
    $conn->query("UPDATE Prodotti SET giacenza = giacenza - $quantitaUsata WHERE idProdotto = $input");

    echo "Lavorazione completata: $nomeOutput creata/aggiornata con $quantitaUsata {$prodotto['unitaMisura']} e prezzo €$prezzoLavorato";
}
?>

<br>
<a href="index_gestore.php">⬅ Torna alla home page gestore</a>