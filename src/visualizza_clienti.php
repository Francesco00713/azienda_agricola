<?php include "db.php"; ?>

<h2>Elenco Clienti</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Telefono</th>
        <th>Email</th>
    </tr>

<?php
$res = $conn->query("SELECT * FROM Clienti ORDER BY idCliente ASC");

if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
                <td>{$row['idCliente']}</td>
                <td>{$row['nome']}</td>
                <td>{$row['telefono']}</td>
                <td>{$row['email']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4'>Nessun cliente presente.</td></tr>";
}
?>
</table>

<br>
<a href="index_gestore.php">⬅ Torna alla home page gestore</a>