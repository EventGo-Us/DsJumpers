<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-rocket me-2"></i><?= $_SESSION['NombreCompania'] ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

<?php 
if ($_SESSION['role_id'] == 'ADMIN'){
?>
                <!-- INICIO -->
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <i class="fas fa-home"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Home" : "Inicio"; ?>
                    </a>
                </li>
                <!-- VENTAS Y EVENTOS -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Sales" : "Ventas"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Rents" : "Rentas"; ?></h6></li>
                        <li><a class="dropdown-item" href="lead.php"><i class="fas fa-plus me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "New Lead" : "Nuevo Evento"; ?></a></li>
                        <li><a class="dropdown-item" href="leads.php"><i class="fas fa-list me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Leads" : "Eventos"; ?></a></li>
                        <li><a class="dropdown-item" href="pending_payments.php"><i class="fas fa-money-bill me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Payments" : "Pagos Pendientes"; ?></a></li>
                        <li><a class="dropdown-item" href="payment_report.php"><i class="fa-solid fa-file-invoice-dollar"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Reports" : "Reporte de Pagos"; ?></a></li>
                        <li><a class="dropdown-item" href="graficas.php"><i class="fas fa-chart-line me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Graphics" : "Gráficas"; ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Sales" : "Ventas"; ?></h6></li>
                        <li><a class="dropdown-item" href="sale.php"><i class="fas fa-plus me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "New Sale" : "Nueva Venta"; ?></a></li>
                        <li><a class="dropdown-item" href="sales.php"><i class="fas fa-list me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Sales" : "Ventas"; ?></a></li>
                        <li><a class="dropdown-item" href="admin_comments.php"><i class="fa-regular fa-comments"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Sales Comments" : "Comentarios de Ventas"; ?></a></li>
                        <li><a class="dropdown-item" href="blog.php"><i class="fa-solid fa-blog"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Blog" : "Blog"; ?></a></li> 
                        <li><a class="dropdown-item" href="banner.php"><i class="fa-solid fa-sign-hanging"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Sales banner" : "Banner de ventas"; ?></a></li>                                                           
                    </ul>
                </li>

                <!-- OPERACIÓN Y LOGÍSTICA -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-truck"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Operation" : "Operación"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="route.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Route Mapping" : "Armado de rutas"; ?></a></li>
                        <li><a class="dropdown-item" href="operation.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "General Operation" : "Operación General"; ?></a></li>
                        <li><a class="dropdown-item" href="acondicionamiento.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Conditioning" : "Acondicionamiento"; ?></a></li>
                        <li><a class="dropdown-item" href="monitor.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Monitor" : "Monitor"; ?></a></li>
                    </ul>
                </li>

                <!-- CATÁLOGOS / CONFIGURACIÓN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Settings" : "Configuración"; ?>
                    </a>
                    <ul class="dropdown-menu scrollable-menu" style="max-height: 600px; overflow-y: auto;">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Administration" : "Administración"; ?></h6></li>
                        <li><a class="dropdown-item" href="crud.php?Id=account"><?php echo ($_SESSION['Idioma'] == 'en') ? "Account" : "Cuenta"; ?></a></li>
                        <li><a class="dropdown-item" href="pay_platform.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Payment Platform" : "Plataforma de pago"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=customers"><?php echo ($_SESSION['Idioma'] == 'en') ? "Customers" : "Clientes"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=sale_customers"><?php echo ($_SESSION['Idioma'] == 'en') ? "Sale Customers" : "Clientes de ventas"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=sale_customer_addresses"><?php echo ($_SESSION['Idioma'] == 'en') ? "Sale Customers Address" : "Direcciones de clientes de ventas"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=customer_type"><?php echo ($_SESSION['Idioma'] == 'en') ? "Customer Type" : "Tipo Cliente"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=organizations"><?php echo ($_SESSION['Idioma'] == 'en') ? "Organizations" : "Organizaciones"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=venues"><?php echo ($_SESSION['Idioma'] == 'en') ? "Venues" : "Lugares de eventos"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=referals"><?php echo ($_SESSION['Idioma'] == 'en') ? "Referrals" : "Referidos"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=operators"><?php echo ($_SESSION['Idioma'] == 'en') ? "Operators" : "Operadores"; ?></a></li>
                        <li><a class="dropdown-item" href="attendance.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Attendance Registration" : "Registro de Asistencia"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=schedules"><?php echo ($_SESSION['Idioma'] == 'en') ? "Schedule Management" : "Gestión de Horarios"; ?></a></li>
                        <li><a class="dropdown-item" href="attendance_report.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Attendance report" : "Reporte asistencia"; ?></a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li><a class="dropdown-item" href="crud.php?Id=products"><?php echo ($_SESSION['Idioma'] == 'en') ? "Products" : "Productos"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=categories"><?php echo ($_SESSION['Idioma'] == 'en') ? "Categories" : "Categorías"; ?></a></li>                        
                        <li><a class="dropdown-item" href="crud.php?Id=price_lists"><?php echo ($_SESSION['Idioma'] == 'en') ? "Price Lists" : "Listas de Precios"; ?></a></li>                        
                        <li><a class="dropdown-item" href="crud.php?Id=item_prices"><?php echo ($_SESSION['Idioma'] == 'en') ? "Item Prices" : "Precios de Artículos"; ?></a></li>                        
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li><a class="dropdown-item" href="crud.php?Id=discounts"><?php echo ($_SESSION['Idioma'] == 'en') ? "Discounts-Coupons-Rates" : "Descuentos-Cupones-Tarifas"; ?></a></li>
                        <li><a class="dropdown-item" href="documentcenter.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Document Center" : "Centro de Documentos"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=gifcard"><?php echo ($_SESSION['Idioma'] == 'en') ? "Gift Cards" : "Tarjetas de regalo"; ?></a></li>                
                        <li><a class="dropdown-item" href="crud.php?Id=surfaces"><?php echo ($_SESSION['Idioma'] == 'en') ? "Surfaces" : "Superficies"; ?></a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Logistics" : "Logística"; ?></h6></li>
                        <li><a class="dropdown-item" href="crud.php?Id=wharehouses"><?php echo ($_SESSION['Idioma'] == 'en') ? "Warehouses" : "Almacenes"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=inventory_stock"><?php echo ($_SESSION['Idioma'] == 'en') ? "Inventory" : "Inventario"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=venues"><?php echo ($_SESSION['Idioma'] == 'en') ? "Locations" : "Lugares"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=vehicles"><?php echo ($_SESSION['Idioma'] == 'en') ? "Vehicles" : "Vehículos"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=distance_charges"><?php echo ($_SESSION['Idioma'] == 'en') ? "Distance Charges" : "Cargos Distancia"; ?></a></li>
                        
                    </ul>
                </li>
<?php    
}
?>


