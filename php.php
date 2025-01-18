<?php

// Obtener parámetros GET
$categorias = isset($_GET['categorias']) ? $_GET['categorias'] : [];
$min_paginas = isset($_GET['min_paginas']) ? $_GET['min_paginas'] : 0;
$max_paginas = isset($_GET['max_paginas']) ? $_GET['max_paginas'] : 1000;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '2000-01-01';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '2100-12-31';

// Construcción dinámica de la consulta SQL
$sql = "SELECT * FROM libros WHERE 1=1";

if (!empty($categorias)) {
    $categorias_placeholders = implode(',', array_fill(0, count($categorias), '?'));
    $sql .= " AND id_categoria IN ($categorias_placeholders)";
}

$sql .= " AND paginas BETWEEN ? AND ? AND fecha_publicacion BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);

$types = str_repeat('i', count($categorias)) . 'iiss';
$params = array_merge($categorias, [$min_paginas, $max_paginas, $fecha_inicio, $fecha_fin]);

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$libros = [];
while ($row = $result->fetch_assoc()) {
    $libros[] = $row;
}