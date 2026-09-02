<style>
    #atencionMedica .seccion-atencion {
        margin-bottom: 2rem;
    }

    #atencionMedica .titulo-seccion-atencion {
        background: #f8f9fa;
        border-left: 4px solid #17a2b8;
        border-bottom: 1px solid #dee2e6;
        padding: 0.8rem 1rem;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
    }

    #atencionMedica .card {
        height: 100%;
    }

    #atencionMedica .card-body {
        display: flex;
        flex-direction: column;
    }

    #atencionMedica textarea.form-control {
        resize: vertical;
    }

    #atencionMedica .sticky-buttons {
        position: sticky;
        top: 70px;
        z-index: 20;
        background: #fff;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    #atencionMedica #seccion_datos_paciente {
        margin-bottom: 1.25rem;
    }

    #atencionMedica #seccion_datos_paciente .titulo-seccion-atencion {
        margin-bottom: 0.65rem;
        padding: 0.65rem 0.85rem;
    }

    #atencionMedica #seccion_datos_paciente .form-row {
        margin-bottom: 0;
    }

    #atencionMedica #seccion_datos_paciente [class*="col-"] {
        margin-bottom: 0.55rem !important;
    }

    #atencionMedica #seccion_datos_paciente label {
        margin-bottom: 0.25rem;
    }

    #atencionMedica #seccion_datos_paciente .input-group {
        margin-bottom: 0 !important;
    }

    #atencionMedica #seccion_datos_paciente .form-control,
    #atencionMedica #seccion_datos_paciente .bootstrap-select > .dropdown-toggle {
        min-height: 38px;
    }

    #atencionMedica #seccion_seguimiento .card {
        border: 1px solid #dfe7ef;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        background: #fff;
    }

    #atencionMedica .seguimiento-premium-subtitle {
        color: #6c757d;
        font-size: 0.92rem;
        margin-top: 0.35rem;
    }

    #atencionMedica .seguimiento-premium-wrap {
        background: #fbfdff;
        border: 1px solid #e6edf2;
        border-radius: 14px;
        padding: 1rem;
        min-height: 280px;
    }

    #atencionMedica .seguimiento-premium-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.85rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #e9eef3;
    }

    #atencionMedica .seguimiento-premium-title {
        font-size: 1rem;
        font-weight: 600;
        color: #22313f;
    }

    #atencionMedica .seguimiento-premium-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: #e8f6f8;
        color: #0f6d7a;
        font-weight: 600;
        font-size: 0.85rem;
    }

    #atencionMedica .seguimiento-timeline {
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
        max-height: 420px;
        overflow-y: auto;
        padding-right: 0.15rem;
    }

    #atencionMedica .seguimiento-item {
        border: 1px solid #e5ecf2;
        border-left: 4px solid #17a2b8;
        border-radius: 12px;
        background: #fff;
        padding: 0.9rem 1rem;
    }

    #atencionMedica .seguimiento-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.55rem;
    }

    #atencionMedica .seguimiento-item-title {
        font-weight: 600;
        color: #22313f;
    }

    #atencionMedica .seguimiento-item-date {
        font-size: 0.88rem;
        color: #0f6d7a;
        font-weight: 500;
    }

    #atencionMedica .seguimiento-item-body {
        color: #495057;
        line-height: 1.6;
        white-space: pre-line;
    }

    #atencionMedica .seguimiento-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: #6c757d;
        background: #fff;
        border: 1px dashed #cfd9df;
        border-radius: 12px;
    }

    #atencionMedica .seguimiento-empty i {
        display: block;
        font-size: 1.75rem;
        color: #17a2b8;
        margin-bottom: 0.65rem;
    }

    #atencionMedica #seguimiento_read {
        display: none;
    }


    .btn-expediente-pdf {
        background: #0f7f8d;
        border-color: #0f7f8d;
        color: #fff;
        font-weight: 600;
    }
    .btn-expediente-pdf:hover,
    .btn-expediente-pdf:focus { background: #0b6975; border-color: #0b6975; color: #fff; }

    #modal_buscar_expediente_pdf .modal-dialog {
        max-width: 900px;
        width: calc(100% - 30px);
        height: min(760px, calc(100vh - 30px));
        margin: 15px auto;
    }
    #modal_buscar_expediente_pdf .modal-content,
    #modal_visualizar_expediente_pdf .modal-content {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .22);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    #modal_buscar_expediente_pdf .modal-header,
    #modal_visualizar_expediente_pdf .modal-header {
        background: #0f7f8d;
        color: #fff;
        border-bottom: 0;
        padding: 16px 20px;
    }
    #modal_buscar_expediente_pdf .modal-header .close,
    #modal_visualizar_expediente_pdf .modal-header .close { color: #fff; opacity: 1; text-shadow: none; }
    #modal_buscar_expediente_pdf .modal-body { padding: 20px !important; background: #f7f9fa; flex: 1 1 auto; min-height: 0; overflow-y: auto; }
    #modal_buscar_expediente_pdf .expediente-search-box {
        background: #fff;
        border: 1px solid #dce6ea;
        border-radius: 12px;
        padding: 16px;
        min-height: 100%;
    }
    #modal_buscar_expediente_pdf .expediente-search-input-wrap { position: relative; }
    #modal_buscar_expediente_pdf .expediente-search-input-wrap i {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #73808c;
    }
    #modal_buscar_expediente_pdf #buscar_paciente_expediente_pdf { height: 44px; padding-left: 40px; border-radius: 8px; }
    #modal_buscar_expediente_pdf .expediente-resultados {
        margin-top: 12px;
        max-height: 270px;
        overflow-y: auto;
        border: 1px solid #e1e8eb;
        border-radius: 10px;
        background: #fff;
    }
    #modal_buscar_expediente_pdf .expediente-resultado-item {
        display: grid;
        grid-template-columns: 42px minmax(0,1fr) auto;
        gap: 10px;
        align-items: center;
        width: 100%;
        padding: 11px 12px;
        border: 0;
        border-bottom: 1px solid #edf1f3;
        background: #fff;
        text-align: left;
        cursor: pointer;
    }
    #modal_buscar_expediente_pdf .expediente-resultado-item:last-child { border-bottom: 0; }
    #modal_buscar_expediente_pdf .expediente-resultado-item:hover,
    #modal_buscar_expediente_pdf .expediente-resultado-item.active { background: #f0f9fa; }
    #modal_buscar_expediente_pdf .expediente-avatar {
        width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: #e5f5f7; color: #0f7f8d; font-weight: 700;
    }
    #modal_buscar_expediente_pdf .expediente-resultado-nombre { font-weight: 700; color: #23313c; line-height: 1.2; }
    #modal_buscar_expediente_pdf .expediente-resultado-meta { font-size: .78rem; color: #74818d; margin-top: 3px; }
    #modal_buscar_expediente_pdf .expediente-seleccionado {
        margin-top: 14px;
        border: 1px solid #cfe1e5;
        border-left: 4px solid #0f7f8d;
        border-radius: 10px;
        background: #fff;
        padding: 14px;
        display: none;
    }
    #modal_buscar_expediente_pdf .expediente-seleccionado.show { display: block; }
    #modal_buscar_expediente_pdf .expediente-seleccionado-title { font-weight: 700; font-size: 1rem; color: #21313c; margin-bottom: 10px; }
    #modal_buscar_expediente_pdf .expediente-mini-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 8px 14px; }
    #modal_buscar_expediente_pdf .expediente-mini-dato { min-width: 0; }
    #modal_buscar_expediente_pdf .expediente-mini-label { display: block; font-size: .7rem; font-weight: 700; color: #7a8792; text-transform: uppercase; }
    #modal_buscar_expediente_pdf .expediente-mini-value { display: block; color: #263642; overflow-wrap: anywhere; }
    #modal_buscar_expediente_pdf .expediente-empty { padding: 24px 14px; text-align: center; color: #77838e; }
    #modal_buscar_expediente_pdf .modal-footer { border-top: 1px solid #e5ecef; background: #fff; padding: 12px 20px; }

    #modal_visualizar_expediente_pdf .modal-dialog {
        max-width: 1100px;
        width: calc(100% - 30px);
        height: calc(100vh - 30px);
        margin: 15px auto;
    }
    #modal_visualizar_expediente_pdf .modal-body { padding: 0; background: #eef3f5; flex: 1 1 auto; min-height: 0; overflow: hidden; }
    #visor_expediente_pdf { width: 100%; height: 100%; min-height: 0; border: 0; display: block; background: #fff; }

    @media (max-width: 767.98px) {
        #modal_buscar_expediente_pdf .modal-dialog,
        #modal_visualizar_expediente_pdf .modal-dialog { width: calc(100% - 12px); height: calc(100vh - 12px); margin: 6px auto; }
        #modal_buscar_expediente_pdf .modal-body { padding: 14px !important; }
        #modal_buscar_expediente_pdf .expediente-mini-grid { grid-template-columns: 1fr 1fr; }
        #visor_expediente_pdf { height: 100%; min-height: 0; }
    }
    @media (max-width: 480px) {
        #modal_buscar_expediente_pdf .expediente-mini-grid { grid-template-columns: 1fr; }
        #modal_buscar_expediente_pdf .expediente-resultado-item { grid-template-columns: 38px minmax(0,1fr); }
        #modal_buscar_expediente_pdf .expediente-resultado-item .fa-chevron-right { display: none; }
    }

    @media (max-width: 767.98px) {
        #atencionMedica .sticky-buttons {
            position: static;
        }
    }