<?php 
if ($_SESSION['role_id'] == 'LOGISTICS'){
?>
                <!-- INICIO -->
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <i class="fas fa-home"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Home" : "Inicio"; ?>
                    </a>
                </li>
                <!-- OPERACIÓN Y LOGÍSTICA -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-truck"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Operation" : "Operación"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="route.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Route Mapping" : "Armado de rutas"; ?></a></li>
                        <li><a class="dropdown-item" href="operation.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "General Operation" : "Operación General"; ?></a></li>
                        <li><a class="dropdown-item" href="acondicionamiento.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Conditioning" : "Acondicionamiento"; ?></a></li>
                        <li><a class="dropdown-item" href="monitor.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Monitor" : "Monitor"; ?></a></li>
                    </ul>
                </li>

                <!-- CATÁLOGOS / CONFIGURACIÓN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Settings" : "Configuración"; ?>
                    </a>
                    <ul class="dropdown-menu scrollable-menu" style="max-height: 600px; overflow-y: auto;">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Administration" : "Administración"; ?></h6></li>
                        <li><a class="dropdown-item" href="attendance.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Attendance Registration" : "Registro de Asistencia"; ?></a></li>
                        
                        <li><hr class="dropdown-divider"></li>
                        
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Logistics" : "Logística"; ?></h6></li>
                        <li><a class="dropdown-item" href="crud.php?Id=wharehouses"><?php echo ($_SESSION['Idioma'] == 'en') ? "Warehouses" : "Almacenes"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=inventory_stock"><?php echo ($_SESSION['Idioma'] == 'en') ? "Inventory" : "Inventario"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=venues"><?php echo ($_SESSION['Idioma'] == 'en') ? "Locations" : "Lugares"; ?></a></li>
                        <li><a class="dropdown-item" href="crud.php?Id=vehicles"><?php echo ($_SESSION['Idioma'] == 'en') ? "Vehicles" : "Vehículos"; ?></a></li>
                    </ul>
                </li>
<?php    
}
?>


<?php 
if ($_SESSION['role_id'] == 'DRIVER'){
?>
                <!-- OPERACIÓN Y LOGÍSTICA -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-truck"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Operation" : "Operación"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="operation.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "General Operation" : "Operación General"; ?></a></li>
                        <li><a class="dropdown-item" href="monitor.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Monitor" : "Monitor"; ?></a></li>
                    </ul>
                </li>

                <!-- CATÁLOGOS / CONFIGURACIÓN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Settings" : "Configuración"; ?>
                    </a>
                    <ul class="dropdown-menu scrollable-menu" style="max-height: 600px; overflow-y: auto;">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Administration" : "Administración"; ?></h6></li>
                        <li><a class="dropdown-item" href="attendance.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Attendance Registration" : "Registro de Asistencia"; ?></a></li>
                    </ul>
                </li>
<?php    
}
?>

