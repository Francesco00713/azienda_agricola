<?php include "db.php"; ?>

<h2>Aggiungi Prodotto</h2>

<form method="POST">
    Nome: <input type="text" name="nome" required><br>

    Tipo:
    <select name="tipo">
        <option value="fresco">Fresco</option>
        <option value="riserva">Riserva</option>
        <option value="confezionato">Confezionato</option>
    </select><br>

    Unità:
    <select name="unita">
        <option value="kg">Kg</option>
        <option value="pezzo">Pezzo</option>
        <option value="litro">Litro</option>
    </select><br>

    Giacenza: <input type="number" step="0.01" name="giacenza" required><br>

    Categoria:
    <select name="categoria" required>
        <option value="frutta">Frutta</option>
        <option value="frutta secca">Frutta secca</option>
        <option value="ortaggio">Ortaggio</option>
        <option value="agrume">Agrume</option>
        <option value="legume">Legume</option>
        <option value="spezie">Spezie</option>
    </select><br>

    Prezzo iniziale: <input type="number" step="0.01" name="prezzo" required><br>

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

    // Inserimento prodotto
    $sql = "INSERT INTO Prodotti (nome, tipo, unitaMisura, giacenza, categoria)
            VALUES ('$nome', '$tipo', '$unita', $giacenza, '$categoria')";
    if ($conn->query($sql)) {
        $idProdotto = $conn->insert_id;

        // Inserimento prezzo iniziale
        $conn->query("INSERT INTO Prezzi (idProdotto, prezzo, dataInizioValidita, dataFineValidita)
                      VALUES ($idProdotto, $prezzo, NOW(), NULL)");

        echo "<p>✅ Prodotto e prezzo iniziale aggiunti con successo!</p>";
    } else {
        echo "<p>❌ Errore: " . $conn->error . "</p>";
    }
}
?>

<h2>Lista Prodotti</h2>

<table border="1" cellpadding="5" cellspacing="0">
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
<a href="index_gestore.php">⬅ Torna alla home page gestore</a>