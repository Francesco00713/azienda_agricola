CREATE DATABASE IF NOT EXISTS azienda_agricola;
USE azienda_agricola;

CREATE TABLE Utenti (
    idUtente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    telefono VARCHAR(15),
    email VARCHAR(150),
    ruolo ENUM('cliente', 'gestore'),
    password VARCHAR(255)
);

INSERT INTO Utenti (idUtente, nome, telefono, email, ruolo, password) VALUES 
(1, 'Francesco', 3770834662, 'francesco@gmail.com', 'gestore', '$2y$10$wmbv.yPEJDtwSi6KhE88jO2/X1/NR94ta8FTAu4mumKsHgEmrFPJy'),
(2, 'Nicola', 3338327134, 'nicola@gmail.com', 'cliente', '$2y$10$51HXNBnlvMVBuAx3E3TusuCvMUZuZVbDCNcyr6Fiwjno3OCHFU2IW'),
(3, 'Patty', 123456789, 'patty@gmail.com', 'cliente', '$2y$10$5nWEHhuIJR1qmJLCJwtBQ.ILQUwpo19GcyXiCmA.RXVXGCxePwZry');

CREATE TABLE Prodotti (
    idProdotto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    giacenza DECIMAL(10,2),
    unitaMisura ENUM('kg', 'l', 'pezzo'),
    categoria VARCHAR(100),
    tipo ENUM('fresco', 'riserva', 'confezionato')
);

INSERT INTO Prodotti (idProdotto, nome, giacenza, unitaMisura, tipo, categoria) VALUES 
(1, 'Mele', 50, 'kg', 'fresco', 'frutta'),
(2, 'Pere', 30, 'kg', 'fresco', 'frutta'),
(3, 'Limoni', 45, 'kg', 'fresco', 'agrume'),
(4, 'Nocciole', 20, 'kg', 'fresco', 'frutta secca'),
(5, 'Patate', 70, 'kg', 'fresco', 'ortaggio'),
(6, 'Lenticchie', 35, 'kg', 'fresco', 'legume'),
(7, 'Rosmarino', 15, 'pezzo', 'fresco', 'spezia');

CREATE TABLE Prezzi (
    idPrezzo INT AUTO_INCREMENT PRIMARY KEY,
    idProdotto INT,
    prezzo DECIMAL(10,2),
    dataInizioValidita DATE,
    dataFineValidita DATE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto) ON DELETE CASCADE
);

INSERT INTO Prezzi (idPrezzo, idProdotto, prezzo, dataInizioValidita, dataFineValidita) VALUES 
(1, 1, 2, '2026/04/01', NULL),
(2, 2, 3, '2026/04/01', NULL),
(3, 3, 2, '2026/04/01', NULL),
(4, 4, 4, '2026/04/01', NULL),
(5, 5, 1.50, '2026/04/01', NULL),
(6, 6, 1, '2026/04/01', NULL),
(7, 7, 0.30, '2026/04/01', NULL);

CREATE TABLE Luoghi (
    idLuogo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    indirizzo VARCHAR(100)
);

INSERT INTO Luoghi (idLuogo, nome, indirizzo) VALUES 
(1, 'Azienda Agricola', 'Palo del Colle - Via Giotto 18'),
(2, 'Campi Aziendali', 'Palo del Colle');

CREATE TABLE Tipi (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100)
);

INSERT INTO Tipi (idTipo, tipo) VALUES
(1, 'Raccolta'),
(2, 'Essiccazione'),
(3, 'Macinazione'),
(4, 'Salatura'),
(5, 'Affumicatura'),
(6, 'Smallatura'),
(7, 'Spremitura'),
(8, 'Fermentazione'),
(9, 'Confezionamento'),
(10, 'Produzione marmellata');

