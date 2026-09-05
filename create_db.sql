-- create_db.sql
-- SQL schema for Oficina Inteligente (MySQL)

CREATE DATABASE IF NOT EXISTS oficina_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE oficina_db;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(150),
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(150)
);

-- Tabela de peças
CREATE TABLE IF NOT EXISTS pecas (
    id_peca INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    modelo_da_peça text,
    marca text,
    descricao TEXT,
    preco DECIMAL(10,2),
    quantidade_estoque INT DEFAULT 0,
    id_fornecedor INT,
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Tabela de manutenções
CREATE TABLE IF NOT EXISTS manutencoes (
    id_manutencao INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    data_manutencao DATE,
    descricao TEXT,
    status ENUM('Agendada','Concluída','Cancelada') DEFAULT 'Agendada',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabela de solicitações de peças
CREATE TABLE IF NOT EXISTS solicitacoes_pecas (
    id_solicitacao INT AUTO_INCREMENT PRIMARY KEY,
    id_peca INT,
    id_fornecedor INT,
    id_cliente INT,
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pendente','Atendida','Cancelada') DEFAULT 'Pendente',
    FOREIGN KEY (id_peca) REFERENCES pecas(id_peca) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id_fornecedor) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE SET NULL ON UPDATE CASCADE
);
