<?php
$url_base = "http://localhost/bike_store/";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tipo_usuario = $_SESSION['tipo_usuario'] ?? 'cliente';
$es_cliente = ($tipo_usuario === 'cliente');

$scriptActual = basename($_SERVER['PHP_SELF']);
$paginas_publicas = ['index_cliente.php', 'productos_categoria.php', 'carrito.php'];
if (!in_array($scriptActual, $paginas_publicas) && !isset($_SESSION['usuario'])) {
    header("Location: " . $url_base . "index_cliente.php");
    exit;
}
?>
<!doctype html>
<html lang="es">

<head>
    <title>Bike Store - Tienda Online</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Tema verde adaptable -->
    <style>
        :root {
            --brand-50: #f0fdf4;
            --brand-100: #dcfce7;
            --brand-200: #bbf7d0;
            --brand-300: #86efac;
            --brand-400: #4ade80;
            --brand-500: #167539ff;
            /* botón principal */
            --brand-600: #169645ff;
            /* barra */
            --brand-700: #15803d;
            --brand-800: #166534;
            --brand-900: #14532d;
        }

        .navbar.bs-green {
            background: linear-gradient(90deg, var(--brand-700), var(--brand-600)) !important;
        }

        .navbar.bs-green .nav-link,
        .navbar.bs-green .navbar-brand,
        .navbar.bs-green .navbar-text {
            color: #fff !important;
        }

        .navbar.bs-green .nav-link:hover,
        .navbar.bs-green .nav-link:focus,
        .navbar.bs-green .nav-link.active {
            color: #fff !important;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

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
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bs-green">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo $url_base; ?>index_cliente.php">
                    <i class="bi bi-bicycle"></i> Bike Store
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>index_cliente.php">Inicio</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="categoriaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Categorías</a>
                            <ul class="dropdown-menu" aria-labelledby="categoriaDropdown">
                                <?php
                                // NOTA: requiere $conexion (PDO) disponible desde el archivo que incluye este header
                                $stmt_categorias = $conexion->prepare("SELECT * FROM categorias ORDER BY descripcion ASC");
                                $stmt_categorias->execute();
                                $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($categorias as $cat): ?>
                                    <li><a class="dropdown-item" href="<?php echo $url_base; ?>productos_por_categoria.php?categoria_id=<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['descripcion']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>pedidos.php">Mis Pedidos</a></li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="<?php echo $url_base; ?>carrito.php">
                                <i class="bi bi-cart"></i> Carrito
                                <span id="carrito-contador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
                            </a>
                        </li>
                    </ul>
                    <ul class="navbar-nav">
                        <?php if (isset($_SESSION['usuario'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?php echo $url_base; ?>perfil.php">Mi Perfil</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="<?php echo $url_base; ?>cerrar.php">Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>login.php">Iniciar Sesión</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container-fluid px-4 py-4">
        <script>
            function actualizarContadorCarritoNav() {
                const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
                const totalItems = carrito.reduce((s, i) => s + (i.cantidad || 0), 0);
                const b = document.getElementById('carrito-contador');
                if (b) {
                    b.textContent = totalItems;
                    b.style.display = totalItems > 0 ? 'inline-block' : 'none';
                }
            }
            document.addEventListener('DOMContentLoaded', actualizarContadorCarritoNav);
        </script>