<?php

$DB_HOST = '172.28.246.67';
$DB_USER = 'phpuser';
$DB_PASS = '123';
$DB_NAME = 'sakila';

$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$err = $mysqli->connect_error ? 'Error de conexión: ' . htmlspecialchars($mysqli->connect_error) : '';

$rows = [];
if (!$err) {
  $sql = "SELECT actor_id, first_name, last_name FROM actor ORDER BY actor_id LIMIT 100";
  if ($res = $mysqli->query($sql)) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    $res->free();
  } else {
    $err = 'Error en consulta: ' . htmlspecialchars($mysqli->error);
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Tabla de Actores</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    h1 { margin-bottom: 16px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
    .error { color: red; }
  </style>
</head>
<body>
  <h1>Lista de Actores</h1>

  <?php if ($err): ?>
    <p class="error"><?= $err ?></p>
  <?php elseif (count($rows) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellido</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['actor_id']) ?></td>
            <td><?= htmlspecialchars($row['first_name']) ?></td>
            <td><?= htmlspecialchars($row['last_name']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p>No hay actores registrados.</p>
  <?php endif; ?>
</body>
</html>

