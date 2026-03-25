CREATE DATABASE IF NOT EXISTS azienda_agricola;
USE azienda_agricola;

CREATE TABLE Clienti (
    idCliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    telefono VARCHAR(15),
    email VARCHAR(150)
);

CREATE TABLE Prodotti (
    idProdotto INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    tipo ENUM('fresco', 'riserva', 'confezionato'),
    unitaMisura ENUM('kg', 'pezzo', 'litro'),
    giacenza DECIMAL(10,2),
    categoria VARCHAR(100)
);

CREATE TABLE Prezzi (
    idPrezzo INT AUTO_INCREMENT PRIMARY KEY,
    idProdotto INT,
    prezzo DECIMAL(10,2),
    dataInizioValidita DATE,
    dataFineValidita DATE,
    FOREIGN KEY (idProdotto) REFERENCES Prodotti(idProdotto)
);

CREATE TABLE Luoghi (
    idLuogo INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    indirizzo VARCHAR(100)
);

CREATE TABLE Tipi (
    idTipo INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(100)
);

CREATE TABLE Lavorazioni (
    idLavorazione INT AUTO_INCREMENT PRIMARY KEY,
    dataLavorazione DATE,
    note VARCHAR(100),
    idLuogo INT,
    idTipo INT,
    FOREIGN KEY (idLuogo) REFERENCES Luoghi(idLuogo),
    FOREIGN KEY (idTipo) REFERENCES Tipi(idTipo)
);

CREATE TABLE Acquisti (
    idAcquisto INT AUTO_INCREMENT PRIMARY KEY,
    idCliente INT,
    dataAcquisto DATE,
    totScontato DECIMAL(10,2),
    note VARCHAR(100),
    FOREIGN KEY (idCliente) REFERENCES Clienti(idCliente)
);

CREATE TABLE Dettaglio_acquisto (
    idDettaglio INT AUTO_INCREMENT PRIMARY KEY,
    quantita DECIMAL(10,2),
    idAcquisto INT,
    idProdotto INT,
    FOREIGN KEY (idAcquisto) REFERENCES Acquisti(idAcquisto),
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