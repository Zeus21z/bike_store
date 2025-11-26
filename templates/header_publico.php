<?php
$url_base = "http://localhost/bike_store/";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$esta_logueado = isset($_SESSION['usuario']);
$es_cliente = ($esta_logueado && ($_SESSION['tipo_usuario'] ?? '') === 'cliente');
$es_admin = ($esta_logueado && ($_SESSION['tipo_usuario'] ?? '') === 'admin');
?>
<!doctype html>
<html lang="es">

<head>
    <title>Bike Store - Tu tienda de bicicletas</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Tienda de bicicletas y accesorios - Las mejores marcas al mejor precio">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Tema verde -->
    <style>
        :root {
            --brand-50: #f0fdf4;
            --brand-100: #dcfce7;
            --brand-200: #bbf7d0;
            --brand-300: #86efac;
            --brand-400: #4ade80;
            --brand-500: #1a8a43ff;
            --brand-600: #56c6ceff;
            --brand-700: #20a14fff;
            --brand-800: #166534;
            --brand-900: #14532d;
        }

        /* Navbar verde */
        .navbar.bs-green {
            background: linear-gradient(90deg, var(--brand-700), var(--brand-600)) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar.bs-green .nav-link,
        .navbar.bs-green .navbar-brand,
        .navbar.bs-green .navbar-text {
            color: #fff !important;
        }

        .navbar.bs-green .nav-link:hover,
        .navbar.bs-green .nav-link:focus {
            color: #fff !important;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .navbar.bs-green .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Botón principal verde */
        .btn-primary {
            background-color: var(--brand-500);
            border-color: var(--brand-600);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--brand-600);
            border-color: var(--brand-700);
        }

        .text-brand {
            color: var(--brand-600) !important;
        }

        /* Badge del carrito */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        /* Hero section */
        .hero-section {
            background: linear-gradient(135deg, var(--brand-600) 0%, var(--brand-700) 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }

        /* Cards de productos */
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .product-card .card-img-top {
            height: 200px;
            object-fit: contain;
            padding: 15px;
            background-color: #f8f9fa;
        }

        /* Precio destacado */
        .price-tag {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--brand-600);
        }

        /* Botón agregar al carrito */
        .btn-add-cart {
            background-color: var(--brand-500);
            color: white;
            border: none;
            transition: all 0.3s;
        }

        .btn-add-cart:hover {
            background-color: var(--brand-600);
            color: white;
            transform: scale(1.05);
        }

        .btn-add-cart.added {
            background-color: #28a745;
        }
    </style>
</head>

<body>
    <header>
        <!-- Navbar público/cliente -->
        <nav class="navbar navbar-expand-lg navbar-dark bs-green sticky-top">
            <div class="container-fluid">
                <!-- Logo/Marca -->
                <a class="navbar-brand fw-bold" href="<?php echo $url_base; ?>">
                    <i class="bi bi-bicycle"></i> Bike Store
                </a>

                <!-- Toggle para móvil -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPublic">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Menú -->
                <div class="collapse navbar-collapse" id="navbarPublic">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $url_base; ?>">
                                <i class="bi bi-house-door"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $url_base; ?>tienda.php">
                                <i class="bi bi-shop"></i> Tienda
                            </a>
                        </li>
                        <?php if ($esta_logueado): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $url_base; ?>pedidos.php">
                                <i class="bi bi-receipt"></i> Mis Pedidos
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Usuario y carrito -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Carrito -->
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="<?php echo $url_base; ?>carrito.php">
                                <i class="bi bi-cart3" style="font-size: 1.3rem;"></i>
                                <span class="cart-badge" id="carrito-contador" style="display: none;">0</span>
                            </a>
                        </li>

                        <?php if ($esta_logueado): ?>
                            <!-- Usuario logueado -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-person-circle"></i> 
                                    <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo $url_base; ?>perfil.php">
                                            <i class="bi bi-person"></i> Mi Perfil
                                        </a>
                                    </li>
                                    <?php if ($es_admin): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo $url_base; ?>index_admin.php">
                                            <i class="bi bi-speedometer2"></i> Panel Admin
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="<?php echo $url_base; ?>cerrar.php">
                                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <!-- Usuario NO logueado -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo $url_base; ?>login.php">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-outline-light btn-sm ms-2" href="<?php echo $url_base; ?>registro.php">
                                    <i class="bi bi-person-plus"></i> Registrarse
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4">

    <script>
    // Actualizar contador del carrito al cargar
    function actualizarContadorCarrito() {
        const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
        const totalItems = carrito.reduce((sum, item) => sum + (item.cantidad || 1), 0);
        
        const contador = document.getElementById('carrito-contador');
        if (contador) {
            contador.textContent = totalItems;
            contador.style.display = totalItems > 0 ? 'inline-block' : 'none';
        }
    }

    // Actualizar al cargar página
    document.addEventListener('DOMContentLoaded', actualizarContadorCarrito);

    // Actualizar cuando cambie el localStorage (desde otra pestaña)
    window.addEventListener('storage', function(e) {
        if (e.key === 'carrito') {
            actualizarContadorCarrito();
        }
    });
    </script>