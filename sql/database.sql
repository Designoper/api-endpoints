DROP DATABASE IF EXISTS biblioteca_libros;
CREATE DATABASE biblioteca_libros CHARACTER SET utf8mb4;
USE biblioteca_libros;

CREATE TABLE categorias (
	id_categoria INT PRIMARY KEY AUTO_INCREMENT,
	categoria VARCHAR(200) NOT NULL
);

CREATE TABLE libros (
	id_libro INT PRIMARY KEY AUTO_INCREMENT,
	titulo VARCHAR(200) NOT NULL UNIQUE,
	descripcion VARCHAR(200) NOT NULL,
	portada VARCHAR(100) NOT NULL,
	portada_ruta_relativa VARCHAR(200),
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

-- INSERT INTO libros (titulo, descripcion, paginas, fecha_publicacion, id_categoria) VALUES
-- ("Pedro y Paco", "Dos grandes amigos", 27, '2006-06-02', 4),
-- ("Don Quijote", "Un caballero con delirios", 600, '1505-12-07', 2);