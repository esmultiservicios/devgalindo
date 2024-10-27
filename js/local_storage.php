<script>
const limpiarCampos = () => {
    const campos = [
        'antecedentes_medicos_no_psiquiatricos',
        'hospitalizaciones',
        'cirugias',
        'alergias',
        'antecedentes_medicos_psiquiatricos',
        'historia_gineco_obstetrica',
        'medicamentos_previos',
        'medicamentos_actuales',
        'legal',
        'sustancias',
        'rasgos_personalidad',
        'informacion_adicional',
        'paciente_consulta',
        'fecha',
        'edad',
        'religion_id',
        'estado_civil',
        'profesion_id',
        'num_hijos',
        'servicio_id',
        'fecha_nac',
        'telefono1',
        'identidad',
        'escolaridad',
        'red_apoyo',
        'terapeuta_actual',
        'procedencia'
    ];

    $.each(campos, (index, campo) => {
        $('#' + campo).val(''); // Limpia el valor de cada campo en el formulario
    });
    console.log('Campos limpiados.');
};

// Función para guardar el contenido de los campos en LocalStorage
const guardarContenido = () => {
    const campos = [
        'antecedentes_medicos_no_psiquiatricos',
        'hospitalizaciones',
        'cirugias',
        'alergias',
        'antecedentes_medicos_psiquiatricos',
        'historia_gineco_obstetrica',
        'medicamentos_previos',
        'medicamentos_actuales',
        'legal',
        'sustancias',
        'rasgos_personalidad',
        'informacion_adicional',
        'paciente_consulta',
        'fecha',
        'edad',
        'religion_id',
        'estado_civil',
        'profesion_id',
        'num_hijos',
        'servicio_id',
        'fecha_nac',
        'telefono1',
        'identidad',
        'escolaridad',
        'red_apoyo',
        'terapeuta_actual',
        'procedencia'
    ];

    $.each(campos, (index, campo) => {
        const valor = $('#' + campo).val();
        if (valor) {
            localStorage.setItem(campo, valor);
            console.log(`Guardado en localStorage: ${campo} = ${valor}`);
        }
    });
}

// Función para cargar el contenido guardado desde LocalStorage
const cargarContenido = () => {
    const campos = [
        'antecedentes_medicos_no_psiquiatricos',
        'hospitalizaciones',
        'cirugias',
        'alergias',
        'antecedentes_medicos_psiquiatricos',
        'historia_gineco_obstetrica',
        'medicamentos_previos',
        'medicamentos_actuales',
        'legal',
        'sustancias',
        'rasgos_personalidad',
        'informacion_adicional',
        'paciente_consulta',
        'fecha',
        'edad',
        'religion_id',
        'estado_civil',
        'profesion_id',
        'num_hijos',
        'servicio_id',
        'fecha_nac',
        'telefono1',
        'identidad',
        'escolaridad',
        'red_apoyo',
        'terapeuta_actual',
        'procedencia'
    ];

    $.each(campos, (index, campo) => {
        const valorGuardado = localStorage.getItem(campo);
        if (valorGuardado) {
            $('#' + campo).val(valorGuardado);
            console.log(`Cargado desde localStorage: ${campo} = ${valorGuardado}`);
        }
    });
}

// Cargar contenido al cargar la página
cargarContenido();

// Guardar contenido al escribir en los campos
$('textarea, input, select').on('input change', () => {
    guardarContenido();
});

// Guardar contenido cada 5 segundos
setInterval(guardarContenido, 5000);

// Detectar reconexión a internet
window.addEventListener('online', () => {
    cargarContenido(); // Cargar contenido al reconectar
});

// Botón de limpiar y verificar contenido en localStorage
$(() => {
    if (localStorage.getItem('identidad')) {
        console.log('Existen datos guardados en localStorage.');
    } else {
        console.log('No hay datos en localStorage.');
    }

    $('#limpiar-registro-atenciones').on('click', () => {
        limpiarCampos(); // Limpiar los campos
        localStorage.clear(); // Limpiar el localStorage
        console.log('LocalStorage y campos limpiados');
    });
});
</script>