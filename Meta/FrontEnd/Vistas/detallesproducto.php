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
            <form id="rentalForm">

                <?php if ($tipo == 1): ?>
                    <h2>Datos del Disfraz</h2>
                <?php elseif ($tipo == 2): ?>
                    <h2>Datos del Accesorio</h2>
                <?php endif; ?>

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
                <input type="date" id="desde" name="desde" required value="<?= escapar($_POST['desde'] ?? '') ?>">
    
                <label for="hasta">Fecha Hasta...</label>
                <input type="date" id="hasta" name="hasta" required value="<?= escapar($_POST['hasta'] ?? '') ?>">


                <label for="cantidad">Cantidad de unidades a Alquilar...</label>
                <input type="number" id="cantidad" name="cantidad" min="1" required value="<?= escapar($_POST['cantidad'] ?? '') ?>">
    
                <h2>Métodos de Pago</h2>
                <section class="payment-methods">
                    <section>
                        <input type="radio" id="efectivo" name="payment" value="efectivo" onclick="togglePaymentFields()" checked>
                        <label for="efectivo">Efectivo / Transferencia (Presencial)</label>
                    </section>
                    <br>
                    <section>
                        <input type="radio" id="debito" name="payment" value="debito" onclick="togglePaymentFields()">
                        <label for="debito">Débito</label>
                    </section>
                    <br>
                    <section>
                        <input type="radio" id="credito" name="payment" value="credito" onclick="togglePaymentFields()">
                        <label for="credito">Crédito</label>
                    </section>
                </section>            
    
                <section id="cardDetails" style="display:none;">
                    <h3>Detalles de la Tarjeta</h3>
                    <label for="cardNumber">Número de Tarjeta:</label>
                    <br>
                    <input type="text" id="cardNumber" name="cardNumber" required>
                    <br>
                    <label for="cardExpiry">Fecha de Expiración:</label>
                    <br>
                    <input type="month" id="cardExpiry" name="cardExpiry" required>
                    <br>
                    <label for="cardCVV">CVV:</label>
                    <br>
                    <input type="text" id="cardCVV" name="cardCVV" required>
                </section>
    
                <button type="submit">Enviar</button>
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
            event.preventDefault();
            // Aquí puedes agregar la lógica para enviar el formulario
            alert('Formulario enviado');
            closeModal(); // Cierra el modal después de enviar
        }
                return;
            }

            
    </script>
    
    <script>
        function togglePaymentFields() 
        {
            const creditDebit = document.querySelector('input[name="payment"]:checked').value;
            const cardDetails = document.getElementById('cardDetails');
        
            if (creditDebit === 'credito' || creditDebit === 'debito') 
                {
                    cardDetails.style.display = 'block';
                } 
            else 
                {
                    cardDetails.style.display = 'none';
                }
        }
    </script>

   <script>
       document.addEventListener('DOMContentLoaded', () => 
        {
            let currentSlide = 0;
            const slides = document.querySelector('.slides');
            const intervalMs   = 5000; //Esto equivale a 5 segunfos
            let timerId        = null;
            movimiento();  //Iniciamos el proceso

            // Iniciamos los botones  //
            document.getElementById('botonSliderPrev').addEventListener('click', () => 
            {
                CambioSlide(-1);
                ReiniciarMov();
            });

            document.getElementById('botonSliderNext').addEventListener('click', () => 
            {
                CambioSlide(1);
                ReiniciarMov();
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
                        // Si elige "Quizá más tarde", simplemente no hacemos nada
                    });
                });
            });
            
        }
    </script>

</body>
</html>