<!-- INICIO PACIENTES -->
<div class="form-row">
    <div class="col-md-3 mb-3">
        <label for="paciente_consulta">Paciente <span class="priority">*<span /></label>
        <div class="input-group mb-3">
            <select class="selectpicker" id="paciente_consulta" name="paciente_consulta" data-live-search="true"
                title="Paciente" data-width="100%" data-size="7">
            </select>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <label>Fecha de Registro <span class="priority">*<span /></label>
        <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
    </div>
    <div class="col-md-3 mb-3">
        <label for="edad">Edad</label>
        <input type="text" id="edad" name="edad" readonly class="form-control" />
    </div>
    <div class="col-md-3 mb-3">
        <label for="religion_id">Religión</label>
        <div class="input-group mb-3">
            <select class="selectpicker" id="religion_id" name="religion_id" data-live-search="true" title="Religión"
                data-width="100%" data-size="7">
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
        <label for="profesion_id">Ocupación Actual</label>
        <div class="input-group mb-3">
            <select class="selectpicker" id="profesion_id" name="profesion_id" data-live-search="true"
                title="Ocupación Actual" data-width="100%" data-size="7">
            </select>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label for="num_hijos">Número de Hijos</label>
        <input type="number" name="num_hijos" id="num_hijos" value="" class="form-control" />
    </div>
    <div class="col-md-3 mb-3">
        <label for="servicio_id">Consultorio <span class="priority">*<span /></label>
        <div class="input-group mb-3">
            <select class="selectpicker" id="servicio_id" name="servicio_id" data-live-search="true" title="Consultorio"
                data-width="100%" data-size="7">
            </select>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="col-md-3 mb-3">
        <label>Fecha de Nacimiento <span class="priority">*<span /></label>
        <div class="input-group mb-3">
            <input type="date" id="fecha_nac" name="fecha_nac" value="<?php echo date('Y-m-d'); ?>"
                class="form-control" />
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label for="telefono">Teléfono 1 <span class="priority">*<span /></label>
        <div class="input-group mb-3">
            <input type="number" id="telefono1" name="telefono1" class="form-control" placeholder="Primer Teléfono"
                required />
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label for="identidad">Identidad o RTN</label>
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="identidad" name="identidad" placeholder="Identidad o RTN">
        </div>
    </div>
</div>

<div class="form-row">
    <div class="col-md-3 mb-3">
        <label for="escolaridad">Escolaridad</label>
        <div class="input-group mb-3">
            <select class="selectpicker" id="escolaridad" name="escolaridad" data-live-search="true" title="Escolaridad"
                data-width="100%" data-size="7">
            </select>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <label for="red_apoyo">Red de Apoyo</label>
        <input type="text" name="red_apoyo" id="red_apoyo" placeholder="Red de Apoyo" class="form-control"
            maxlength="100" />
    </div>
    <div class="col-md-6 mb-3">
        <label for="terapeuta_actual">Terapeuta Actual</label>
        <input type="text" name="terapeuta_actual" id="terapeuta_actual" placeholder="Terapeuta Actual"
            class="form-control" maxlength="100" />
    </div>
</div>

<div class="form-row">
    <div class="col-md-12 mb-3">
        <label for="procedencia">Domicilio</label>
        <input type="text" name="procedencia" id="procedencia" placeholder="Dirección" class="form-control" />
    </div>
</div>
<!-- FIN PACIENTES -->