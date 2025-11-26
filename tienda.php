<?php
/**
 * BIKE STORE - Tienda (Catálogo de Productos)
 * Accesible sin login - Lista todos los productos
 */

include("bd.php");

// Obtener categoría seleccionada (si existe)
$categoria_id = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;

// Obtener todas las categorías para el filtro
$stmt_categorias = $conexion->prepare("SELECT * FROM categorias ORDER BY descripcion ASC");
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Construir query de productos
$sql = "SELECT p.*, c.descripcion as categoria_nombre 
        FROM productos p 
        INNER JOIN categorias c ON p.category_id = c.category_id 
        WHERE 1=1";

$params = [];

if ($categoria_id > 0) {
    $sql .= " AND p.category_id = :categoria_id";
    $params[':categoria_id'] = $categoria_id;
}

$sql .= " ORDER BY p.create_date DESC";

$stmt_productos = $conexion->prepare($sql);
$stmt_productos->execute($params);
$productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

include("templates/header_publico.php");
?>

<!-- Header de la Tienda -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1>
            <i class="bi bi-shop"></i> 
            <?php 
            if ($categoria_id > 0) {
                $cat_actual = array_filter($categorias, function($c) use ($categoria_id) {
                    return $c['category_id'] == $categoria_id;
                });
                $cat_actual = reset($cat_actual);
                echo htmlspecialchars($cat_actual['descripcion'] ?? 'Tienda');
            } else {
                echo 'Todos los Productos';
            }
            ?>
        </h1>
        <p class="text-muted">
            <?php echo count($productos); ?> producto(s) disponible(s)
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Inicio
        </a>
    </div>
</div>

<div class="row">
    <!-- Sidebar de Categorías -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-funnel"></i> Filtrar por Categoría
                </h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="tienda.php" 
                   class="list-group-item list-group-item-action <?php echo $categoria_id === 0 ? 'active' : ''; ?>">
                    <i class="bi bi-grid-3x3-gap"></i> Todas las Categorías
                </a>
                <?php foreach ($categorias as $cat): ?>
                <a href="tienda.php?categoria=<?php echo $cat['category_id']; ?>" 
                   class="list-group-item list-group-item-action <?php echo $categoria_id === $cat['category_id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['descripcion']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Info adicional -->
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-info-circle text-primary"></i> ¿Necesitas ayuda?
                </h6>
                <p class="card-text small text-muted">
                    Contáctanos para asesoría personalizada sobre qué bicicleta se adapta mejor a tus necesidades.
                </p>
                <a href="#" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-whatsapp"></i> Contactar
                </a>
            </div>
        </div>
    </div>
    
    <!-- Grid de Productos -->
    <div class="col-md-9">
        <?php if (empty($productos)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                No hay productos disponibles en esta categoría.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($productos as $producto): ?>
                <div class="col-md-4">
                    <div class="card product-card h-100 shadow-sm">
                        <?php if (!empty($producto['foto'])): ?>
                        <img src="secciones/Productos/img/<?php echo htmlspecialchars($producto['foto']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($producto['product_name']); ?>"
                             loading="lazy">
                        <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="bi bi-bicycle" style="font-size: 80px; color: #ccc;"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2 align-self-start">
                                <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                            </span>
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($producto['product_name']); ?></h5>
                            
                            <p class="card-text text-muted mb-2">
                                <small>
                                    <i class="bi bi-calendar3"></i> 
                                    Año: <?php echo htmlspecialchars($producto['model_year']); ?>
                                </small>
                            </p>
                            
                            <p class="price-tag mb-3">
                                Bs. <?php echo number_format($producto['price'], 2); ?>
                            </p>
                            
                            <div class="mt-auto">
                                <button class="btn btn-add-cart w-100" 
                                        onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>, '<?php echo htmlspecialchars($producto['product_name']); ?>', <?php echo $producto['price']; ?>, '<?php echo htmlspecialchars($producto['foto']); ?>')">
                                    <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
/**
 * Función para agregar producto al carrito
 */
function agregarAlCarrito(productId, productName, price, foto) {
    let carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    
    const itemIndex = carrito.findIndex(item => item.product_id === productId);
    
    if (itemIndex > -1) {
        carrito[itemIndex].cantidad += 1;
    } else {
        carrito.push({
            product_id: productId,
            product_name: productName,
            price: price,
            foto: foto,
            cantidad: 1
        });
    }
    
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Feedback visual
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    
    btn.classList.add('added');
    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Agregado';
    btn.disabled = true;
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('added');
        btn.disabled = false;
    }, 1500);
    
    actualizarContadorCarrito();
    mostrarNotificacion('Producto agregado al carrito', 'success');
}

function mostrarNotificacion(mensaje, tipo = 'success') {
    const notif = document.createElement('div');
    notif.className = `alert alert-${tipo} position-fixed top-0 end-0 m-3 shadow`;
    notif.style.zIndex = '9999';
    notif.innerHTML = `
        <i class="bi bi-check-circle-fill"></i> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notif);
    
    setTimeout(() => notif.remove(), 3000);
}
</script>

<?php include("templates/footer.php"); ?>