</style>

<div class="container-fluid" id="atencionMedica" style="display: none;">
    <form class="FormularioAjax" id="formulario_atenciones" action="" method="POST" data-form="" autocomplete="off"
        enctype="multipart/form-data">
        <div class="d-flex flex-wrap justify-content-start mb-4 sticky-buttons">
            <button class="btn btn-primary mr-2 mb-2" type="submit" id="reg_atencion" form="formulario_atenciones">
                <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
            </button>
            <button class="btn btn-primary mr-2 mb-2" type="button" id="limpiar-registro-atenciones"
                form="formulario_atenciones">
                <div class="sb-nav-link-icon"></div><i class="fas fa-plus fa-lg"></i> Nuevo Registro
            </button>
            <button class="btn btn-expediente-pdf mr-2 mb-2" type="button" id="descargar-expediente-pdf" disabled>
                <i class="fas fa-file-pdf fa-lg mr-1"></i> Ver PDF del Paciente
            </button>
            <button class="btn btn-secondary mr-2 mb-2" type="button" id="buscar-expediente-pdf">
                <i class="fas fa-search fa-lg mr-1"></i> Buscar Expediente
            </button>
            <button class="btn btn-primary mr-2 mb-2" type="submit" id="edi_atencion" form="formulario_atenciones">
                <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
            </button>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
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

                <section class="seccion-atencion" id="seccion_datos_paciente">
                    <div class="titulo-seccion-atencion">
                        <i class="fas fa-info-circle mr-2"></i>Datos del Paciente
                    </div>
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="paciente_consulta">Paciente <span class="priority">*</span></label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="paciente_consulta" name="paciente_consulta" required
                                data-live-search="true" title="Paciente" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Fecha de Registro <span class="priority">*</span></label>
                        <input type="date" id="fecha" name="fecha" required value="<?php echo date('Y-m-d'); ?>"
                            class="form-control" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="edad">Edad</label>
                        <input type="text" id="edad" name="edad" readonly class="form-control" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="religion_id">Religión</label>
                        <input type="text" id="religion_id" name="religion_id"
                            placeholder="Religión" class="form-control" maxlength="100" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label>Fecha de Nacimiento <span class="priority">*</span></label>
                        <div class="input-group mb-3">
                            <input type="date" id="fecha_nac" name="fecha_nac" required value="<?php echo date('Y-m-d'); ?>"
                                class="form-control" />
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="telefono">Teléfono 1 <span class="priority">*</span></label>
                        <div class="input-group mb-3">
                            <input type="text" id="telefono1" name="telefono1" class="form-control"
                                placeholder="Primer Teléfono" required maxlength="8"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="identidad">Identidad o RTN</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="identidad" name="identidad"
                                placeholder="Identidad o RTN">
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estado_civil">Estado Civil</label>
                        <input type="text" id="estado_civil" name="estado_civil"
                            placeholder="Estado Civil" class="form-control" maxlength="100" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="profesion_id">Profesión</label>
                        <input type="text" id="profesion_id" name="profesion_id"
                            placeholder="Profesión" class="form-control" maxlength="150" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="num_hijos">Número de Hijos</label>
                        <input type="number" name="num_hijos" id="num_hijos" value="" class="form-control" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="servicio_id">Consultorio</label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="servicio_id" name="servicio_id" required data-live-search="true"
                                title="Consultorio" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="escolaridad">Escolaridad</label>
                        <input type="text" id="escolaridad" name="escolaridad"
                            placeholder="Escolaridad" class="form-control" maxlength="100" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="red_apoyo">Red de Apoyo</label>
                        <input type="text" name="red_apoyo" id="red_apoyo" placeholder="Red de Apoyo"
                            class="form-control" maxlength="100" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="terapeuta_actual">Terapeuta Actual</label>
                        <input type="text" name="terapeuta_actual" id="terapeuta_actual" placeholder="Terapeuta Actual"
                            class="form-control" maxlength="100" />
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-12 mb-3">
                        <label for="procedencia">Dirección</label>
                        <input type="text" name="procedencia" id="procedencia" placeholder="Dirección"
                            class="form-control" maxlength="255" />
                    </div>
                </div>
                </section>

                <section class="seccion-atencion" id="seccion_historia_clinica">
                    <div class="titulo-seccion-atencion">
                        <i class="fas fa-book-medical mr-2"></i>Historia Clínica
                    </div>
                <div class="form-row">
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Antecedentes Médicos no Psiquiatricos
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="antecedentes_medicos_no_psiquiatricos"
                                        name="antecedentes_medicos_no_psiquiatricos"
                                        placeholder="Antecedentes Médicos no Psiquiatricos" class="form-control"
                                        maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_antecedentes_medicos_no_psiquiatricos_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_antecedentes_medicos_no_psiquiatricos_stop"></i>
                                        </span>
                                    </div>
                                </div>
                                <p id="charNum_antecedentes_medicos_no_psiquiatricos">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Hospitalizaciones
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="hospitalizaciones" name="hospitalizaciones"
                                        placeholder="Hospitalizaciones" class="form-control" maxlength="3200"
                                        rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_hospitaliaciones_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_hospitaliaciones_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_hospitaliaciones">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Cirugías
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="cirugias" name="cirugias" placeholder="Cirugías" class="form-control"
                                        maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_cirugias_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_cirugias_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_cirugias">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Alergias
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="alergias" name="alergias" placeholder="Alergias" class="form-control"
                                        maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_alergias_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_alergias_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_alergias">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Antecedentes médicos psiquiátricos
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="antecedentes_medicos_psiquiatricos"
                                        name="antecedentes_medicos_psiquiatricos"
                                        placeholder="Antecedentes médicos psiquiátricos" class="form-control"
                                        maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_antecedentes_medicos_psiquiatricos_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_antecedentes_medicos_psiquiatricos_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_antecedentes_medicos_psiquiatricos">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Historia Gineco-obstétrica: Menarquia, ciclos menstruales y síntomas asociados,
                                Embarazos, hijos, abortos
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="historia_gineco_obstetrica" name="historia_gineco_obstetrica"
                                        placeholder="Historia Gineco-obstétrica: Menarquia, ciclos menstruales y síntomas asociados, Embarazos, hijos, abortos"
                                        class="form-control" maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_historia_gineco_obstetrica_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_historia_gineco_obstetrica_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_historia_gineco_obstetrica">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Medicamentos previos
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="medicamentos_previos" name="medicamentos_previos"
                                        placeholder="Medicamentos previos" class="form-control" maxlength="3200"
                                        rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_medicamentos_previos_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_medicamentos_previos_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_medicamentos_previos">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Medicamentos actuales (esquema de tratamiento)
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="medicamentos_actuales" name="medicamentos_actuales"
                                        placeholder="Medicamentos actuales (esquema de tratamiento)"
                                        class="form-control" maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_medicamentos_actuales_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_medicamentos_actuales_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_medicamentos_actuales">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Legal
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="legal" name="legal" placeholder="Legal" class="form-control"
                                        maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_legal_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_legal_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_legal">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Sustancias
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="sustancias" name="sustancias" placeholder="Sustancias"
                                        class="form-control" maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_sustancias_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_sustancias_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_sustancias">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Rasgos de personalidad (relevantes)
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="rasgos_personalidad" name="rasgos_personalidad"
                                        placeholder="Rasgos de personalidad (relevantes)" class="form-control"
                                        maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_rasgos_personalidad_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_rasgos_personalidad_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_rasgos_personalidad">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Información adicional
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="informacion_adicional" name="informacion_adicional"
                                        placeholder="Información adicional" class="form-control" maxlength="3200"
                                        rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_informacion_adicional_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_informacion_adicional_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_informacion_adicional">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Pendientes
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="pendientes" name="pendientes" placeholder="Pendientes"
                                        class="form-control" maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_pendientes_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_pendientes_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_pendientes">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Diagnóstico
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="diagnostico" name="diagnostico" placeholder="Información adicional"
                                        class="form-control" maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_diagnostico_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_diagnostico_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_diagnostico">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-lg-4 col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Seguimiento - Historia de enfermedad (por sesión)
                            </div>
                            <div class="card-body">
                                <div class="input-group">
                                    <textarea id="seguimiento" name="seguimiento"
                                        placeholder=" Seguimiento - Historia de enfermedad (por sesión)"
                                        class="form-control" maxlength="3200" rows="8"></textarea>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="btn btn-outline-success fas fa-microphone-alt"
                                                id="search_seguimiento_start"></i>
                                            <i class="btn btn-outline-success fas fa-microphone-slash"
                                                id="search_seguimiento_stop"></i>
                                    </div>
                                </div>
                                <p id="charNum_seguimiento">3200 Caracteres</p>
                            </div>
                        </div>
                    </div>

                </div>
                </section>

                <section class="seccion-atencion" id="seccion_seguimiento">
                    <div class="titulo-seccion-atencion">
                        <i class="fas fa-stethoscope mr-2"></i>Seguimiento
                    </div>
                <div class="form-row">
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Historia Seguimiento (Tratamiento)
                            </div>
                            <div class="card-body">
                                <div class="seguimiento-premium-wrap">
                                    <div class="seguimiento-premium-header">
                                        <div>
                                            <div class="seguimiento-premium-title">Evolución clínica del paciente</div>
                                            <div class="seguimiento-premium-subtitle">Vista más clara y cronológica del seguimiento registrado en cada sesión.</div>
                                        </div>
                                        <span class="seguimiento-premium-badge" id="seguimiento_total_registros">0 registros</span>
                                    </div>
                                    <div id="seguimiento_timeline" class="seguimiento-timeline">
                                        <div class="seguimiento-empty">
                                            <i class="fas fa-notes-medical"></i>
                                            <div>No hay seguimiento registrado para este paciente.</div>
                                        </div>
                                    </div>
                                </div>
                                <textarea id="seguimiento_read" name="seguimiento_read" readonly
                                    placeholder="Tratamiento" class="form-control" maxlength="500" rows="14"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                </section>
            </div>
        </div>
    </form>
