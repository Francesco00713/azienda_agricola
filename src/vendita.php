<?php include "db.php"; ?>

<h2>Nuova Vendita</h2>

<form method="POST">
    ID Cliente: <input type="number" name="cliente"><br>
    ID Prodotto: <input type="number" name="prodotto"><br>
    Quantità: <input type="number" step="0.01" name="quantita"><br>

    <button type="submit" name="vendi">Vendi</button>
</form>

<?php
if (isset($_POST['vendi'])) {

    $idProdotto = $_POST['prodotto'];
    $quantita = $_POST['quantita'];
    $idCliente = $_POST['cliente'];

    // prezzo attuale
    $res = $conn->query("SELECT prezzo FROM prezzo WHERE idProdotto = $idProdotto ORDER BY dataInizio DESC LIMIT 1");
    $prezzo = $res->fetch_assoc()['prezzo'];

    $totale = $prezzo * $quantita;

    // inserisci acquisto
    $conn->query("INSERT INTO acquisto (idCliente, dataAcquisto, totaleCalcolato, totalePagato)
                  VALUES ($idCliente, NOW(), $totale, $totale)");

    $idAcquisto = $conn->insert_id;

    // dettaglio
    $conn->query("INSERT INTO dettaglio_acquisto (idAcquisto, idProdotto, quantita, prezzoUnitario)
                  VALUES ($idAcquisto, $idProdotto, $quantita, $prezzo)");

    // aggiorna giacenza
    $conn->query("UPDATE prodotto
                  SET giacenza = giacenza - $quantita
                  WHERE idProdotto = $idProdotto");

    echo "Vendita effettuata!";
}
?>