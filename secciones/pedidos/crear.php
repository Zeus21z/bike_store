<?php include("../../bd.php");
// Asegurar sesión disponible antes de usar $_SESSION
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Cargar catálogos
$sentencia_productos = $conexion->prepare("SELECT product_id, product_name, price FROM productos ORDER BY product_name");
$sentencia_productos->execute();
$lista_productos = $sentencia_productos->fetchAll(PDO::FETCH_ASSOC);

$sentencia_clientes = $conexion->prepare("SELECT cliente_id, nombre, apellido FROM clientes ORDER BY nombre, apellido");
$sentencia_clientes->execute();
$lista_clientes = $sentencia_clientes->fetchAll(PDO::FETCH_ASSOC);

// Catálogo de usuarios (ya no se usa para fallback, solo si es necesario en UI)
$sentencia_usuarios = $conexion->prepare("SELECT user_id, usuario FROM usuarios ORDER BY usuario");
$sentencia_usuarios->execute();
$lista_usuarios = $sentencia_usuarios->fetchAll(PDO::FETCH_ASSOC);

// Guardar pedido
if($_POST){
    $cliente_id = isset($_POST['cliente_id']) ? $_POST['cliente_id'] : '';
    $fecha_pedido = isset($_POST['fecha_pedido']) ? $_POST['fecha_pedido'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : 'Pendiente';
    
    // Obtener el usuario logueado de la sesión de forma robusta (array o string)
    $usuario_id = null;
    $sessionUsername = null;
    if (isset($_SESSION['usuario'])) {
        if (is_array($_SESSION['usuario'])) {
            $usuario_id = $_SESSION['usuario']['user_id'] ?? null;
            $sessionUsername = $_SESSION['usuario']['usuario'] ?? null;
        } else {
            $sessionUsername = $_SESSION['usuario'];
        }
    }
    if (!$usuario_id && $sessionUsername) {
        $sentencia_usuario_logueado = $conexion->prepare("SELECT user_id FROM usuarios WHERE usuario = :usuario LIMIT 1");
        $sentencia_usuario_logueado->bindParam(":usuario", $sessionUsername);
        $sentencia_usuario_logueado->execute();
        $usuario_logueado = $sentencia_usuario_logueado->fetch(PDO::FETCH_ASSOC);
        if ($usuario_logueado) { $usuario_id = $usuario_logueado['user_id']; }
    }

    // Normalizar fecha si viene en dd/mm/aaaa
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fecha_pedido)) {
        list($d,$m,$y) = explode('/', $fecha_pedido);
        $fecha_pedido = $y.'-'.$m.'-'.$d;
    }

    // Validaciones previas

    if(empty($cliente_id) || empty($fecha_pedido) || empty($usuario_id)){
        echo "<div class='alert alert-warning'>No se pudo identificar al usuario de la sesión. Inicie sesión nuevamente.</div>";
        exit;
    }
    if(!isset($_POST['productos']) || !is_array($_POST['productos']) || count($_POST['productos'])===0){
        echo "<div class='alert alert-warning'>Agregue al menos un producto</div>";
        exit;
    }

    try {
        $conexion->beginTransaction();
        $stmt = $conexion->prepare("INSERT INTO pedidos(pedido_id, cliente_id, fecha_pedido, usuario_id, estado) VALUES(NULL,:cliente_id,:fecha_pedido,:usuario_id,:estado)");
        $stmt->bindParam(":cliente_id", $cliente_id);
        $stmt->bindParam(":fecha_pedido", $fecha_pedido);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();
        $pedido_id = $conexion->lastInsertId();

        $stmtDet = $conexion->prepare("INSERT INTO detalles_pedido(pedido_id, producto_id, cantidad, precio, descuento) VALUES(:pedido_id,:producto_id,:cantidad,:precio,:descuento)");
        foreach($_POST['productos'] as $prod){
            $pid = $prod['producto_id'];
            $cant = $prod['cantidad'];
            $prec = $prod['precio'];
            $desc = isset($prod['descuento']) ? $prod['descuento'] : 0;
            $stmtDet->bindParam(":pedido_id", $pedido_id);
            $stmtDet->bindParam(":producto_id", $pid);
            $stmtDet->bindParam(":cantidad", $cant);
            $stmtDet->bindParam(":precio", $prec);
            $stmtDet->bindParam(":descuento", $desc);
            $stmtDet->execute();
        }
        $conexion->commit();
        header("Location: index.php?mensaje=".urlencode('Pedido guardado exitosamente'));
        exit;
    } catch(Exception $e){
        if($conexion->inTransaction()){ $conexion->rollBack(); }
        echo "<div class='alert alert-danger'>";
        echo "<h4>Error al guardar el pedido:</h4>";
        echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Cliente ID:</strong> " . $cliente_id . "</p>";
        echo "<p><strong>Fecha:</strong> " . $fecha_pedido . "</p>";
        echo "<p><strong>Usuario ID:</strong> " . $usuario_id . "</p>";
        echo "<p><strong>Estado:</strong> " . $estado . "</p>";
        echo "<p><strong>Productos recibidos:</strong></p>";
        echo "<pre>" . print_r($_POST['productos'], true) . "</pre>";
        echo "</div>";
        exit;
    }
}
?>
<?php include("../../templates/header.php");?>
<br>

