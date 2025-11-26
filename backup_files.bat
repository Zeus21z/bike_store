@echo off
REM ============================================
REM BIKE STORE - Script de Backup Automático
REM Versión: 1.0
REM Fecha: 2025-11-25
REM ============================================

echo.
echo ================================================
echo  BIKE STORE - SISTEMA DE BACKUP AUTOMATICO
echo ================================================
echo.

REM Obtener fecha y hora actual
set TIMESTAMP=%date:~-4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%

REM Directorio del proyecto
set PROJECT_DIR=C:\xampp\htdocs\bike_store

REM Directorio de backups
set BACKUP_DIR=%PROJECT_DIR%\backups
set BACKUP_FOLDER=%BACKUP_DIR%\backup_%TIMESTAMP%

REM Crear directorio de backups si no existe
if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
    echo [OK] Directorio de backups creado: %BACKUP_DIR%
)

REM Crear carpeta específica para este backup
mkdir "%BACKUP_FOLDER%"
echo [OK] Creando backup en: %BACKUP_FOLDER%
echo.

REM ============================================
REM BACKUP DE ARCHIVOS PHP PRINCIPALES
REM ============================================
echo [PASO 1] Respaldando archivos PHP principales...

if exist "%PROJECT_DIR%\bd.php" (
    copy "%PROJECT_DIR%\bd.php" "%BACKUP_FOLDER%\bd.php.bak" >nul
    echo   - bd.php [OK]
)

if exist "%PROJECT_DIR%\login.php" (
    copy "%PROJECT_DIR%\login.php" "%BACKUP_FOLDER%\login.php.bak" >nul
    echo   - login.php [OK]
)

if exist "%PROJECT_DIR%\index.php" (
    copy "%PROJECT_DIR%\index.php" "%BACKUP_FOLDER%\index.php.bak" >nul
    echo   - index.php [OK]
)

if exist "%PROJECT_DIR%\index_admin.php" (
    copy "%PROJECT_DIR%\index_admin.php" "%BACKUP_FOLDER%\index_admin.php.bak" >nul
    echo   - index_admin.php [OK]
)

if exist "%PROJECT_DIR%\index_cliente.php" (
    copy "%PROJECT_DIR%\index_cliente.php" "%BACKUP_FOLDER%\index_cliente.php.bak" >nul
    echo   - index_cliente.php [OK]
)

if exist "%PROJECT_DIR%\carrito.php" (
    copy "%PROJECT_DIR%\carrito.php" "%BACKUP_FOLDER%\carrito.php.bak" >nul
    echo   - carrito.php [OK]
)

if exist "%PROJECT_DIR%\procesar_pago.php" (
    copy "%PROJECT_DIR%\procesar_pago.php" "%BACKUP_FOLDER%\procesar_pago.php.bak" >nul
    echo   - procesar_pago.php [OK]
)

if exist "%PROJECT_DIR%\factura.php" (
    copy "%PROJECT_DIR%\factura.php" "%BACKUP_FOLDER%\factura.php.bak" >nul
    echo   - factura.php [OK]
)

if exist "%PROJECT_DIR%\generar_factura_pdf.php" (
    copy "%PROJECT_DIR%\generar_factura_pdf.php" "%BACKUP_FOLDER%\generar_factura_pdf.php.bak" >nul
    echo   - generar_factura_pdf.php [OK]
)

if exist "%PROJECT_DIR%\enviar_factura_email.php" (
    copy "%PROJECT_DIR%\enviar_factura_email.php" "%BACKUP_FOLDER%\enviar_factura_email.php.bak" >nul
    echo   - enviar_factura_email.php [OK]
)

echo.

REM ============================================
REM BACKUP DE TEMPLATES
REM ============================================
echo [PASO 2] Respaldando templates...

if exist "%PROJECT_DIR%\templates" (
    xcopy "%PROJECT_DIR%\templates" "%BACKUP_FOLDER%\templates\" /E /I /Y >nul
    echo   - templates [OK]
)

echo.

