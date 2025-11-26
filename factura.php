<?php

include("bd.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pedido_id = $_GET['pedido_id'] ?? 0;

// Obtener información del pedido
$stmt_pedido = $conexion->prepare("
    SELECT p.*, c.nombre, c.apellido, c.correo, c.telefono 
    FROM pedidos p 
    INNER JOIN clientes c ON p.cliente_id = c.cliente_id 
    WHERE p.pedido_id = :pedido_id
");
$stmt_pedido->execute([':pedido_id' => $pedido_id]);
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

// Obtener detalles del pedido
$stmt_detalles = $conexion->prepare("
    SELECT dp.*, pr.product_name 
    FROM detalles_pedido dp 
    INNER JOIN productos pr ON dp.producto_id = pr.product_id 
    WHERE dp.pedido_id = :pedido_id
");
$stmt_detalles->execute([':pedido_id' => $pedido_id]);
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

// Obtener información de pago
$stmt_pago = $conexion->prepare("
    SELECT * FROM pagos WHERE pedido_id = :pedido_id
");
$stmt_pago->execute([':pedido_id' => $pedido_id]);
$pago = $stmt_pago->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - Pedido #<?php echo $pedido_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4 mb-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-0">Bike Store</h4>
                                <small>Factura de Venta</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Pedido #<?php echo str_pad($pedido_id, 6, '0', STR_PAD_LEFT); ?></strong><br>
                                <small>Fecha: <?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Información del cliente -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Cliente:</h5>
                                <p class="mb-0"><strong><?php echo htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']); ?></strong></p>
                                <p class="mb-0"><small>Tel: <?php echo htmlspecialchars($pedido['telefono']); ?></small></p>
                                <p class="mb-0"><small>Email: <?php echo htmlspecialchars($pedido['correo']); ?></small></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5>Método de Pago:</h5>
                                <p class="mb-0"><strong><?php echo htmlspecialchars($pago['metodo_pago']); ?></strong></p>
                                <p class="mb-0"><small>Estado: <?php echo htmlspecialchars($pago['estado']); ?></small></p>
                            </div>
                        </div>

                        <!-- Detalles del pedido -->
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Descuento</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                foreach ($detalles as $detalle):
                                    $subtotal = $detalle['precio'] * $detalle['cantidad'];
                                    $descuento = ($subtotal * $detalle['descuento']) / 100;
                                    $subtotal_final = $subtotal - $descuento;
                                    $total += $subtotal_final;
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detalle['product_name']); ?></td>
                                        <td><?php echo $detalle['cantidad']; ?></td>
                                        <td>$<?php echo number_format($detalle['precio'], 2); ?></td>
                                        <td><?php echo $detalle['descuento']; ?>%</td>
                                        <td>$<?php echo number_format($subtotal_final, 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <th colspan="4" class="text-end">Total:</th>
                                    <th>$<?php echo number_format($total, 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="mt-4 text-center">
                            <a href="generar_factura_pdf.php?pedido_id=<?php echo $pedido_id; ?>" class="btn btn-danger" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
                            </a>
                            <button class="btn btn-primary" onclick="enviarEmail()">
                                <i class="bi bi-envelope"></i> Enviar por Email
                            </button>
                            <a href="index_cliente.php" class="btn btn-secondary">
                                Volver a Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function enviarEmail() {
            if (confirm('¿Desea enviar esta factura por correo electrónico?')) {
                fetch('enviar_factura_email.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            pedido_id: <?php echo $pedido_id; ?>,
                            correo: '<?php echo $pedido['correo']; ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Factura enviada exitosamente a ' + data.correo);
                        } else {
                            alert('Error al enviar el email: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al enviar el email');
                    });
            }
        }
    </script>
</body>

</html>