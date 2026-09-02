<?php
session_start();
include '../php/funtions.php';

if (isset($_SESSION['colaborador_id']) == false) {
    header('Location: login.php');
}

$_SESSION['menu'] = 'Atenciones Medicas';

if (isset($_SESSION['colaborador_id'])) {
    $colaborador_id = $_SESSION['colaborador_id'];
} else {
    $colaborador_id = '';
}

$type = $_SESSION['type'];

$nombre_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);  // HOSTNAME
$fecha = date('Y-m-d H:i:s');
$comentario = mb_convert_case('Ingreso al Modulo de Atenciones Medicas', MB_CASE_TITLE, 'UTF-8');

if ($colaborador_id != '' || $colaborador_id != null) {
    historial_acceso($comentario, $nombre_host, $colaborador_id);
}
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
    <title>Atenciones Medicas :: <?php echo SERVEREMPRESA; ?></title>
    <?php include ('script_css.php'); ?>

    <style>
        /* Toolbar profesional y responsive: etiquetas arriba, sin controles partidos */
        #main_facturacion .atenciones-toolbar {
            display: grid;
            grid-template-columns: minmax(160px,.75fr) minmax(175px,.8fr) minmax(175px,.8fr) minmax(300px,1.45fr);
            gap: 12px;
            align-items: end;
            margin-bottom: 12px;
        }
        #main_facturacion .toolbar-field { min-width: 0; }
        #main_facturacion .toolbar-label {
            display: block;
            margin: 0 0 6px 2px;
            font-size: .78rem;
            line-height: 1.15;
            font-weight: 700;
            color: #536473;
        }
        #main_facturacion .toolbar-field .form-control,
        #main_facturacion .toolbar-field .bootstrap-select,
        #main_facturacion .toolbar-field .bootstrap-select > .dropdown-toggle { width: 100% !important; min-height: 38px; }
        #main_facturacion .toolbar-actions {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            padding-top: 2px;
        }
        #main_facturacion .toolbar-actions .btn,
        #main_facturacion .toolbar-actions .dropdown > .btn {
            min-height: 39px;
            margin: 0 !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
            border-radius: 5px;
        }
        #main_facturacion #agrega-registros-atenciones { overflow: visible !important; }

        /* Listado final 100% DIVs */
        .atenciones-div-list { width: 100%; border: 1px solid #d9e3e8; border-radius: 10px; overflow: hidden; background: #fff; }
        .atenciones-div-header, .atenciones-div-row {
            display: grid;
            grid-template-columns: minmax(90px,.65fr) minmax(90px,.65fr) minmax(210px,1.45fr) minmax(150px,1fr) minmax(130px,.9fr) minmax(150px,1fr) minmax(115px,.8fr) minmax(170px,1.15fr) minmax(170px,1.15fr) minmax(130px,.9fr);
            width: 100%;
        }
        .atenciones-div-header { background: #1c9fb0; color: #fff; font-weight: 600; }
        .atenciones-div-header > div, .atenciones-div-cell { padding: 12px 11px; min-width: 0; overflow-wrap: anywhere; }
        .atenciones-div-row { border-top: 1px solid #e8eef1; align-items: center; }
        .atenciones-div-row:nth-child(odd) { background: #fbfcfd; }
        .atenciones-div-cell .atenciones-cell-label { display: none; }
        .atenciones-div-cell .atenciones-cell-value { min-width: 0; }
        .atenciones-div-cell .btn, .atenciones-div-cell .dropdown-toggle { max-width: 100%; white-space: normal; }
        .atenciones-div-empty { grid-column: 1 / -1; padding: 28px 16px; text-align: center; color: #dc3545; background: #fafafa; }

        @media (max-width: 1199.98px) {
            #main_facturacion .atenciones-toolbar { grid-template-columns: repeat(2, minmax(0,1fr)); }
            #main_facturacion .toolbar-field-search { grid-column: 1 / -1; }
            .atenciones-div-list { border: 0; background: transparent; overflow: visible; }
            .atenciones-div-header { display: none; }
            .atenciones-div-row { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)) !important; gap: 0; margin-bottom: 12px; border: 1px solid #d9e3e8; border-radius: 10px; overflow: hidden; background: #fff !important; }
            .atenciones-div-cell { border-bottom: 1px solid #edf1f3; padding: 10px 12px; }
            .atenciones-div-cell .atenciones-cell-label { display: block; font-size: .75rem; font-weight: 700; color: #607080; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .02em; }
        }
        @media (max-width: 767.98px) {
            #main_facturacion .atenciones-toolbar { grid-template-columns: 1fr; gap: 10px; }
            #main_facturacion .toolbar-field-search { grid-column: auto; }
            #main_facturacion .toolbar-actions { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 8px; }
            #main_facturacion .toolbar-actions .btn,
            #main_facturacion .toolbar-actions .dropdown,
            #main_facturacion .toolbar-actions .dropdown > .btn { width: 100%; }
            .atenciones-div-row { grid-template-columns: 1fr !important; }
            .atenciones-div-cell { display: grid; grid-template-columns: minmax(105px, 38%) 1fr; gap: 10px; align-items: start; }
            .atenciones-div-cell .atenciones-cell-label { margin: 0; }
            #main_facturacion .pagination { flex-wrap: wrap; gap: 4px; }
        }
        @media (max-width: 420px) {
            #main_facturacion .toolbar-actions { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <!--Ventanas Modales-->
    <!-- Small modal -->
    <?php include ('templates/modals.php'); ?>

    <!--MODAL BUSCAR ATENCIONES-->
    <div class="modal fade" id="buscar_atencion">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Búsqueda de Atenciones</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="FormularioAjax" id="formulario_buscarAtencion" data-async data-target="#rating-modal"
                        action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <input type="hidden" id="atencion_id" name="atencion_id" class="form-control"
                                    required="required">
                                <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control"
                                    required="required">
                            </div>
                        </div>
                        <div class="form-row" id="grupo_expediente">
                            <div class="col-md-12 mb-3">
                                <input type="text" name="busqueda" id="busqueda"
                                    placeholder="Buscar por: Nombre, Apellido o Identidad" data-toggle="tooltip"
                                    data-placement="top"
                                    title="Búsqueda de Atenciones por: Nombre, Apellido o Identidad"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <div class="registros overflow-auto" id="agrega_registros_busqueda"></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination justify-content-center" id="pagination_busqueda"></ul>
                                </nav>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <div class="registros overflow-auto" id="agrega_registros_busqueda_"></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <nav aria-label="Page navigation example">
                                    <ul class="pagination justify-content-center" id="pagination_busqueda_"></ul>
                                </nav>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">

                </div>
            </div>
        </div>
    </div>
    <!-- FIN MODAL BUSCAR ATENCIONES -->

<!--INICIO MODAL TRANSITO-->
    <div class="modal fade" id="registro_transito_eviada">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Transito Enviada</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="FormularioAjax" id="formulario_transito_enviada" data-async data-target="#rating-modal"
                        action="" method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control"
                                    required="required">
                                <input type="hidden" id="colaborador_id" name="colaborador_id" class="form-control"
                                    required="required">
                                <div class="input-group mb-3">
                                    <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label for="paciente_te">Paciente <span class="priority">*</span></label>
                                <div class="input-group mb-3">
                                    <select class="selectpicker" id="paciente_te" name="paciente_te"
                                        data-live-search="true" title="Paciente" data-width="100%" data-size="7">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha">Fecha <span class="priority">*</span></label>
                                <input type="date" required id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>"
                                    class="form-control" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label for="identidad">Identidad</label>
                                <input type="text" name="identidad" id="identidad" placeholder="Identidad" readonly
                                    class="form-control" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="enviada">Enviada a <span class="priority">*</span></label>
                                <div class="input-group mb-3">
                                    <select class="selectpicker" id="enviada" name="enviada" data-live-search="true"
                                        title="Enviada a" data-width="100%" data-size="7">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <label for="motivo">Motivo <span class="priority">*</span></label>
                                <textarea id="motivo" name="motivo" required placeholder="Motivo de la Referencia"
                                    class="form-control" maxlength="255" rows="3"></textarea>
                                <p id="charNumMotivoTE">255 Caracteres</p>
                            </div>
                        </div>


                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary ml-2" form="formulario_transito_enviada" type="submit"
                        id="reg_transitoe">
                        <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registro_transito_recibida">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Transito Recibida</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="FormularioAjax" id="formulario_transito_recibida" data-async
                        data-target="#rating-modal" action="" method="POST" data-form="" autocomplete="off"
                        enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <input type="hidden" id="pacientes_id" name="pacientes_id" class="form-control"
                                    required="required">
                                <input type="hidden" id="colaborador_id" name="colaborador_id" class="form-control"
                                    required="required">
                                <div class="input-group mb-3">
                                    <input type="text" required readonly id="pro" name="pro" class="form-control" />
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <div class="sb-nav-link-icon"></div><i class="fa fa-plus-square"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label for="paciente_tr">Paciente <span class="priority">*</span></label>
                                <div class="input-group mb-3">
                                    <select class="selectpicker" id="paciente_tr" name="paciente_tr"
                                        data-live-search="true" title="Paciente" data-width="100%" data-size="7">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha">Fecha <span class="priority">*</span></label>
                                <input type="date" required id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>"
                                    class="form-control" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-3">
                                <label for="identidad">Identidad</label>
                                <input type="text" name="identidad" id="identidad" placeholder="Identidad" readonly
                                    class="form-control" />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="recibida">Recibida de <span class="priority">*</span></label>
                                <div class="input-group mb-3">
                                    <select class="selectpicker" id="recibida" name="recibida" data-live-search="true"
                                        title="Recibida de" data-width="100%" data-size="7">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-md-12 mb-3">
                                <label for="motivo">Motivo <span class="priority">*</span></label>
                                <textarea id="motivo" name="motivo" required placeholder="Motivo de la Referencia"
                                    class="form-control" maxlength="255" rows="3"></textarea>
                                <p id="charNumMotivoTE">255 Caracteres</p>
                            </div>
                        </div>


                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary ml-2" form="formulario_transito_recibida" type="submit"
                        id="reg_transitor">
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
            <li class="breadcrumb-item" id="acciones_atras"><a id="ancla_volver" class="breadcrumb-link"
                    href="#">Atenciones Medicas</a></li>
            <li class="breadcrumb-item active" id="acciones_factura"><span id="label_acciones_factura"></span></li>
        </ol>

        <div id="main_facturacion">
            <form id="form_main" class="atenciones-toolbar">
                <div class="toolbar-field">
                    <label class="toolbar-label" for="estado">Atención</label>
                    <select class="selectpicker" id="estado" name="estado" data-live-search="true" title="Seleccione" data-width="100%" data-size="7"></select>
                </div>
                <div class="toolbar-field">
                    <label class="toolbar-label" for="fecha_b">Fecha Inicio</label>
                    <input type="date" required id="fecha_b" name="fecha_b" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
                </div>
                <div class="toolbar-field">
                    <label class="toolbar-label" for="fecha_f">Fecha Fin</label>
                    <input type="date" required id="fecha_f" name="fecha_f" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
                </div>
                <div class="toolbar-field toolbar-field-search">
                    <label class="toolbar-label" for="bs_regis">Paciente</label>
                    <input type="text" placeholder="Buscar por expediente, nombre o identidad" data-toggle="tooltip" data-placement="top" title="Buscar por Expediente, Nombre, Apellido o Identidad" id="bs_regis" autofocus class="form-control" />
                </div>

                <div class="toolbar-actions">
                    <button class="btn btn-primary" type="submit" id="nuevo_registro"><i class="fas fa-plus-circle fa-lg"></i> Generar Atención</button>
                    <button class="btn btn-primary" type="submit" id="nuevo-registro"><i class="fas fa-user-plus fa-lg"></i> Registrar Pacientes</button>
                    <button class="btn btn-primary" type="submit" id="nueva_factura"><i class="fas fa-file-invoice fa-lg"></i> Pre Factura</button>
                    <div class="dropdown">
                        <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-plus-circle fa-lg"></i> Transito Pacientes</a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="#" id="transito_enviada">Transito Enviada</a>
                            <a class="dropdown-item" href="#" id="transito_recibida">Transito Recibida</a>
                        </div>
                    </div>
                    <button class="btn btn-info" type="button" id="buscar-expediente-pdf-principal" data-toggle="tooltip" data-placement="top" title="Buscar un paciente y visualizar su expediente clínico en PDF"><i class="fas fa-folder-open fa-lg"></i> Buscar Expediente</button>
                    <button class="btn btn-success" type="submit" id="historial"><i class="fas fa-search fa-lg"></i> Buscar</button>
                </div>
            </form>
            <hr />
            <div class="registros" id="agrega-registros-atenciones"></div>
            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center" id="pagination-atenciones"></ul>
            </nav>
        </div>
        <?php include ('templates/atencionMedica.php'); ?>
        <?php include ('templates/factura.php'); ?>
        <?php include ('templates/footer.php'); ?>
        <?php include ('templates/footer_facturas.php'); ?>
    </div>

    <!-- add javascripts -->
    <?php
        include 'script.php';

        include '../js/main.php';
        include '../js/invoice.php';
        include '../js/myjava_pacientes.php';
        include '../js/local_storage.php';
        include '../js/myjava_atencion_medica.php';
        include '../js/select.php';
        include '../js/functions.php';
        include '../js/myjava_cambiar_pass.php';
    ?>

</body>

</html>