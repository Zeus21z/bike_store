<?php
include("bd.php");
header('Content-Type: application/json');

if (isset($_GET['producto_id'])) {
    $producto_id = $_GET['producto_id'];
    
    try {
        $stmt = $conexion->prepare("
            SELECT c.descripcion 
            FROM productos p 
            JOIN categorias c ON p.category_id = c.category_id 
            WHERE p.product_id = :producto_id
        ");
        $stmt->execute([':producto_id' => $producto_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            echo json_encode(['categoria' => $resultado['descripcion']]);
        } else {
            echo json_encode(['categoria' => null]);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Falta el parámetro producto_id']);
}
?>