<div class="card">
    <div class="card-header">Nuevo Pedido</div>
    <div class="card-body">
        <form id="pedidoForm" method="post">
            <div class="mb-3">
                <label for="fecha_pedido" class="form-label">Fecha pedido</label>
                <input type="date" class="form-control" id="fecha_pedido" name="fecha_pedido" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-select" id="cliente_id" name="cliente_id" required>
                    <option value="">Seleccionar cliente</option>
                    <?php foreach($lista_clientes as $cliente) { ?>
                        <option value="<?php echo $cliente['cliente_id']; ?>"><?php echo htmlspecialchars($cliente['nombre'].' '.$cliente['apellido']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php 
            // Obtener el usuario logueado
            $usuario_logueado_id = null;
            if (isset($_SESSION['usuario'])) {
                $sentencia_usuario_logueado = $conexion->prepare("SELECT user_id FROM usuarios WHERE usuario = :usuario LIMIT 1");
                $sentencia_usuario_logueado->bindParam(":usuario", $_SESSION['usuario']);
                $sentencia_usuario_logueado->execute();
                $usuario_logueado = $sentencia_usuario_logueado->fetch(PDO::FETCH_ASSOC);
                if ($usuario_logueado) {
                    $usuario_logueado_id = $usuario_logueado['user_id'];
                }
            }
            // Si no hay usuario logueado, usar el primer usuario disponible
            if (!$usuario_logueado_id && !empty($lista_usuarios)) {
                $usuario_logueado_id = $lista_usuarios[0]['user_id'];
            }
            ?>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="Pendiente" selected>Pendiente</option>
                    <option value="Entregado">Entregado</option>
                </select>
            </div>

            <div class="mb-4">
                <h5>Productos del Pedido</h5>
                <div class="product-entry bg-light rounded p-3 mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label mb-1">Producto</label>
                            <select class="form-select" id="producto_select">
                                <option value="">Seleccionar producto</option>
                                <?php foreach($lista_productos as $producto) { ?>
                                    <option value="<?php echo $producto['product_id']; ?>" data-precio="<?php echo $producto['price']; ?>">
                                        <?php echo htmlspecialchars($producto['product_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad_input" min="1" value="1">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1">Precio</label>
                            <input type="number" class="form-control" id="precio_input" step="0.01" min="0">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label mb-1">Descuento</label>
                            <input type="number" class="form-control" id="descuento_input" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-6 col-md-1 d-grid">
                            <button type="button" class="btn btn-primary" id="agregar_producto">Agregar</button>
                        </div>
                    </div>
                </div>

                <table class="table table-striped table-hover mb-0" id="productos_table">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-start" style="width: 25%;">Producto</th>
                            <th class="text-center" style="width: 15%;">Cantidad</th>
                            <th class="text-end" style="width: 15%;">Precio</th>
                            <th class="text-end" style="width: 15%;">Descuento</th>
                            <th class="text-end" style="width: 15%;">Subtotal</th>
                            <th class="text-center" style="width: 15%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productos_tbody"></tbody>
                </table>
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-end">
                            <div class="border-top pt-3">
                                <h5 class="mb-0">Total: $<span id="total_pedido">0.00</span></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-outline-success" type="submit">Guardar Pedido</button>
            <a class="btn btn-outline-primary" href="index.php">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

<style>
.table th { font-weight: 600; }
.table td { vertical-align: middle; }
#campos_entrada, #productos_table { margin-left: 0; margin-right: 0; table-layout: fixed; }
#campos_entrada th, #campos_entrada td, #productos_table th, #productos_table td { padding-left: 15px; padding-right: 15px; }
.product-entry { border: 1px solid #e9ecef; }
#productos_table tbody tr:hover { background-color: #f8f9fa; cursor: pointer; }
#productos_table tbody tr { transition: background-color 0.2s ease; }
.product-list-header { border: 1px solid #e9ecef; border-bottom: none; border-radius: .5rem .5rem 0 0 !important; }
.table#productos_table { border: 1px solid #e9ecef; border-top: none; }
.border-top { border-top: 2px solid #dee2e6 !important; }
.form-label { font-weight: 500; color: #495057; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productoSelect = document.getElementById('producto_select');
    const cantidadInput = document.getElementById('cantidad_input');
    const precioInput = document.getElementById('precio_input');
    const descuentoInput = document.getElementById('descuento_input');
    const agregarBtn = document.getElementById('agregar_producto');
    const productosTbody = document.getElementById('productos_tbody');
    const totalPedido = document.getElementById('total_pedido');
    const pedidoForm = document.getElementById('pedidoForm');

    const productosAgregados = [];

    agregarBtn.addEventListener('click', function() {
        const nombre = productoSelect.options[productoSelect.selectedIndex]?.text || '';
        const productoId = productoSelect.value;
        const cantidad = parseInt(cantidadInput.value || '0', 10);
        const precio = parseFloat(precioInput.value || '0');
        const descuento = parseFloat(descuentoInput.value || '0');
        if (!productoId || cantidad <= 0 || precio <= 0) return;
        const subtotal = (precio * cantidad) - descuento;
        productosAgregados.push({ id: productoId, nombre, cantidad, precio, descuento, subtotal });
        renderTabla();
        productoSelect.value = '';
        cantidadInput.value = '1';
        precioInput.value = '';
        descuentoInput.value = '0';
    });

    // Autocompletar precio al seleccionar producto
    productoSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const precio = parseFloat(option.getAttribute('data-precio') || '');
        const productoId = this.value;
        
        if (!isNaN(precio)) { 
            precioInput.value = precio;
            
            // Obtener la categoría del producto para aplicar descuento automático
            fetch(`../../get_categoria.php?producto_id=${productoId}`)
                .then(response => response.json())
                .then(data => {
                    let descuento = 0;
                    if (data.categoria) {
                        const descripcion = data.categoria.toLowerCase();
                        if (descripcion.includes('bicicleta') || descripcion.includes('bike')) {
                            descuento = 10; // 10% para bicicletas
                        } else if (descripcion.includes('accesorio') || descripcion.includes('accessory')) {
                            descuento = 5; // 5% para accesorios
                        }
                    }
                    descuentoInput.value = descuento;
                })
                .catch(error => console.error('Error:', error));
        }
    });

    function renderTabla() {
        productosTbody.innerHTML = '';
        let total = 0;
        productosAgregados.forEach((p, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-start">${p.nombre}</td>
                <td class="text-center">${p.cantidad}</td>
                <td class="text-end">$${p.precio.toFixed(2)}</td>
                <td class="text-end">$${p.descuento.toFixed(2)}</td>
                <td class="text-end"><strong>$${p.subtotal.toFixed(2)}</strong></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarProducto(${i})">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </td>`;
            productosTbody.appendChild(tr);
            total += p.subtotal;
        });
        totalPedido.textContent = total.toFixed(2);
    }

    // Función para eliminar producto
    window.eliminarProducto = function(index) {
        if (confirm('¿Estás seguro de que quieres eliminar este producto?')) {
            productosAgregados.splice(index, 1);
            renderTabla();
        }
    }

    pedidoForm.addEventListener('submit', function(e) {
        if (productosAgregados.length === 0) {
            e.preventDefault();
            alert('Agregue al menos un producto.');
            return;
        }
        // limpiar ocultos anteriores
        pedidoForm.querySelectorAll('input[type="hidden"]').forEach(el => el.remove());
        // crear campos ocultos para productos
        productosAgregados.forEach((p, i) => {
            const f1 = document.createElement('input'); f1.type = 'hidden'; f1.name = `productos[${i}][producto_id]`; f1.value = p.id; pedidoForm.appendChild(f1);
            const f2 = document.createElement('input'); f2.type = 'hidden'; f2.name = `productos[${i}][cantidad]`; f2.value = p.cantidad; pedidoForm.appendChild(f2);
            const f3 = document.createElement('input'); f3.type = 'hidden'; f3.name = `productos[${i}][precio]`; f3.value = p.precio; pedidoForm.appendChild(f3);
            const f4 = document.createElement('input'); f4.type = 'hidden'; f4.name = `productos[${i}][descuento]`; f4.value = p.descuento; pedidoForm.appendChild(f4);
        });
    });
});
</script>

