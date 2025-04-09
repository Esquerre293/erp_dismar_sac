<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dismar SAC</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Variables */
        :root {
            --sidebar-width: 260px;
            --primary-color: #2F3A4F;
            --accent-color: #4A90E2;
            --text-primary: #F5F9FC;
            --text-secondary: #B0C4D9;
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #F9FBFD;
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            height: 100vh;
            padding: 1.5rem;
            position: fixed;
            transition: all var(--transition-speed) ease;
        }

        .nav-section {
            margin: 2rem 0;
        }

        .nav-section-title {
            color: var(--text-secondary);
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 1.2rem;
            padding: 0 1rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.25rem;
            color: var(--text-primary);
            text-decoration: none;
            border-radius: 8px;
            transition: all var(--transition-speed) ease;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(74, 144, 226, 0.15) 0%, transparent 100%);
            color: var(--accent-color);
            font-weight: 500;
        }

        .nav-icon {
            width: 24px;
            margin-right: 1rem;
            font-size: 1.1rem;
        }

        /* Contenido principal */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 20px;
            transition: all var(--transition-speed) ease;
        }

        .content-frame {
            width: 100%;
            height: 100vh;
            border: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <a class="nav-item active" onclick="loadPage('subir_empleado.php', this)">
                    <i class="fas fa-users nav-icon"></i>
                    <span class="nav-text">Trabajadores</span>
                </a>
                <a class="nav-item" onclick="loadPage('estadisticas.php', this)">
                    <i class="fas fa-chart-line nav-icon"></i>
                    <span class="nav-text">Estadísticas</span>
                </a>
                <a class="nav-item" onclick="loadPage('gastos.php', this)">
                    <i class="fas fa-wallet nav-icon"></i>
                    <span class="nav-text">Gastos</span>
                </a>
                <a class="nav-item" onclick="loadPage('almacen.php', this)">
                    <i class="fas fa-boxes nav-icon"></i>
                    <span class="nav-text">Almacén</span>
                </a>
                <a class="nav-item" onclick="loadPage('hielo.php', this)">
                    <i class="fas fa-snowflake nav-icon"></i>
                    <span class="nav-text">Hielo</span>
                </a>
                <a class="nav-item" onclick="loadPage('proveedores.php', this)">
                    <i class="fas fa-truck nav-icon"></i>
                    <span class="nav-text">Proveedores</span>
                </a>
                <a class="nav-item" onclick="loadPage('ventas.php', this)">
                    <i class="fas fa-shopping-cart nav-icon"></i>
                    <span class="nav-text">Ventas</span>
                </a>
            </div>
        </nav>
    </aside>

    <!-- Contenido Principal -->
    <div class="main-content">
        <iframe src="subir_empleado.php" id="content-frame" class="content-frame"></iframe>
    </div>

    <script>
        function loadPage(page, element) {
            document.getElementById('content-frame').src = page;
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
            element.classList.add('active');
        }
    </script>
</body>
</html>
