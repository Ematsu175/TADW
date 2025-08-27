<?php
// ----- CONFIG -----
$DB_HOST = 'localhost';
$DB_USER = '20030356';
$DB_PASS = '20030356';
$DB_NAME = 'sakila';

// ----- CONEXIÓN -----
$mysqli = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$mysqli->set_charset('utf8mb4');
$err = $mysqli->connect_error ? 'Error de conexión: ' . htmlspecialchars($mysqli->connect_error) : '';

// ----- UPDATE: POST → Redirect → GET -----
if (!$err && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = isset($_POST['actor_id']) ? (int)$_POST['actor_id'] : 0;
    $fn = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $ln = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';

    if ($id > 0 && $fn !== '' && $ln !== '') {
        try {
            $stmt = $mysqli->prepare("UPDATE actor SET first_name = ?, last_name = ? WHERE actor_id = ?");
            $stmt->bind_param('ssi', $fn, $ln, $id);
            $stmt->execute();
            $stmt->close();

            // PRG: redirige para recargar la tabla con datos frescos y resaltar la fila
            header('Location: ' . $_SERVER['PHP_SELF'] . '?updated=' . urlencode((string)$id) . '#r' . urlencode((string)$id));
            exit;
        } catch (Throwable $e) {
            $err = 'Error al actualizar: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $err = 'Debes indicar un actor_id válido y valores para first_name y last_name.';
    }
}

// ----- CONSULTA LISTA -----
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

$updatedId = isset($_GET['updated']) ? (int)$_GET['updated'] : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Tabla de Actores</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    h1 { margin-bottom: 16px; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #f2f2f2; }
    .error { color: #b00020; margin: 8px 0; }
    .card { border: 1px solid #ddd; border-radius: 10px; padding: 12px; max-width: 720px; }
    .row { display: grid; grid-template-columns: 160px 1fr; gap: 8px; margin-bottom: 8px; align-items: center; }
    input[type="text"], input[type="number"] { padding: 6px 8px; width: 100%; box-sizing: border-box; }
    button { padding: 8px 14px; cursor: pointer; }
    /* Resaltado de la fila actualizada */
    .hi { background: #fff8c4; animation: flash 1s ease-in-out 2; }
    @keyframes flash { 50% { background: #ffe88a; } }
  </style>
</head>
<body>
  <h1>Lista de Actores</h1>

  <?php if ($err): ?>
    <p class="error"><?= $err ?></p>
  <?php elseif ($updatedId): ?>
    <p style="color:#0a7a0a;">✅ Registro actualizado (actor_id = <?= htmlspecialchars((string)$updatedId) ?>).</p>
  <?php endif; ?>

  <?php if (!$err && count($rows) > 0): ?>
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
          <?php $hi = ($updatedId === (int)$row['actor_id']) ? ' class="hi"' : ''; ?>
          <tr id="r<?= htmlspecialchars($row['actor_id']) ?>"<?= $hi ?>>
            <td><?= htmlspecialchars($row['actor_id']) ?></td>
            <td><?= htmlspecialchars($row['first_name']) ?></td>
            <td><?= htmlspecialchars($row['last_name']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- FORMULARIO DE ACTUALIZACIÓN -->
    <div class="card">
      <h2>Actualizar actor</h2>
      <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
        <div class="row">
          <label for="actor_id">actor_id (ID a actualizar):</label>
          <input type="number" name="actor_id" id="actor_id" min="1" required>
        </div>
        <div class="row">
          <label for="first_name">first_name (nuevo):</label>
          <input type="text" name="first_name" id="first_name" maxlength="45" required>
        </div>
        <div class="row">
          <label for="last_name">last_name (nuevo):</label>
          <input type="text" name="last_name" id="last_name" maxlength="45" required>
        </div>
        <button type="submit" name="update" value="1">Actualizar</button>
      </form>
    </div>

  <?php else: ?>
    <p>No hay actores registrados.</p>
  <?php endif; ?>

  <!-- (Opcional) Autollenado al hacer clic en una fila -->
  <script>
    document.querySelectorAll('tbody tr').forEach(tr => {
      tr.addEventListener('click', () => {
        const tds = tr.querySelectorAll('td');
        document.getElementById('actor_id').value   = tds[0].textContent.trim();
        document.getElementById('first_name').value = tds[1].textContent.trim();
        document.getElementById('last_name').value  = tds[2].textContent.trim();
        document.getElementById('first_name').focus();
      });
    });
  </script>
</body>
</html>
