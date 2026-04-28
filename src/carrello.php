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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                            <th>Prezzo Unitario</th>
                            <th>Subtotale</th>
                            <th>Azione</th>
                        </tr>";
                
                $items = []; 
                while ($row = $res->fetch_assoc()) {
                    $items[] = $row;
                    $sub = $row['quantita'] * $row['prezzo'];
                    $totale += $sub;

                    echo "<tr>
                            <td>{$row['nome']}</td>
                            <td>{$row['quantita']} {$row['unitaMisura']}</td>
                            <td>€" . number_format($row['prezzo'], 2, ',', '.') . "</td>
                            <td>€" . number_format($sub, 2, ',', '.') . "</td>
                            <td><a href='?rimuovi={$row['idProdotto']}' style='color:red;'>Rimuovi</a></td>
                        </tr>";
                }

                echo "<tr>
                        <td colspan='3' style='text-align:right;'><strong>Totale Complessivo</strong></td>
                        <td colspan='2'><strong>€" . number_format($totale, 2, ',', '.') . "</strong></td>
                    </tr>
                    </table>";

                echo '<form method="POST" style="margin-top:20px;">
                        <button type="submit" name="acquista">Conferma e Paga</button>
                    </form>';

            } else {
                echo "<p>Il tuo carrello è vuoto.</p>";
            }

            if (isset($_POST['acquista']) && $totale > 0) {
                $stmtAcquisto = $conn->prepare("INSERT INTO Acquisti (idCliente, dataAcquisto, totale, note) VALUES (?, NOW(), ?, '')");
                $stmtAcquisto->bind_param("id", $cliente, $totale);
                $stmtAcquisto->execute();
                $idAcquisto = $conn->insert_id;

                $resItems = $conn->query("SELECT c.quantita, p.nome, pr.prezzo, p.idProdotto 
                                        FROM Carrello c 
                                        INNER JOIN Prodotti p ON c.idProdotto = p.idProdotto 
                                        INNER JOIN Prezzi pr ON p.idProdotto = pr.idProdotto AND pr.dataFineValidita IS NULL 
                                        WHERE c.idCliente = $cliente");

                echo "<div class='scontrino' style='background:#f9f9f9; padding:20px; border:1px solid #ddd; margin-top:20px;'>";
                echo "<h3>Ricevuta Acquisto #$idAcquisto</h3><hr>";

                while ($r = $resItems->fetch_assoc()) {
                    $prezzoCorrente = $r['prezzo'];
                    $qta = $r['quantita'];
                    $idProd = $r['idProdotto'];

                    $conn->query("INSERT INTO Dettaglio_acquisto (idAcquisto, idProdotto, prezzoUnitario, quantita) 
                                 VALUES ($idAcquisto, $idProd, $prezzoCorrente, $qta)");

                    $conn->query("UPDATE Prodotti SET giacenza = giacenza - $qta WHERE idProdotto = $idProd");

                    echo "<p>{$r['nome']} - $qta x €" . number_format($prezzoCorrente, 2, ',', '.') . 
                         " = <strong>€" . number_format($qta * $prezzoCorrente, 2, ',', '.') . "</strong></p>";
                }

                $conn->query("DELETE FROM Carrello WHERE idCliente = $cliente");

                echo "<hr>
                    <h4>TOTALE PAGATO: €" . number_format($totale, 2, ',', '.') . "</h4>
                    <p class='success' style='color:green; font-weight:bold;'>Acquisto completato con successo!</p>
                    <script>setTimeout(function(){ window.location.href = 'acquisti.php'; }, 5000);</script>
                    </div>";
            }
        ?>
        <br>
        <a href="acquisti.php">⬅ Continua acquisti</a><br>
        <a href="index_cliente.php">⬅ Torna all'area clienti</a>
    </div>
</body>
</html>