REM ============================================
REM BACKUP DE SECCIONES CRÍTICAS
REM ============================================
echo [PASO 3] Respaldando secciones...

if exist "%PROJECT_DIR%\secciones" (
    xcopy "%PROJECT_DIR%\secciones" "%BACKUP_FOLDER%\secciones\" /E /I /Y >nul
    echo   - secciones [OK]
)

echo.

REM ============================================
REM BACKUP DE BASE DE DATOS
REM ============================================
echo [PASO 4] Respaldando base de datos...

REM Ruta de mysqldump en XAMPP
set MYSQLDUMP="C:\xampp\mysql\bin\mysqldump.exe"

REM Verificar si mysqldump existe
if exist %MYSQLDUMP% (
    REM Ejecutar backup de BD
    %MYSQLDUMP% --user=root --password= --host=localhost zeta > "%BACKUP_FOLDER%\zeta_database.sql" 2>nul
    
    if %ERRORLEVEL% EQU 0 (
        echo   - Base de datos 'zeta' [OK]
    ) else (
        echo   - Base de datos 'zeta' [ERROR - Verificar MySQL]
    )
) else (
    echo   - mysqldump no encontrado [SKIP]
    echo     Ubicacion esperada: C:\xampp\mysql\bin\mysqldump.exe
)

echo.

REM ============================================
REM CREAR ARCHIVO DE INFORMACIÓN DEL BACKUP
REM ============================================
echo [PASO 5] Creando archivo de información...

(
    echo ================================================
    echo BIKE STORE - INFORMACION DEL BACKUP
    echo ================================================
    echo.
    echo Fecha: %date%
    echo Hora: %time%
    echo Usuario: %USERNAME%
    echo Computadora: %COMPUTERNAME%
    echo.
    echo Directorio del proyecto: %PROJECT_DIR%
    echo Directorio del backup: %BACKUP_FOLDER%
    echo.
    echo ================================================
    echo ARCHIVOS RESPALDADOS:
    echo ================================================
    echo.
    dir "%BACKUP_FOLDER%" /B
    echo.
    echo ================================================
    echo NOTAS:
    echo ================================================
    echo - Este backup fue creado automaticamente
    echo - Los archivos .bak son copias exactas de los originales
    echo - El archivo .sql contiene el backup de la base de datos
    echo - Para restaurar, copie los archivos .bak al directorio principal
    echo   y quite la extension .bak
    echo.
) > "%BACKUP_FOLDER%\backup_info.txt"

echo   - backup_info.txt [OK]
echo.

REM ============================================
REM LIMPIAR BACKUPS ANTIGUOS (más de 30 días)
REM ============================================
echo [PASO 6] Limpiando backups antiguos...

forfiles /P "%BACKUP_DIR%" /M backup_* /D -30 /C "cmd /c if @isdir==TRUE rmdir /S /Q @path" 2>nul

if %ERRORLEVEL% EQU 0 (
    echo   - Backups antiguos eliminados [OK]
) else (
    echo   - No hay backups antiguos para eliminar [OK]
)

echo.

REM ============================================
REM RESUMEN FINAL
REM ============================================
echo ================================================
echo  BACKUP COMPLETADO EXITOSAMENTE
echo ================================================
echo.
echo Ubicacion del backup:
echo %BACKUP_FOLDER%
echo.
echo Archivos respaldados:
dir "%BACKUP_FOLDER%" | find "archivo(s)"
echo.
echo Tamano total del backup:
powershell -command "(Get-ChildItem -Path '%BACKUP_FOLDER%' -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB"
echo MB
echo.
echo ================================================
echo  IMPORTANTE:
echo ================================================
echo.
echo - El backup incluye archivos PHP y base de datos
echo - Guarde este backup en un lugar seguro
echo - Para restaurar, copie los archivos .bak y quite la extension
echo - Para restaurar la BD, importe el archivo .sql en phpMyAdmin
echo.

pause

REM ============================================
REM ABRIR CARPETA DE BACKUP
REM ============================================
explorer "%BACKUP_FOLDER%"