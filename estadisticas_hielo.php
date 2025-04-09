<?php
$host = '127.0.0.1';
$port = 3309;
$user = 'root';
$pass = '';
$db = 'dismar_sac';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "SELECT 
            COALESCE(SUM(peso_pagado + peso_credito), 0) AS total_kg 
        FROM transacciones_hielo";

$result = $conn->query($sql);
$data = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Producción - DISMAR SAC</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        :root {
            --color-primary: #2c3e50;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-container {
            background: var(--surface);
            width: min(90%, 500px);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .input-grid {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-label {
            display: block;
            font-size: 0.875rem;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            color: var(--text-secondary);
        }

        .currency-symbol {
            position: absolute;
            left: 2.5rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .cost-input {
            width: 100%;
            padding: 0.625rem 0.75rem 0.625rem 4rem;
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-primary);
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--color-primary);
            color: white;
            border-color: var(--color-primary);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: var(--color-primary);
            border: 1px solid var(--border);
        }

        .stat-card {
        padding: 1.5rem;
        border-radius: 0.75rem;
        background: var(--surface);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
        
    }

    .stat-icon {
        font-size: 1.8rem;
        color: #64748b;
        padding: 0.5rem;
        border-radius: 0.5rem;
        background: #f8fafc;
    }

    .stat-title {
        font-size: 0.95rem;
        color: #64748b;
        font-weight: 500;
        margin: 0;
    }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: -0.5px;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="button-group">
            <a href="hielo.php" class="btn btn-secondary">
                <span class="material-symbols-outlined">arrow_back</span>
                Volver
            </a>
            <button class="btn btn-primary" onclick="toggleModal()">
                <span class="material-symbols-outlined">edit</span>
                Editar Valores
            </button>
        </div>
    </div>

    <div class="modal-overlay" id="modal">
        <div class="modal-container">
            <button class="btn btn-secondary" style="position: absolute; top: 1rem; right: 1rem;" onclick="toggleModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
            
            <div class="input-grid">
                <div class="input-group">
                    <label class="input-label">Gastos de Producción</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">factory</span>
                        <span class="currency-symbol">S/</span>
                        <input type="number" class="cost-input" step="0.01" id="produccion" required>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Operadores</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">engineering</span>
                        <span class="currency-symbol">S/</span>
                        <input type="number" class="cost-input" step="0.01" id="operadores" required>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Recibo de Luz/Agua</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">public</span>
                        <span class="currency-symbol">S/</span>
                        <input type="number" class="cost-input" step="0.01" id="servicios" required>
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Precio por Tonelada</label>
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">currency_exchange</span>
                        <span class="currency-symbol">S/</span>
                        <input type="number" class="cost-input" step="0.01" id="precio_tonelada" required>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn btn-primary" onclick="saveData()">Guardar Cambios</button>
            </div>
        </div>
    </div>





    <div class="stats-container">
    <style>
    .stat-card {
        position: relative;
        overflow: hidden;
    }

    .stat-subtitle {
        color: #64748b;
        font-size: 0.75rem;
        display: block;
        margin-top: 0.25rem;
    }

    .text-success { color: #16a34a; }
    .text-danger { color: #dc2626; }
    .text-warning { color: #d97706; }

    .trend-indicator {
        margin-left: 0.5rem;
        font-size: 0.9em;
    }

    .stat-value[data-trend="positive"] .trend-indicator {
        color: #16a34a;
        content: '↑';
    }

    .stat-value[data-trend="negative"] .trend-indicator {
        color: #dc2626;
        content: '↓';
    }

    .value-amount {
        transition: all 0.3s ease;
    }
</style>
    <!-- Ventas Totales (en Toneladas) -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="material-symbols-outlined stat-icon">receipt_long</span>
            <div>
                <h3 class="stat-title">Ventas Totales (TN)</h3>
                <small class="stat-subtitle">Precio por TN * Ventas Totales</span></small>
            </div>
        </div>
        <div class="stat-value text-success" id="ventas-totales">S/ 0.00</div>
    </div>

    <!-- Costos de Producción -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="material-symbols-outlined stat-icon">show_chart</span>
            <div>
                <h3 class="stat-title">Costos Totales</h3>
                <small class="stat-subtitle">Incluye producción, operarios y servicios</small>
            </div>
        </div>
        <div class="stat-value text-danger" id="total-gastos">S/ 0.00</div>
    </div>

    <!-- Utilidad Neta -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="material-symbols-outlined stat-icon">account_balance</span>
            <div>
                <h3 class="stat-title">Utilidad Neta</h3>
                <small class="stat-subtitle">Ventas - Costos Totales</small>
            </div>
        </div>
        <div class="stat-value" id="saldo-ventas">
            <span class="value-amount">S/ 0.00</span>
            <span class="trend-indicator"></span>
        </div>
    </div>

    <!-- Costo por Kilogramo -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="material-symbols-outlined stat-icon">scale</span>
            <div>
                <h3 class="stat-title">Costo por Tonelada</h3>
                <small class="stat-subtitle">Costo total / TN producidos</small>
            </div>
        </div>
        <div class="stat-value text-warning" id="costo-kilogramo">S/ 0.00</div>
    </div>
</div>



<!-- Gráficos -->
<div class="charts-container">
    <!-- Distribución de Costos (Izquierda) -->
    <div class="chart-box left-chart">
        <canvas id="graficoCostos"></canvas>
    </div>
    
    <!-- Resumen Financiero (Derecha) -->
    <div class="chart-box right-chart">
        <canvas id="graficoFinanciero"></canvas>
    </div>
</div>

<style>
.charts-container {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 1.5rem;
    width: 100%;
    max-width: 1000px;
    height: 380px;
    margin: 0 auto;
    padding: 15px;
}

.chart-box {
    background: white;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    padding: 18px;
    transition: transform 0.2s ease;
}

.chart-box:hover {
    transform: translateY(-2px);
}

.chart-box canvas {
    width: 100%!important;
    height: 100%!important;
}

@media (max-width: 768px) {
    .charts-container {
        grid-template-columns: 1fr;
        height: auto;
        gap: 1rem;
    }
    
    .chart-box {
        height: 320px;
        padding: 12px;
    }
}
</style>



</head>
<body>

<script>
    // Datos iniciales desde PHP (en kilogramos)
    <?php
    $host = '127.0.0.1';
    $port = 3309;
    $user = 'root';
    $pass = '';
    $db = 'dismar_sac';

    $conn = new mysqli($host, $user, $pass, $db, $port);
    
    // Obtener peso total en kilogramos
    $sql = "SELECT COALESCE(SUM(peso_pagado + peso_credito), 0) AS total_kg FROM transacciones_hielo";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    $conn->close();
    ?>
    
    const totalKg = <?= $data['total_kg'] ?? 0 ?>;
    
    let costData = {
        produccion: 0,    // Gastos de producción
        operadores: 0,    // Costo de operarios
        servicios: 0,     // Recibo de luz/agua
        precio_tonelada: 0 // Precio por tonelada
    };

    let graficoFinanciero = null;
let graficoCostos = null;

    function toggleModal() {
        const modal = document.getElementById('modal');
        modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
        
        if(modal.style.display === 'flex') {
            document.getElementById('produccion').value = costData.produccion;
            document.getElementById('operadores').value = costData.operadores;
            document.getElementById('servicios').value = costData.servicios;
            document.getElementById('precio_tonelada').value = costData.precio_tonelada;
        }
    }

    function saveData() {
        // Función mejorada para obtener valores
        const getValue = (id) => {
            const input = document.getElementById(id);
            const value = parseFloat(input.value.replace(/,/g, ''));
            
            if (isNaN(value) || value < 0) {
                input.classList.add('input-error');
                return null;
            }
            input.classList.remove('input-error');
            return value;
        };

        const nuevosDatos = {
            produccion: getValue('produccion'),
            operadores: getValue('operadores'),
            servicios: getValue('servicios'),
            precio_tonelada: getValue('precio_tonelada')
        };

        // Validación completa
        if (Object.values(nuevosDatos).some(v => v === null)) {
            alert('Por favor complete todos los campos con valores válidos (números positivos)');
            return;
        }

        costData = nuevosDatos;
        localStorage.setItem('costData', JSON.stringify(costData));
        actualizarEstadisticos();
        toggleModal();
    }

    function actualizarEstadisticos() {
        // 1. Cálculo de Ventas Totales
        const toneladasVendidas = totalKg;
        const ventasTotales = toneladasVendidas * costData.precio_tonelada;
        
        // 2. Cálculo de Costos Totales
        const costosTotales = costData.produccion + costData.operadores + costData.servicios;
        
        // 3. Cálculo de Utilidad Neta
        const utilidadNeta = ventasTotales - costosTotales;

        // 4. Cálculo de Costo por TONELADA (redondeado a 2 decimales)
const costoTonelada = toneladasVendidas > 0 
    ? Math.round((costosTotales / toneladasVendidas) * 100) / 100 
    : 0;
        
    
    const formatear = (valor) => new Intl.NumberFormat('es-PE', { 
        style: 'currency', 
        currency: 'PEN' 
    }).format(valor);

    document.getElementById('ventas-totales').textContent = formatear(ventasTotales);
    document.getElementById('total-gastos').textContent = formatear(costosTotales);
    document.getElementById('saldo-ventas').textContent = formatear(utilidadNeta);
    document.getElementById('costo-kilogramo').textContent = formatear(costoTonelada);

    // Destruir gráficos anteriores
    if(graficoFinanciero) graficoFinanciero.destroy();
    if(graficoCostos) graficoCostos.destroy();

    // Crear nuevos gráficos
    const ctx1 = document.getElementById('graficoFinanciero');
    const ctx2 = document.getElementById('graficoCostos');

  // Gráfico 1: Comparativa financiera (Derecha)
graficoFinanciero = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Ventas', 'Costos', 'Utilidad', 'Costo/Tn'],
        datasets: [{
            label: 'Monto en Soles',
            data: [ventasTotales, costosTotales, utilidadNeta, costoTonelada],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(255, 206, 86, 1)'
            ],
            borderWidth: 1,
            barThickness: 20, // Barras más delgadas
            categoryPercentage: 0.3, // Más espacio entre categorías
            borderRadius: 6 // Bordes redondeados
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Resumen Financiero',
                padding: {bottom: 8},
                font: {
                    size: 16,
                    weight: '600'
                }
            },
            legend: {display: false},
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.9)',
                bodyFont: {size: 12},
                padding: 10,
                callbacks: {
                    label: (context) => ` S/ ${context.parsed.y.toLocaleString('es-PE')}`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.03)',
                    drawTicks: false
                },
                border: {display: false},
                ticks: {
                    font: {size: 11},
                    padding: 5,
                    callback: value => `S/ ${value.toLocaleString('es-PE', {maximumFractionDigits: 0})}`
                }
            },
            x: {
                grid: {display: false},
                border: {display: false},
                ticks: {
                    font: {size: 12},
                    color: '#2c3e50'
                }
            }
        },
        layout: {
            padding: {top: 15, right: 10, bottom: 10, left: 10}
        }
    }
});

