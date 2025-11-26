<?php
include("bd.php");
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $pedido_id = $data['pedido_id'];
    $correo = $data['correo'];

    try {
        // Obtener información del pedido
        $stmt_pedido = $conexion->prepare("
            SELECT p.*, c.nombre, c.apellido, c.correo 
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

        // Calcular total
        $total = 0;
        foreach ($detalles as $detalle) {
            $subtotal = $detalle['precio'] * $detalle['cantidad'];
            $descuento = ($subtotal * $detalle['descuento']) / 100;
            $total += $subtotal - $descuento;
        }

        // Crear contenido HTML del email
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { background-color: #0066cc; color: white; padding: 20px; }
                .content { padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; font-size: 18px; color: #0066cc; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>Bike Store - Factura de Venta</h2>
            </div>
            <div class='content'>
                <p><strong>Estimado/a " . htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) . ",</strong></p>
                <p>Gracias por comprar con Bike Store. A continuación encontrará el detalle de su pedido:</p>
                
                <p><strong>Pedido #" . str_pad($pedido_id, 6, '0', STR_PAD_LEFT) . "</strong><br>
                Fecha: " . date('d/m/Y', strtotime($pedido['fecha_pedido'])) . "</p>
                
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Descuento</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($detalles as $detalle) {
            $subtotal = $detalle['precio'] * $detalle['cantidad'];
            $descuento = ($subtotal * $detalle['descuento']) / 100;
            $subtotal_final = $subtotal - $descuento;

            $html .= "
                        <tr>
                            <td>" . htmlspecialchars($detalle['product_name']) . "</td>
                            <td>" . $detalle['cantidad'] . "</td>
                            <td>$" . number_format($detalle['precio'], 2) . "</td>
                            <td>" . $detalle['descuento'] . "%</td>
                            <td>$" . number_format($subtotal_final, 2) . "</td>
                        </tr>";
        }

        $html .= "
                        <tr>
                            <td colspan='4' style='text-align: right; font-weight: bold;'>Total:</td>
                            <td class='total'>$" . number_format($total, 2) . "</td>
                        </tr>
                    </tbody>
                </table>
                
                <p>Su pedido será procesado y enviado a la brevedad posible.</p>
                <p>Si tiene alguna consulta, no dude en contactarnos.</p>
                
                <p>Saludos cordiales,<br>
                <strong>Equipo Bike Store</strong></p>
            </div>
        </body>
        </html>";

        // Nota: Para enviar emails en producción, necesitas configurar un servicio SMTP
        // Opciones: PHPMailer con Gmail/SMTP, SendGrid, Mailgun, etc.
        // Por ahora, simular el envío exitoso

        // En producción, aquí iría algo como:
        /*
        require_once('libs/PHPMailer/PHPMailer.php');
        require_once('libs/PHPMailer/SMTP.php');
        
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tu-email@gmail.com';
        $mail->Password = 'tu-password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom('noreply@bikestore.com', 'Bike Store');
        $mail->addAddress($correo);
        $mail->Subject = "Factura de Pedido #" . str_pad($pedido_id, 6, '0', STR_PAD_LEFT);
        $mail->Body = $html;
        $mail->isHTML(true);
        $mail->send();
        */

        echo json_encode([
            'success' => true,
            'message' => 'Email configurado correctamente. Para activar el envío real, configura un servicio SMTP.',
            'correo' => $correo,
            'nota' => 'En desarrollo local, configure PHPMailer o servicio SMTP'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
