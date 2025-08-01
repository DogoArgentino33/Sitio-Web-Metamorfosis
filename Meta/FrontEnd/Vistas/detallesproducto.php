<?php session_start(); include('conexion.php'); 

$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? null;

// Validaciones opcionales:
if (!$id || !$tipo) 
{
    // Redirigir o mostrar error si faltan datos
}

$datos = null;

if ($id && $tipo) 
{
    $stmt = $conexion->prepare("
        SELECT 
            p.id,
            p.nombre,
            p.tipo,
            p.unidades_disponibles,
            p.precio,
            p.fechamod,
            p.usumod,
            GROUP_CONCAT(DISTINCT c.nombre_cat SEPARATOR ', ') AS categorias,
            GROUP_CONCAT(DISTINCT t.talla SEPARATOR ', ') AS tallas,
            GROUP_CONCAT(DISTINCT tm.nombre_tema SEPARATOR ', ') AS tematicas,
            (SELECT ip.img FROM img_producto ip WHERE ip.id_producto = p.id LIMIT 1) AS imagenes
        FROM producto p
        LEFT JOIN producto_categoria pc ON pc.id_producto = p.id
        LEFT JOIN categoria c ON c.id = pc.id_categoria
        LEFT JOIN producto_talla pt ON pt.id_producto = p.id
        LEFT JOIN talla t ON t.id = pt.id_talla
        LEFT JOIN producto_tematica ptem ON ptem.id_producto = p.id
        LEFT JOIN tematica tm ON tm.id = ptem.id_tematica
        WHERE p.id = ? AND p.tipo = ?
        GROUP BY p.id
    ");

    $stmt->bind_param("ii", $id, $tipo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos = $resultado->fetch_assoc();
    $stmt->close();
}

$imagenes = [];

if ($datos) {
    $stmtImgs = $conexion->prepare("SELECT img FROM img_producto WHERE id_producto = ?");
    $stmtImgs->bind_param("i", $datos['id']);
    $stmtImgs->execute();
    $resImgs = $stmtImgs->get_result();

    while ($row = $resImgs->fetch_assoc()) {
        $imagenes[] = $row['img'];
    }

    $stmtImgs->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alquilar'])) {
    
    // Obtener datos del formulario
    $id_usuario = $_SESSION['id'];
    $id_producto = $_POST['id_producto'];
    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    $cantidad = intval($_POST['cantidad']);
    $metodo_pago = $_POST['metodo_pago'];
    $total = floatval($_POST['total']);
    $fechamod = date('Y-m-d H:i:s'); // Fecha completa
    $usumod = $id_usuario;

    // Validaciones básicas
    if (!$id_producto || !$desde || !$hasta || $cantidad <= 0 || !$metodo_pago || $total <= 0) {
        die("Datos inválidos o incompletos.");
    }

    // Obtener ID del método de pago desde la base de datos
    $stmt = $conexion->prepare("SELECT id FROM metodo_pago WHERE nombre = ? AND activo = 1");
    $stmt->bind_param("s", $metodo_pago);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_metodopago = (int)$row['id'];  // Convertir a entero
    } else {
        die("Método de pago inválido o no disponible.");
    }

    $stmt->close();

    // Validación para métodos que requieren tarjeta
    if ($id_metodopago === 3 || $id_metodopago === 4) {
        if (empty($_POST['cardNumber']) || empty($_POST['cardExpiry']) || empty($_POST['cardCVV'])) {
            die("Debes completar los datos de tarjeta para este método de pago.");
        }

        // Validaciones adicionales para los datos de tarjeta
        if (!preg_match('/^\d{16}$/', $_POST['cardNumber'])) {
            die("Número de tarjeta inválido.");
        }
        if (!preg_match('/^\d{3}$/', $_POST['cardCVV'])) {
            die("CVV inválido.");
        }
    }

    // Insertar en tabla alquiler
    $stmt = $conexion->prepare("INSERT INTO alquiler (id_usuario, id_producto, desde, hasta, cantidad, total, id_metodopago, fechamod, usumod)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissidisi", $id_usuario, $id_producto, $desde, $hasta, $cantidad, $total, $id_metodopago, $fechamod, $usumod);
    $stmt->execute();
    $stmt->close();

    // Redirige para evitar reenvío de formulario y mostrar SweetAlert
    header("Location: detallesproducto.php?id=$id_producto&tipo=$tipo&exito=1");
    exit;
}

    $exito = false;
    if (isset($_GET['exito']) && $_GET['exito'] == 1) {
        $exito = true;
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS -->
    <link rel="stylesheet" href="../Estilos/infoProducto.css">
    <link rel="stylesheet" href="../Estilos/modales.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const usuarioLogueado = <?= isset($_SESSION['id']) ? 'true' : 'false' ?>;
    </script>

</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <!-- NAV -->
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <?php if ($tipo == 1): ?>
                <a href="disfraces.php">Disfraces / </a>
            <?php elseif ($tipo == 2): ?>
                <a href="accesorios.php">Accesorios / </a>
            <?php endif; ?>
            <?php if ($tipo == 1): ?>
                <a href="disfraces.php">Detalles del Disfraz / </a>
            <?php elseif ($tipo == 2): ?>
                <a href="accesorios.php">Detalles del Accesorio / </a>
            <?php endif; ?>
        </section>

        <!-- Determinando el tipo del producto -->
        <?php if ($tipo == 1): ?>
            <h2 style="text-align: center; color: black;">Informacion del Disfraz</h2>
        <?php elseif ($tipo == 2): ?>
            <h2 style="text-align: center; color: black;">Informacion del Accesorio</h2>
        <?php endif; ?>

       <!-- Contenedor -->
        <section class="contenedor-producto">
            <?php if ($datos): ?>
            <section class="product-layout">
                
                <!-- Galería de imágenes -->
                <section class="slider">
                    <?php if (!empty($imagenes)): ?>
                        <div class="slides">
                            <?php foreach ($imagenes as $index => $img): ?>
                                <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="uploads/producto/<?= htmlspecialchars($img) ?>" alt="Imagen <?= $index + 1 ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($imagenes) > 1): ?>
                            <div class="slider-controls">
                                <button class="control-button" id="botonSliderPrev"><i class="bi bi-arrow-left-circle"></i></button>
                                <button class="control-button" id="botonSliderNext"><i class="bi bi-arrow-right-circle"></i></button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Este producto no tiene imágenes.</p>
                    <?php endif; ?>
                </section>

                <!-- Información del producto -->
                <section class="product-info">
                    <h2><?= htmlspecialchars($datos['nombre']) ?></h2>

                    <p><strong>Temática:</strong> <?= htmlspecialchars($datos['tematicas']) ?></p>
                    <p><strong>Categoría:</strong> <?= htmlspecialchars($datos['categorias']) ?></p>

                    <?php if ($tipo == 1): ?>
                        <p><strong>Talles:</strong> <?= htmlspecialchars($datos['tallas']) ?></p>
                    <?php endif; ?>

                    <p><strong>Precio:</strong> $<?= htmlspecialchars($datos['precio']) ?></p>
                    <p><strong>Disponible:</strong> <?= $datos['unidades_disponibles'] > 0 ? 'Disponible' : 'No disponible' ?></p>
                    <p><strong>Unidades Disponibles:</strong> <?= htmlspecialchars($datos['unidades_disponibles']) ?></p>

                    <button type="button" class="btn" id="btn-alquilar" onclick="openModal('<?= htmlspecialchars($datos['nombre']) ?>')">Alquilar</button>
                </section>

            </section>
            <?php else: ?>
                <p>No se encontró información del producto.</p>
            <?php endif; ?>
        </section>

    </main>

    <section id="rentalModal" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h1>Alquilar</h1>
            <form id="rentalForm" method="POST">

                <?php if ($tipo == 1): ?>
                    <h2>Datos del Disfraz</h2>
                <?php elseif ($tipo == 2): ?>
                    <h2>Datos del Accesorio</h2>
                <?php endif; ?>

                <input type="hidden" name="id_producto" value="<?= htmlspecialchars($datos['id']) ?>">

                <label for="costume">Nombre:</label>
                <input type="text" id="costume" name="costume" readonly disabled>
    
                <label for="theme">Temática:</label>
                <input type="text" id="theme" name="theme" readonly disabled>
    
                <label for="category">Categoría:</label>
                <input type="text" id="category" name="category" readonly disabled>

                <label for="stock">Cantidad Disponible:</label>
                <input type="text" id="stock" name="stock" readonly disabled>
    
                <!-- Datos del aquiler: no son readonly -->
                <h2>Datos del Alquiler</h2>

                <!-- Desde y hasta -->
                <label for="desde">Fecha Desde...</label>
                <input type="date" id="desde" name="desde" required min="<?= $hoy ?>">

                <label for="hasta">Fecha Hasta...</label>
                <input type="date" id="hasta" name="hasta" required>

                <label for="cantidad">Cantidad de unidades a Alquilar...</label>
                <input type="number" id="cantidad" name="cantidad" min="1" required>
    
                <h2>Métodos de Pago</h2>
                <?php
                $query = "SELECT id, nombre, requiere_tarjeta FROM metodo_pago WHERE activo = 1";
                $result = $conexion->query($query);
                ?>

                <section class="metodo_pago">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <section>
                            <input 
                            type="radio" 
                            data-requiere-tarjeta="<?= $row['requiere_tarjeta'] ? 'true' : 'false' ?>" 
                            id="<?= strtolower($row['nombre']) ?>" 
                            name="metodo_pago" 
                            value="<?= $row['nombre'] ?>" 
                            onclick="togglePaymentFields()" 
                            <?= ($row['nombre'] == 'Efectivo' ? 'checked' : '') ?>>
                            <label for="<?= strtolower($row['nombre']) ?>"><?= htmlspecialchars($row['nombre']) ?></label>
                        </section>
                        <section id="cardDetails" style="display: none;">
                            <h3>Detalles de la Tarjeta</h3>
                            <label for="cardNumber">Número de Tarjeta:</label><br>
                            <input type="text" id="cardNumber" name="cardNumber" maxlength="16"><br>

                            <label for="cardExpiry">Fecha de Expiración:</label><br>
                            <input type="month" id="cardExpiry" name="cardExpiry"><br>

                            <label for="cardCVV">CVV:</label><br>
                            <input type="text" id="cardCVV" name="cardCVV" maxlength="3"><br>
                        </section>
                        <br>
                    <?php endwhile; ?>
                </section>

                <input type="hidden" id="inputTotal" name="total">
                <p><strong>TOTAL: $<span id="displayTotal">0</span></strong></p>
    
                <button type="submit" name="alquilar" value="1">Enviar</button>
            </form>
        </section>
    </section>
    
    <?php include('footer.php');?>

    <script>
        function openModal(costumeName) {
            if (usuarioLogueado) {
                const card = document.querySelector('.product-info');
                const theme = card.querySelector('p:nth-of-type(1)').innerText.replace('Temática: ', '');
                const category = card.querySelector('p:nth-of-type(2)').innerText.replace('Categoría: ', '');
                const stock = card.querySelector('p:nth-of-type(<?= $tipo == 1 ? 5 : 4 ?>)').innerText.replace('Unidades Disponibles: ', '');

                document.getElementById('rentalModal').style.display = 'block';
                document.getElementById('costume').value = costumeName;
                document.getElementById('theme').value = theme;
                document.getElementById('category').value = category;
                document.getElementById('stock').value = stock;
            }

            function closeModal() {
                document.getElementById('rentalModal').style.display = 'none';
            }
        
            // Cierra el modal si se hace click fuera del modal
            window.onclick = function(event) 
            {
                const modal = document.getElementById('rentalModal');
                if (event.target === modal) 
                    {
                    closeModal();
                    }
            }
        
            // Manejar el envío del formulario
            document.getElementById('rentalForm').onsubmit = function(event) 
            {
                return true; // Permite que el formulario se envíe
            }
                return;
        }
    </script>
        
    <script>
        if (!usuarioLogueado) {
            document.querySelectorAll('#btn-alquilar').forEach(link => {
                link.addEventListener('click', evt => {
                    evt.preventDefault();

                    Swal.fire({
                        title: 'Advertencia',
                        text: 'Necesitas estar registrado para alquilar este producto.',
                        icon: 'warning',
                        confirmButtonText: 'Registrarme.',
                        showDenyButton: true,
                        denyButtonText: 'Quizá más tarde.'
                    }).then(res => {
                        if (res.isConfirmed) {
                            window.location.href = 'registrarseusuario.php';
                        }
                    });
                });
            });
            
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const desdeInput = document.getElementById('desde');
            const hastaInput = document.getElementById('hasta');
            const cantidadInput = document.getElementById('cantidad');
            const totalInput = document.getElementById('inputTotal');
            const displayTotal = document.getElementById('displayTotal');

            const precioPorUnidad = <?= $datos['precio'] ?>;

            function calcularTotal() {
                const desde = new Date(desdeInput.value);
                const hasta = new Date(hastaInput.value);
                const cantidad = parseInt(cantidadInput.value) || 0;

                if (!isNaN(desde.getTime()) && !isNaN(hasta.getTime()) && cantidad > 0) {
                    const diffTime = hasta.getTime() - desde.getTime();
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    if (diffDays > 0) {
                        const total = diffDays * cantidad * precioPorUnidad;
                        totalInput.value = total;
                        displayTotal.textContent = total;
                    }
                }
            }

            desdeInput.addEventListener('change', calcularTotal);
            hastaInput.addEventListener('change', calcularTotal);
            cantidadInput.addEventListener('input', calcularTotal);
        });
    </script>

    <script>
        function togglePaymentFields() {
            const selected = document.querySelector('input[name="metodo_pago"]:checked');
            if (!selected) return;

            const requiereTarjeta = selected.getAttribute('data-requiere-tarjeta') === 'true';
            const tarjetaSection = document.getElementById('cardDetails');
            tarjetaSection.style.display = requiereTarjeta ? 'block' : 'none';
        }

        document.addEventListener("DOMContentLoaded", () => {
            togglePaymentFields(); // Llama al cargar
        });
    </script>

    <?php if ($exito): ?>
    <script>
        Swal.fire({
            title: '¡Éxito!',
            text: 'Alquiler registrado con éxito.',
            icon: 'success',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'detallesproducto.php?id=<?= $id ?>&tipo=<?= $tipo ?>';
        });
    </script>
    <?php endif; ?>

</body>
</html>