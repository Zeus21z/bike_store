<?php
include("../../bd.php");
require_once('../../libs/tcpdf/tcpdf.php');

// Detectar si existe columna foto
$tieneFoto = false;
try{
    $d = $conexion->prepare("DESCRIBE clientes");
    $d->execute();
    $cols = $d->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) { 
        if($col['Field'] === 'foto') { 
            $tieneFoto = true; 
            break; 
        } 
    }
} catch(Exception $e) { 
    $tieneFoto = false; 
}

// Listado de clientes
$sentencia = $conexion->prepare("SELECT * FROM clientes ORDER BY cliente_id ASC");
$sentencia->execute();
$lista_clientes = $sentencia->fetchAll(PDO::FETCH_ASSOC);

// Crear nuevo PDF en orientación horizontal
$pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator('Sistema de Clientes');
$pdf->SetAuthor('Bike Store');
$pdf->SetTitle('Lista de Clientes');
$pdf->SetSubject('Reporte de Clientes');

// Configurar márgenes más pequeños para más espacio
$pdf->SetMargins(10, 15, 10);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// Agregar página
$pdf->AddPage();

// Título principal
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'BIKE STORE - LISTA DE CLIENTES', 0, 1, 'C');
$pdf->Ln(3);

// Información del reporte
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 6, 'Fecha de generación: ' . date('d/m/Y H:i:s'), 0, 1, 'L');
$pdf->Cell(0, 6, 'Total de clientes: ' . count($lista_clientes), 0, 1, 'L');
$pdf->Ln(5);

// Crear tabla con tamaño más grande y mejor alineación
$html = '
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px; /* Tamaño de fuente más grande */
        font-family: helvetica;
    }
    th {
        background-color: #4a6572;
        color: white;
        font-weight: bold;
        padding: 8px 4px; /* Más padding */
        border: 1px solid #344955;
        text-align: center;
        font-size: 11px;
        white-space: nowrap; /* ← LÍNEA AGREGADA AQUÍ */
    }
    td {
        padding: 7px 4px; /* Más padding */
        border: 1px solid #dddddd;
        text-align: left;
        font-size: 10px;
    }
    tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    .centrado {
        text-align: center;
    }
    .id-col {
        width: 112px; /* Ancho fijo para ID */
        text-align: center;
    }
    .telefono-col {
        width: 112px; /* Ancho fijo para teléfono */
        text-align: center;
    }
    .ciudad-col, .estado-col {
        width: 112px; /* Ancho fijo para ciudad/estado */
        text-align: center;
    }
</style>

<table>
    <thead>
        <tr>
            <th class="id-col">ID</th>
            <th>NOMBRE</th>
            <th class="telefono-col">TELÉFONO</th>
            <th>CORREO</th>
            <th>DIRECCIÓN</th>
            <th class="ciudad-col">CIUDAD</th>
            <th class="estado-col">DEPARTAMENTO</th>
        </tr>
    </thead>
    <tbody>';

if (empty($lista_clientes)) {
    $html .= '
        <tr>
            <td colspan="7" style="text-align: center; padding: 20px; font-style: italic; font-size: 11px;">
                No hay clientes registrados en el sistema
            </td>
        </tr>';
} else {
    foreach($lista_clientes as $c) {
        $html .= '
        <tr>
            <td class="centrado" style="font-weight: bold;">' . $c['cliente_id'] . '</td>
            <td style="font-weight: bold;">' . htmlspecialchars(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? '')) . '</td>
            <td class="centrado">' . htmlspecialchars($c['telefono'] ?? 'N/A') . '</td>
            <td>' . htmlspecialchars($c['correo'] ?? 'Sin correo') . '</td>
            <td>' . htmlspecialchars($c['calle'] ?? 'Sin dirección') . '</td>
            <td class="centrado">' . htmlspecialchars($c['ciudad'] ?? '') . '</td>
            <td class="centrado">' . htmlspecialchars($c['estado'] ?? '') . '</td>
        </tr>';
    }
}

$html .= '
    </tbody>
</table>';

// Escribir contenido HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Pie de página
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 9);
$pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages() . ' | Sistema Bike Store', 0, false, 'C', 0, '', 0, false, 'T', 'M');

// Salida del PDF
$pdf->Output('clientes_bike_store_' . date('Y-m-d_H-i') . '.pdf', 'I');
?>