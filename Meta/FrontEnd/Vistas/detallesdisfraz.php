<?php
session_start();
include('conexion.php'); // Ajusta la ruta si es necesario

$id = $_GET['id'] ?? null;
$tipo = $_GET['tipo'] ?? null;

// Validaciones opcionales:
if (!$id || !$tipo) {
    // Redirigir o mostrar error si faltan datos
}

$datos = null;

if ($id && $tipo) {
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


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
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
        <h2 style="text-align: center; color: black;">Informacion del Disfraz</h2>

        <section class="cards-container-costume" id="costume-Container">
            <?php if ($datos): ?>
                <section class="card-costume">
                    <img src="uploads/producto/<?= htmlspecialchars($datos['imagenes']) ?>" class="category-image" width="250" height="300" style="object-fit: cover; border-radius: 3%;">
                    <h4><?= htmlspecialchars($datos['nombre']) ?></h4>
                    <p>Temática: <?= htmlspecialchars($datos['tematicas']) ?></p>
                    <p>Categoría: <?= htmlspecialchars($datos['categorias']) ?></p>
                    <?php if ($tipo == 1): ?>
                        <p>Talles: <?= htmlspecialchars($datos['tallas']) ?></p>
                    <?php endif; ?>
                    <p>Precio: $<?= htmlspecialchars($datos['precio']) ?></p>
                    <p>Disponible: <?= $datos['unidades_disponibles'] > 0 ? 'Disponible' : 'No disponible' ?></p>
                    <p>Unidades Disponibles: <?= htmlspecialchars($datos['unidades_disponibles']) ?></p>
                    <button type="button" class="btn" onclick="openModal('<?= htmlspecialchars($datos['nombre']) ?>')">Alquilar</button>
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
                <h2>Datos del Disfraz</h2>
                <label for="costume">Nombre:</label>
                <input type="text" id="costume" name="costume" readonly disabled>
    
                <label for="theme">Temática:</label>
                <input type="text" id="theme" name="theme" readonly disabled>
    
                <label for="category">Categoría:</label>
                <input type="text" id="category" name="category" readonly disabled>

                <label for="stock">Cantidad Disponible:</label>
                <input type="text" id="stock" name="stock" readonly disabled>
    
                <h2>Datos del Alquiler</h2>
                <label for="date-from">Fecha Desde...</label>
                <input type="date" id="date-from" name="date-from" required>
    
                <label for="date-to">Fecha Hasta...</label>
                <input type="date" id="date-to" name="date-to" required>

                <label for="amount">Cantidad de unidades a Alquilar...</label>
                <input type="number" id="amount" name="amount" min="1" required>
    
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
            if (!usuarioLogueado) {
                alert("Debés iniciar sesión para alquilar un disfraz.");
                window.location.href = "login.php";
                return;
            }

            const card = document.querySelector('.card-costume');
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
    
        // Cierra el modal si se hace clic fuera del contenido del modal
        window.onclick = function(event) {
            const modal = document.getElementById('rentalModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    
        // Manejar el envío del formulario
        document.getElementById('rentalForm').onsubmit = function(event) {
            event.preventDefault();
            // Aquí puedes agregar la lógica para enviar el formulario
            alert('Formulario enviado');
            closeModal(); // Cierra el modal después de enviar
        }
    </script>
    
    <script>
        function togglePaymentFields() {
            const creditDebit = document.querySelector('input[name="payment"]:checked').value;
            const cardDetails = document.getElementById('cardDetails');
        
            if (creditDebit === 'credito' || creditDebit === 'debito') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        }
    </script>
    
</body>
</html>