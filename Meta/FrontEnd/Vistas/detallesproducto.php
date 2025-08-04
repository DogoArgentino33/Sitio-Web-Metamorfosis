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

    $errores = [];
    
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

    // Validación de fechas
    if ($desde > $hasta) {
        $errores[] = "La fecha de inicio no puede ser posterior a la fecha de fin.";
    }

    $desdeDate = strtotime($desde);
    $hastaDate = strtotime($hasta);

    if ($hastaDate <= $desdeDate) {
        $errores[] = "Debe haber al menos un día completo entre la fecha de inicio y de fin del alquiler.";
    }

    $desdeDate = new DateTime($desde);
    $hastaDate = new DateTime($hasta);
    $intervalo = $desdeDate->diff($hastaDate);

    if ($intervalo->days < 1) {
        die("Debe haber al menos un día completo de alquiler.");
    }

    // Obtener ID del método de pago desde la base de datos
    $stmt = $conexion->prepare("SELECT id FROM metodo_pago WHERE nombre = ? AND activo = 1");
    $stmt->bind_param("s", $metodo_pago);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $id_metodopago = (int)$row['id'];  // Convertir a entero
    } else {
        $errores[] = "Método de pago inválido o no disponible.";
    }

    $stmt->close();

    // Validación para métodos que requieren tarjeta
    if ($id_metodopago === 3 || $id_metodopago === 4) {

        $cardNumber = $_POST['cardNumber'] ?? '';
        $cardExpiry = $_POST['cardExpiry'] ?? '';
        $cardCVV = $_POST['cardCVV'] ?? '';
        $cardDNI = preg_replace('/\D/', '', $_POST['dni'] ?? ''); // Solo números

        if (empty($cardNumber) || empty($cardExpiry) || empty($cardCVV) || empty($cardDNI)) {
            $errores[] = "Todos los campos de tarjeta son obligatorios.";
        }

        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            $errores[] = "Número de tarjeta inválido (16 dígitos requeridos).";
        }

        if (!preg_match('/^\d{3}$/', $cardCVV)) {
            $errores[] = "CVV inválido (3 dígitos requeridos).";
        }

        if (!preg_match('/^\d{7,8}$/', $cardDNI)) {
            $errores[] = "DNI inválido. Debe tener 7 u 8 dígitos.";
        } else {
            // Verificar existencia en persona y vínculo con usuario
            $sql = "SELECT id FROM persona WHERE dni = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $cardDNI);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $fila = $result->fetch_assoc();
                $id_persona = $fila['id'];

                $sql_usuario = "SELECT id FROM usuario WHERE id_persona = ?";
                $stmt_usuario = $conexion->prepare($sql_usuario);
                $stmt_usuario->bind_param("i", $id_persona);
                $stmt_usuario->execute();
                $result_usuario = $stmt_usuario->get_result();

                if (!$result_usuario || $result_usuario->num_rows === 0) {
                    $errores[] = "El DNI pertenece a una persona registrada, pero no tiene un usuario vinculado.";
                }

            } else {
                $errores[] = "El DNI ingresado no está registrado en el sistema.";
            }
        }

        // Validación de fecha de expiración
        $hoy = date('Y-m');
        if ($cardExpiry < $hoy) {
            $errores[] = "La tarjeta ya está vencida.";
        }
    }

    // SI HAY ERRORES, MOSTRARLOS
    if (!empty($errores)) {
        $_SESSION['errores_alquiler'] = $errores;
        header("Location: detallesproducto.php?id=$id_producto&tipo=$tipo&error=1");
        exit;
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

    <!-- Funcion del slider -->
    <script>
    document.addEventListener('DOMContentLoaded', () => 
    {
        let currentSlide    = 0;
        const slides        = document.querySelector('.slides');
        const intervalMs    = 5000; //Esto equivale a 5 segundos
        let timerId         = null;
        movimiento();  //Iniciamos el proceso

        // Iniciamos los botones  //
        document.getElementById('botonSliderPrev').addEventListener('click', () => 
        {
            CambioSlide(-1);
        });

        document.getElementById('botonSliderNext').addEventListener('click', () => 
        {
            CambioSlide(1);
        });

        // Agregamos el cambio de imagen //
        function CambioSlide(direction = 1) 
        { 
            const totalSlides = document.querySelectorAll('.slide').length;
            currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
            slides.style.transform = `translateX(-${currentSlide * 100}%)`;
        }

        // Agregamos movimiento //
        function movimiento() 
        {
            if (timerId) 
            {
                return;
            }                        
                timerId = setInterval(() => CambioSlide(1), intervalMs);
        }

        function pararmovimiento() 
        {
            clearInterval(timerId);
            timerId = null;
        }
    });
    </script>

    <style>
        .barra-validacion {
            height: 5px;
            margin-top: 3px;
            transition: background-color 0.3s ease;
        }

        .barra-validacion.valido {
            background-color: #4caf50;
        }

        .barra-validacion.invalido {
            background-color: #f44336;
        }

        .mensaje-validacion {
            font-size: 0.85em;
            margin-top: 2px;
        }

        .mensaje-validacion.valido {
            color: #4caf50;
        }

        .mensaje-validacion.invalido {
            color: #f44336;
        }
    </style>

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
                        <section class="slides">
                            <?php foreach ($imagenes as $index => $img): ?>
                                <section class="slide <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="uploads/producto/<?= htmlspecialchars($img) ?>" alt="Imagen <?= $index + 1 ?>">
                                </section>
                            <?php endforeach; ?>
                        </section>

                        <?php if (count($imagenes) > 1): ?>
                            <section class="slider-controls">
                                <button class="control-button" id="botonSliderPrev"><i class="bi bi-arrow-left-circle"></i></button>
                                <button class="control-button" id="botonSliderNext"><i class="bi bi-arrow-right-circle"></i></button>
                            </section>
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
                        <p><strong>Talle:</strong> <?= htmlspecialchars($datos['tallas']) ?></p>
                    <?php endif; ?>

                    <p><strong>Precio por unidad:</strong> $<?= htmlspecialchars($datos['precio']) ?></p>
                    <p><strong>Disponible:</strong> <?= $datos['unidades_disponibles'] > 0 ? 'Si' : 'No' ?></p>
                    <p><strong>Unidades Disponibles:</strong> <?= htmlspecialchars($datos['unidades_disponibles']) ?></p>

                    <button type="button" class="btn" id="btn-alquilar" onclick="openModal('<?= htmlspecialchars($datos['nombre']) ?>')">Alquilar</button>
                </section>

            </section>
            <?php else: ?>
                <p>No se encontró información del producto.</p>
            <?php endif; ?>
        </section>

    </main>

    <?php if (isset($_SESSION['errores_alquiler'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Errores en el formulario',
                    html: `<?= implode('<br>', array_map('addslashes', $_SESSION['errores_alquiler'])) ?>`,
                    confirmButtonText: 'Volver a corregir'
                });
            });
        </script>
        <?php unset($_SESSION['errores_alquiler']); ?>
    <?php endif; ?>

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
                <input type="text" id="costume" name="costume" value="<?= htmlspecialchars($datos['nombre']) ?>" readonly disabled>
    
                <label for="theme">Temática:</label>
                <input type="text" id="theme" name="theme" value="<?= htmlspecialchars($datos['tematicas']) ?>" readonly disabled>
    
                <label for="category">Categoría:</label>
                <input type="text" id="category" name="category" value="<?= htmlspecialchars($datos['categorias']) ?>" readonly disabled>

                <?php if ($tipo == 1): ?>
                        <label for="size">Talla:</label>
                        <input type="text" id="size" name="size" value="<?= htmlspecialchars($datos['tallas']) ?>" readonly disabled>
                <?php endif; ?>

                <label for="price">Precio por unidad:</label>
                <input type="text" id="price" name="price" value="$<?= htmlspecialchars($datos['precio']) ?>" readonly disabled>

                <label for="stock">Disponible:</label>
                <input type="text" id="stock" name="stock" value="<?= $datos['unidades_disponibles'] > 0 ? 'Si' : 'No' ?>" readonly disabled>

                <label for="stockDisponible">Unidades disponibles:</label>
                <input type="text" id="stockDisponible" name="stockDisponible" value="<?= htmlspecialchars($datos['unidades_disponibles']) ?>" readonly disabled>
    
                <!-- Datos del aquiler: no son readonly -->
                <h2>Datos del Alquiler</h2>

                <!-- Desde y hasta -->
                <label for="desde">Fecha Desde...</label>
                <input type="date" id="desde" name="desde">
                <div class="barra-validacion" id="barra-desde"></div>
                <div class="mensaje-validacion" id="mensaje-desde"></div>

                <label for="hasta">Fecha Hasta...</label>
                <input type="date" id="hasta" name="hasta" required>
                <div class="barra-validacion" id="barra-hasta"></div>
                <div class="mensaje-validacion" id="mensaje-hasta"></div>

                <label for="cantidad">Cantidad de unidades a Alquilar...</label>
                <input type="number" id="cantidad" name="cantidad" min="1" required>
                <div class="barra-validacion" id="barra-cantidad"></div>
                <div class="mensaje-validacion" id="mensaje-cantidad"></div>
    
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
                    <?php endwhile; ?>
                </section>

                <!-- SOLO UNA SECCIÓN DE DETALLES DE TARJETA -->
                <section id="cardDetails" style="display: none;">
                    <h3>Detalles de la Tarjeta</h3>

                    <label for="cardNumber">Número de Tarjeta:</label><br>
                    <input type="text" id="cardNumber" name="cardNumber" maxlength="16"><br>
                    <div class="barra-validacion" id="barra-cardNumber"></div>
                    <div class="mensaje-validacion" id="mensaje-cardNumber"></div>

                    <label for="cardExpiry">Fecha de Expiración:</label><br>
                    <input type="month" id="cardExpiry" name="cardExpiry"><br>
                    <div class="barra-validacion" id="barra-cardExpiry"></div>
                    <div class="mensaje-validacion" id="mensaje-cardExpiry"></div>

                    <label for="cardCVV">CVV:</label><br>
                    <input type="text" id="cardCVV" name="cardCVV" maxlength="3"><br>
                    <div class="barra-validacion" id="barra-cardCVV"></div>
                    <div class="mensaje-validacion" id="mensaje-cardCVV"></div>

                    <label for="dni">DNI del titular</label>
                    <input type="text" id="dni" name="dni" maxlength="8" autocomplete="off" inputmode="numeric">
                    <div class="barra-validacion" id="barra-dni"></div>
                    <div class="mensaje-validacion" id="mensaje-dni"></div>
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const getFormattedDate = (date) => {
            const year = date.getFullYear();
            const month = `${date.getMonth() + 1}`.padStart(2, '0');
            const day = `${date.getDate()}`.padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

            const campos = {
                desde: {
                    validate: () => {
                        const desdeInput = document.getElementById("desde").value;
                        const hastaInput = document.getElementById("hasta").value;

                        if (!desdeInput) return [false, "Debes seleccionar una fecha."];

                        const hoy = getFormattedDate(new Date());
                        const desde = desdeInput;
                        const hasta = hastaInput || null;

                        if (hasta && desde > hasta) return [false, "La fecha 'Desde' no puede ser posterior a 'Hasta'."];
                        if (desde < hoy) return [false, "La fecha 'Desde' no puede ser anterior al día de hoy."];

                        return [true, "Fecha válida."];
                    }
                },
                hasta: {
                    validate: () => {
                        const desde = new Date(document.getElementById("desde").value);
                        const hasta = new Date(document.getElementById("hasta").value);
                        const hoy = getFormattedDate(new Date()); 

                        if (isNaN(hasta.getTime())) return [false, "Debes seleccionar una fecha."];
                        if (desde && hasta < desde) return [false, "La fecha 'Hasta' no puede ser anterior a 'Desde'."];
                        if (hasta < hoy) return [false, "La fecha 'Hasta' debe ser posterior al día de hoy."];
                        
                        // Nueva condición:
                        const diferenciaEnMs = hasta - desde;
                        const unDia = 24 * 60 * 60 * 1000;
                        if (diferenciaEnMs < unDia) return [false, "Debe haber al menos 1 día de diferencia."];

                        return [true, "Fecha válida."];
                    }
                },
                cantidad: {
                    validate: () => {
                        const cantidad = parseInt(document.getElementById("cantidad").value);
                        const stock = parseInt(document.getElementById("stockDisponible").value);
                        if (isNaN(cantidad) || cantidad < 1) return [false, "Debe ser al menos 1 unidad."];
                        if (cantidad > stock) return [false, `No hay suficiente stock. Máximo: ${stock}`];
                        return [true, "Cantidad válida."];
                    }
                },
                cardNumber: {
                    validate: () => {
                        const input = document.getElementById("cardNumber");
                        const requerido = input.closest("#cardDetails")?.style.display !== "none";
                        const valor = input.value.trim();
                        if (!requerido) return [true, ""];
                        if (!/^\d{16}$/.test(valor)) return [false, "Debe contener 16 dígitos numéricos."];
                        return [true, "Número válido."];
                    }
                },
                cardCVV: {
                    validate: () => {
                        const input = document.getElementById("cardCVV");
                        const requerido = input.closest("#cardDetails")?.style.display !== "none";
                        const valor = input.value.trim();
                        if (!requerido) return [true, ""];
                        if (!/^\d{3}$/.test(valor)) return [false, "Debe contener 3 dígitos numéricos."];
                        return [true, "CVV válido."];
                    }
                },
                cardExpiry: {
                    validate: () => {
                        const input = document.getElementById("cardExpiry");
                        const requerido = input.closest("#cardDetails")?.style.display !== "none";
                        const valor = input.value;
                        if (!requerido) return [true, ""];
                        const hoy = new Date().toISOString().slice(0, 7);
                        if (valor < hoy) return [false, "La tarjeta está vencida."];
                        return [true, "Fecha válida."];
                    }
                },
                dni: {
                    validate: async () => {
                        const input = document.getElementById("dni");
                        const valor = input.value.trim();

                        if (!/^\d{7,8}$/.test(valor)) {
                            return [false, "DNI inválido. Debe tener 7 u 8 dígitos."];
                        }

                        try {
                            const response = await fetch(`validar_dni.php?dni=${valor}`);
                            const data = await response.json();

                            if (data.valido) {
                                return [true, data.mensaje];
                            } else {
                                return [false, data.mensaje];
                            }

                        } catch (error) {
                            return [false, "Error de conexión al verificar DNI."];
                        }
                    }
                }
            };

            function aplicarEstilos(idCampo, valido, mensajeTexto) {
                const input = document.getElementById(idCampo);
                if (!input) return;

                let barra = input.nextElementSibling;
                let mensaje = barra?.nextElementSibling;

                if (!barra || !barra.classList.contains("barra-validacion")) {
                    barra = document.createElement("div");
                    barra.classList.add("barra-validacion");
                    input.insertAdjacentElement("afterend", barra);
                    mensaje = document.createElement("div");
                    mensaje.classList.add("mensaje-validacion");
                    barra.insertAdjacentElement("afterend", mensaje);
                }

                barra.className = "barra-validacion " + (valido ? "valido" : "invalido");
                mensaje.className = "mensaje-validacion " + (valido ? "valido" : "invalido");
                mensaje.textContent = mensajeTexto;
            }

            for (let id in campos) {
                const input = document.getElementById(id);
                if (!input) continue;

                input.addEventListener("input", async () => {
                const resultado = campos[id].validate();
                let valido, mensaje;

                if (resultado instanceof Promise) {
                    [valido, mensaje] = await resultado;
                } else {
                    [valido, mensaje] = resultado;
                }

                aplicarEstilos(id, valido, mensaje);
             });
            }
            
            // Validar todo antes de enviar
            document.getElementById("rentalForm").addEventListener("submit", async (e) => {
                let todoValido = true;
                for (let id in campos) {
                    const resultado = campos[id].validate();
                    let valido, mensaje;

                    if (resultado instanceof Promise) {
                        [valido, mensaje] = await resultado;
                    } else {
                        [valido, mensaje] = resultado;
                    }

                    aplicarEstilos(id, valido, mensaje);
                    if (!valido) todoValido = false;
                }
                if (!todoValido) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Formulario incompleto o inválido',
                        text: 'Por favor, revisá los campos marcados antes de continuar.'
                    });
                }
            });
        });
    </script>

</body>
</html>