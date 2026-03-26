<?php include "db.php"; ?>

<h2>Aggiungi Cliente</h2>

<form method="POST">
    Nome: <input type="text" name="nome" required><br>
    Telefono: <input type="text" name="telefono"><br>
    Email: <input type="text" name="email"><br><br>
    <button type="submit" name="add">Inserisci</button>
</form>

<?php
if (isset($_POST['add'])) {
    $nome = $_POST['nome'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];

    // Controllo duplicati: telefono o email già presenti
    $resCheck = $conn->query("SELECT * FROM Clienti WHERE telefono = '$telefono' OR email = '$email'");
    if ($resCheck->num_rows > 0) {
        echo "<p style='color:red;'>Errore: Esiste già un cliente con questo numero di telefono o email. Verifica prima di aggiungere un nuovo cliente.</p>";
    } else {
        // Inserimento nuovo cliente
        $sql = "INSERT INTO Clienti (nome, telefono, email)
                VALUES ('$nome', '$telefono', '$email')";
        if ($conn->query($sql)) {
            echo "<p>Cliente aggiunto con successo!</p>";
        } else {
            echo "<p>Errore: " . $conn->error . "</p>";
        }
    }
}
?>

<br>
<a href="index_cliente.php">⬅ Torna alla home page cliente</a>