<?php include "db.php"; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Storico Guadagni</title>
</head>
<body>
    <div class="container">
        <h2>Storico guadagni</h2>
        
        <?php
        $cliente_id = isset($_GET['cliente']) && $_GET['cliente'] !== "" ? (int)$_GET['cliente'] : null;
        $prodotto_id = isset($_GET['prodotto']) && $_GET['prodotto'] !== "" ? (int)$_GET['prodotto'] : null;
        $data_inizio = isset($_GET['data_inizio']) && $_GET['data_inizio'] !== "" ? $_GET['data_inizio'] : '1970-01-01';
        $data_fine = isset($_GET['data_fine']) && $_GET['data_fine'] !== "" ? $_GET['data_fine'] : '2099-12-31';

        $resTot = $conn->query("SELECT SUM(quantita * prezzoUnitario) as totale_assoluto FROM Dettaglio_acquisto");
        $totale_azienda = $resTot->fetch_assoc()['totale_assoluto'] ?? 0;
        ?>

        <div class="avviso">
            <span>FATTURATO TOTALE AZIENDA:</span>
            <h1>€ <?php echo number_format($totale_azienda, 2, ',', '.'); ?></h1>
        </div>

        <fieldset style="border: 1px solid #ddd; padding: 15px; margin-bottom: 20px;">
            <legend>Filtri di ricerca</legend>
            <form method="GET">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div>
                        <label>Cliente:</label><br>
                        <select name="cliente">
                            <option value="">Tutti i clienti</option>
                            <?php
                            $resC = $conn->query("SELECT idUtente, nome FROM Utenti WHERE ruolo = 'cliente' ORDER BY nome ASC");
                            while ($c = $resC->fetch_assoc()) {
                                $sel = ($cliente_id == $c['idUtente']) ? "selected" : "";
                                echo "<option value='{$c['idUtente']}' $sel>{$c['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Prodotto:</label><br>
                        <select name="prodotto">
                            <option value="">Tutti i prodotti</option>
                            <?php
                            $resP = $conn->query("SELECT idProdotto, nome FROM Prodotti ORDER BY nome ASC");
                            while ($p = $resP->fetch_assoc()) {
                                $sel = ($prodotto_id == $p['idProdotto']) ? "selected" : "";
                                echo "<option value='{$p['idProdotto']}' $sel>{$p['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Dal:</label><br>
                        <input type="date" name="data_inizio" value="<?php echo $_GET['data_inizio'] ?? ''; ?>">
                    </div>
                    <div>
                        <label>Al:</label><br>
                        <input type="date" name="data_fine" value="<?php echo $_GET['data_fine'] ?? ''; ?>">
                    </div>
                    <div style="align-self: flex-end;">
                        <button type="submit">Filtra</button>
                        <a href="storico_guadagni.php" style="margin-left:10px; color: crimson; text-decoration:none;">Reset</a>
                    </div>
                </div>
            </form>
        </fieldset>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Prodotto</th>
                    <th>Prezzo</th>
                    <th>Quantità</th>
                    <th>Unità di misura</th>
                    <th>Totale</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT a.dataAcquisto, p.nome, p.unitaMisura, da.prezzoUnitario, da.quantita 
                          FROM Dettaglio_acquisto da
                          JOIN Acquisti a ON da.idAcquisto = a.idAcquisto
                          JOIN Prodotti p ON da.idProdotto = p.idProdotto
                          WHERE a.dataAcquisto BETWEEN '$data_inizio' AND '$data_fine'";

                if ($cliente_id) $query .= " AND a.idCliente = $cliente_id";
                if ($prodotto_id) $query .= " AND da.idProdotto = $prodotto_id";
                
                $query .= " ORDER BY a.dataAcquisto DESC";

                $res = $conn->query($query);
                $totale_filtro = 0;

                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $subtotale = $row['prezzoUnitario'] * $row['quantita'];
                        $totale_filtro += $subtotale;
                        echo "<tr>
                                <td>" . date("d/m/Y", strtotime($row['dataAcquisto'])) . "</td>
                                <td>{$row['nome']}</td>
                                <td>€ " . number_format($row['prezzoUnitario'], 2, ',', '.') . "</td>
                                <td>{$row['quantita']}</td>
                                <td>{$row['unitaMisura']}</td>
                                <td style='font-weight: bold;'>€ " . number_format($subtotale, 2, ',', '.') . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>Nessun dato trovato.</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr style="background: #eee; font-weight: bold;">
                    <td colspan="5" style="text-align: right;">TOTALE RISULTATI:</td>
                    <td style="color: #2e7d32;">€ <?php echo number_format($totale_filtro, 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
        <br>
        <a href="index_gestore.php" class="btn">⬅ Torna all'area gestore</a>
    </div>
</body>
</html>