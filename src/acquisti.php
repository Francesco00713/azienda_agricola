<?php include "db.php"; ?>

<h2>Nuova Vendita</h2>

<form method="POST">
    ID Cliente: <input type="number" name="cliente" required><br>
    ID Prodotto: <input type="number" name="prodotto" required><br>
    Quantità: <input type="number" step="0.01" name="quantita" required><br>

    <button type="submit" name="vendi">Vendi</button>
</form>

<?php
if (isset($_POST['vendi'])) {

    $idProdotto = $_POST['prodotto'];
    $quantita = $_POST['quantita'];
    $idCliente = $_POST['cliente'];

    // prezzo attuale corretto
    $res = $conn->query("
        SELECT prezzo 
        FROM Prezzi 
        WHERE idProdotto = $idProdotto 
        ORDER BY dataInizioValidita DESC 
        LIMIT 1
    ");

    $row = $res->fetch_assoc();

    if (!$row) {
        die("Prezzo non trovato!");
    }

    $prezzo = $row['prezzo'];
    $totale = $prezzo * $quantita;

    // inserisci acquisto (CORRETTO DB)
    $conn->query("
        INSERT INTO Acquisti (idCliente, dataAcquisto, totScontato, note)
        VALUES ($idCliente, NOW(), $totale, '')
    ");

    $idAcquisto = $conn->insert_id;

    // dettaglio acquisto (CORRETTO DB)
    $conn->query("
        INSERT INTO Dettaglio_acquisto (quantita, idAcquisto, idProdotto)
        VALUES ($quantita, $idAcquisto, $idProdotto)
    ");

    // aggiorna giacenza
    $conn->query("
        UPDATE Prodotti
        SET giacenza = giacenza - $quantita
        WHERE idProdotto = $idProdotto
    ");

    echo "Vendita effettuata!";
}
?>

<a href="index_cliente.php">⬅ Torna alla home page cliente</a>