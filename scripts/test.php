<?php

$sql = "CREATE TABLE animes (
    siglas VARCHAR(250) NOT NULL PRIMARY KEY,
    idiomas VARCHAR(255) DEFAULT NULL,
    date_publication VARCHAR(250) DEFAULT NULL,
    date_finalization VARCHAR(250) DEFAULT NULL,
    state VARCHAR(250) DEFAULT NULL,
    kind VARCHAR(25) DEFAULT NULL,
    valorations int4 DEFAULT 0,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    temporada VARCHAR(25) DEFAULT NULL
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE media_animes (
    id SERIAL NOT NULL PRIMARY KEY
    type VARCHAR(250) NOT NULL,
    name VARCHAR(250) NOT NULL,
    extension VARCHAR(250) NOT NULL,
    anime VARCHAR(250) NOT NULL
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE translation_animes (
    id SERIAL NOT NULL PRIMARY KEY
    translation VARCHAR(250) NOT NULL,
    kind VARCHAR(25) DEFAULT NULL,
    lang int4,
    anime VARCHAR(250) NOT NULL
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE anime_generes (
    id SERIAL NOT NULL PRIMARY KEY,
    genere int4,
    anime VARCHAR(250) NOT NULL
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE episodes (
    id VARCHAR(250)  NOT NULL PRIMARY KEY,
    anime VARCHAR(250) NOT NULL,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    num int4 DEFAULT 1,
    seasion VARCHAR(250) DEFAULT null,
    madia_type VARCHAR(250) NOT NULL,
    madia_name VARCHAR(250) NOT NULL,
    madia_extension VARCHAR(250) NOT NULL,
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE translation_episodes (
    id SERIAL NOT NULL PRIMARY KEY
    translation VARCHAR(250) NOT NULL,
    lang int4,
    episodes VARCHAR(250) NOT NULL
);";
$db->ejecutar($sql);
$sql = "CREATE TABLE openings (
    id VARCHAR(250) PRIMARY KEY,
    nombre VARCHAR(150),
    descripcion VARCHAR(255) NOT NULL,
    anime int4 NOT NULL,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    num int4 DEFAULT 1,
    seasion VARCHAR(250) DEFAULT null,
    madia_type VARCHAR(250) NOT NULL,
    madia_name VARCHAR(250) NOT NULL,
    madia_extension VARCHAR(250) NOT NULL
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE endings (
    id VARCHAR(250) PRIMARY KEY,
    nombre VARCHAR(150),
    descripcion VARCHAR(255) NOT NULL,
    anime int4 NOT NULL,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    num int4 DEFAULT 1,
    seasion VARCHAR(250) DEFAULT null,
    madia_type VARCHAR(250) NOT NULL,
    madia_name VARCHAR(250) NOT NULL,
    madia_extension VARCHAR(250) NOT NULL
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE filters (
    id SERIAL NOT NULL PRIMARY KEY,
    code VARCHAR(255) DEFAULT NULL,
    kind VARCHAR(255) NOT NULL,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );";
$db->ejecutar($sql);
$sql = "CREATE TABLE translation_filters (
  id SERIAL NOT NULL PRIMARY KEY
  translation VARCHAR(250) NOT NULL,
  lang int4,
  id_external int4
);";
$db->ejecutar($sql);
$sql = "CREATE TABLE langs (
    id SERIAL NOT NULL PRIMARY KEY,
    code VARCHAR(5) DEFAULT NULL,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );";
$db->ejecutar($sql);
$db->ejecutar("INSERT INTO langs(id, code) VALUES(1, 'es');");
$db->ejecutar("INSERT INTO langs(id, code) VALUES(2, 'en');");
$db->ejecutar("INSERT INTO langs(id, code) VALUES(3, 'va');");
$db->ejecutar("INSERT INTO langs(id, code) VALUES(4, 'ca');");

$sql = "CREATE TABLE seasons (
    id VARCHAR(250) NOT NULL PRIMARY KEY,
    title VARCHAR(50) DEFAULT NULL,
    created timestamp DEFAULT CURRENT_TIMESTAMP,
    updated timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );";
$db->ejecutar($sql);
