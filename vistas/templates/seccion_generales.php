<!-- INICIO PACIENTES -->

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="paciente_consulta">Paciente <span class="priority">*</span></label>
        <select class="selectpicker form-control" id="paciente_consulta" name="paciente_consulta"
            data-live-search="true" title="Paciente" data-width="100%" data-size="7">
        </select>
    </div>

    <div class="form-group col-md-2">
        <label for="fecha">Fecha de Registro <span class="priority">*</span></label>
        <input type="date" id="fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
    </div>

    <div class="form-group col-md-2">
        <label for="edad">Edad</label>
        <input type="text" id="edad" name="edad" readonly class="form-control" />
    </div>

    <div class="form-group col-md-2">
        <label for="religion_id">Religión</label>
        <input type="text" class="form-control" id="religion_id" name="religion_id" placeholder="Religión">
    </div>
    <div class="form-group col-md-2">
        <label for="estado_civil">Estado Civil</label>
        <input type="text" class="form-control" id="estado_civil" name="estado_civil" placeholder="Estado Civil">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="profesion">Ocupación Actual</label>
        <input type="text" class="form-control" id="profesion" name="profesion" placeholder="Ocupación Actual">
    </div>

    <div class="form-group col-md-2">
        <label for="num_hijos">Número de Hijos</label>
        <input type="number" name="num_hijos" id="num_hijos" value="" class="form-control" />
    </div>

    <div class="form-group col-md-2">
        <label for="servicio_id">Consultorio <span class="priority">*</span></label>
        <select class="selectpicker form-control" id="servicio_id" name="servicio_id" data-live-search="true"
            title="Consultorio" data-width="100%" data-size="7" requiered>
        </select>
    </div>
    <div class="form-group col-md-2">
        <label for="fecha_nac">Fecha de Nacimiento <span class="priority">*</span></label>
        <input type="date" id="fecha_nac" name="fecha_nac" value="<?php echo date('Y-m-d'); ?>" class="form-control" />
    </div>
    <div class="form-group col-md-3">
        <label for="telefono1">Teléfono 1 <span class="priority">*</span></label>
        <input type="number" id="telefono1" name="telefono1" class="form-control" placeholder="Primer Teléfono"
            required />
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="identidad">Identidad o RTN</label>
        <input type="text" class="form-control" id="identidad" name="identidad" placeholder="Identidad o RTN">
    </div>

    <div class="form-group col-md-3">
        <label for="escolaridad">Escolaridad</label>
        <input type="text" class="form-control" id="escolaridad" name="escolaridad" placeholder="Escolaridad">
    </div>
    <div class="form-group col-md-3">
        <label for="red_apoyo">Red de Apoyo</label>
        <input type="text" name="red_apoyo" id="red_apoyo" placeholder="Red de Apoyo" class="form-control"
            maxlength="100" />
    </div>
    <div class="form-group col-md-3">
        <label for="terapeuta_actual">Terapeuta Actual</label>
        <input type="text" name="terapeuta_actual" id="terapeuta_actual" placeholder="Terapeuta Actual"
            class="form-control" maxlength="100" />
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-12">
        <label for="procedencia">Domicilio</label>
        <input type="text" name="procedencia" id="procedencia" placeholder="Dirección" class="form-control" />
    </div>
</div>
</form>
<!-- FIN PACIENTES -->