<?php
$host = '127.0.0.1';
$port = 3309;
$user = 'root';
$pass = '';
$db = 'dismar_sac';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT rol, COUNT(*) as total FROM empleados GROUP BY rol";
$result = $conn->query($sql);

$labels = [];
$values = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['rol'];
    $values[] = $row['total'];
}

$sql_total = "SELECT COUNT(*) as total FROM empleados";
$result_total = $conn->query($sql_total);
$total_empleados = $result_total->fetch_assoc()['total'];

// Consulta para calcular el total de gastos del mes actual
$total_query = $conn->query("
    SELECT SUM(monto) AS total 
    FROM gastos 
    WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
    AND YEAR(fecha) = YEAR(CURRENT_DATE())
");

// Obtener el resultado y asegurar que sea un número válido
$total_gastos = intval($total_query->fetch_assoc()['total'] ?? 0);


$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Estadísticas</title>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>

        
        :root {
            --primary: #3B82F6;
            --accent: #10B981;
            --background: #FFFFFF;
            --surface: #F8FAFC;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --success: #10B981;
            --warning: #F59E0B;
            --info: #3B82F6;
            --error: #EF4444;
        }

        html, body {
            overflow: hidden;
        }


        body {
            background-color: var(--surface);
            color: var(--text-primary);
            padding: 2rem;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--background);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }

        .icon-primary { background-color: rgba(59, 130, 246, 0.1); }
        .icon-success { background-color: rgba(16, 185, 129, 0.1); }
        .icon-warning { background-color: rgba(245, 158, 11, 0.1); }
        .icon-error { background-color: rgba(239, 68, 68, 0.1); }
        .icon-info { background-color: rgba(139, 92, 246, 0.1); }

        .icon {
            width: 24px;
            height: 24px;
        }

        .card-content h3 {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .card-content p {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .card-content .subtext {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .chart-container {
    background: var(--background);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    width: 315px; /* Igual al ancho de una tarjeta */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

#apex-doughnut-chart {
    width: 100%;
    height: 250px; /* Ajuste de altura proporcional */
}


        .chart-container h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.25rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        
    </style>
</head>
<body>

<div class="container">
    <div class="stats-container">
        <!-- Tarjeta 1 - Empleados -->
        <div class="card">
            <div class="card-header">
                <div class="icon-wrapper icon-primary">
                    <i data-lucide="users" class="icon" style="color: var(--primary);"></i>
                </div>
                <div class="card-content">
                    <h3>Total de Empleados</h3>
                    <p><?php echo $total_empleados; ?></p>
                    <span class="subtext">Activos: <?php echo $total_empleados; ?></span>
                </div>
            </div>
        </div>

      
        
<!-- Tarjeta 2 - Gastos -->
<div class="card">
    <div class="card-header">
        <div class="icon-wrapper icon-success">
            <i data-lucide="banknote" class="icon" style="color: var(--success);"></i>
        </div>
        <div class="card-content">
            <h3>Gastos Mensuales</h3>
            <p>S/ <?php echo number_format($total_gastos, 2, ',', '.'); ?></p>
            <span class="subtext">vs mes anterior: +2.5%</span>
        </div>
    </div>
</div>

        <!-- Tarjeta 3 - Proyectos -->
        <div class="card">
            <div class="card-header">
                <div class="icon-wrapper icon-warning">
                    <i data-lucide="briefcase" class="icon" style="color: var(--warning);"></i>
                </div>
                <div class="card-content">
                    <h3>Proyectos Activos</h3>
                    <p>12</p>
                    <span class="subtext">En progreso: 9</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4 - Capacitación -->
        <div class="card">
            <div class="card-header">
                <div class="icon-wrapper icon-error">
                    <i data-lucide="graduation-cap" class="icon" style="color: var(--error);"></i>
                </div>
                <div class="card-content">
                    <h3>Horas Capacitación</h3>
                    <p>0h</p>
                    <span class="subtext">Este mes: </span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 5 - Satisfacción -->
        <div class="card">
            <div class="card-header">
                <div class="icon-wrapper icon-info">
                    <i data-lucide="smile" class="icon" style="color: var(--info);"></i>
                </div>
                <div class="card-content">
                    <h3>Ventas Semanales</h3>
                    <p>S/800</p>
                    <span class="subtext">Hielo Dia</span>
                </div>
            </div>
        </div>
    </div>

   


    <!-- Gráfico -->
    <div class="chart-container">
        <div id="apex-doughnut-chart"></div>
    </div>
</div>

<script>
    

    // Inicializar iconos Lucide
    lucide.createIcons();
    
    // Configuración del gráfico
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                height: 300,
                type: 'donut',
                fontFamily: 'Inter'
            },
            series: <?php echo json_encode($values,JSON_NUMERIC_CHECK); ?>,
            labels: <?php echo json_encode($labels); ?>,
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%',
                        labels: {
                            show: true,
                            name: {
                                fontSize: '14px',
                                color: '#64748B'
                            },
                            value: {
                                fontSize: '20px',
                                fontWeight: 700,
                                color: '#1E293B',
                                formatter: function (val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: 'Total',
                                fontSize: '14px',
                                color: '#64748B',
                                formatter: function (w) {
                                    return <?php echo $total_empleados; ?>;
                                }
                            }
                        }
                    }
                }
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '14px',
                markers: {
                    radius: 12
                },
                itemMargin: {
                    horizontal: 8,
                    vertical: 8
                }
            },
            colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
            dataLabels: {
                enabled: false
            },
            tooltip: {
                enabled: true,
                style: {
                    fontSize: '14px'
                },
                y: {
                    formatter: function(value) {
                        return value + ' empleados';
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 300
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#apex-doughnut-chart"), options);
        chart.render();
    });
</script>

</body>
</html>