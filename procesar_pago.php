<?php
include("bd.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        $conexion->beginTransaction();
        
        // Obtener o crear cliente
        $cliente_id = null;
        
        if (isset($_SESSION['cliente_id']) && $_SESSION['tipo_usuario'] === 'cliente') {
            $cliente_id = $_SESSION['cliente_id'];
        } else {
            // Crear cliente temporal
            $stmt_cliente = $conexion->prepare("
                INSERT INTO clientes (nombre, apellido, telefono, correo, calle) 
                VALUES (:nombre, :apellido, :telefono, :correo, :calle)
            ");
            
            $nombre_completo = explode(' ', $_SESSION['usuario'] ?? 'Cliente');
            $stmt_cliente->execute([
                ':nombre' => $nombre_completo[0] ?? 'Cliente',
                ':apellido' => $nombre_completo[1] ?? 'Temporal',
                ':telefono' => $data['telefono'],
                ':correo' => $data['correo'],
                ':calle' => $data['direccion']
            ]);
            
            $cliente_id = $conexion->lastInsertId();
        }
        
        // Obtener tienda y empleado de esa tienda
        $tienda_id = $data['tienda_id'];
        $stmt_empleado = $conexion->prepare("SELECT empleado_id FROM empleados WHERE tienda_id = :tienda_id LIMIT 1");
        $stmt_empleado->execute([':tienda_id' => $tienda_id]);
        $empleado = $stmt_empleado->fetch(PDO::FETCH_ASSOC);
        $empleado_id = $empleado['empleado_id'] ?? 1;
        
        // Crear pedido
        $stmt_pedido = $conexion->prepare("
            INSERT INTO pedidos (cliente_id, fecha_pedido, usuario_id, empleado_id, estado) 
            VALUES (:cliente_id, CURDATE(), 1, :empleado_id, 'Activo')
        ");
        $stmt_pedido->execute([':cliente_id' => $cliente_id, ':empleado_id' => $empleado_id]);
        $pedido_id = $conexion->lastInsertId();
        
        // Agregar detalles del pedido y descontar del inventario
        foreach ($data['productos'] as $producto) {
            // Obtener la categoría del producto para aplicar descuento automático
            $stmt_categoria = $conexion->prepare("
                SELECT c.descripcion 
                FROM productos p 
                JOIN categorias c ON p.category_id = c.category_id 
                WHERE p.product_id = :producto_id
            ");
            $stmt_categoria->execute([':producto_id' => $producto['product_id']]);
            $categoria = $stmt_categoria->fetch(PDO::FETCH_ASSOC);
            
            // Aplicar descuento según categoría: 10% para bicicletas, 5% para accesorios
            $descuento = 0;
            if ($categoria) {
                $descripcion = strtolower($categoria['descripcion']);
                if (strpos($descripcion, 'bicicleta') !== false || strpos($descripcion, 'bike') !== false) {
                    $descuento = 10; // 10% para bicicletas
                } elseif (strpos($descripcion, 'accesorio') !== false || strpos($descripcion, 'accessory') !== false) {
                    $descuento = 5; // 5% para accesorios
                }
            }
            
            // Insertar detalle del pedido
            $stmt_detalle = $conexion->prepare("
                INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio, descuento) 
                VALUES (:pedido_id, :producto_id, :cantidad, :precio, :descuento)
            ");
            $stmt_detalle->execute([
                ':pedido_id' => $pedido_id,
                ':producto_id' => $producto['product_id'],
                ':cantidad' => $producto['cantidad'],
                ':precio' => $producto['price'],
                ':descuento' => $descuento
            ]);
            
            // Descontar del inventario de la tienda seleccionada
            $stmt_update_inventario = $conexion->prepare("
                UPDATE inventario 
                SET cantidad = cantidad - :cantidad 
                WHERE tienda_id = :tienda_id AND product_id = :producto_id AND cantidad >= :cantidad
            ");
            $stmt_update_inventario->execute([
                ':tienda_id' => $tienda_id,
                ':producto_id' => $producto['product_id'],
                ':cantidad' => $producto['cantidad']
            ]);
        }
        
        // Registrar pago
        $stmt_pago = $conexion->prepare("
            INSERT INTO pagos (pedido_id, cliente_id, empleado_id, fecha_pago, monto_total, metodo_pago, estado) 
            VALUES (:pedido_id, :cliente_id, :empleado_id, NOW(), :monto_total, :metodo_pago, 'Completado')
        ");
        $stmt_pago->execute([
            ':pedido_id' => $pedido_id,
            ':cliente_id' => $cliente_id,
            ':empleado_id' => $empleado_id,
            ':monto_total' => $data['total'],
            ':metodo_pago' => $data['metodo_pago']
        ]);
        
        $conexion->commit();
        
        echo json_encode([
            'success' => true,
            'pedido_id' => $pedido_id,
            'message' => 'Pedido procesado exitosamente'
        ]);
        
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error al procesar el pedido: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
