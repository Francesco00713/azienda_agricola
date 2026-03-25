<?php include "db.php"; ?>

<h2>Aggiungi Cliente</h2>

<form method="POST">
    Nome: <input type="text" name="nome"><br>
    Nickname: <input type="number" name="telefono"><br>
    Contatto: <input type="text" name="email"><br>
    <button type="submit" name="add">Inserisci</button>
</form>

<?php
if (isset($_POST['add'])) {
    $conn->query("INSERT INTO Clienti (nome, telefono, email)
                  VALUES ('{$_POST['nome']}', '{$_POST['telefono']}', '{$_POST['email']}')");
}
?>

<h2>Clienti</h2>

<?php
$res = $conn->query("SELECT * FROM Clienti");

while ($row = $res->fetch_assoc()) {
    echo "{$row['nome']}<br>";
}
?>