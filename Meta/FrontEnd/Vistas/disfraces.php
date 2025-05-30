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
            <a>Disfraces</a>
        </section>

        <section class="searchSection">
            <h2>¡Disfraces!</h2>
            <input type="text" id="searchInput" placeholder="Buscar disfraces..." onkeyup="filterDisfraces()">
            <h4>Puedes filtrar nuestros disfraces por su nombre, tematica o categoria</h4>
        </section>
        
        <section class="cards-container-costume" id="costume-Container">
            <a href="detallesdisfraz.php" class="asection">
                <section class="card-costume">
                    <img src="../img/Disfraces/niños/historico/niño/pirata_1_niños.jpeg" class="category-image">
                    <h4>Pirata</h4>
                    <p>Tematica: historia</p>
                    <p>Categoria: niño</p>
                    <label class="btn">Disponible hoy</label>
                    
                </section>
            </a>

            <section class="card-costume">
                <img src="../img/Disfraces/niños/superhéroes/niño/flash_dc_1_niños.jpg" class="category-image">
                <h4>Flash</h4>
                <p>Tematica: superheroes</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible desde 10/11</label>

            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/circo/niño/mago_3_niños.jpg" class="category-image">
                <h4>Mago</h4>
                <p>Tematica: circo</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible desde 25/12</label>

            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/halloween/niño/fantasma_1_niños.jpg" class="category-image">
                <h4>Fantasta de la B</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible desde 26/06</label>

            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/argentina/fechaspatrias/niño/general_niños.jpg" class="category-image">
                <h4>Granadero</h4>
                <p>Tematica: folcklore argentino</p>
                <p>Categoria: niño</p>
                
                <label class="btn">Disponible hoy</label>

            </section>


            <section class="card-costume">
                <img src="../img/Disfraces/niños/animales/niña/ardilla_1_niños.jpg" class="category-image">
                <h4>Ardilla</h4>
                <p>Tematica: animales</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>

            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/circo/niña/payaso_6_niños.jpg" class="category-image">
                <h4>Payasa</h4>
                <p>Tematica: circo</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/cuentos/niña/blancanieves_1_niños.jpg" class="category-image">
                <h4>Blancanieves</h4>
                <p>Tematica: cuentos de hadas</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/argentina/niña/paisana_1_niños.jpg" class="category-image">
                <h4>Paisana</h4>
                <p>Tematica: folcklore argentino</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>         
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/halloween/niña/bruja_6_niños.jpg" class="category-image">
                <h4>Bruja</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible desde 01/02</label>       
            </section>

            
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/circo/hombre/domador_1_adultos.jpg" class="category-image">
                <h4>Domador</h4>
                <p>Tematica: circo</p>
                <p>Categoria: hombre</p>
                <label class="btn">Disponible hoy</label>       
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/histórico/hombre/rey_3_adultos.jpg" class="category-image">
                <h4>Rey</h4>
                <p>Tematica: historia</p>
                <p>Categoria: hombre</p>
                <label class="btn">Disponible hoy</label>   
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/halloween/hombre/frankenstein_1_adultos.jpg" class="category-image">
                <h4>Frankenstein</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: hombre</p>
                <label class="btn">Disponible hoy</label>      
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/argentina/hombre/gaucho_2_adultos.jpg" class="category-image">
                <h4>Gaucho</h4>
                <p>Tematica: folcklore argentino</p>
                <p>Categoria: hombre</p>
                <label class="btn">Disponible hoy</label>     
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/oficios/hombre/soldado_2_adultos.jpg" class="category-image">
                <h4>Soldado</h4>
                <p>Tematica: accion</p>
                <p>Categoria: hombre</p>
                <label class="btn">Disponible hoy</label>       
            </section>


            <section class="card-costume">
                <img src="../img/Disfraces/adultos/culturas_del_mundo/mujer/indio_3_adultos.jpg" class="category-image">
                <h4>Nativa</h4>
                <p>Tematica: culturas del mundo</p>
                <p>Categoria: mujer</p>
                <label class="btn">Disponible hoy</label>    
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/argentina/mujer/paisana_2_adultos.jpg" class="category-image">
                <h4>Paisana</h4>
                <p>Tematica: folcklore argentino</p>
                <p>Categoria: mujer</p>
                <label class="btn">Disponible hoy</label>      
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/animales/mujer/gato_adultos.jpg" class="category-image">
                <h4>Gata</h4>
                <p>Tematica: animales</p>
                <p>Categoria: mujer</p>
                <label class="btn">Disponible hoy</label>        
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/halloween/mujer/payasoasesino_2_adultos.jpg" class="category-image">
                <h4>Payasa</h4>
                <p>Tematica: circo</p>
                <p>Categoria: mujer</p>
                <label class="btn">Disponible hoy</label>      
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/prehistoria/mujer/cavernicola_adultos.jpg" class="category-image">
                <h4>Cavernicola</h4>
                <p>Tematica: prehistoria</p>
                <p>Categoria: mujer</p>
                <label class="btn">Disponible hoy</label>  
            </section>

        </section>
    </main>

   <!-- Modal -->
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

            <h2>Datos del Usuario</h2>
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
        
            <section class="user-links">
                <a href="../Vistas/registrarsepersona.php">¿No tienes cuenta?</a>
                <a href="../Vistas/recuperar.php">¿Olvidaste tu contraseña?</a>
            </section>

            <h2>Datos del Alquiler</h2>
            <label for="date-from">Fecha Desde...</label>
            <input type="date" id="date-from" name="date-from" required>

            <label for="date-to">Fecha Hasta...</label>
            <input type="date" id="date-to" name="date-to" required>

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
    
<section>
    <ul class="pagination">
        <li><a href="#">&laquo; </a></li>
        <li class="active"><a href="#">1</a></li>
        <li><a href="#">2</a></li>
        <li><a href="#">3</a></li>
        <li><a href="#">...</a></li>  
        <li><a href="#"> &raquo;</a></li>
    </ul>
</section>

<?php include('footer.php');?>

<script>
    function filterDisfraces() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const container = document.getElementById('costume-Container');
        const cards = container.getElementsByClassName('card-costume');

        for (let i = 0; i < cards.length; i++) {
            const title = cards[i].getElementsByTagName('h4')[0].innerText.toLowerCase();
            const category = cards[i].getElementsByTagName('p')[1].innerText.toLowerCase();
            const theme = cards[i].getElementsByTagName('p')[0].innerText.toLowerCase();

            if (title.includes(filter) || category.includes(filter) || theme.includes(filter)) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
</script>

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