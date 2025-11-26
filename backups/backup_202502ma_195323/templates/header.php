<?php
$url_base = "http://localhost/bike_store/";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteger páginas de admin - solo verificar login
$scriptActual = basename($_SERVER['PHP_SELF']);
$paginas_publicas = ['login.php', 'index_cliente.php', 'productos_categoria.php', 'carrito.php', 'factura.php', 'cerrar.php'];
$paginas_admin    = ['index_admin.php'];

if (!in_array($scriptActual, $paginas_publicas) && !isset($_SESSION['usuario'])) {
    header("Location: " . $url_base . "login.php");
    exit;
}
?>
<!doctype html>
<html lang="es">

<head>
    <title>Bike_Store</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Tema verde adaptable + Dashboard BG -->
    <style>
        :root {
            --brand-50: #f0fdf4;
            --brand-100: #dcfce7;
            --brand-200: #bbf7d0;
            --brand-300: #86efac;
            --brand-400: #4ade80;
            --brand-500: #1a8a43ff;
            /* botón principal */
            --brand-600: #56c6ceff;
            /* barra */
            --brand-700: #20a14fff;
            --brand-800: #166534;
            --brand-900: #14532d;
        }

        /* Navbar verde */
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

        /* ===== Fondo especial Dashboard Admin =====
           Se aplica si el <body> tiene la clase .dashboard-bg */
        .dashboard-bg {
            /* Degradado más visible */
            background:
                radial-gradient(1200px 600px at 0% -20%, rgba(34, 197, 94, 0.22) 0%, rgba(34, 197, 94, 0.00) 60%),
                radial-gradient(900px 500px at 110% 10%, rgba(22, 163, 74, 0.20) 0%, rgba(22, 163, 74, 0.00) 60%),
                linear-gradient(180deg, #f1fff5 0%, #ffffff 55%, #f6fff9 100%);
            min-height: 100vh;
        }

        /* Patrón sutil para notar el cambio */
        .dashboard-bg::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                repeating-linear-gradient(45deg, rgba(22, 163, 74, 0.06) 0 10px, rgba(22, 163, 74, 0.00) 10px 20px);
            opacity: .35;
        }

        /* Dejar el container transparente para que se vea el fondo */
        .dashboard-bg .container {
            background: transparent;
        }

        /* Tarjetas con sombra suave en dashboard */
        .dashboard-bg .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(16, 24, 40, 0.06);
        }

        .dashboard-bg .card .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            border-top-left-radius: 14px;
            border-top-right-radius: 14px;
        }

        /* Navbar sticky en dashboard */
        .dashboard-bg .navbar.bs-green {
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
        }
    </style>
</head>

<!-- Clase para fondo de dashboard si estás en index_admin.php o el usuario es admin -->

<body class="<?php echo ($scriptActual === 'index_admin.php' || (($_SESSION['tipo_usuario'] ?? '') === 'admin')) ? 'dashboard-bg' : ''; ?>">
    <?php if ($scriptActual !== 'login.php') { ?>
        <header>
            <!-- Navbar -->
            <nav class="navbar navbar-expand navbar-dark bs-green">
                <ul class="nav navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo $url_base; ?><?php echo (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin') ? 'index_admin.php' : 'index_cliente.php'; ?>">Inicio</a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/categorias/">Categorias</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/clientes/">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/Productos/">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/pedidos/">Pedidos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/Usuarios/">Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/tiendas/">Tiendas</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>secciones/Inventario/">Inventario</a></li>
                </ul>
                <ul class="nav navbar-nav ms-auto">
                    <li class="nav-item d-flex align-items-center px-2">
                        <span class="navbar-text">Sesión:</span>
                        <span class="navbar-text ms-1 fw-semibold"><?php echo htmlspecialchars($_SESSION['usuario']['usuario'] ?? ($_SESSION['usuario'] ?? '')); ?></span>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $url_base; ?>cerrar.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </header>
    <?php } ?>
    <main class="container">