DROP DATABASE IF EXISTS biblioteca_libros;
CREATE DATABASE biblioteca_libros CHARACTER SET utf8mb4;
USE biblioteca_libros;

CREATE TABLE categorias (
	id_categoria INT PRIMARY KEY AUTO_INCREMENT,
	categoria VARCHAR(50) NOT NULL
);

CREATE TABLE libros (
	id_libro INT PRIMARY KEY AUTO_INCREMENT,
	titulo VARCHAR(50) NOT NULL UNIQUE,
	descripcion VARCHAR(200) NOT NULL,
	portada VARCHAR(100) NOT NULL,
	paginas INT NOT NULL,
	fecha_publicacion DATE NOT NULL,
	id_categoria INT NOT NULL,
	FOREIGN KEY (id_categoria) REFERENCES categorias (id_categoria)
);

INSERT INTO categorias (categoria) VALUES
("Arte y cultura"),
("Negocios"),
("Infantil"),
("Cultura popular"),
("Historia"),
("Idiomas"),
("Ciencia y naturaleza"),
("Gastronomía"),
("Deporte"),
("Religión");