// Gráfico 2: Composición de costos (Izquierda)
graficoCostos = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Producción', 'Operarios', 'Servicios Luz/Agua'],
        datasets: [{
            data: [
                costData.produccion,
                costData.operadores,
                costData.servicios
            ],
            backgroundColor: [
                'rgba(255, 159, 64, 0.9)',
                'rgba(153, 102, 255, 0.9)',
                'rgba(255, 205, 86, 0.9)'
            ],
            borderColor: 'rgba(255, 255, 255, 0.3)',
            borderWidth: 2,
            hoverBorderColor: 'rgba(255, 255, 255, 0.6)',
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Distribución de Costos',
                padding: {bottom: 8},
                font: {
                    size: 16,
                    weight: '600'
                }
            },
            legend: {
                position: 'right',
                align: 'center',
                labels: {
                    padding: 12,
                    boxWidth: 14,
                    boxHeight: 14,
                    font: {size: 12},
                    color: '#2c3e50'
                }
            },
            tooltip: {
                bodyFont: {size: 12},
                callbacks: {
                    label: (context) => {
                        const total = context.dataset.data.reduce((a, b) => a + b);
                        const percentage = (context.raw / total * 100).toFixed(1);
                        return ` ${context.label}: S/ ${context.raw.toLocaleString('es-PE')} (${percentage}%)`;
                    }
                }
            }
        },
        cutout: '65%',
        layout: {
            padding: {top: 15, bottom: 15}
        }
    }
});
    }

    // Inicialización de gráficos
    const ctx1 = document.getElementById('graficoFinanciero').getContext('2d');
    const ctx2 = document.getElementById('graficoCostos').getContext('2d');

    // Cargar datos guardados en localStorage
    const savedData = localStorage.getItem('costData');
    if(savedData) costData = JSON.parse(savedData);

    // Actualizar estadísticas al cargar la página
    actualizarEstadisticos();

// Inicialización
window.addEventListener('load', () => {
    const savedData = localStorage.getItem('costData');
    if(savedData) costData = JSON.parse(savedData);
    actualizarEstadisticos();
});




    



</script>
</body>
</html>