<!DOCTYPE html>
<html lang="es">
<head>
    <title>Gestión de Ganancias - Metamorfosis</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/estadisticas.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/paneles.css">
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="gerente.php">Gerente /</a>
            <a>Estadisticas</a>
        </section>

        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Estadísticas de Alquileres y Ganancias</h1>
        <h2 style="text-align: center; margin-top: 6%;">Tabla General de ventas mensuales</h2>
        <section class="container-table">
            <table>
                <thead>
                    <tr><strong>ENERO</strong></tr>
                    <tr>
                        <th>DESCRIPCION</th>
                        <th>VALOR</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total de alquileres realizados</strong></td>
                        <td>254 alquileres</td>
                    </tr>
                    <tr>
                        <td><strong>Total de ganancias generadas</strong></td>
                        <td>$12,500</td>
                    </tr>
                    <tr>
                        <td><strong>Promedio de alquiler</strong></td>
                        <td>150 alquileres</td>
                    </tr>
                    <tr>
                        <td><strong>Total de disfraces alquilados</strong></td>
                        <td>175 disfraces</td>
                    </tr>
                    <tr>
                        <td><strong>Total de accesorios alquilados</strong></td>
                        <td>79 accesorios</td>
                    </tr>
                    <tr>
                        <td><strong>Ingresos totales generados</strong></td>
                        <td>$500.000</td>
                    </tr>
                </tbody>
            </table>
            <td><strong>Mejor mes de alquileres - Octubre (350 alquileres)</strong></td>
        </section>

        <section>
            <ul class="pagination">
                <li><a href="#">&laquo;</a></li>
                <li class="active"><a href="#">1</a></li>
                <li><a href="#">2</a></li>
                <li><a href="#">3</a></li>
                <li><a href="#">...</a></li>
                <li><a href="#">10</a></li>
                <li><a href="#">11</a></li>
                <li><a href="#">12</a></li>  
                <li><a href="#">&raquo;</a></li>
            </ul>
        </section>

        <h2 style="text-align: center; margin-top: 6%;">Ventas por Categoria</h2>

        <section class="container-graphic"> 
            <canvas id="ventasPorCategoria"></canvas>
        </section>

        <h2 style="text-align: center; margin-top: 6%;">Actividad Mensual</h2>

        <section style="text-align: center; margin-top: 6%; color: black;">
            <label for="date-from">Desde...</label>
            <input type="date" id="date-from" name="date-from" required>

            <label for="date-to">Hasta...</label>
            <input type="date" id="date-to" name="date-to" required>

            <input type="button" value="Filtrar">
        </section>

        <section class="container-graphic"> 
            <canvas id="tendenciasMensuales"></canvas>
        </section>
    </main>

    <?php include('footer.php');?>

    <script>
        const ctx1 = document.getElementById('ventasPorCategoria').getContext('2d');
        const ventasPorCategoria = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Niños','Niñas','Hombres','Mujeres','Accesorios'],
                datasets: [{
                    label: 'Alquileres',
                    data: [120,100,70,60,54], // Cantidad de alquileres
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        const ctx2 = document.getElementById('tendenciasMensuales').getContext('2d');
        const tendenciasMensuales = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Actividad Mensual',
                    data: [100, 120, 150, 130, 140, 160, 180, 170, 190, 200, 210, 250], // Ventas por mes
                    fill: false,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

    </script>

</body>
</html>
