<!--INICIO MODAL CAMBIAR CONTRASEÑA -->
<div class="modal fade" id="ModalContraseña">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Modificar Contraseña</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form id="form-cambiarcontra" action="" method="POST" data-form="" autocomplete="off"
                    enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <div class="input-group mb-3">
                                <input type="text" required="required" readonly id="id-registro" name="id-registro"
                                    readonly="readonly" style="display: none;" class="form-control" />
                                <input type="password" name="contranaterior" class="form-control" id="contranaterior"
                                    placeholder="Contraseña Anterior" required="required">
                                <div class="input-group-append">
                                    <span class="btn btn-outline-success" id="show_password1" style="cursor:pointer;"><i
                                            id="icon1" class="fa-solid fa-eye-slash fa-lg"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <div class="input-group mb-3">
                                <input type="password" name="nuevacontra" class="form-control" id="nuevacontra"
                                    placeholder="Nueva Contraseña" required="required">
                                <div class="input-group-append">
                                    <span class="btn btn-outline-success" id="show_password2" style="cursor:pointer;"><i
                                            id="icon2" class="fa-solid fa-eye-slash fa-lg"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <div class="input-group mb-3">
                                <input type="password" name="repcontra" class="form-control" id="repcontra"
                                    placeholder="Repetir Contraseña" required="required">
                                <div class="input-group-append">
                                    <span class="btn btn-outline-success" id="show_password3" style="cursor:pointer;"><i
                                            id="icon3" class="fa-solid fa-eye-slash fa-lg"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <div id="mensaje_cmabiar_contra"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <ul title="La contraseña debe cumplir con todas estas características">
                                <li id="mayus"> 1 Mayúscula</li>
                                <li id="special">1 Caracter Especial (Símbolo)</li>
                                <li id="numbers">Números</li>
                                <li id="lower">Minúsculas</li>
                                <li id="len">Mínimo 8 Caracteres</li>
                            </ul>
                        </div>
                    </div>
                    <input type="hidden" name="id" class="form-control" id="id"
                        value="<?php echo $_SESSION['colaborador_id']; ?>">
                    <div class="modal-footer">
                        <button class="btn btn-success ml-2" type="submit" id="Modalcambiarcontra_Edit">
                            <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Modificar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL CAMBIAR CONTRASEÑA -->

<!--INICIO MODAL PARA SALIR-->
<div class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" id="salir"
    data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <center>¿Realmente quiere salir?</center>
                </h4>
            </div>
            <div class="modal-body">
                <center>
                    <button type="button" class="btn btn-primary" onClick="salir();" id="Si"><span
                            class="glyphicon glyphicon-ok"></span> Si</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="No"><span
                            class="glyphicon glyphicon-remove-circle"></span> No</button>
                    <p>
                    <div id="salida" style="display: none;">
                    </div>
                    </p>
                </center>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL PARA SALIR-->

<!-- Modal Start here-->
<div class="modal fade bs-example-modal-sm" id="myPleaseWait" tabindex="-1" role="dialog" aria-hidden="true"
    data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-time">
                    </span> Por favor espere.
                </h4>
            </div>
            <div class="modal-body">
                <div class="mensaje">
                    <center><img src="../img/gif-load.gif" width="35%" heigh="35%"></center>
                    </center>
                </div>
            </div>
        </div>
    </div>
</div>
<!--Fin Ventanas Modales-->

