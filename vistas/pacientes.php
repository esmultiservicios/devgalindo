<?php
session_start();
include '../php/funtions.php';

// CONEXION A DB
$mysqli = connect_mysqli();

if (isset($_SESSION['colaborador_id']) == false) {
    header('Location: login.php');
}

$_SESSION['menu'] = 'Pacientes';

if (isset($_SESSION['colaborador_id'])) {
    $colaborador_id = $_SESSION['colaborador_id'];
} else {
    $colaborador_id = '';
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);  // HOSTNAME
$fecha = date('Y-m-d H:i:s');
$comentario = mb_convert_case('Ingreso al Modulo de Pacientes', MB_CASE_TITLE, 'UTF-8');

if ($colaborador_id != '' || $colaborador_id != null) {
    historial_acceso($comentario, $nombre_host, $colaborador_id);
}

$mysqli->close();  // CERRAR CONEXIÓN
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="author" content="Script Tutorials" />
    <meta name="description" content="Responsive Websites Orden Hospitalaria de San Juan de Dios">
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pacientes :: <?php echo SERVEREMPRESA; ?></title>
    <?php include ('script_css.php'); ?>
</head>

<body>
    <!--Ventanas Modales-->
    <!-- Small modal -->
    <?php include ('templates/modals.php'); ?>

    <!--INICIO MODAL-->

    <div class="modal fade" id="mensaje_show" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Información Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="mensaje_sistema">
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <div class="modal-title" id="mensaje_mensaje_show"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success ml-2" type="submit" id="okay" data-dismiss="modal">
                        <div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay
                    </button>
                    <button class="btn btn-danger ml-2" type="submit" id="bad" data-dismiss="modal">
                        <div class="sb-nav-link-icon"></div><i class="fas fa-thumbs-up fa-lg"></i> Okay
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_profesiones">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Profesiones</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="container"></div>
                <div class="modal-body">
                    <form class="FormularioAjax" id="formulario_profesiones" action="" method="POST" data-form=""
                        autocomplete="off" enctype="multipart/form-data">
                        <div class="form-row" id="grupo_expediente">
                            <div class="col-md-12 mb-3">
                                <input type="text" required="required" id="profesionales_buscar"
                                    name="profesionales_buscar" class="form-control"
                                    placeholder="Buscar por: Profesionales" id="centros_buscar" maxlength="100" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination" id="agrega_registros_profesionales"></ul>
                                </nav>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <center>
                                    <ul class="pagination justify-content-center" id="pagination_profesionales"></ul>
                                </center>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary ml-2" type="submit" id="reg_profesionales"
                        form="formulario_profesiones">
                        <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                    </button>
                </div>
            </div>
        </div>
    </div>


    <?php include ('modals/modals.php'); ?>

    <!--Fin Ventanas Modales-->
    <!--MENU-->
    <?php include ('templates/menu.php'); ?>
    <!--FIN MENU-->

    <br><br><br>
    <div class="container-fluid">
        <ol class="breadcrumb mt-2 mb-4">
            <li class="breadcrumb-item"><a class="breadcrumb-link"
                    href="<?php echo SERVERURL; ?>vistas/inicio.php">Dashboard</a></li>
            <li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span>Pacientes
            </li>
        </ol>

        <div id="main_facturacion">
            <div class="card mb-4">
                <div class="card-body">
                    <form class="form-inline" id="form_main">
                        <div class="form-group mx-sm-3 mb-1">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <div class="sb-nav-link-icon"></div>Estado
                                </span>
                                <select id="estado" name="estado" class="selectpicker" title="Estado"
                                    data-live-search="true">
                                </select>
                            </div>
                        </div>
                        <div class="form-group mx-sm-3 mb-1">
                            <input type="text" placeholder="Buscar por: Expediente, Nombre, Apellido o Identidad"
                                data-toggle="tooltip" data-placement="top"
                                title="Buscar por: Expediente, Nombre, Apellido o Identidad" id="bs_regis" autofocus
                                class="form-control" size="50" />
                        </div>
                        <div class="form-group mx-sm-3 mb-1">
                            <button class="btn btn-primary ml-1" type="submit" id="nuevo-registro">
                                <div class="sb-nav-link-icon" data-toggle="tooltip" data-placement="top"
                                    title="Registrar Pacientes"></div><i class="fas fa-user-plus fa-lg"></i> Registrar
                                Pacientes
                            </button>
                            <button class="btn btn-primary ml-1" type="submit" id="profesion">
                                <div class="sb-nav-link-icon" data-toggle="tooltip" data-placement="top"
                                    title="Registrar Profesión"></div><i class="fas fa-download fa-lg"></i> Registrar
                                Profesión
                            </button>
                            <button class="btn btn-success ml-1" type="submit" id="reporte">
                                <div class="sb-nav-link-icon" data-toggle="tooltip" data-placement="top"
                                    title="Exportar"></div><i class="fas fa-download fa-lg"></i> Exportar Pacientes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user mr-1"></i>
                    Pacientes
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <div class="registros overflow-auto" id="agrega-registros"></div>
                        </div>
                    </div>
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>
    <?php include ('templates/factura.php'); ?>
    <?php include ('templates/footer.php'); ?>
    </div>

    <!-- add javascripts -->
    <?php
        include 'script.php';

        include '../js/main.php';
        include '../js/myjava_pacientes.php';
        include '../js/select.php';
        include '../js/functions.php';
        include '../js/myjava_cambiar_pass.php';
    ?>
</body>

</html>