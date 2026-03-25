<?php include "db.php"; ?>

<h2>Aggiungi Cliente</h2>

<form method="POST">
    Nome: <input type="text" name="nome"><br>
    Nickname: <input type="text" name="nick"><br>
    Contatto: <input type="text" name="contatto"><br>
    <button type="submit" name="add">Inserisci</button>
</form>

<?php
if (isset($_POST['add'])) {
    $conn->query("INSERT INTO cliente (nome, nickname, contatto)
                  VALUES ('{$_POST['nome']}', '{$_POST['nick']}', '{$_POST['contatto']}')");
}
?>

<h2>Clienti</h2>

<?php
$res = $conn->query("SELECT * FROM cliente");

while ($row = $res->fetch_assoc()) {
    echo "{$row['nome']} ({$row['nickname']})<br>";
}
?>