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
(2, 'Nicola', 3338327134, 'nicola@gmail.com', 'cliente', '$2y$10$51HXNBnlvMVBuAx3E3TusuCvMUZuZVbDCNcyr6Fiwjno3OCHFU2IW');

CREATE TABLE Prodotti (
    idProdotto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    giacenza DECIMAL(10,2),
    unitaMisura ENUM('kg', 'pezzo', 'litro'),
    tipo ENUM('fresco', 'riserva', 'confezionato'),
    categoria VARCHAR(100)
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
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
        ON DELETE CASCADE
);

INSERT INTO Prezzi (idPrezzo, idProdotto, prezzo, dataInizioValidita, dataFineValidita) VALUES 
(1, 1, 2, '2026-04-01', NULL),
(2, 2, 3, '2026-04-01', NULL),
(3, 3, 2, '2026-04-01', NULL),
(4, 4, 4, '2026-04-01', NULL),
(5, 5, 1.50, '2026-04-01', NULL),
(6, 6, 1, '2026-04-01', NULL),
(7, 7, 0.30, '2026-04-01', NULL);

CREATE TABLE Luoghi (
    idLuogo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    indirizzo VARCHAR(100)
);

INSERT INTO Luoghi (nome, indirizzo) VALUES ('Azienda Agricola', 'Palo del Colle - Via Giotto 18');

CREATE TABLE Tipi (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100)
);

INSERT INTO Tipi (tipo) VALUES
('Essiccazione'),
('Macinazione'),
('Salatura'),
('Affumicatura'),
('Smallatura'),
('Spremitura'),
('Fermentazione'),
('Confezionamento'),
('Produzione marmellata');

CREATE TABLE Lavorazioni (
    idLavorazione INT AUTO_INCREMENT PRIMARY KEY,
    dataLavorazione DATE,
    note VARCHAR(100),
    idLuogo INT,
    idTipo INT,
    FOREIGN KEY (idLuogo) REFERENCES Luoghi(idLuogo),
    FOREIGN KEY (idTipo) REFERENCES Tipi(idTipo)
);

CREATE TABLE Carrello (
    idCarrello INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    idProdotto INT,
    quantita DECIMAL(10,2),
    FOREIGN KEY (idCliente) REFERENCES Utenti(idUtente)
        ON DELETE CASCADE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
        ON DELETE CASCADE
);

CREATE TABLE Acquisti (
    idAcquisto INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    dataAcquisto DATE,
    totale DECIMAL(10,2),
    note VARCHAR(100),
    FOREIGN KEY (idCliente) REFERENCES Utenti(idUtente)
        ON DELETE CASCADE
);

CREATE TABLE Dettaglio_acquisto (
    idDettaglio INT AUTO_INCREMENT PRIMARY KEY,
    quantita DECIMAL(10,2),
    idAcquisto INT,
    idProdotto INT,
    FOREIGN KEY (idAcquisto) REFERENCES Acquisti(idAcquisto)
        ON DELETE CASCADE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
);

CREATE TABLE produce (
    idProdotto INT,
    idLavorazione INT,
    quantitaProdotta DECIMAL(10,2),
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto),
    FOREIGN KEY (idLavorazione) REFERENCES Lavorazioni(idLavorazione)
);

CREATE TABLE usa (
    idProdotto INT,
    idLavorazione INT,
    quantitaUsata DECIMAL(10,2),
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto),
    FOREIGN KEY (idLavorazione) REFERENCES Lavorazioni(idLavorazione)
);

CREATE TABLE detiene (
    idCarrello INT,
    idProdotto INT,
    FOREIGN KEY (idCarrello) REFERENCES Carrello(idCarrello),
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
);

CREATE TABLE aggiorna (
    idGestore INT,
    idPrezzo INT,
    FOREIGN KEY (idGestore) REFERENCES Utenti(idUtenti),
    FOREIGN KEY (idPrezzo) REFERENCES Prezzi(idPrezzo)
);

CREATE TABLE aggiuge (
    idGestore INT,
    idProdotto INT,
    FOREIGN KEY (idGestore) REFERENCES Utenti(idUtenti),
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
);

CREATE TABLE compie (
    idGestore INT,
    idLavorazione INT,
    FOREIGN KEY (idGestore) REFERENCES Utenti(idUtenti),
    FOREIGN KEY (idLavorazione) REFERENCES Lavorazioni(idLavorazioni)
);


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