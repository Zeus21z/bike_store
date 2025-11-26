<?php
// Iniciar sesión y marcar que esta página no requiere redirección a login
session_start();
$no_auth_required = true;
include("templates/header.php");
?>
<br>
<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-bold">Welcome/Bienvenido</h1>
                <p class="fs-5 text-secondary mb-4">
                    Bienvenido a Bike Store
                </p>

            </div>

        </div>
    </div>

</div>
<?php include("templates/footer.php"); ?>