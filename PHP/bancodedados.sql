 CREATE DATABASE novatendencia; 
 DROP DATABASE novatendencia;
 USE novatendencia;

-- Criar Tabelas
CREATE TABLE CLIENTE(
idCliente INT PRIMARY KEY auto_increment,
nome varchar(150) NOT NULL,
cpf INT NOT NULL UNIQUE,
telefone VARCHAR(20) NOT NULL,
email VARCHAR(200),
senha VARCHAR(12),
foto_perfil LONGBLOB
);

CREATE TABLE ENDERECO(
idEndereco INT PRIMARY KEY AUTO_INCREMENT,
cep INT NOT NULL,
cidade VARCHAR(100) NOT NULL,
numero VARCHAR(20) NOT NULL,
complemento VARCHAR(100),
logradouro VARCHAR(100),
bairro VARCHAR(100),
tipo VARCHAR(20)
);

-- TABELA CHAVE ESTRANGEIRA

CREATE TABLE CLIENTE_E_ENDERECO(
cliente_idCliente INT,
endereco_idEndereco INT,
CONSTRAINT foreign key (cliente_idCliente) REFERENCES
CLIENTE(idCliente),
CONSTRAINT foreign key (endereco_idEndereco) REFERENCES
ENDERECO(idEndereco)
);


CREATE TABLE CATEGORIA (
idCategoria INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(50) not null,
desconto DOUBLE
);


CREATE TABLE BANNERS(
idBannres INT PRIMARY KEY AUTO_INCREMENT,
imagem LONGBLOB NOT NULL,
data_validade DATE NOT NULL,
descricao VARCHAR(45) NOT NULL,
link VARCHAR(100),
categoria_id INT,
CONSTRAINT FOREIGN KEY (categoria_id) REFERENCES
CATEGORIA (idCategoria)
);

CREATE TABLE CUPOM (
idCupom INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(45) NOT NULL,
valor DOUBLE NOT NULL, 
data_validade DATE NOT NULL,
quantidade INT NOT NULL
);

CREATE TABLE FRETE (
idFrete INT PRIMARY KEY AUTO_INCREMENT,
bairro VARCHAR(45) NOT NULL,
valor DOUBLE ,
transportadora VARCHAR(45) NOT NULL
);


CREATE TABLE FORMA_PAGAMENTO(
idForma_pagamento INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(45),
cupom_id INT,
CONSTRAINT FOREIGN KEY (cupom_id) references
CUPOM(idCupom)
);


CREATE TABLE VENDAS (
idVendas INT PRIMARY KEY AUTO_INCREMENT,
data_venda DATE NOT NULL,
valor_produto DOUBLE NOT NULL,
valor_total DOUBLE NOT NULL,
data_entrega DATE NOT NULL,
situacao VARCHAR(45) NOT NULL,
cod_pix VARCHAR(100),
cod_barras VARCHAR(100),
valor_total_desconto DOUBLE,
cliente_id INT NOT NULL,
forma_pagamento_id INT,
cupom_id INT,
frete_id INT,
CONSTRAINT FOREIGN KEY (cliente_id) REFERENCES 
CLIENTE(idCliente),
CONSTRAINT FOREIGN KEY (forma_pagamento_id) REFERENCES
FORMA_PAGAMENTO(idForma_pagamento),
CONSTRAINT FOREIGN KEY (cupom_id) REFERENCES
CUPOM(idCupom),
CONSTRAINT FOREIGN KEY (frete_id) REFERENCES 
FRETE(idFrete)
);

CREATE TABLE MARCAS (
IdMarcas INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(45),
imagem LONGBLOB
);

CREATE TABLE IMAGEM_PRODUTO(
idImagem_produto INT PRIMARY KEY AUTO_INCREMENT,
foto LONGBLOB,
texto_alternativo VARCHAR(45)
);

CREATE TABLE PRODUTOS (
idProdutos INT PRIMARY KEY AUTO_INCREMENT,
nome VARCHAR(45),
descricao TEXT(500),
quantidade int not null,
preco DOUBLE NOT NULL,
tamanho VARCHAR(45),
cor VARCHAR(45),
codigo INT NOT NULL,
preco_promocional DOUBLE,
marcas_id INT,
CONSTRAINT FOREIGN KEY (marcas_id) REFERENCES 
MARCAS(IdMarcas)
);

CREATE TABLE PRODUTO_IMAGEM (
produto_id INT,
produto_marca_id INT,
imagem_produto INT,
CONSTRAINT FOREIGN KEY (produto_id) REFERENCES 
PRODUTOS(idProdutos),
CONSTRAINT FOREIGN KEY (produto_marca_id) REFERENCES 
PRODUTOS(marcas_id),
CONSTRAINT FOREIGN KEY (imagem_produto) REFERENCES 
IMAGEM_PRODUTO (idImagem_produto)
);

CREATE TABLE PRODUTO_CATEGORIA (
produtos_id INT,
produtos_marcas_id INT,
categoria_produtos INT,
CONSTRAINT FOREIGN KEY (produtos_id) REFERENCES PRODUTOS(idProdutos),
CONSTRAINT FOREIGN KEY (produtos_marcas_id) REFERENCES PRODUTOS(marcas_id),
CONSTRAINT FOREIGN KEY (categoria_produtos) REFERENCES CATEGORIA (idCategoria)
);

