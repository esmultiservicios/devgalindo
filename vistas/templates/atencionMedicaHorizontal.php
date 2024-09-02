<div class="container-fluid" id="atencionMedica" style="display: none;">
    <form class="FormularioAjax" id="formulario_atenciones" action="" method="POST" data-form="" autocomplete="off"
        enctype="multipart/form-data">
        <button class="btn btn-primary ml-2" type="submit" id="reg_atencion" form="formulario_atenciones">
            <i class="far fa-save fa-lg"></i> Registrar
        </button>
        <button class="btn btn-primary ml-2" type="submit" id="edi_atencion" form="formulario_atenciones">
            <i class="far fa-save fa-lg"></i> Registrar
        </button>
        <br /><br />
        <ul class=" nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item waves-effect waves-light">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home"
                    aria-selected="false">
                    <i class="fas fa-info-circle fa-lg"></i> Datos Generales
                </a>
            </li>
            <li class="nav-item waves-effect waves-light">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#historia_clinica_tab" role="tab"
                    aria-controls="historia_clinica" aria-selected="false">
                    <i class="fas fa-book-medical fa-lg"></i> Historia Clínica
                </a>
            </li>
            <li class="nav-item waves-effect waves-light">
                <a class="nav-link" id="seguimiento-tab" data-toggle="tab" href="#seguimiento_tab" role="tab"
                    aria-controls="seguimiento_tab" aria-selected="true">
                    <i class="fas fa-stethoscope fa-lg"></i> Seguimiento
                </a>
            </li>
        </ul>

        <br>
        <div class="tab-content" id="myTabContent">
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
            <!-- INICIO TAB CONTENT-->
            <div class="tab-pane fade active show" id="home" role="tabpanel" aria-labelledby="home-tab">
                <!-- INICIO TAB HOME-->
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="paciente_consulta">Paciente <span class="priority">*<span /></label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="paciente_consulta" name="paciente_consulta"
                                data-live-search="true" title="Paciente" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Fecha de Registro <span class="priority">*<span /></label>
                        <input type="date" id="fecha" name="fecha" value="<?php echo date ("Y-m-d");?>"
                            class="form-control" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="edad">Edad</label>
                        <input type="text" id="edad" name="edad" readonly class="form-control" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="religion_id">Religión</label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="religion_id" name="religion_id" data-live-search="true"
                                title="Religión" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="estado_civil">Estado Civil</label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="estado_civil" name="estado_civil" data-live-search="true"
                                title="Estado Civil" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="profesion_id">Profesión</label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="profesion_id" name="profesion_id" data-live-search="true"
                                title="Profesión" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="num_hijos">Número de Hijos</label>
                        <input type="number" name="num_hijos" id="num_hijos" value="" class="form-control" />
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="servicio_id">Consultorio</label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="servicio_id" name="servicio_id" data-live-search="true"
                                title="Consultorio" data-width="100%" data-size="7">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="escolaridad">Escolaridad</label>
                        <div class="input-group mb-3">
                            <select class="selectpicker" id="escolaridad" name="escolaridad" data-live-search="true"
                                title="Escolaridad" data-width="100%" data-size="7">
                            </select>
                        </div>
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
                        <input type="text" name="procedencia" id="procedencia" placeholder="Dirección" readonly
                            class="form-control" />
                    </div>
                </div>
            </div><!-- FIN TAB HOME-->
            <div class="tab-pane fade" id="historia_clinica_tab" role="tabpanel" aria-labelledby="historia_clinica-tab">
                <!-- INICIO TAB HISTORIA CLINICA-->
                <div class="form-row">
                    <div class="col-md-12 mb-3">
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
                    <div class="col-md-12 mb-3">
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
                    <div class="col-md-12 mb-3">
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
                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                    <div class="col-md-12 mb-3">
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

                </div>
            </div><!-- FIN TAB HISTORIA CLINICA-->
            <div class="tab-pane fade" id="seguimiento_tab" role="tabpanel" aria-labelledby="seguimiento-tab">
                <!-- INICIO TAB SEGUIMIENTO-->
                <div class="form-row">
                    <div class="col-md-12 mb-3">
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
                    <div class="col-md-12 mb-3">
                        <div class="card">
                            <div class="card-header text-white bg-info mb-3" align="center">
                                Historia Seguimiento (Tratamiento)
                            </div>
                            <div class="card-body">
                                <textarea id="seguimiento_read" name="seguimiento_read" readonly
                                    placeholder="Tratamiento" class="form-control" maxlength="500" rows="11"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- FIN TAB SEGUIMIENTO-->
        </div><!-- FIN TAB CONTENT-->
</div>
</form>
</div>