<!-- Inicio Registro de Pacientes -->
<div class="modal fade" id="modal_pacientes">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pacientes</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="FormularioAjax" id="formulario_pacientes" data-async data-target="#rating-modal" action=""
                    method="POST" data-form="" autocomplete="off" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
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
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-6 mb-3">
                            <label for="expediente">Expediente</label>
                            <input type="number" name="expediente" class="form-control" id="expediente"
                                placeholder="Expediente o Identidad">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edad">Edad</label>
                            <input type="text" class="form-control" name="edad" id="edad" maxlength="100"
                                readonly="readonly" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="nombre">Nombre <span class="priority">*<span /></label>
                            <input type="text" required id="name" name="name" placeholder="Nombre"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="apellido">Apellido <span class="priority">*<span /></label>
                            <input type="text" required id="lastname" name="lastname" placeholder="Apellido"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3" style="display: none;">
                            <label for="fecha">Fecha <span class="priority">*<span /></label>
                            <input type="date" required id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>"
                                class="form-control" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="identidad">Identidad o RTN</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="identidad" name="identidad"
                                    placeholder="Identidad o RTN">
                                <div class="input-group-append" id="grupo_editar_rtn">
                                    <span data-toggle="tooltip" data-placement="top" title="Editar RTN"><a
                                            data-toggle="modal" href="#"
                                            class="btn btn-outline-success form-control editar_rtn">
                                            <div class="sb-nav-link-icon"></div><i class="fas fa-edit fa-lg"></i>
                                        </a></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label>Fecha de Nacimiento <span class="priority">*<span /></label>
                            <input type="date" id="fecha_nac" name="fecha_nac" value="<?php echo date('Y-m-d'); ?>"
                                class="form-control" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sexo">Sexo <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="sexo" name="sexo" required data-live-search="true"
                                    title="Genero" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="telefono">Teléfono 1 <span class="priority">*<span /></label>
                            <input type="number" id="telefono1" name="telefono1" class="form-control"
                                placeholder="Primer Teléfono" required maxlength="8"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="telefono">Teléfono 2</label>
                            <input type="number" id="telefono2" name="telefono2" class="form-control"
                                placeholder="Segundo Teléfono" maxlength="8"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="pais_id">País</label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="pais_id" name="pais_id" data-live-search="true"
                                    title="País" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="departamento_id">Departamentos</label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="departamento_id" name="departamento_id"
                                    data-live-search="true" title="Departamentos" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="municipio_id">Municipios</label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="municipio_id" name="municipio_id"
                                    data-live-search="true" title="Municipios" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="direccion">Dirección </label>
                            <input type="text" id="direccion" name="direccion" placeholder="Dirección Completa"
                                placeholder="Dirección" class="form-control" />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="correo">Correo</label>
                            <input type="email" name="correo" id="correo" placeholder="alguien@algo.com"
                                class="form-control" data-toggle="tooltip" data-placement="top"
                                title="Este correo será utilizado para enviar las citas creadas y las reprogramaciones, como las notificaciones de las citas pendientes de los usuarios."
                                maxlength="100" /><label id="validate"></label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8 mb-3">
                            <label for="responsable">Responsable </label>
                            <input type="text" id="responsable" name="responsable" class="form-control"
                                placeholder="Responsable" maxlength="70"
                                oninput="if(this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="responsable_id">Parentesco </label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="responsable_id" name="responsable_id"
                                    data-live-search="true" title="Parentesco" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="referido_id">Referido por: </label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="referido_id" name="referido_id" data-live-search="true"
                                    title="Referido por" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" form="formulario_pacientes" type="submit" id="reg">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Fin Registro de Pacientes -->


<!-- Inicio editar RTN -->
<div class="modal fade" id="agregar_expediente_manual">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Identidad y/o Expediente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="container"></div>
            <div class="modal-body">
                <form id="formulario_agregar_expediente_manual">
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <input type="hidden" required readonly id="pacientes_id" name="pacientes_id"
                                class="form-control" />
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
                    <div class="form-row" id="grupo_expediente">
                        <div class="col-md-6 mb-3">
                            <label for="name_manual">Nombre</label>
                            <input type="text" required readonly id="" name="name_manual" class="form-control"
                                readonly />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edad_manual">Edad</label>
                            <input type="text" required class="form-control" name="edad_manual" id="edad_manual"
                                maxlength="100" readonly />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="expediente_usuario_manual">Expediente </label>
                            <input type="text" class="form-control" id="expediente_usuario_manual" readonly
                                name="expediente_usuario_manual" autofocus placeholder="Expediente" maxlength="100" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="identidad_ususario_manual">Identidad </label>
                            <input type="text" class="form-control" name="identidad_ususario_manual" autofocus
                                placeholder="Identidad" id="identidad_ususario_manual" maxlength="100" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fecha_re_manual">Fecha</label>
                            <input type="date" class="form-control" name="fecha_re_manual" id="fecha_re_manual"
                                value="<?php echo date('Y-m-d'); ?>" maxlength="100" readonly />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="expediente_manual">Expediente </label>
                            <input type="text" name="expediente_manual" class="form-control" id="expediente_manual"
                                maxlength="100" value="0" readonly />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="identidad_manual">Identidad </label>
                            <input type="number" name="identidad_manual" class="form-control" id="identidad_manual"
                                maxlength="100" value="0" readonly />
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sexo_manual">Sexo <span class="priority">*<span /></label>
                            <div class="input-group mb-3">
                                <select class="selectpicker" id="sexo_manual" name="sexo_manual" required
                                    data-live-search="true" title="Sexo" data-width="100%" data-size="7">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-check-inline">
                        <p for="end" class="col-sm-9 form-check-label">¿Desea convertir usuario en temporal?</p>
                        <div class="col-sm-5">
                            <input type="radio" class="form-check-input" name="respuesta" id="respuestasi" value="1"> Sí
                            <input type="radio" class="form-check-input" name="respuesta" id="respuestano" value="2"
                                checked> No
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary ml-2" type="submit" id="reg_manual"
                    form="formulario_agregar_expediente_manual">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Registrar
                </button>
                <button class="btn btn-warning ml-2" type="submit" id="convertir_manual"
                    form="formulario_agregar_expediente_manual">
                    <div class="sb-nav-link-icon"></div><i class="far fa-save fa-lg"></i> Convertir
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Fin Ediar RTN -->