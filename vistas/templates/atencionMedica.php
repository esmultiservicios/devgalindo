<div class="container-fluid" id="atencionMedica" style="display: none;">
    <form class="FormularioAjax" id="formulario_atenciones" action="" method="POST" data-form="" autocomplete="off"
        enctype="multipart/form-data">

        <div class="d-flex justify-content-start mb-4 sticky-buttons">
            <button class="btn btn-primary mr-2" type="submit" id="reg_atencion" form="formulario_atenciones">
                <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
            </button>
            <button class="btn btn-primary mx-2" type="button" id="limpiar-registro-atenciones"
                form="formulario_atenciones">
                <div class="sb-nav-link-icon"></div><i class="fas fa-plus fa-lg"></i> Nuevo Registro
            </button>
            <button class="btn btn-primary mx-2" type="submit" id="edi_atencion" form="formulario_atenciones">
                <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
            </button>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <!-- Ocupar todo el ancho disponible -->
                <div class="form-row">
                    <div class="col-md-12 mb-3">
                        <input type="hidden" id="agenda_id" name="agenda_id" class="form-control">
                        <input type="hidden" required readonly id="pacientes_id" name="pacientes_id" />
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

                <!-- INICIO PACIENTES -->
                <?php include ('templates/seccion_generales.php'); ?>
                <!-- FIN PACIENTES -->

                <!-- INICIO HISTORIA CLINICA -->
                <?php include ('templates/historia_clinca.php'); ?>
                <!-- FIN HISTORIA CLINICA -->
            </div>
        </div>
    </form>
</div>