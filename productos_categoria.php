<?php include("bd.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<?php include("templates/header_cliente.php"); ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Nuestros Productos</h1>
        <a href="index_cliente.php" class="btn btn-outline-primary">
            <i class="bi bi-house-door"></i> Volver al Inicio
        </a>
    </div>
    
    <?php
    // Obtener todas las categorías ordenadas A-Z
    $stmt_categorias = $conexion->prepare("SELECT * FROM categorias ORDER BY descripcion ASC");
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($categorias as $categoria):
        // Obtener productos de esta categoría
        $stmt_productos = $conexion->prepare("SELECT * FROM productos WHERE category_id = :category_id ORDER BY product_name ASC");
        $stmt_productos->bindParam(':category_id', $categoria['category_id']);
        $stmt_productos->execute();
        $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);
        
        if(!empty($productos)):
    ?>
    
    <div class="mb-5">
        <h2 class="border-bottom pb-2 mb-4"><?php echo htmlspecialchars($categoria['descripcion']); ?></h2>
        
        <div class="row">
            <?php foreach($productos as $producto): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if(!empty($producto['foto'])): ?>
                    <img src="secciones/Productos/img/<?php echo htmlspecialchars($producto['foto']); ?>" class="card-img-top" style="height: 200px; object-fit: contain; padding: 10px;" alt="<?php echo htmlspecialchars($producto['product_name']); ?>">
                    <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                        <i class="bi bi-bicycle" style="font-size: 80px; color: #ccc;"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($producto['product_name']); ?></h5>
                        <p class="card-text mb-2">
                            <small class="text-muted">Modelo <?php echo htmlspecialchars($producto['model_year']); ?></small>
                        </p>
                        <div class="mb-2">
                            <span class="text-success fw-bold fs-4">$<?php echo number_format($producto['price'], 2); ?></span>
                            <?php if($producto['price'] > 500): ?>
                            <span class="badge bg-danger ms-2">Descuento 10%</span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary mt-auto" onclick="agregarAlCarrito(<?php echo $producto['product_id']; ?>, '<?php echo htmlspecialchars($producto['product_name']); ?>', <?php echo $producto['price']; ?>)">
                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php 
        endif;
    endforeach; 
    ?>
</div>

<script>
function agregarAlCarrito(productId, productName, price) {
    let carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    
    const itemExistente = carrito.findIndex(item => item.product_id === productId);
    
    if (itemExistente > -1) {
        carrito[itemExistente].cantidad += 1;
    } else {
        carrito.push({
            product_id: productId,
            product_name: productName,
            price: price,
            cantidad: 1
        });
    }
    
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Mostrar notificación
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Agregado';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 1500);
    
    // Actualizar contador del carrito
    actualizarContadorCarritoNav();
}

function actualizarContadorCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    const totalItems = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    
    const contador = document.getElementById('carrito-contador');
    if (contador) {
        contador.textContent = totalItems;
        contador.style.display = totalItems > 0 ? 'inline-block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', actualizarContadorCarrito);
</script>

<?php include("templates/footer.php"); ?>
