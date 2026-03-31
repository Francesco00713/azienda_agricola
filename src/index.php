<?php
    session_start();
    include "db.php";

    if (isset($_POST['login'])) {

        $email = $_POST['emailLogin'];
        $password = $_POST['passwordLogin'];

        $res = $conn->query("SELECT * FROM Utenti WHERE email = '$email'");

        if ($res->num_rows > 0) {
            $utente = $res->fetch_assoc();
            if (password_verify($password, $utente['password'])) {

                $_SESSION['idUtente'] = $utente['idUtente'];
                $_SESSION['ruolo'] = $utente['ruolo'];

                if ($utente['ruolo'] == "cliente") {
                    header("Location: index_cliente.php");
                } else {
                    header("Location: index_gestore.php");
                }
                exit();
            } else {
                $erroreLogin = "Password errata!";
            }

        } else {
            $erroreLogin = "Utente non trovato!";
        }
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Home Page</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
    <h1>Benvenuto nell'Azienda Agricola</h1>
    <p>Accedi per acquistare prodotti o gestire l'azienda.</p>
    <hr>
    <h2>Accesso</h2>
    <form method="POST">
        Email: <input type="text" name="emailLogin" required><br>
        Password: <input type="password" name="passwordLogin" required><br><br>
        <button type="submit" name="login">Accedi</button>
    </form>
    <?php
    if (isset($erroreLogin)) {
        echo "<p style='color:red;'>$erroreLogin</p>";
    }
    ?>
    <br>
    <p>Non sei registrato? 
        <a href="registrazione.php">Clicca qui</a>
    </p>
    </div>
</body>
</html>