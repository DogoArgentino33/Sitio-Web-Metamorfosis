<!DOCTYPE html>
<html lang="es">
<head>
    <title>Contactos - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/contactos.css">
</head>
<body>
   <?php include('cabecera.php'); ?>
    
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a>Contactos</a>
        </section>
        <h2 class="main-h2">Contáctanos</h2>
        
        <section class="wrapper">
            <form action="#" method="post" id="employee">
                <fieldset>
                    <legend>Deja aquí tus consultas y responderemos lo antes posible</legend>
                    <h4>Datos personales</h4>
                    <section class="input-box">
                        <input type="text" placeholder="Nombre" required>
                    </section>
                    <section class="input-box">
                        <input type="text" placeholder="Apellido" required>
                    </section>
                    <section class="input-box">
                        <input type="email" placeholder="Correo Electrónico" required>
                    </section>
                    <section class="input-box">
                        <label for="id-consulta" class="floating-placeholder">Deja tu consulta:</label>
                        <input type="text" id="id-consulta" required>
                    </section>
                    <button type="submit" class="btn">Enviar</button>
                </fieldset>
            </form>
        </section>
        
        <section class="contact-info">
            <p>Si tienes alguna pregunta o consulta, no dudes en ponerte en contacto con nosotros:</p>
            <ul>
                <li><i class="bi bi-geo-alt-fill"></i> Tucumán 355, K4700 San Fernando del Valle de Catamarca, Catamarca</li>
                <li><i class="bi bi-telephone-fill"></i> Teléfono: +54 123 456 789</li>
                <li><i class="bi bi-envelope-fill"></i> Email: <a href="mailto:info@metamorfosis.com">info@metamorfosis.com</a></li>
                <li><i class="bi bi-instagram"></i> Instagram: <a href="https://www.instagram.com/disfracesmetamorfosis/">@disfracesmetamorfosis</a></li>
            </ul>
        </section>
        
        <section class="map-container" style="text-align: center; margin-top: 20px;">
            <h3>Nuestra Ubicación</h3>
            <iframe class="iframe"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3154.823694249857!2d-66.264825!3d-28.467292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x943a06bdf176be8b%3A0x6de4f091bb6e3e27!2sTucum%C3%A1n%20355%2C%20K4700%20San%20Fernando%20del%20Valle%20de%20Catamarca%2C%20Catamarca!5e0!3m2!1ses-419!2sar!4v1634576401573!5m2!1ses-419!2sar" 
                width="600" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </section>
    </main>
    
    <?php include('footer.php');?>
</body>
</html>