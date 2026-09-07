create database bd_mundo;
use bd_mundo;

create table continentes (
  id int auto_increment primary key,
  nome varchar(100) not null,
  populacao bigint not null,
  area decimal(12,2) not null,
  total_paises int not null
);

create table governantes (
  id int auto_increment primary key,
  nome varchar(120) not null,
  partido_politico varchar(100) not null,
  data_nascimento date not null,
  idade int not null,
  data_inicio_mandato date not null,
  data_fim_mandato date
);

create table paises (
  id int auto_increment primary key,
  nome varchar(120) not null,
  continente_id int not null,
  populacao bigint not null,
  area decimal(12,2) not null,
  idioma varchar(80) not null,
  governante_id int,
  clima varchar(80) not null,
  regime_politico varchar(100) not null,
  moeda varchar(60) not null,
  foreign key (continente_id) references continentes(id),
  foreign key (governante_id) references governantes(id)
);

create table cidades (
  id int auto_increment primary key,
  nome varchar(120) not null,
  pais_id int not null,
  populacao bigint not null,
  area decimal(12,2) not null,
  clima varchar(80) not null,
  governante_id int,
  data_fundacao date,
  foreign key (pais_id) references paises(id),
  foreign key (governante_id) references governantes(id)
);


create table usuarios (
  id int auto_increment primary key,
  nome varchar(120) not null,
  login varchar(60) not null unique,
  senha varchar(255) not null,
  primeiro_acesso tinyint(1) not null default 1,
  tentativas_erradas int not null default 0,
  bloqueado tinyint(1) not null default 0
);

create table logs (
  id int auto_increment primary key,
  usuario_id int,
  login_tentado varchar(60),
  acao varchar(150) not null,
  data_hora datetime not null default current_timestamp,
  foreign key (usuario_id) references usuarios(id)
);
