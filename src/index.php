<?php
session_start();
include "db.php";

// ================= LOGIN =================
if (isset($_POST['login'])) {

    $email = $_POST['emailLogin'];
    $password = $_POST['passwordLogin'];

    $res = $conn->query("SELECT * FROM Utenti WHERE email = '$email' AND password = '$password'");

    if ($res->num_rows > 0) {
        $utente = $res->fetch_assoc();

        $_SESSION['idUtente'] = $utente['idUtente'];
        $_SESSION['ruolo'] = $utente['ruolo'];

        if ($utente['ruolo'] == "cliente") {
            header("Location: index_cliente.php");
        } else {
            header("Location: index_gestore.php");
        }
        exit();
    } else {
        $erroreLogin = "Credenziali errate!";
    }
}

// ================= REGISTRAZIONE =================
if (isset($_POST['registrati'])) {

    $nome = $_POST['nome'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $ruolo = $_POST['ruolo'];
    $codice = $_POST['codice'] ?? "";

    $check = $conn->query("SELECT * FROM Utenti WHERE email = '$email' OR telefono = '$telefono'");

    if ($check->num_rows > 0) {
        $erroreRegistrazione = "Email o telefono già registrati!";
    } else {

        if ($ruolo == "gestore" && $codice != "123") {
            $erroreRegistrazione = "Codice gestore errato!";
        } else {
            $conn->query("
                INSERT INTO Utenti (nome, telefono, email, ruolo, password)
                VALUES ('$nome', '$telefono', '$email', '$ruolo', '$password')
            ");

            $successoRegistrazione = "Registrazione completata! Ora effettua il login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">

<h1>Azienda Agricola</h1>

<hr>

<h2>Registrazione</h2>

<form method="POST">
    Nome: <input type="text" name="nome" required><br>
    Telefono: <input type="text" name="telefono" required><br>
    Email: <input type="text" name="email" required><br>
    Password: <input type="password" name="password" required><br>

    Ruolo:
    <select name="ruolo" id="ruolo" onchange="toggleCodice()">
        <option value="cliente">Cliente</option>
        <option value="gestore">Gestore</option>
    </select><br>

    <div id="codiceGestore" style="display:none;">
        Codice gestore: <input type="text" name="codice"><br>
    </div>

    <button type="submit" name="registrati">Registrati</button>
</form>

<?php
if (isset($erroreRegistrazione)) echo "<p style='color:red;'>$erroreRegistrazione</p>";
if (isset($successoRegistrazione)) echo "<p style='color:green;'>$successoRegistrazione</p>";
?>

<hr>

<h2>Accesso</h2>

<form method="POST">
    Email: <input type="text" name="emailLogin" required><br>
    Password: <input type="password" name="passwordLogin" required><br>
    <button type="submit" name="login">Accedi</button>
</form>

<?php
if (isset($erroreLogin)) echo "<p style='color:red;'>$erroreLogin</p>";
?>

</div>

<script>
function toggleCodice() {
    var ruolo = document.getElementById("ruolo").value;
    document.getElementById("codiceGestore").style.display =
        (ruolo === "gestore") ? "block" : "none";
}
</script>

</body>
</html>