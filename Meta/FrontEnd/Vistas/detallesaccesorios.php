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
    <header>
        <section class="logo-container">
            <h1>Metamorfosis</h1>
            <form action="resultadosbusqueda.php">
                <input type="text" id="Idinputtextbuscar" placeholder="Buscar">
            </form>

            <section class="container-login-cart">
                <a href="../Vistas/login.php"><i class="bi bi-person-circle"></i></a>
                <a href="../Vistas/gerente.php"><i class="bi bi-gear-fill"></i></a>
                <a href="../Vistas/empleado.php"><i class="bi bi-pencil-square"></i></a>
                <a href="../Vistas/administrador.php"><i class="bi bi-pc-display"></i></a>
            </section>
        </section>
        <br>
        <section class="container-nav">
            <p id="nav-links">
                <a href="../Vistas/index.php">Inicio</a>
                <a href="../Vistas/disfraces.php">Disfraces</a>
                <a href="../Vistas/accesorios.php">Accesorios</a>
                <a href="../Vistas/contactos.php">Contactos</a>
                <a href="../Vistas/acerca.php">Acerca de</a>
            </p>
        </section>
    </header> 

    
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="accesorios.php">Accesorios /</a>
            <a>Detalles del Accesorio</a>
        </section>
        <h2 style="text-align: center; color: black;">Información del Accesorio</h2>

        <section class="cards-container-accessory" id="accessory-Container">

            <section class="card-accessory">
                <img src="../img/Accesorios/Historico/espada_pirata_1.jpg" class="category-image">
                <h4>Espada Pirata</h4>
                <p>Tematica: historia</p>
                <p>Categoria: niño</p>
                <p>Tamaño: 60cm</p>
                <p>Precio: $500</p>
                <p>Disponible : Disponible</p>
                <p>Unidades Disponibles: 5</p>
                
                <button type="button" class="btn" onclick="openModal('Espada Pirata')">Alquilar</button>
            </section>

        </section>
    </main>

    
    <section id="rentalModal" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h1>Alquilar</h1>
            <form id="rentalForm">
                <h2>Datos del Accesorio</h2>
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