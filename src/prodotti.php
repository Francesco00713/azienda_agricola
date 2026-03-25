<?php include "db.php"; ?>

<h2>Aggiungi Prodotto</h2>

<form method="POST">
    Nome: <input type="text" name="nome"><br>
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
    Giacenza: <input type="number" step="0.01" name="giacenza"><br>
    ID Categoria: <input type="number" name="categoria"><br>

    <button type="submit" name="add">Inserisci</button>
</form>

<?php
if (isset($_POST['add'])) {
    $sql = "INSERT INTO prodotto (nome, tipo, unitaMisura, giacenza, idCategoria)
            VALUES ('{$_POST['nome']}', '{$_POST['tipo']}', '{$_POST['unita']}', {$_POST['giacenza']}, {$_POST['categoria']})";

    $conn->query($sql);
}
?>

<h2>Lista Prodotti</h2>

<?php
$res = $conn->query("SELECT * FROM prodotto");

while ($row = $res->fetch_assoc()) {
    echo "{$row['nome']} - Giacenza: {$row['giacenza']}<br>";
}
?>