</div>

<div class="modal fade" id="modal_buscar_expediente_pdf" tabindex="-1" role="dialog" aria-labelledby="titulo_buscar_expediente_pdf" aria-hidden="true" data-backdrop="static" data-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1" id="titulo_buscar_expediente_pdf"><i class="fas fa-folder-open mr-2"></i>Buscar expediente</h5>
                    <small>Busque y seleccione el paciente para revisar su expediente clínico.</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="expediente-search-box">
                    <label for="buscar_paciente_expediente_pdf" class="font-weight-bold mb-2">Paciente</label>
                    <div class="expediente-search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="buscar_paciente_expediente_pdf" class="form-control" placeholder="Escriba nombre, identidad o expediente" autocomplete="off">
                    </div>
                    <div id="pacientes_expediente_resultados" class="expediente-resultados">
                        <div class="expediente-empty"><i class="fas fa-spinner fa-spin mr-1"></i> Cargando pacientes...</div>
                    </div>
                    <div id="paciente_expediente_seleccionado" class="expediente-seleccionado"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                <button type="button" class="btn btn-expediente-pdf" id="descargar-expediente-buscado" disabled><i class="fas fa-eye mr-1"></i> Visualizar PDF</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_visualizar_expediente_pdf" tabindex="-1" role="dialog" aria-labelledby="titulo_visualizar_expediente_pdf" aria-hidden="true" data-backdrop="static" data-keyboard="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header align-items-center">
                <div>
                    <h5 class="modal-title mb-0" id="titulo_visualizar_expediente_pdf"><i class="fas fa-file-pdf mr-2"></i>Expediente clínico del paciente</h5>
                    <small>Vista previa en tamaño carta</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <iframe id="visor_expediente_pdf" title="Vista previa del expediente clínico"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info d-none" id="volver_busqueda_expediente"><i class="fas fa-arrow-left mr-1"></i> Volver a la búsqueda</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cerrar</button>
                <a class="btn btn-expediente-pdf" id="descargar_expediente_desde_visor" href="#" target="_blank" rel="noopener"><i class="fas fa-download mr-1"></i> Descargar PDF</a>
            </div>
        </div>
    </div>
</div>