<?php 
if ($_SESSION['role_id'] == 'ASSISTANT'){
?>
                <!-- INICIO -->
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <i class="fas fa-home"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Home" : "Inicio"; ?>
                    </a>
                </li>
                <!-- OPERACIÓN Y LOGÍSTICA -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-truck"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Operation" : "Operación"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="operation.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "General Operation" : "Operación General"; ?></a></li>
                        <li><a class="dropdown-item" href="acondicionamiento.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Conditioning" : "Acondicionamiento"; ?></a></li>
                        <li><a class="dropdown-item" href="monitor.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Monitor" : "Monitor"; ?></a></li>
                    </ul>
                </li>

                <!-- CATÁLOGOS / CONFIGURACIÓN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Settings" : "Configuración"; ?>
                    </a>
                    <ul class="dropdown-menu scrollable-menu" style="max-height: 600px; overflow-y: auto;">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Administration" : "Administración"; ?></h6></li>
                        <li><a class="dropdown-item" href="attendance.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Attendance Registration" : "Registro de Asistencia"; ?></a></li>
                    </ul>
                </li>
<?php    
}
?>

<?php 
if ($_SESSION['role_id'] == 'SALES'){
?>
                <!-- INICIO -->
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">
                        <i class="fas fa-home"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Home" : "Inicio"; ?>
                    </a>
                </li>
                <!-- VENTAS Y EVENTOS -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar-alt"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Sales" : "Ventas"; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Rents" : "Rentas"; ?></h6></li>
                        <li><a class="dropdown-item" href="lead.php"><i class="fas fa-plus me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "New Lead" : "Nuevo Evento"; ?></a></li>
                        <li><a class="dropdown-item" href="leads.php"><i class="fas fa-list me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Leads" : "Eventos"; ?></a></li>
                        <li><a class="dropdown-item" href="pending_payments.php"><i class="fas fa-money-bill me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Payments" : "Pagos Pendientes"; ?></a></li>
                        <li><a class="dropdown-item" href="payment_report.php"><i class="fa-solid fa-file-invoice-dollar"></i> <?php echo ($_SESSION['Idioma']== 'en') ? "Reports" : "Reporte de Pagos"; ?></a></li>
                        <li><a class="dropdown-item" href="graficas.php"><i class="fas fa-chart-line me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Graphics" : "Gráficas"; ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Sales" : "Ventas"; ?></h6></li>
                        <li><a class="dropdown-item" href="sale.php"><i class="fas fa-plus me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "New Sale" : "Nueva Venta"; ?></a></li>
                        <li><a class="dropdown-item" href="sales.php"><i class="fas fa-list me-2"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Sales" : "Ventas"; ?></a></li>
                        <li><a class="dropdown-item" href="admin_comments.php"><i class="fa-regular fa-comments"></i><?php echo ($_SESSION['Idioma']== 'en') ? "Sales Comments" : "Comentarios de Ventas"; ?></a></li>
                    </ul>
                </li>

                <!-- CATÁLOGOS / CONFIGURACIÓN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs"></i> <?php echo ($_SESSION['Idioma'] == 'en') ? "Settings" : "Configuración"; ?>
                    </a>
                    <ul class="dropdown-menu scrollable-menu" style="max-height: 600px; overflow-y: auto;">
                        <li><h6 class="dropdown-header"><?php echo ($_SESSION['Idioma'] == 'en') ? "Administration" : "Administración"; ?></h6></li>
                        <li><a class="dropdown-item" href="attendance.php"><?php echo ($_SESSION['Idioma'] == 'en') ? "Attendance Registration" : "Registro de Asistencia"; ?></a></li>
                    </ul>
                </li>

<?php    
}
?>


            </ul>
<!-- BUSCADOR, IDIOMA Y USUARIO A LA DERECHA -->
<div class="d-flex align-items-center gap-2">
    <!-- Buscador -->
    <form class="me-2" role="search">
        <div class="input-group input-group-sm">
            <input class="form-control" type="search" id="search_text" placeholder="<?php echo ($_SESSION['Idioma']== 'en') ? "Search..." : "Buscar..."; ?>">
            <button class="btn btn-outline-light" id="SearchButton" type="button"><i class="fas fa-search"></i></button>
        </div>
    </form>

    <!-- Idioma -->
    <div class="dropdown me-2">
        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-globe"></i> <?php echo strtoupper($_SESSION['Idioma'])?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item lang-option" href="#" data-lang="es">Español</a></li>
            <li><a class="dropdown-item lang-option" href="#" data-lang="en">English</a></li>
        </ul>
    </div>

    <!-- MENÚ DEL USUARIO LOGUEADO -->
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle me-2 fs-5"></i>
            <span><?php echo ($_SESSION['Idioma']== 'en') ? "User" : "Usuario"; ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow">
            <li>
                <div class="dropdown-header">
                    <strong><?php echo $_SESSION['role_id'] ?? 'Rol'; ?></strong>
                </div>
            </li>
            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2 text-muted"></i> <?php echo $_SESSION['user'] ?? 'Usuario'; ?></a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="#" id="logout-link">
                    <i class="fas fa-sign-out-alt me-2"></i><?php echo ($_SESSION['Idioma'] == 'en') ? "Sign Out" : "Salir"; ?>
                </a>
            </li>
        </ul>
    </div>
</div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('logout-link').addEventListener('click', function(e) {
        e.preventDefault(); // Evita que el enlace redirija inmediatamente
        
        // 1. Borramos el token del navegador
        localStorage.removeItem('apiToken');
        
        // 2. Redirigimos al archivo PHP que cierra la sesión en el servidor
        window.location.href = 'logout.php';
    });
</script>  