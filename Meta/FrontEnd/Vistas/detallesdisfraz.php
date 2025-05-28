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
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="disfraces.php">Disfraces / </a>
            <a>Detalles del Disfraz</a>
        </section>
        <h2 style="text-align: center; color: black;">Informacion del Disfraz</h2>

        <section class="cards-container-costume" id="costume-Container">
            <section class="card-costume">
                <img src="../img/Disfraces/niños/historico/niño/pirata_1_niños.jpeg" class="category-image">
                <h4>Pirata</h4>
                <p>Tematica: historia</p>
                <p>Categoria: niño</p>
                <p>Talle: S</p>
                <p>Precio: $2000</p>
                <p>Disponible : Disponible</p>
                <p>Unidades Disponibles: 3</p>
                <button type="button" class="btn" onclick="openModal('Pirata')">Alquilar</button>
            </section>
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
        
    
    <footer>
        <p><i class="bi bi-geo-alt-fill"></i> Tucumán 355, K4700 San Fernando del Valle de Catamarca, Catamarca</p>
        <p><i class="bi bi-envelope-fill"></i> info@metamorfosis.com</p>
        <p><i class="bi bi-telephone-fill"></i> +54 123 456 789</p>
        <p>&copy; 2024 Metamorfosis. Todos los derechos reservados.</p>
        <section class="social-icons">
            <a href="https://www.instagram.com/disfracesmetamorfosis/"><i class="bi bi-instagram"></i></a>
            <i class="bi bi-twitter-x"></i>
            <i class="bi bi-facebook"></i>
            <i class="bi bi-whatsapp"></i>
        </section>
    </footer>

    <script>
        function openModal(costumeName) {
            document.getElementById('rentalModal').style.display = 'block';
            document.getElementById('costume').value = costumeName; // Asigna el nombre del disfraz al input
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