CREATE TABLE Lavorazioni (
    idLavorazione INT AUTO_INCREMENT PRIMARY KEY,
    dataLavorazione DATE,
    idLuogo INT,
    idTipo INT,
    FOREIGN KEY (idLuogo) REFERENCES Luoghi(idLuogo),
    FOREIGN KEY (idTipo) REFERENCES Tipi(idTipo)
);

INSERT INTO Lavorazioni (idLavorazione, dataLavorazione, idLuogo, idTipo) VALUES
(1, '2026/04/01', 2, 1),
(2, '2026/04/01', 2, 1),
(3, '2026/04/01', 2, 1),
(4, '2026/04/01', 2, 1),
(5, '2026/04/01', 2, 1),
(6, '2026/04/01', 2, 1),
(7, '2026/04/01', 2, 1);

CREATE TABLE Carrello (
    idCarrello INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    idProdotto INT,
    quantita DECIMAL(10,2),
    FOREIGN KEY (idCliente) REFERENCES Utenti(idUtente) ON DELETE CASCADE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto) ON DELETE CASCADE
);

CREATE TABLE Acquisti (
    idAcquisto INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    dataAcquisto DATE,
    totale DECIMAL(10,2),
    note VARCHAR(100),
    FOREIGN KEY (idCliente) REFERENCES Utenti(idUtente) ON DELETE CASCADE
);

CREATE TABLE Dettaglio_acquisto (
    idDettaglio INT AUTO_INCREMENT PRIMARY KEY,
    quantita DECIMAL(10,2),
    idAcquisto INT,
    idProdotto INT,
    prezzoUnitario DECIMAL(10,2),
    FOREIGN KEY (idAcquisto) REFERENCES Acquisti(idAcquisto) ON DELETE CASCADE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
);

CREATE TABLE produce (
    idProdotto INT,
    idLavorazione INT,
    quantitaProdotta DECIMAL(10,2),
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto),
    FOREIGN KEY (idLavorazione) REFERENCES Lavorazioni(idLavorazione)
);

INSERT INTO produce (idProdotto, idLavorazione, quantitaProdotta) VALUES
(1, 1, 50),
(2, 2, 30),
(3, 3, 45),
(4, 4, 20),
(5, 5, 70),
(6, 6, 35),
(7, 7, 15);

CREATE TABLE usa (
    idProdotto INT,
    idLavorazione INT,
    quantitaUsata DECIMAL(10,2),
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto) ON DELETE CASCADE,
    FOREIGN KEY (idLavorazione) REFERENCES Lavorazioni(idLavorazione) ON DELETE CASCADE
);

CREATE TABLE detiene (
    idCarrello INT,
    idProdotto INT,
    FOREIGN KEY (idCarrello) REFERENCES Carrello(idCarrello) ON DELETE CASCADE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto) ON DELETE CASCADE
);

CREATE TABLE aggiorna (
    idGestore INT,
    idPrezzo INT,
    FOREIGN KEY (idGestore) REFERENCES Utenti(idUtente) ON DELETE CASCADE,
    FOREIGN KEY (idPrezzo) REFERENCES Prezzi(idPrezzo) ON DELETE CASCADE
);

CREATE TABLE aggiuge (
    idGestore INT,
    idProdotto INT,
    FOREIGN KEY (idGestore) REFERENCES Utenti(idUtente) ON DELETE CASCADE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto) ON DELETE CASCADE
);

CREATE TABLE compie (
    idGestore INT,
    idLavorazione INT,
    FOREIGN KEY (idGestore) REFERENCES Utenti(idUtente) ON DELETE CASCADE,
    FOREIGN KEY (idLavorazione) REFERENCES Lavorazioni(idLavorazione) ON DELETE CASCADE
);

INSERT INTO compie (idGestore, idLavorazione) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7);

DELIMITER $$
CREATE TRIGGER check_giacenza
BEFORE UPDATE ON Prodotti
FOR EACH ROW
BEGIN
    IF NEW.giacenza < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Giacenza negativa non consentita';
    END IF;
END$$
DELIMITER ;