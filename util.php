<?php
// URL completa de la imagen
$url_completa = "https://www.example.com/path/to/your/image.jpg";

// Parsear la URL
$parsed_url = parse_url($url_completa);

// Extraer la ruta relativa
$ruta_relativa = $parsed_url['path'];

echo $ruta_relativa; // Output: /path/to/your/image.jpg
?>
