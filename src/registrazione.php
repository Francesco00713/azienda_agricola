<?php
    include "db.php";
    if (isset($_POST['registrati'])) {
        $nome = $_POST['nome'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $ruolo = $_POST['ruolo'];
        $codice = $_POST['codice'] ?? "";

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->query("SELECT * FROM Utenti WHERE email = '$email' OR telefono = '$telefono'");

        if ($check->num_rows > 0) {
            $errore = "Email o telefono già registrati!";
        } else {
            if ($ruolo == "gestore" && $codice != "NICOLINOFRANCONE") {
                $errore = "Codice gestore errato!";
            } else {
                $conn->query("
                    INSERT INTO Utenti (nome, telefono, email, ruolo, password)
                    VALUES ('$nome', '$telefono', '$email', '$ruolo', '$passwordHash')
                ");
                $successo = "Registrazione completata! Torna al login.";
            }
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrazione</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
    <body>
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
    if (isset($errore)) echo "<p style='color:red;'>$errore</p>";
    if (isset($successo)) echo "<p style='color:green;'>$successo</p>";
    ?>

    <br>
    <a href="index.php">⬅ Torna al login</a>

    <script>
    function toggleCodice() {
        var ruolo = document.getElementById("ruolo").value;
        document.getElementById("codiceGestore").style.display =
            (ruolo === "gestore") ? "block" : "none";
    }
    </script>
</body>
</html>