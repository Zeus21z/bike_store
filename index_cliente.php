<?php
/**
 * BIKE STORE - Página Principal para Cliente (misma vista que index.php)
 * Diseñado para verse igual que la página pública principal
 */

include("bd.php");

// Obtener productos destacados para el carrusel (primeros 5 con imagen)
$stmt_destacados = $conexion->prepare("
    SELECT * FROM productos 
    WHERE foto IS NOT NULL AND foto != '' 
    ORDER BY create_date DESC 
    LIMIT 5
");
$stmt_destacados->execute();
$productos_destacados = $stmt_destacados->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías
$stmt_categorias = $conexion->prepare("SELECT * FROM categorias ORDER BY descripcion ASC");
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

include("templates/header_publico.php");
?>

<!-- Hero Section -->
<div class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Bienvenido a Bike Store</h1>
        <p class="lead mb-4">Las mejores bicicletas y accesorios al mejor precio</p>
        <a href="tienda.php" class="btn btn-light btn-lg">
            <i class="bi bi-shop"></i> Explorar Productos
        </a>
    </div>
</div>

<?php if (!empty($productos_destacados)): ?>
<!-- Carrusel de Productos Destacados -->
<div class="mb-5">
    <h2 class="text-center mb-4">
        <i class="bi bi-star-fill text-warning"></i> Productos Destacados
    </h2>
    
    <div id="carouselDestacados" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach($productos_destacados as $index => $producto): ?>
            <button type="button" 
                    data-bs-target="#carouselDestacados" 
                    data-bs-slide-to="<?php echo $index; ?>" 
                    class="<?php echo $index === 0 ? 'active' : ''; ?>">
            </button>
            <?php endforeach; ?>
        </div>
        
        <div class="carousel-inner">
            <?php foreach($productos_destacados as $index => $producto): ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="row align-items-center bg-light rounded p-4">
                    <div class="col-md-6 text-center">
                        <?php if(!empty($producto['foto'])): ?>
                        <img src="secciones/Productos/img/<?php echo htmlspecialchars($producto['foto']); ?>" 
                             class="img-fluid" 
                             style="max-height: 400px; object-fit: contain;" 
                             alt="<?php echo htmlspecialchars($producto['product_name']); ?>">
                        <?php else: ?>
                        <i class="bi bi-bicycle" style="font-size: 200px; color: #ccc;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h3 class="mb-3"><?php echo htmlspecialchars($producto['product_name']); ?></h3>
                        <p class="text-muted mb-2">
                            <i class="bi bi-calendar3"></i> Año: <?php echo htmlspecialchars($producto['model_year']); ?>
                        </p>
                        <p class="price-tag mb-4">
                            Bs. <?php echo number_format($producto['price'], 2); ?>
                        </p>
                        <button class="btn btn-add-cart btn-lg" 
                                onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>, '<?php echo htmlspecialchars($producto['product_name']); ?>', <?php echo $producto['price']; ?>, '<?php echo htmlspecialchars($producto['foto']); ?>')">
                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselDestacados" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselDestacados" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Productos Más Vendidos por Categoría -->
<?php foreach($categorias as $categoria): 
    // Obtener productos más vendidos de esta categoría (top 4)
    $stmt_vendidos = $conexion->prepare("
        SELECT p.*, COALESCE(SUM(dp.cantidad), 0) as total_vendido
        FROM productos p
        LEFT JOIN detalles_pedido dp ON p.product_id = dp.producto_id
        WHERE p.category_id = :category_id
        GROUP BY p.product_id
        ORDER BY total_vendido DESC, p.create_date DESC
        LIMIT 4
    ");
    $stmt_vendidos->execute([':category_id' => $categoria['category_id']]);
    $productos = $stmt_vendidos->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($productos)) continue;
?>

<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>
            <i class="bi bi-trophy-fill text-warning"></i> 
            <?php echo htmlspecialchars($categoria['descripcion']); ?> - Más Vendidos
        </h3>
        <a href="tienda.php?categoria=<?php echo $categoria['category_id']; ?>" class="btn btn-outline-primary">
            Ver todos <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    
    <div class="row g-4">
        <?php foreach($productos as $producto): ?>
        <div class="col-md-3">
            <div class="card product-card h-100 shadow-sm">
                <?php if(!empty($producto['foto'])): ?>
                <img src="secciones/Productos/img/<?php echo htmlspecialchars($producto['foto']); ?>" 
                     class="card-img-top" 
                     alt="<?php echo htmlspecialchars($producto['product_name']); ?>">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-bicycle" style="font-size: 80px; color: #ccc;"></i>
                </div>
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($producto['product_name']); ?></h5>
                    <p class="card-text text-muted mb-2">
                        <small><i class="bi bi-calendar3"></i> Año: <?php echo htmlspecialchars($producto['model_year']); ?></small>
                    </p>
                    <?php if ($producto['total_vendido'] > 0): ?>
                    <p class="card-text mb-2">
                        <small class="text-success">
                            <i class="bi bi-graph-up"></i> <?php echo $producto['total_vendido']; ?> vendidos
                        </small>
                    </p>
                    <?php endif; ?>
                    <p class="price-tag mb-3">Bs. <?php echo number_format($producto['price'], 2); ?></p>
                    
                    <button class="btn btn-add-cart w-100 mt-auto" 
                            onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>, '<?php echo htmlspecialchars($producto['product_name']); ?>', <?php echo $producto['price']; ?>, '<?php echo htmlspecialchars($producto['foto']); ?>')">
                        <i class="bi bi-cart-plus"></i> Agregar
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php endforeach; ?>

<!-- Call to Action -->
<div class="text-center py-5 bg-light rounded mb-5">
    <h3 class="mb-3">¿Listo para comenzar?</h3>
    <p class="text-muted mb-4">Crea tu cuenta y comienza a comprar</p>
    <a href="registro.php" class="btn btn-primary btn-lg me-2">
        <i class="bi bi-person-plus"></i> Registrarse
    </a>
    <a href="tienda.php" class="btn btn-outline-primary btn-lg">
        <i class="bi bi-shop"></i> Ver Tienda
    </a>
</div>

<script>
/**
 * Función para agregar producto al carrito (localStorage)
 */
function agregarAlCarrito(productId, productName, price, foto) {
    // Obtener carrito actual
    let carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    
    // Buscar si el producto ya existe
    const itemIndex = carrito.findIndex(item => item.product_id === productId);
    
    if (itemIndex > -1) {
        // Producto existe, aumentar cantidad
        carrito[itemIndex].cantidad += 1;
    } else {
        // Producto nuevo, agregarlo
        carrito.push({
            product_id: productId,
            product_name: productName,
            price: price,
            foto: foto,
            cantidad: 1
        });
    }
    
    // Guardar en localStorage
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
    
    // Actualizar contador del carrito
    actualizarContadorCarrito();
    
    // Mostrar notificación (opcional)
    mostrarNotificacion('Producto agregado al carrito', 'success');
}

/**
 * Mostrar notificación toast
 */
function mostrarNotificacion(mensaje, tipo = 'success') {
    // Crear elemento de notificación
    const notif = document.createElement('div');
    notif.className = `alert alert-${tipo} position-fixed top-0 end-0 m-3`;
    notif.style.zIndex = '9999';
    notif.innerHTML = `
        <i class="bi bi-check-circle-fill"></i> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notif);
    
    // Auto-eliminar después de 3 segundos
    setTimeout(() => {
        notif.remove();
    }, 3000);
}
</script>

<?php include("templates/footer.php"); ?>
