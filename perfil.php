<?php
// perfil.php
declare(strict_types=1);
session_start();

require __DIR__ . '/bd.php'; // Debe exponer $pdo (PDO)
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$userId = (int) $_SESSION['user_id'];

// ===== helpers mínimos =====
function h(?string $s): string
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function post(string $k, $default = '')
{
  return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $default;
}

// ===== cargar usuario =====
$st = $pdo->prepare("SELECT user_id, usuario, email, password FROM usuarios WHERE user_id = ?");
$st->execute([$userId]);
$usuarioRow = $st->fetch(PDO::FETCH_ASSOC);
if (!$usuarioRow) {
  header('Location: index.php');
  exit;
}

// ===== intentar mapear cliente por email (si existe) =====
$cliente = null;
$stc = $pdo->prepare("SELECT * FROM clientes WHERE email = ? ORDER BY 1 LIMIT 1");
$stc->execute([$usuarioRow['email']]);
$cliente = $stc->fetch(PDO::FETCH_ASSOC);

// ===== CSRF simple =====
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

$msg = null;
$msgType = 'success';

// ===== actualizar perfil (usuario/email + datos cliente) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('_action') === 'save_profile' && hash_equals($csrf, (string)post('_csrf'))) {
  $usuario = post('usuario', $usuarioRow['usuario']);
  $email   = post('email',   $usuarioRow['email']);
  $nombre  = post('nombre',  $cliente['nombre']  ?? '');
  $telefono = post('telefono', $cliente['telefono'] ?? '');
  $direccion = post('direccion', $cliente['direccion'] ?? '');

  if ($usuario === '' || $email === '') {
    $msg = 'Usuario y correo no pueden estar vacíos.';
    $msgType = 'danger';
  } else {
    // verificar colisión de email con otros usuarios
    $check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND user_id <> ?");
    $check->execute([$email, $userId]);
    if ((int)$check->fetchColumn() > 0) {
      $msg = 'El correo ya está en uso por otro usuario.';
      $msgType = 'danger';
    } else {
      // actualizar usuarios
      $up = $pdo->prepare("UPDATE usuarios SET usuario = ?, email = ? WHERE user_id = ?");
      $up->execute([$usuario, $email, $userId]);

      // actualizar/insertar cliente (si manejas cliente por email)
      if ($cliente) {
        $upc = $pdo->prepare("UPDATE clientes SET nombre=?, telefono=?, direccion=?, email=? WHERE cliente_id=?");
        $upc->execute([$nombre, $telefono, $direccion, $email, $cliente['cliente_id']]);
      } else {
        // crear cliente si no existía
        $ins = $pdo->prepare("INSERT INTO clientes (nombre, telefono, direccion, email) VALUES (?,?,?,?)");
        $ins->execute([$nombre, $telefono, $direccion, $email]);
        // recargar cliente
        $stc->execute([$email]);
        $cliente = $stc->fetch(PDO::FETCH_ASSOC);
      }

      // refrescar datos de sesión visibles
      $_SESSION['usuario'] = $usuario;
      $usuarioRow['usuario'] = $usuario;
      $usuarioRow['email']   = $email;

      $msg = 'Perfil actualizado correctamente.';
      $msgType = 'success';
    }
  }
}

// ===== cambio de contraseña (migra a hash si estaba en texto plano) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('_action') === 'change_pass' && hash_equals($csrf, (string)post('_csrf'))) {
  $actual = post('pass_actual');
  $nueva  = post('pass_nueva');
  $nueva2 = post('pass_nueva2');

  // recargar pass
  $st->execute([$userId]);
  $usuarioRow = $st->fetch(PDO::FETCH_ASSOC);

  $stored = (string)$usuarioRow['password'];
  $isHashed = str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2') || str_starts_with($stored, '$1$');

  $okActual = $isHashed ? password_verify($actual, $stored) : hash_equals($stored, $actual);

  if (!$okActual) {
    $msg = 'La contraseña actual no es correcta.';
    $msgType = 'danger';
  } elseif (strlen($nueva) < 6) {
    $msg = 'La nueva contraseña debe tener al menos 6 caracteres.';
    $msgType = 'warning';
  } elseif ($nueva !== $nueva2) {
    $msg = 'La confirmación no coincide.';
    $msgType = 'warning';
  } else {
    $newHash = password_hash($nueva, PASSWORD_DEFAULT);
    $up = $pdo->prepare("UPDATE usuarios SET password = ? WHERE user_id = ?");
    $up->execute([$newHash, $userId]);
    $msg = 'Contraseña actualizada.';
    $msgType = 'success';
  }
}

