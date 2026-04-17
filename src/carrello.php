<?php
    session_start();
    include "db.php";
    if (!isset($_SESSION['idUtente'])) {
        die("Devi effettuare il login!");
    }
    $cliente = $_SESSION['idUtente'];
    if (isset($_GET['rimuovi'])) {
        $idProdotto = (int)$_GET['rimuovi'];
        $conn->query("DELETE FROM Carrello WHERE idCliente = $cliente AND idProdotto = $idProdotto");
        header("Location: carrello.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Carrello</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Il tuo Carrello</h2>
        <?php
            $res = $conn->query("SELECT c.idProdotto, p.nome, c.quantita, pr.prezzo, p.unitaMisura
                                FROM Carrello c
                                JOIN Prodotti p ON c.idProdotto = p.idProdotto
                                JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL
                                WHERE c.idCliente = $cliente");
            $totale = 0;

            if ($res && $res->num_rows > 0) {
                echo "<table>
                        <tr>
                            <th>Prodotto</th>
                            <th>Quantità</th>
                            <th>Prezzo</th>
                            <th>Totale</th>
                            <th>Azione</th>
                        </tr>";
                while ($row = $res->fetch_assoc()) {
                    $sub = $row['quantita'] * $row['prezzo'];
                    $totale += $sub;

                    echo "<tr>
                            <td>{$row['nome']}</td>
                            <td>{$row['quantita']} {$row['unitaMisura']}</td>
                            <td>€" . number_format($row['prezzo'], 2) . "</td>
                            <td>€" . number_format($sub, 2) . "</td>
                            <td><a href='?rimuovi={$row['idProdotto']}'>Rimuovi</a></td>
                        </tr>";
                }

                echo "<tr>
                        <td colspan='3'><strong>Totale Complessivo</strong></td>
                        <td colspan='2'><strong>€" . number_format($totale, 2) . "</strong></td>
                    </tr>
                    </table>";

                echo '<form method="POST">
                        <button type="submit" name="acquista">Conferma Acquisto</button>
                    </form>';

            } else {
                echo "<p>Il tuo carrello è vuoto.</p>";
            }

            if (isset($_POST['acquista']) && $totale > 0) {

                $conn->query("INSERT INTO Acquisti (idCliente, dataAcquisto, totale, note) VALUES ($cliente, NOW(), $totale, '')");
                $idAcquisto = $conn->insert_id;

                $resItems = $conn->query("SELECT c.quantita, p.nome, pr.prezzo, p.idProdotto 
                                        FROM Carrello c 
                                        INNER JOIN Prodotti p ON c.idProdotto = p.idProdotto 
                                        INNER JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL 
                                        WHERE c.idCliente = $cliente");

                echo "<div><h3>Scontrino</h3><hr>";

                while ($r = $resItems->fetch_assoc()) {

                    $conn->query("INSERT INTO Dettaglio_acquisto (idAcquisto, idProdotto, quantita) 
                                VALUES ($idAcquisto, {$r['idProdotto']}, {$r['quantita']})");

                    $conn->query("UPDATE Prodotti 
                                SET giacenza = giacenza - {$r['quantita']} 
                                WHERE idProdotto = {$r['idProdotto']}");

                    echo "<p>{$r['nome']} - {$r['quantita']} x €" . number_format($r['prezzo'], 2) . 
                        " = €" . number_format($r['quantita'] * $r['prezzo'], 2) . "</p>";
                }

                $conn->query("DELETE FROM Carrello WHERE idCliente = $cliente");

                echo "<hr>
                    <h3>TOTALE: €" . number_format($totale, 2) . "</h3>
                    <p class='success'>Acquisto completato!</p>
                    </div>";
            }
        ?>
        <br>
        <a href="acquisti.php">⬅ Continua acquisti</a><br>
        <a href="index_cliente.php">⬅ Torna all'area clienti</a>
    </div>
</body>
</html>