CREATE TABLE VENDAS_PRODUTOS(
venda_id INT,
venda_cliente INT,
produtos_id INT,
CONSTRAINT FOREIGN KEY(venda_id) REFERENCES 
VENDAS(idVendas),
CONSTRAINT FOREIGN KEY(venda_cliente) REFERENCES 
VENDAS(cliente_id),
CONSTRAINT FOREIGN KEY(produtos_id) REFERENCES
PRODUTOS(idProdutos)
);



CREATE TABLE EMPRESA(
idEmpresa INT PRIMARY KEY AUTO_INCREMENT,
nome_fantasia VARCHAR(100),
cnpj_cpf INT,
telefone VARCHAR(45),
intagram varchar(100),
facebook varchar(100),
whatsapp varchar(100),
logo LONGBLOB,
usuario varchar(100),
senha varchar(12)
);


CREATE TABLE LOGIN_USUARIO(
idLogin_funcionario INT PRIMARY KEY AUTO_INCREMENT,
usuario VARCHAR(45) NOT NULL,
senha  VARCHAR(12),
empresa_id INT,
CONSTRAINT FOREIGN KEY (empresa_id) REFERENCES 
EMPRESA(idEmpresa)
);

/** 
----CRIAÇÃO NO BANCO DE DADOS SQL----

CREATE DATABASE - CRIA O BANCO DE DADOS 
CRETE TABLE - CRIAR TABELAS 

USE - USAR O BANCO DE DADOS 

---- EXCLUSÃO NO BANCO EDE DADOS ----

DROP DATABASE - SERVE PARA EXCLUIR BANCO DE DADOS 
DROP TABLE - EXCLUI TABELAS (SÓ É POSSÍVEL CASO NÃO TENHA NENHUMA OUTRA TABELA CONECTADA A ELA)

*/


-- SELECT - VISUALIZAR OS DADOS REGISTRADOS DENTRO DA TABELA --
SELECT * FROM CLIENTE;
SELECT * FROM ENDERECO;

SELECT * FROM CLIENTE_E_ENDERECO;


SELECT idCliente, nome,cpf from CLIENTE;

-- INSERT - SERVE PARA CADASTRAR DADOS DENTRO DA TABELA  --
-- EXEMPLO: --
INSERT INTO nometabela (campo1,campo2,campo3)
VALUES ("",2.7,2);

-- INSERT NA TABELA CLIENTE --     
INSERT INTO CLIENTE(nome,cpf,email,telefone,senha)
VALUES("Evellyn","12345674567","giovanna@gmail.com","63 99132-3307", "123456");

-- COMANDO PARA ALTERAR O TIPO DO CAMPO
ALTER TABLE CLIENTE MODIFY cpf VARCHAR(45) UNIQUE;

-- INSERINDO DADOS NA TABELA ENDERECO
INSERT INTO ENDERECO(cep, cidade, numero, complemento, logradouro, bairro, tipo)
VALUES (7784480,"Araguaína","2900", "Q. 5 L16","RUA S1", "VILA SANTIAGO","casa"); 


-- CONECTANDO O CLIENTE COM SEU ENDERECO
INSERT INTO CLIENTE_E_ENDERECO (cliente_idcliente, endereco_idendereco)
VALUES(1,1);


-- INSERINDO DADOS PARA MARCAS 

INSERT INTO MARCAS(nome, imagem)
VALUES("Nova Tendência","C:\Users\ALUNO\Documents\imagem\Logo1");

SELECT * FROM MARCAS;


-- INSERINDO DADOS EM PRODUTOS

INSERT INTO PRODUTOS (nome, descricao, quantidade,preco,tamanho, codigo,preco_promocional,marcas_id)
VALUES("VESTIDO - AZUL","VESTIDO AZUL COM BABADO",5,199.99,"M" ,"12556", 159.99, 1);

-- INSERT INTO CUPOM (idCupom,nome,);



SELECT * FROM frete; 
INSERT INTO CUPOM (nome, valor, data_validade, quantidade)
VALUES("SETEMBRO123",50.00,"2025-09-30", 50);

--------------------------------------------------------
SELECT * FROM FORMA_PAGAMENTO;
--------------------------------------------------------

select * from FORMA_PAGAMENTO;
select * from cupom;

DELETE FROM FORMA_PAGAMENTO
WHERE idForma_pagamento IN (1);


ALTER TABLE FORMA_PAGAMENTO
DROP COLUMN cupom_id;


INSERT INTO FORMA_PAGAMENTO (cupom_id)
VALUES (1);

SELECT 
    CONSTRAINT_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'FORMA_PAGAMENTO'
  AND CONSTRAINT_SCHEMA = DATABASE()
  AND REFERENCED_TABLE_NAME IS NOT NULL;
  
  -- forma_pagamento_ibfk_1


SHOW CREATE TABLE FORMA_PAGAMENTO;

ALTER TABLE FORMA_PAGAMENTO DROP FOREIGN KEY forma_pagamento_ibfk_1;

ALTER TABLE FORMA_PAGAMENTO DROP COLUMN cupom_id;


select * from forma_pagamento;
select * from frete; 

select * from cliente_id