// ===== pedidos recientes del cliente (si hay cliente) =====
// Nota: ajusta nombres de columnas si difieren.
$pedidos = [];
if ($cliente) {
  $sql = "
  SELECT 
    p.pedido_id,
    p.fecha_pedido,
    p.estado AS estado_pedido,
    COALESCE(pg.monto_total, SUM(dp.cantidad * dp.precio)) AS total,
    pg.metodo_pago,
    pg.estado AS estado_pago
  FROM pedidos p
  LEFT JOIN detalles_pedido dp ON dp.pedido_id = p.pedido_id
  LEFT JOIN pagos pg ON pg.pedido_id = p.pedido_id
  WHERE p.cliente_id = ?
  GROUP BY p.pedido_id, p.fecha_pedido, p.estado, pg.monto_total, pg.metodo_pago, pg.estado
  ORDER BY p.fecha_pedido DESC
  LIMIT 20";
  $stp = $pdo->prepare($sql);
  $stp->execute([$cliente['cliente_id']]);
  $pedidos = $stp->fetchAll(PDO::FETCH_ASSOC);
}

// ===== UI =====
include __DIR__ . '/templates/header_cliente.php'; // o header.php si prefieres
?>
<div class="container py-4">
  <h1 class="h3 mb-3">Mi perfil</h1>

  <?php if ($msg): ?>
    <div class="alert alert-<?= h($msgType) ?>"><?= h($msg) ?></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header">Datos de cuenta</div>
        <div class="card-body">
          <form method="post" class="row g-3" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="_action" value="save_profile">

            <div class="col-12">
              <label class="form-label">Usuario</label>
              <input name="usuario" class="form-control" required value="<?= h($usuarioRow['usuario']) ?>">
            </div>

            <div class="col-12">
              <label class="form-label">Correo</label>
              <input name="email" type="email" class="form-control" required value="<?= h($usuarioRow['email']) ?>">
            </div>

            <hr class="my-2">

            <div class="col-12">
              <label class="form-label">Nombre</label>
              <input name="nombre" class="form-control" value="<?= h($cliente['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input name="telefono" class="form-control" value="<?= h($cliente['telefono'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Dirección</label>
              <textarea name="direccion" class="form-control" rows="2"><?= h($cliente['direccion'] ?? '') ?></textarea>
            </div>

            <div class="col-12">
              <button class="btn btn-success">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div><!-- col -->

    <div class="col-lg-7">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Cambiar contraseña</div>
        <div class="card-body">
          <form method="post" class="row g-3" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="_action" value="change_pass">

            <div class="col-md-4">
              <label class="form-label">Actual</label>
              <input type="password" name="pass_actual" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Nueva</label>
              <input type="password" name="pass_nueva" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Confirmar</label>
              <input type="password" name="pass_nueva2" class="form-control" required>
            </div>
            <div class="col-12">
              <button class="btn btn-outline-success">Actualizar contraseña</button>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header">Mis pedidos recientes</div>
        <div class="card-body table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Método</th>
                <th>Pago</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($pedidos)): foreach ($pedidos as $p): ?>
                  <tr>
                    <td><a href="secciones/pedidos/ver.php?id=<?= (int)$p['pedido_id'] ?>">#<?= (int)$p['pedido_id'] ?></a></td>
                    <td><?= h(date('d/m/Y H:i', strtotime((string)$p['fecha_pedido']))) ?></td>
                    <td><?= h($p['estado_pedido'] ?? '—') ?></td>
                    <td><?= h($p['metodo_pago'] ?? '—') ?></td>
                    <td><?= h($p['estado_pago'] ?? '—') ?></td>
                    <td class="text-end">$<?= number_format((float)$p['total'], 2, '.', ',') ?></td>
                  </tr>
                <?php endforeach;
              else: ?>
                <tr>
                  <td colspan="6" class="text-muted">Aún no tienes pedidos.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- col -->
  </div><!-- row -->
</div>

<?php include __DIR__ . '/templates/footer.php';
