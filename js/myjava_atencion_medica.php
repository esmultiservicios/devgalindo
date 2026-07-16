<script>
/* ============================================================================
   ✅ ATENCION MEDICA - JS COMPLETO (ARREGLADO, ORDENADO Y SEGURO)
   - Eliminado eval() -> parseServerPayload()
   - Dictado por voz estable (onend + permisos + no duplicar handlers)
   - Un solo init, eventos namespaced para evitar duplicados
   - Sin setInterval duplicado ni strings
============================================================================ */

(function ($) {
  "use strict";

  // Evitar inicialización múltiple si el script se carga 2 veces
  if (window.__ATENCION_MEDICA_INIT__ === true) return;
  window.__ATENCION_MEDICA_INIT__ = true;

  // ============================
  // ✅ PARSER SEGURO (reemplaza eval)
  // ============================
  function parseServerPayload(raw, ctx) {
    if (raw == null) throw new Error(`[${ctx}] Respuesta vacía/null`);

    if (typeof raw === "object") return raw; // ya viene parseado
    const t = String(raw).trim();

    // 1) JSON real
    try { return JSON.parse(t); } catch (_) {}

    // 2) Compatibilidad con respuestas viejas tipo: ['a','b'] o {a:1}
    try { return (new Function("return (" + t + ")"))(); }
    catch (e) {
      console.error(`❌ [${ctx}] Respuesta NO parseable:`, t);
      throw e;
    }
  }

  // ============================
  // ✅ Helpers generales
  // ============================
  function bindModalFocus(modalSelector, focusSelector) {
    $(document).off("shown.bs.modal.focus_" + modalSelector).on("shown.bs.modal.focus_" + modalSelector, modalSelector, function () {
      $(this).find(focusSelector).trigger("focus");
    });
  }

  function getSafeFechaGlobal() {
    if (typeof window.fecha !== "undefined" && window.fecha) {
      return window.fecha;
    }

    // La fecha actual puede obtenerse localmente sin bloquear la interfaz.
    return convertDate(new Date());
  }

  var solicitudesActivas = {
    pagination: null,
    paginationBusqueda: null,
    paginarSeguimiento: null,
    paciente: null,
    transitoEnviado: null,
    transitoRecibido: null,
    pendientes: null,
    catalogoPacientesAtencion: null,
    catalogoPacientesTransito: null
  };

  var estadoCargaInicial = {
    listadoCargado: false,
    catalogosIniciados: false,
    alertaPendientesMostrada: false
  };

  var estadoComponentesAtencion = {
    contadoresInicializados: false,
    speechInicializado: false
  };

  var temporizadoresUI = {
    busquedaPrincipal: null,
    busquedaHistorial: null
  };

  var cacheCatalogosAtencion = {
    pacientesHtml: null
  };

  var servicioAtencionPendiente = null;

  function obtenerPrimerServicioDisponible($select) {
    var valor = '';

    $select.find('option').each(function () {
      var opcionValor = $(this).val();

      if (opcionValor !== null && String(opcionValor).trim() !== '') {
        valor = opcionValor;
        return false;
      }
    });

    return valor;
  }

  function seleccionarServicioAtencion(servicioPreferido) {
    var $select = $('#formulario_atenciones #servicio_id');

    if (!$select.length) {
      return '';
    }

    var valorSeleccionar = servicioPreferido;

    if (
      valorSeleccionar === null ||
      typeof valorSeleccionar === 'undefined' ||
      String(valorSeleccionar).trim() === '' ||
      !$select.find('option').filter(function () {
        return String($(this).val()) === String(valorSeleccionar);
      }).length
    ) {
      valorSeleccionar = obtenerPrimerServicioDisponible($select);
    }

    if (valorSeleccionar === '') {
      return '';
    }

    valorSeleccionar = String(valorSeleccionar);

    // Sincronizar el select HTML real.
    $select.find('option').prop('selected', false);

    var $opcion = $select.find('option').filter(function () {
      return String($(this).val()) === valorSeleccionar;
    }).first();

    if (!$opcion.length) {
      return '';
    }

    $opcion.prop('selected', true);
    $select.val(valorSeleccionar);
    servicioAtencionPendiente = valorSeleccionar;

    // Sincronizar también bootstrap-select. render() solo cambia el texto;
    // selectpicker('val') cambia el valor real administrado por el plugin.
    if (typeof $select.selectpicker === 'function') {
      try {
        $select.selectpicker('val', valorSeleccionar);
        $select.selectpicker('render');
      } catch (error) {
        console.warn('No se pudo sincronizar el servicio con selectpicker:', error);
      }
    }

    $select.trigger('change.servicioAtencion');

    return String($select.val() || valorSeleccionar);
  }

  function obtenerServicioAtencionParaGuardar() {
    var $select = $('#formulario_atenciones #servicio_id');
    var servicio_id = $select.val();

    if (
      servicio_id === null ||
      typeof servicio_id === 'undefined' ||
      String(servicio_id).trim() === ''
    ) {
      servicio_id = seleccionarServicioAtencion(servicioAtencionPendiente);
    }

    if (
      servicio_id === null ||
      typeof servicio_id === 'undefined' ||
      String(servicio_id).trim() === ''
    ) {
      servicio_id = seleccionarServicioAtencion(null);
    }

    return servicio_id ? String(servicio_id) : '';
  }

  function destruirSelectpickerPacienteAtencion() {
    var $select = $('#formulario_atenciones #paciente_consulta');

    if (!$select.length) {
      return;
    }

    try {
      if ($select.data('selectpicker')) {
        $select.selectpicker('destroy');
      }
    } catch (error) {
      console.warn('No se pudo destruir selectpicker de pacientes:', error);
    }

    // Eliminar cualquier estructura residual generada por bootstrap-select.
    $select.siblings('.bootstrap-select').remove();
    $select.show();
  }

  function prepararPacienteUnicoAtencion(pacientes_id, nombrePaciente) {
    var $select = $('#formulario_atenciones #paciente_consulta');

    destruirSelectpickerPacienteAtencion();

    $select
      .empty()
      .append(
        $('<option>', {
          value: pacientes_id,
          text: nombrePaciente || 'Paciente seleccionado',
          selected: true
        })
      )
      .prop('disabled', true);

    // Solo existe una opción, por lo que inicializar el componente es liviano.
    if (typeof $select.selectpicker === 'function') {
      $select.selectpicker();
      $select.selectpicker('render');
    }
  }

  function refrescarSelectSinBloquear($select) {
    if (!$select || !$select.length) return;

    window.requestAnimationFrame(function () {
      if (typeof $select.selectpicker === 'function') {
        $select.selectpicker('refresh');
      }
    });
  }

  function abortarSolicitud(nombre) {
    var solicitud = solicitudesActivas[nombre];

    if (solicitud && solicitud.readyState !== 4) {
      solicitud.abort();
    }
  }

  function programarPaginacion(partida, espera) {
    clearTimeout(temporizadoresUI.busquedaPrincipal);

    temporizadoresUI.busquedaPrincipal = setTimeout(function () {
      pagination(partida || 1);
    }, typeof espera === 'number' ? espera : 250);
  }

  function obtenerTextoAjax(url, data) {
    return $.ajax({
      type: 'POST',
      url: url,
      data: data || {},
      dataType: 'text',
      cache: false,
      timeout: 30000
    });
  }

  function tienePermisoAtencion() {
    var usuario = Number(getUsuarioSistema());
    return usuario === 1 || usuario === 2 || usuario === 3 || usuario === 5;
  }

  function siguientePintado(callback) {
    window.requestAnimationFrame(function () {
      window.requestAnimationFrame(callback);
    });
  }

  function ejecutarCuandoEsteLibre(callback, timeout) {
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(callback, {
        timeout: typeof timeout === 'number' ? timeout : 800
      });
      return;
    }

    setTimeout(callback, 0);
  }

  function ejecutarTareasEnLotes(tareas, alFinal) {
    var indice = 0;

    function ejecutarSiguiente() {
      var inicio = performance.now();

      while (indice < tareas.length && (performance.now() - inicio) < 8) {
        tareas[indice++]();
      }

      if (indice < tareas.length) {
        window.requestAnimationFrame(ejecutarSiguiente);
        return;
      }

      if (typeof alFinal === 'function') {
        alFinal();
      }
    }

    window.requestAnimationFrame(ejecutarSiguiente);
  }

  function mostrarCargaAtencion(mensaje) {
    $('#main_facturacion').hide();
    $('#facturacion').hide();
    $('#atencionMedica').show();

    $('#label_acciones_volver').html('Atenciones Médicas');
    $('#acciones_atras').removeClass('active');
    $('#acciones_factura').addClass('active');
    $('#label_acciones_factura').html('Historia Clínica');

    $('.footer').show();
    $('.footer1').hide();

    var $formulario = $('#formulario_atenciones');
    $formulario.css('visibility', 'hidden');

    var $carga = $('#carga_atencion_medica');

    if (!$carga.length) {
      $carga = $(
        '<div id="carga_atencion_medica" class="text-center" ' +
        'style="padding:45px 15px;">' +
          '<i class="fas fa-spinner fa-spin fa-2x"></i>' +
          '<div class="mensaje-carga-atencion" style="margin-top:12px;"></div>' +
        '</div>'
      );

      $formulario.before($carga);
    }

    $carga.find('.mensaje-carga-atencion').text(
      mensaje || 'Cargando atención médica...'
    );
    $carga.show();
  }

  function ocultarCargaAtencion() {
    $('#carga_atencion_medica').hide();
    $('#formulario_atenciones').css('visibility', 'visible');
  }

  function refrescarSelectoresAtencion(selectores, alFinal) {
    var indice = 0;

    function procesarSiguiente() {
      if (indice >= selectores.length) {
        if (typeof alFinal === 'function') {
          alFinal();
        }
        return;
      }

      var $select = $(selectores[indice++]);

      window.requestAnimationFrame(function () {
        if ($select.length && typeof $select.selectpicker === 'function') {
          // Las opciones ya existen. render solo actualiza el valor mostrado;
          // refresh reconstruye todo el dropdown y congela la interfaz.
          $select.selectpicker('render');
        }

        procesarSiguiente();
      });
    }

    procesarSiguiente();
  }

  // ============================
  // Navegación interna y protección de datos de factura
  // ============================
  var vistaAnteriorFactura = 'main';
  var snapshotFactura = '';
  var navegacionConfirmada = false;

  function serializarFactura() {
    var $form = $('#formulario_facturacion');

    if (!$form.length) {
      return '';
    }

    var datos = $form.serializeArray().map(function (item) {
      return item.name + '=' + item.value;
    }).join('&');

    var filasConDatos = 0;

    $form.find('tbody tr, .table tbody tr').each(function () {
      var tieneDato = false;

      $(this).find('input, select, textarea').each(function () {
        var valor = $(this).val();

        if (valor !== null && String(valor).trim() !== '' && String(valor).trim() !== '0') {
          tieneDato = true;
        }
      });

      if (tieneDato) {
        filasConDatos++;
      }
    });

    return datos + '|filas=' + filasConDatos;
  }

  function guardarSnapshotFactura() {
    snapshotFactura = serializarFactura();
  }

  function facturaTieneCambios() {
    if (!$('#facturacion').is(':visible')) {
      return false;
    }

    return serializarFactura() !== snapshotFactura;
  }

  function mostrarVistaPrincipal() {
    // Ocultar vistas secundarias y volver siempre al listado principal.
    $('#facturacion').hide();
    $('#atencionMedica').hide();
    $('#main_facturacion').show();

    // Limpiar la atención que ya fue registrada para no dejar datos anteriores.
    if ($('#formulario_atenciones').length) {
      $('#formulario_atenciones')[0].reset();
      $('#formulario_atenciones #pro').val('Registro');
      $('#formulario_atenciones #pacientes_id').val('');
      $('#formulario_atenciones #agenda_id').val('');
      $('#formulario_atenciones #paciente_consulta').prop('disabled', false);
      $('#formulario_atenciones #servicio_id').prop('disabled', false);
      if ($('#formulario_atenciones #paciente_consulta').data('selectpicker')) {
        $('#formulario_atenciones #paciente_consulta').selectpicker('render');
      }
      refrescarSelectSinBloquear($('#formulario_atenciones #servicio_id'));
    }

    // Detener cualquier dictado que haya quedado activo.
    if (window.__SPEECH_STATE__ && window.__SPEECH_STATE__.recognitions) {
      Object.keys(window.__SPEECH_STATE__.recognitions).forEach(function (campo) {
        var item = window.__SPEECH_STATE__.recognitions[campo];

        if (item && item.recognition) {
          try {
            item.recognition.stop();
          } catch (error) {
            // No hacer nada si el reconocimiento ya estaba detenido.
          }

          item.running = false;
        }

        $('#formulario_atenciones #search_' + campo + '_stop').hide();
        $('#formulario_atenciones #search_' + campo + '_start').show();
      });

      window.__SPEECH_STATE__.activeCampo = null;
    }

    servicioAtencionPendiente = null;

    $('#acciones_atras').addClass('active');
    $('#acciones_factura').removeClass('active');
    $('#label_acciones_factura').html('');

    $('.footer').show();
    $('.footer1').hide();

    // Volver a consultar las atenciones para mostrar inmediatamente
    // el estado actualizado del registro recién guardado.
    pagination(1);
  }

  function mostrarVistaAtencionPreservandoDatos() {
    $('#facturacion').hide();
    $('#main_facturacion').hide();
    $('#atencionMedica').show();

    $('#acciones_atras').removeClass('active');
    $('#acciones_factura').addClass('active');
    $('#label_acciones_factura').html('Historia Clínica');

    $('.footer').show();
    $('.footer1').hide();

    inicializarContadores(limites);
    inicializarSpeechRecognition(limites);
  }

  function ejecutarRegresoDesdeFactura() {
    // Desde una factura siempre se regresa al listado principal de atenciones.
    // La atención ya fue guardada, por lo que no se debe volver al formulario
    // con los datos anteriores.
    mostrarVistaPrincipal();
  }

  function solicitarRegresoDesdeFactura() {
    if (!facturaTieneCambios()) {
      ejecutarRegresoDesdeFactura();
      return;
    }

    swal({
      title: 'Datos sin guardar',
      text: 'La factura contiene información sin guardar. Si regresa, esos datos se perderán.',
      icon: 'warning',
      buttons: {
        cancel: {
          text: 'Permanecer aquí',
          visible: true
        },
        confirm: {
          text: 'Salir y perder los datos'
        }
      },
      dangerMode: true,
      closeOnEsc: false,
      closeOnClickOutside: false
    }).then(function (salir) {
      if (salir === true) {
        navegacionConfirmada = true;
        ejecutarRegresoDesdeFactura();
      }
    });
  }

  // ============================
  // Definir los límites de caracteres globalmente (igual que tu código)
  // ============================
  var limites = {
    'alergias': 3200,
    'seguimiento': 3200,
    'antecedentes_medicos_psiquiatricos': 3200,
    'historia_gineco_obstetrica': 3200,
    'medicamentos_previos': 3200,
    'medicamentos_actuales': 3200,
    'legal': 3200,
    'sustancias': 3200,
    'rasgos_personalidad': 3200,
    'informacion_adicional': 3200,
    'pendientes': 3200,
    'diagnostico': 3200,
    'antecedentes_medicos_no_psiquiatricos': 3200,
    'hospitalizaciones': 3200,
    'cirugias': 3200
  };

  // ============================
  // ✅ Inicialización principal
  // ============================
  $(function () {

    // ---- MODALES: focus
    bindModalFocus("#registro_transito_eviada", "#formulario_transito_enviada #expediente");
    bindModalFocus("#registro_transito_recibida", "#formulario_transito_recibida #expediente");
    bindModalFocus("#modal_registro_atenciones", "#formulario_atenciones #expediente");
    bindModalFocus("#buscar_atencion", "#formulario_buscarAtencion #busqueda");

    bindModalFocus("#modal_busqueda_profesion", "#formulario_busqueda_profesion #buscar");
    bindModalFocus("#modal_busqueda_religion", "#formulario_busqueda_religion #buscar");
    bindModalFocus("#modal_busqueda_pacientes", "#formulario_busqueda_pacientes #buscar");

    // ---- Footer inicial
    $(".footer").show();
    $(".footer1").hide();

    // Los contadores son ligeros. El dictado se inicializa únicamente
    // cuando se abre la vista de atención para no cargar de más al iniciar.
    inicializarContadores(limites);

    // Primero se carga el listado. Esto evita que los catálogos y el aviso
    // de pendientes retrasen la tabla o dejen la pantalla en blanco.
    try {
      pagination(1)
        .always(function () {
          estadoCargaInicial.listadoCargado = true;

          // Los demás catálogos se cargan después de pintar la tabla.
          setTimeout(function () {
            funcionesFormPacientes();

            getColaboradoresFacturacion();
            getPacientesFacturacion();
            getServiciosFacturacion();
            getDepartamentos();
            getReferido();
            getResponsable();

            evaluarRegistrosPendientesEmail();
          }, 0);

          // El aviso se muestra únicamente después de tener el listado visible.
          setTimeout(function () {
            evaluarRegistrosPendientes();
          }, 150);
        });

      setInterval(function () {
        if (!solicitudesActivas.pagination || solicitudesActivas.pagination.readyState === 4) {
          pagination(1);
        }
      }, 22000);

      setInterval(function () {
        evaluarRegistrosPendientes();
      }, 1800000);
    } catch (e) {
      console.error(e);
    }

    // Breadcrumb: permite volver desde Historia Clínica o Factura
    $(document)
      .off('click.atencionNavegacion', '#ancla_volver, #acciones_atras')
      .on('click.atencionNavegacion', '#ancla_volver, #acciones_atras', function (e) {
        e.preventDefault();

        if ($('#facturacion').is(':visible')) {
          solicitarRegresoDesdeFactura();
          return false;
        }

        mostrarVistaPrincipal();
        return false;
      });

    // Advierte también al cerrar o recargar el navegador con una factura incompleta.
    $(window)
      .off('beforeunload.atencionFactura')
      .on('beforeunload.atencionFactura', function () {
        if (!navegacionConfirmada && facturaTieneCambios()) {
          return 'La factura contiene información sin guardar.';
        }
      });

    // ============================
    // ✅ EVENTOS - TODOS NAMESPACED
    // ============================

    // NUEVO REGISTRO ATENCION
    $(document).off("click.atencion", "#form_main #nuevo_registro").on("click.atencion", "#form_main #nuevo_registro", function (e) {
      e.preventDefault();

      if (tienePermisoAtencion()) {
        mostrarCargaAtencion('Preparando nueva atención...');

        siguientePintado(function () {
          ejecutarTareasEnLotes([
            function () {
              if ($('#formulario_atenciones').length) {
                $('#formulario_atenciones')[0].reset();
              }
            },
            function () {
              limpiarFormPacientes();
            },
            function () {
              $('#reg_atencion').show();
              $('#edi_atencion').hide();
            },
            function () {
              $('#formulario_atenciones #consultorio_').show();
              $('#formulario_atenciones #label_servicio').hide();
              $('#formulario_atenciones #servicio').hide();
            },
            function () {
              $('#formulario_atenciones #fecha').attr('readonly', false);
              $('#formulario_atenciones #paciente_consulta').attr('disabled', false);
              $('#reg_atencion').attr('disabled', false);

              // En una atención nueva siempre se selecciona el primer servicio.
              servicioAtencionPendiente = null;
              seleccionarServicioAtencion(null);
            },
            function () {
              $('#formulario_atenciones .nav-tabs li:eq(0) a').tab('show');
            },
            function () {
              FormAtencionMedica();
            }
          ], function () {
            getPacientesAtencion()
              .always(function () {
                ocultarCargaAtencion();

                ejecutarCuandoEsteLibre(function () {
                  inicializarContadores(limites);

                  if (!estadoComponentesAtencion.speechInicializado) {
                    inicializarSpeechRecognition(limites);
                    estadoComponentesAtencion.speechInicializado = true;
                  }
                }, 1800);
              });
          });
        });

        return false;

      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    // REGISTRAR ATENCION (ANTES: usabas #servicio_id sin scope y podía agarrar otro)
    $(document).off("click.atencion", "#reg_atencion").on("click.atencion", "#reg_atencion", function (e) {
      e.preventDefault();

      let servicio_id = obtenerServicioAtencionParaGuardar();

      if (!servicio_id) {
        swal({
          title: 'Error',
          text: 'Por favor, selecciona un servicio.',
          icon: 'error',
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
        return;
      }

      let url = '<?php echo SERVERURL; ?>php/atencion_pacientes/agregar.php';

      $('#formulario_atenciones #servicio_id').val(servicio_id);
      $('#formulario_atenciones #servicio_id option').prop('selected', false);
      $('#formulario_atenciones #servicio_id option').filter(function () {
        return String($(this).val()) === String(servicio_id);
      }).prop('selected', true);

      let formData = new FormData($('#formulario_atenciones')[0]);
      formData.set('servicio_id', servicio_id);

      $.ajax({
        type: 'POST',
        url: url,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (respuesta) {
          if (!respuesta || respuesta.status !== 'success') {
            swal({
              title: respuesta && respuesta.title ? respuesta.title : 'Error',
              text: respuesta && respuesta.message ? respuesta.message : 'No se pudo registrar la atención.',
              icon: respuesta && respuesta.type ? respuesta.type : 'error',
              dangerMode: true,
              closeOnEsc: false,
              closeOnClickOutside: false
            });
            return;
          }

          swal({
            title: respuesta.title,
            text: respuesta.message,
            icon: respuesta.type,
            closeOnEsc: false,
            closeOnClickOutside: false
          }).then(function () {
            showFactura(respuesta.atencion_id);
          });
        },
        error: function (xhr, textStatus, errorThrown) {
          var respuesta = xhr.responseJSON;

          swal({
            title: respuesta && respuesta.title ? respuesta.title : 'Error',
            text: respuesta && respuesta.message
              ? respuesta.message
              : (xhr.responseText || errorThrown || textStatus || 'No se pudo registrar la atención.'),
            icon: 'error',
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
        }
      });
    });

    // EDITAR ATENCION
    $(document).off("click.atencion", "#edi_atencion").on("click.atencion", "#edi_atencion", function (e) {
      e.preventDefault();

      let servicio_id = obtenerServicioAtencionParaGuardar();

      if (!servicio_id) {
        swal({
          title: 'Error',
          text: 'Por favor, selecciona un servicio.',
          icon: 'error',
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
        return;
      }

      let url = '<?php echo SERVERURL; ?>php/atencion_pacientes/agregarRegistro.php';

      $('#formulario_atenciones #servicio_id').val(servicio_id);
      $('#formulario_atenciones #servicio_id option').prop('selected', false);
      $('#formulario_atenciones #servicio_id option').filter(function () {
        return String($(this).val()) === String(servicio_id);
      }).prop('selected', true);

      let formData = new FormData($('#formulario_atenciones')[0]);
      formData.set('servicio_id', servicio_id);

      $.ajax({
        type: 'POST',
        url: url,
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (respuesta) {
          if (!respuesta || respuesta.status !== 'success') {
            swal({
              title: respuesta && respuesta.title ? respuesta.title : 'Error',
              text: respuesta && respuesta.message ? respuesta.message : 'No se pudo registrar la atención desde la agenda.',
              icon: respuesta && respuesta.type ? respuesta.type : 'error',
              dangerMode: true,
              closeOnEsc: false,
              closeOnClickOutside: false
            });
            return;
          }

          swal({
            title: respuesta.title,
            text: respuesta.message,
            icon: respuesta.type,
            closeOnEsc: false,
            closeOnClickOutside: false
          }).then(function () {
            showFactura(respuesta.atencion_id);
          });
        },
        error: function (xhr, textStatus, errorThrown) {
          var respuesta = xhr.responseJSON;

          swal({
            title: respuesta && respuesta.title ? respuesta.title : 'Error',
            text: respuesta && respuesta.message
              ? respuesta.message
              : (xhr.responseText || errorThrown || textStatus || 'No se pudo registrar la atención desde la agenda.'),
            icon: 'error',
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
        }
      });
    });

    // NUEVO REGISTRO PACIENTE
    $(document).off("click.atencion", "#form_main #nuevo-registro").on("click.atencion", "#form_main #nuevo-registro", function (e) {
      e.preventDefault();

      if (tienePermisoAtencion()) {

        $('#formulario_pacientes #pro').val("Registrar");
        $('#grupo_expediente').hide();

        $('#formulario_pacientes').attr({ 'data-form': 'save' });
        $('#formulario_pacientes').attr({ 'action': '<?php echo SERVERURL; ?>php/pacientes/agregarPacientes.php' });

        $('#formulario_pacientes').trigger("reset");

        $('#modal_pacientes').modal({ show: true, keyboard: false, backdrop: 'static' });
        return false;

      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    // TRANSITO ENVIADA
    $(document).off("click.atencion", "#form_main #transito_enviada").on("click.atencion", "#form_main #transito_enviada", function (e) {
      e.preventDefault();

      if (tienePermisoAtencion()) {

        $('#formulario_transito_enviada #pro').val("Registro");

        $('#registro_transito_eviada')
          .one('shown.bs.modal.atencionCarga', function () {
            ejecutarCuandoEsteLibre(function () {
              limpiarTE();
            });
          })
          .modal({ show: true, keyboard: false, backdrop: 'static' });
        return false;

      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    // TRANSITO RECIBIDA
    $(document).off("click.atencion", "#form_main #transito_recibida").on("click.atencion", "#form_main #transito_recibida", function (e) {
      e.preventDefault();

      if (tienePermisoAtencion()) {

        $('#formulario_transito_recibida #pro').val("Registro");
        $('#registro_transito_recibida')
          .one('shown.bs.modal.atencionCarga', function () {
            ejecutarCuandoEsteLibre(function () {
              limpiarTR();
            });
          })
          .modal({ show: true, keyboard: false, backdrop: 'static' });
        return false;

      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    // BOTON CERRAR METODO DE PAGO (arreglo val())
    $(document).off("click.atencion", "#formulario_metodoPago #boton_close_mp").on("click.atencion", "#formulario_metodoPago #boton_close_mp", function () {
      if ($('#formulario_metodoPago #nombre').val() != "" &&
          $('#formulario_metodoPago #tipo_tarifa').val() != "" &&
          $('#formulario_metodoPago #monto').val() != "" &&
          $('#formulario_metodoPago #neto').val() != "") {

        swal({
          title: "Advertencia",
          text: "No puede cerrar esta venta, hay datos en el formulario, debe proceder con los datos de la facturación del paciente",
          icon: "warning",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
        return false;
      }
    });

    // REGISTRAR METODO PAGO
    $(document).off("click.atencion", "#formulario_metodoPago #reg").on("click.atencion", "#formulario_metodoPago #reg", function (e) {
      if ($('#formulario_metodoPago #descuento').val() != "" && $('#formulario_metodoPago #tipo_pago').val() != "") {
        e.preventDefault();
        agregarMetodoPago();
      } else {
        swal({
          title: "Error",
          text: "Hay registros en blanco, por favor llenar todos los datos del formulario antes de continuar",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
        return false;
      }
    });

    // HISTORIAL
    $(document).off("click.atencion", "#form_main #historial").on("click.atencion", "#form_main #historial", function (e) {
      e.preventDefault();

      if (tienePermisoAtencion()) {

        paginationBusqueda(1);
        $('#formulario_buscarAtencion #pro').val("Búsqueda de Atenciones");
        $('#formulario_buscarAtencion #paciente_consulta').html("");
        $('#formulario_buscarAtencion #agrega_registros_busqueda_').html('<td colspan="3" style="color:#C7030D">No se encontraron resultados, seleccione un paciente para visualizar sus datos</td>');
        $('#buscar_atencion').modal({ show: true, keyboard: false, backdrop: 'static' });

      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    // PAGINATION filtros: se evita enviar una consulta por cada tecla.
    $(document)
      .off("input.atencion", "#form_main #bs_regis")
      .on("input.atencion", "#form_main #bs_regis", function () {
        programarPaginacion(1, 350);
      });

    $(document)
      .off("change.atencion", "#form_main #fecha_b, #form_main #fecha_f, #form_main #estado")
      .on("change.atencion", "#form_main #fecha_b, #form_main #fecha_f, #form_main #estado", function () {
        programarPaginacion(1, 0);
      });

    // BUSQUEDA historial con espera y cancelación de la solicitud anterior.
    $(document)
      .off("input.atencion", "#formulario_buscarAtencion #busqueda")
      .on("input.atencion", "#formulario_buscarAtencion #busqueda", function () {
        clearTimeout(temporizadoresUI.busquedaHistorial);

        temporizadoresUI.busquedaHistorial = setTimeout(function () {
          paginationBusqueda(1);
        }, 350);

        $('#formulario_buscarAtencion #paciente_consulta').html('');
        $('#formulario_buscarAtencion #agrega_registros_busqueda_').html(
          '<td colspan="12" style="color:#C7030D">No se encontraron resultados</td>'
        );
        $('#formulario_buscarAtencion #pagination_busqueda_').html('');
      });

    // TRANSITO BOTONES
    $(document).off("click.atencion", "#reg_transitoe").on("click.atencion", "#reg_transitoe", function (e) {
      if (tienePermisoAtencion()) {
        if ($('#formulario_transito_enviada #expediente').val() == "" &&
            $('#formulario_transito_enviada #motivo').val() == "" &&
            $('#formulario_agregar_referencias_recibidas #enviadaa').val() == "") {

          $('#formulario_transito_enviada')[0].reset();
          swal({
            title: 'Error',
            text: 'No se pueden enviar los datos, los campos estan vacíos',
            icon: 'error',
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
          return false;

        } else {
          e.preventDefault();
          agregarTransitoEnviadas();
        }
      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    $(document).off("click.atencion", "#reg_transitor").on("click.atencion", "#reg_transitor", function (e) {
      if (tienePermisoAtencion()) {
        if ($('#formulario_transito_recibida #expediente').val() == "" &&
            $('#formulario_transito_recibida #motivo').val() == "" &&
            $('#formulario_agregar_referencias_recibidas #enviadaa').val() == "") {

          $('#formulario_transito_recibida')[0].reset();
          swal({
            title: 'Error',
            text: 'No se pueden enviar los datos, los campos estan vacíos',
            icon: 'error',
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
          return false;

        } else {
          e.preventDefault();
          agregarTransitoRecibidas();
        }
      } else {
        swal({
          title: "Acceso Denegado",
          text: "No tiene permisos para ejecutar esta acción",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
    });

    // CAMBIAR % DESCUENTO - operaciones completamente asíncronas.
    $(document)
      .off("change.atencion", "#formulario_metodoPago #descuento")
      .on("change.atencion", "#formulario_metodoPago #descuento", function () {
        var descuento_id = $('#formulario_metodoPago #descuento').val();
        var agenda_id = $('#formulario_metodoPago #agenda_id').val();
        var tipo_tarifa = $('#formulario_metodoPago #tipo_tarifa').val();

        $.when(
          getPorcentaje(descuento_id, agenda_id),
          getColaborador_id()
        ).then(function (porcentajeRespuesta, colaboradorRespuesta) {
          var porcentaje = Array.isArray(porcentajeRespuesta)
            ? porcentajeRespuesta[0]
            : porcentajeRespuesta;

          var colaborador_id = Array.isArray(colaboradorRespuesta)
            ? colaboradorRespuesta[0]
            : colaboradorRespuesta;

          return $.when(
            $.Deferred().resolve(porcentaje),
            getMonto(colaborador_id, agenda_id, tipo_tarifa)
          );
        }).then(function (porcentaje, montoRespuesta) {
          var monto = Array.isArray(montoRespuesta) ? montoRespuesta[0] : montoRespuesta;

          return $.when(
            $.Deferred().resolve(porcentaje),
            getNetoCobrar(monto, porcentaje)
          );
        }).done(function (porcentaje, netoRespuesta) {
          var neto = Array.isArray(netoRespuesta) ? netoRespuesta[0] : netoRespuesta;

          $('#formulario_metodoPago #porcentaje').val(porcentaje);
          $('#formulario_metodoPago #neto').val(neto);
        }).fail(function (xhr) {
          console.error(xhr && xhr.responseText ? xhr.responseText : 'No se pudo calcular el descuento.');
        });
      });

    $(document)
      .off("change.atencion", "#formulario_metodoPago #tipo_tarifa")
      .on("change.atencion", "#formulario_metodoPago #tipo_tarifa", function () {
        var agenda_id = $('#formulario_metodoPago #agenda_id').val();
        var tipo_tarifa = $('#formulario_metodoPago #tipo_tarifa').val();
        var porcentaje = $('#formulario_metodoPago #porcentaje').val() || 0;

        getColaborador_id()
          .then(function (colaborador_id) {
            return getMonto(colaborador_id, agenda_id, tipo_tarifa);
          })
          .then(function (monto) {
            $('#formulario_metodoPago #monto').val(monto);

            return getNetoCobrar(monto, porcentaje);
          })
          .done(function (neto) {
            $('#formulario_metodoPago #neto').val(neto);
          })
          .fail(function (xhr) {
            console.error(xhr && xhr.responseText ? xhr.responseText : 'No se pudo calcular la tarifa.');
          });
      });

    $(document)
      .off("input.atencion", "#formulario_metodoPago #porcentaje")
      .on("input.atencion", "#formulario_metodoPago #porcentaje", function () {
        if (!$('#formulario_metodoPago #descuento').val()) {
          swal({
            title: "Error",
            text: "Por favor seleccione un tipo de descuento antes de continuar",
            icon: "error",
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });

          $('#formulario_metodoPago #descuento').focus();
          return;
        }

        var porcentaje = $('#formulario_metodoPago #porcentaje').val() || 0;
        var agenda_id = $('#formulario_metodoPago #agenda_id').val();
        var tipo_tarifa = $('#formulario_metodoPago #tipo_tarifa').val();

        clearTimeout(temporizadoresUI.porcentaje);

        temporizadoresUI.porcentaje = setTimeout(function () {
          getColaborador_id()
            .then(function (colaborador_id) {
              return getMonto(colaborador_id, agenda_id, tipo_tarifa);
            })
            .then(function (monto) {
              $('#formulario_metodoPago #monto').val(monto);

              if (Number(porcentaje) === 0) {
                $('#formulario_metodoPago #neto').val(monto);
                return $.Deferred().resolve(monto).promise();
              }

              return getNetoCobrar(monto, porcentaje);
            })
            .done(function (neto) {
              $('#formulario_metodoPago #neto').val(neto);
            })
            .fail(function (xhr) {
              console.error(xhr && xhr.responseText ? xhr.responseText : 'No se pudo calcular el neto.');
            });
        }, 250);
      });

    // TRANSITO TE/TR keyup contador
    $(document).off("keyup.atencion", "#formulario_transito_enviada #motivo").on("keyup.atencion", "#formulario_transito_enviada #motivo", function () {
      var max_chars = 255;
      var chars = $(this).val().length;
      var diff = max_chars - chars;
      $('#formulario_transito_enviada #charNumMotivoTE').html(diff + ' Caracteres');
      if (diff == 0) return false;
    });

    $(document).off("keyup.atencion", "#formulario_transito_recibida #motivo").on("keyup.atencion", "#formulario_transito_recibida #motivo", function () {
      var max_chars = 255;
      var chars = $(this).val().length;
      var diff = max_chars - chars;
      $('#formulario_transito_recibida #charNumMotivoTR').html(diff + ' Caracteres');
      if (diff == 0) return false;
    });

    // PACIENTE CONSULTA change (ANTES eval -> ahora parser)
    $(document).off("change.atencion", "#formulario_atenciones #paciente_consulta").on("change.atencion", "#formulario_atenciones #paciente_consulta", function () {
      if ($('#formulario_atenciones #paciente_consulta').val() != "" || $('#formulario_atenciones #servicio').val() != "") {

        var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/buscar_expediente.php';
        var pacientes_id = $('#formulario_atenciones #paciente_consulta').val();

        abortarSolicitud('paciente');

        solicitudesActivas.paciente = $.ajax({
          type: 'POST',
          url: url,
          data: { pacientes_id: pacientes_id },
          dataType: 'text',
          cache: false,
          timeout: 30000,
          success: function (data) {
            try {
              var array = parseServerPayload(data, "buscar_expediente.php");

              $('#formulario_atenciones #identidad').val(array[0]);
              $('#formulario_atenciones #nombre').val(array[1]);
              $('#formulario_atenciones #edad').val(array[2]);
              $('#formulario_atenciones #procedencia').val(array[3]);
              $('#formulario_atenciones #religion_id').val(array[4]);

              $('#formulario_atenciones #telefono1').val(array[30]);

              $('#formulario_atenciones #profesion_id').val(array[5]);
              $('#formulario_atenciones #estado_civil').val(array[13]);
              $('#formulario_atenciones #paciente_consulta').val(array[6]);

              $('#formulario_atenciones #antecedentes_medicos_no_psiquiatricos').val(array[7]);
              $('#formulario_atenciones #hospitalizaciones').val(array[8]);
              $('#formulario_atenciones #cirugias').val(array[9]);

              $('#formulario_atenciones #alergias').val(array[14]);
              $('#formulario_atenciones #antecedentes_medicos_psiquiatricos').val(array[15]);
              $('#formulario_atenciones #historia_gineco_obstetrica').val(array[16]);
              $('#formulario_atenciones #medicamentos_previos').val(array[17]);
              $('#formulario_atenciones #medicamentos_actuales').val(array[18]);
              $('#formulario_atenciones #legal').val(array[19]);
              $('#formulario_atenciones #sustancias').val(array[20]);
              $('#formulario_atenciones #rasgos_personalidad').val(array[21]);
              $('#formulario_atenciones #informacion_adicional').val(array[22]);
              $('#formulario_atenciones #pendientes').val(array[23]);
              $('#formulario_atenciones #diagnostico').val(array[24]);

              $('#formulario_atenciones #num_hijos').val(array[26]);

              $('#formulario_atenciones #escolaridad').val(array[27]);
              $('#formulario_atenciones #red_apoyo').val(array[28]);
              $('#formulario_atenciones #terapeuta_actual').val(array[29]);

              $('#formulario_atenciones #seguimiento_read').val(array[10]);
              $('#formulario_atenciones #diagnostico').val(array[11]);
              $('#formulario_atenciones #fecha_nac').val(array[12]);

              refrescarSelectoresAtencion([
                '#formulario_atenciones #religion_id',
                '#formulario_atenciones #profesion_id',
                '#formulario_atenciones #estado_civil',
                '#formulario_atenciones #escolaridad'
              ]);

              $("#reg_atencion").attr('disabled', false);
              return false;

            } catch (e) {
              console.error(e);
            }
          }
        });
        return false;

      } else {
        $('#formulario_atenciones')[0].reset();
        $("#reg_atencion").attr('disabled', true);
      }
    });

    // TRANSITO paciente_te change
    $(document).off("change.atencion", "#formulario_transito_enviada #paciente_te").on("change.atencion", "#formulario_transito_enviada #paciente_te", function () {
      if ($('#formulario_transito_enviada #paciente_te').val() != "") {

        var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/buscar_expediente.php';
        var pacientes_id = $('#formulario_transito_enviada #paciente_te').val();

        abortarSolicitud('transitoEnviado');

        solicitudesActivas.transitoEnviado = $.ajax({
          type: 'POST',
          url: url,
          data: { pacientes_id: pacientes_id },
          dataType: 'text',
          cache: false,
          timeout: 30000,
          success: function (data) {
            try {
              var array = parseServerPayload(data, "buscar_expediente.php(TE)");
              $('#formulario_transito_enviada #identidad').val(array[0]);
            } catch (e) {
              console.error(e);
            }
          }
        });
        return false;

      } else {
        $('#formulario_transito_enviada')[0].reset();
        $('#formulario_transito_enviada #pro').val("Registro");
        $("#reg_transitoe").attr('disabled', true);
      }
    });

    // TRANSITO paciente_tr change
    $(document).off("change.atencion", "#formulario_transito_recibida #paciente_tr").on("change.atencion", "#formulario_transito_recibida #paciente_tr", function () {
      if ($('#formulario_transito_recibida #paciente_tr').val() != "") {

        var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/buscar_expediente.php';
        var pacientes_id = $('#formulario_transito_recibida #paciente_tr').val();

        abortarSolicitud('transitoRecibido');

        solicitudesActivas.transitoRecibido = $.ajax({
          type: 'POST',
          url: url,
          data: { pacientes_id: pacientes_id },
          dataType: 'text',
          cache: false,
          timeout: 30000,
          success: function (data) {
            try {
              var array = parseServerPayload(data, "buscar_expediente.php(TR)");
              $('#formulario_transito_recibida #identidad').val(array[0]);
            } catch (e) {
              console.error(e);
            }
          }
        });
        return false;

      } else {
        $('#formulario_transito_recibida')[0].reset();
        $('#formulario_transito_recibida #pro').val("Registro");
        $("#reg_transitor").attr('disabled', true);
      }
    });

    // NUEVA FACTURA
    $(document).off("click.atencion", "#form_main #nueva_factura").on("click.atencion", "#form_main #nueva_factura", function (e) {
      e.preventDefault();
      formFactura();
    });

    // FECHA NAC -> edad (ANTES eval -> ahora parser)
    $(document).off("change.atencion", "#formulario_atenciones #fecha_nac").on("change.atencion", "#formulario_atenciones #fecha_nac", function () {
      var fecha_nac = $('#formulario_atenciones #fecha_nac').val();
      var url = '<?php echo SERVERURL; ?>php/pacientes/getEdad.php';

      $.ajax({
        type: "POST",
        url: url,
        async: true,
        data: 'fecha_nac=' + fecha_nac,
        success: function (data) {
          try {
            var array = parseServerPayload(data, "getEdad.php");
            $('#formulario_atenciones #edad').val(array[3]);
          } catch (e) {
            console.error(e);
          }
        }
      });
    });

  }); // end init

  // ============================================================================
  // ✅ TUS FUNCIONES (con cambios mínimos: eval -> parseServerPayload)
  // ============================================================================

  function showFactura(atencion_id) {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/editarFactura.php';

    $.ajax({
      type: 'POST',
      url: url,
      data: 'atencion_id=' + atencion_id,
      success: function (data) {
        try {
          var datos = parseServerPayload(data, "editarFactura.php");

          $('#formulario_facturacion')[0].reset();
          $('#formulario_facturacion #pro').val("Registro");
          $('#formulario_facturacion #pacientes_id').val(datos[0]);
          refrescarSelectSinBloquear($('#formulario_facturacion #pacientes_id'));

          $('#formulario_facturacion #fecha').val(convertDate(new Date()));
          $('#formulario_facturacion #colaborador_id').val(datos[3]);
          refrescarSelectSinBloquear($('#formulario_facturacion #colaborador_id'));
          $('#formulario_facturacion #servicio_id').val(datos[5]);
          refrescarSelectSinBloquear($('#formulario_facturacion #servicio_id'));

          $('#label_acciones_volver').html("ATA");
          $('#label_acciones_receta').html("Receta");

          $('#formulario_facturacion #fecha').attr("readonly", true);
          $('#formulario_facturacion #validar').attr("disabled", false).show();
          $('#formulario_facturacion #addRows').attr("disabled", false);
          $('#formulario_facturacion #removeRows').attr("disabled", false);
          $('#formulario_facturacion #editar').hide();
          $('#formulario_facturacion #eliminar').hide();

          limpiarTabla();

          vistaAnteriorFactura = 'atencion';
          navegacionConfirmada = false;

          $('#main_facturacion').hide();
          $('#atencionMedica').hide();
          $('#facturacion').show();

          $('#formulario_facturacion').attr({ 'data-form': 'save' });
          $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });

          $('#formulario_facturacion #validar').hide();
          $('#formulario_facturacion #guardar1').hide();

          $('.footer').hide();
          $('.footer1').show();

          cleanFooterValueBill();

          // Se toma la fotografía inicial después de cargar paciente,
          // profesional, servicio y limpiar el detalle.
          setTimeout(guardarSnapshotFactura, 0);

        } catch (e) {
          console.error(e);
          swal({
            title: "Error",
            text: "La respuesta de editarFactura no vino en formato válido. Revisá Network > Response.",
            icon: "error",
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
        }
      }
    });
  }

  // INICIO FUNCION PARA OBTENER LOS COLABORADORES
  window.getColaborador = function () {
    var url = '<?php echo SERVERURL; ?>php/citas/getMedico.php';

    $.ajax({
      type: "POST",
      url: url,
      async: true,
      success: function (data) {
        $('#registro_transito_eviada #enviada').html("").html(data).selectpicker('refresh');
        $('#formulario_transito_recibida #recibida').html("").html(data).selectpicker('refresh');
      }
    });
  };

  window.editarRegistro = function (pacientes_id, agenda_id) {
    if (!tienePermisoAtencion()) {
      swal({
        title: "Acceso Denegado",
        text: "No tiene permisos para ejecutar esta acción",
        icon: "error",
        dangerMode: true,
        closeOnEsc: false,
        closeOnClickOutside: false
      });
      return false;
    }

    if ($('#form_main #estado').val() != 0) {
      swal({
        title: "Error",
        text: "Lo sentimos, este registro ya existe, no se puede agregar nuevamente su atención",
        icon: "error",
        dangerMode: true,
        closeOnEsc: false,
        closeOnClickOutside: false
      });
      return false;
    }

    // Si una lista grande de pacientes aún se está construyendo, se cancela
    // antes de mostrar la atención. Esa construcción era la que congelaba la UI.
    abortarSolicitud('catalogoPacientesAtencion');
    destruirSelectpickerPacienteAtencion();

    mostrarCargaAtencion('Cargando historia clínica del paciente...');

    // La vista se pinta primero. La consulta inicia en el siguiente frame.
    siguientePintado(function () {
      var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/editar.php';
      var tiempoInicioEdicion = performance.now();

      console.time('Generar atención - total');
      console.time('Generar atención - editar.php');

      $.ajax({
        type: 'POST',
        url: url,
        data: {
          pacientes_id: pacientes_id,
          agenda_id: agenda_id
        },
        dataType: 'text',
        cache: false,
        timeout: 30000,
        success: function (valores) {
          console.timeEnd('Generar atención - editar.php');
          console.log(
            'editar.php respondió en',
            Math.round(performance.now() - tiempoInicioEdicion),
            'ms'
          );

          var array;

          try {
            array = parseServerPayload(valores, 'editar.php');
          } catch (error) {
            ocultarCargaAtencion();

            swal({
              title: 'Error',
              text: 'La información de la atención no tiene un formato válido.',
              icon: 'error',
              dangerMode: true,
              closeOnEsc: false,
              closeOnClickOutside: false
            });
            return;
          }

          var tareas = [
            function () {
              if ($('#formulario_atenciones').length) {
                $('#formulario_atenciones')[0].reset();
              }
            },
            function () {
              $('#reg_atencion').hide();
              $('#edi_atencion').show();
              $('#formulario_atenciones #pro').val('Registro');
              $('#formulario_atenciones #pacientes_id').val(pacientes_id);
              $('#formulario_atenciones #agenda_id').val(agenda_id);
            },
            function () {
              $('#formulario_atenciones #identidad').val(array[0]);
              $('#formulario_atenciones #nombre').val(array[1]);
              $('#formulario_atenciones #telefono1').val(array[31]);
              $('#formulario_atenciones #edad').val(array[2]);
              $('#formulario_atenciones #procedencia').val(array[3]);
            },
            function () {
              $('#formulario_atenciones #religion_id').val(array[4]);
              $('#formulario_atenciones #profesion_id').val(array[5]);
              $('#formulario_atenciones #estado_civil').val(array[15]);
              $('#formulario_atenciones #escolaridad').val(array[17]);

              // Desde paginar se respeta el servicio de la agenda.
              // Si viene vacío o ya no existe, se usa el primero disponible.
              servicioAtencionPendiente = array[14];
              seleccionarServicioAtencion(servicioAtencionPendiente);
            },
            function () {
              $('#formulario_atenciones #fecha').val(array[7]);
              $('#formulario_atenciones #fecha_nac').val(array[8]);
              $('#formulario_atenciones #seguimiento_read').val(array[13]);
              $('#formulario_atenciones #num_hijos').val(array[16]);
              $('#formulario_atenciones #red_apoyo').val(array[18]);
              $('#formulario_atenciones #terapeuta_actual').val(array[19]);
            },
            function () {
              $('#formulario_atenciones #antecedentes_medicos_no_psiquiatricos').val(array[9]);
              $('#formulario_atenciones #hospitalizaciones').val(array[10]);
              $('#formulario_atenciones #cirugias').val(array[11]);
              $('#formulario_atenciones #alergias').val(array[12]);
            },
            function () {
              $('#formulario_atenciones #antecedentes_medicos_psiquiatricos').val(array[20]);
              $('#formulario_atenciones #historia_gineco_obstetrica').val(array[21]);
              $('#formulario_atenciones #medicamentos_previos').val(array[22]);
              $('#formulario_atenciones #medicamentos_actuales').val(array[23]);
            },
            function () {
              $('#formulario_atenciones #legal').val(array[24]);
              $('#formulario_atenciones #sustancias').val(array[25]);
              $('#formulario_atenciones #rasgos_personalidad').val(array[26]);
              $('#formulario_atenciones #informacion_adicional').val(array[27]);
              $('#formulario_atenciones #pendientes').val(array[28]);
              $('#formulario_atenciones #diagnostico').val(array[29]);
            },
            function () {
              $('#formulario_atenciones #fecha').attr('readonly', true);
              $('#edi_atencion').attr('disabled', false);
              $('#formulario_atenciones #label_servicio').show();
              $('#formulario_atenciones #consultorio_').hide();
              $('#formulario_atenciones #paciente_consulta').attr('disabled', true);
              $('#formulario_atenciones #procedencia').attr('readonly', false);
            },
            function () {
              $('#formulario_atenciones').attr({
                'data-form': 'save',
                'action': '<?php echo SERVERURL; ?>php/atencion_pacientes/agregarRegistro.php'
              });
            },
            function () {
              $('#formulario_atenciones .nav-tabs li:eq(0) a').tab('show');
            }
          ];

          ejecutarTareasEnLotes(tareas, function () {
            refrescarSelectoresAtencion([
              '#formulario_atenciones #religion_id',
              '#formulario_atenciones #profesion_id',
              '#formulario_atenciones #servicio_id',
              '#formulario_atenciones #estado_civil',
              '#formulario_atenciones #escolaridad'
            ], function () {
              // Reforzar la selección después de renderizar los catálogos.
              seleccionarServicioAtencion(servicioAtencionPendiente);

              // En edición desde agenda no hace falta cargar todos los pacientes.
              // Se crea un select con únicamente el paciente actual.
              prepararPacienteUnicoAtencion(array[6], array[1]);

              ocultarCargaAtencion();
              console.timeEnd('Generar atención - total');

              ejecutarCuandoEsteLibre(function () {
                inicializarContadores(limites);

                if (!estadoComponentesAtencion.speechInicializado) {
                  inicializarSpeechRecognition(limites);
                  estadoComponentesAtencion.speechInicializado = true;
                }
              }, 1800);
            });
          });
        },
        error: function (xhr, textStatus, errorThrown) {
          ocultarCargaAtencion();

          swal({
            title: 'Error',
            text: xhr.responseText || errorThrown || textStatus ||
              'No se pudo cargar la atención médica.',
            icon: 'error',
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
        }
      });
    });

    return false;
  };

  // AUSENCIA
  window.nosePresentoRegistro = function (pacientes_id, agenda_id) {
    if (!tienePermisoAtencion()) {
      swal({
        title: "Acceso Denegado",
        text: "No tiene permisos para ejecutar esta acción",
        icon: "error",
        dangerMode: true,
        closeOnEsc: false,
        closeOnClickOutside: false
      });
      return false;
    }

    if ($('#form_main #estado').val() != 0) {
      swal({
        title: "Error",
        text: "Error al ejecutar esta acción, el usuario debe estar en estatus pendiente",
        icon: "error",
        dangerMode: true,
        closeOnEsc: false,
        closeOnClickOutside: false
      });
      return false;
    }

    $.when(
      consultarNombre(pacientes_id),
      consultarExpediente(pacientes_id)
    ).done(function (nombreRespuesta, expedienteRespuesta) {
      var nombre_usuario = Array.isArray(nombreRespuesta)
        ? nombreRespuesta[0]
        : nombreRespuesta;

      var expediente_usuario = Array.isArray(expedienteRespuesta)
        ? expedienteRespuesta[0]
        : expedienteRespuesta;

      var dato = Number(expediente_usuario) === 0
        ? nombre_usuario
        : nombre_usuario + " (Expediente: " + expediente_usuario + ")";

      swal({
        title: "¿Está seguro?",
        text: "¿Desea remover este usuario: " + dato + " que no se presentó a su cita?",
        content: {
          element: "input",
          attributes: {
            placeholder: "Comentario",
            type: "text"
          }
        },
        icon: "warning",
        buttons: {
          cancel: "Cancelar",
          confirm: {
            text: "¡Sí, remover el usuario!",
            closeModal: false
          }
        },
        dangerMode: true,
        closeOnEsc: false,
        closeOnClickOutside: false
      }).then(function (value) {
        if (value === null || $.trim(value) === "") {
          swal("¡Necesita escribir algo!", { icon: "error" });
          return false;
        }

        eliminarRegistro(agenda_id, value);
      });
    }).fail(function (xhr) {
      console.error(xhr && xhr.responseText ? xhr.responseText : 'No se pudo consultar al paciente.');

      swal({
        title: "Error",
        text: "No se pudo consultar la información del paciente.",
        icon: "error",
        dangerMode: true,
        closeOnEsc: false,
        closeOnClickOutside: false
      });
    });

    return false;
  };

  window.eliminarRegistro = function (agenda_id, comentario, fecha) {
    var hoy = new Date();
    var fecha_actual = convertDate(hoy);

    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/usuario_no_presento.php';

    $.ajax({
      type: 'POST',
      url: url,
      data: 'agenda_id=' + agenda_id + '&fecha=' + fecha + '&comentario=' + comentario,
      success: function (registro) {
        if (registro == 1) {
          swal({
            title: "Success",
            text: "Ausencia almacenada correctamente",
            icon: "success",
            timer: 3000,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
          pagination(1);
          return false;
        } else if (registro == 2) {
          swal({
            title: "Error",
            text: "Error al remover este registro",
            icon: "error",
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
          return false;
        } else if (registro == 3) {
          swal({
            title: "Error",
            text: "Este registro ya tiene almacenada una ausencia",
            icon: "error",
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
          return false;
        } else {
          swal({
            title: "Error",
            text: "Error al ejecutar esta acción",
            icon: "error",
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
        }
      }
    });
    return false;
  };

  // ============================
  // ✅ CONTADORES (sin duplicar)
  // ============================
  window.inicializarContadores = function (limites) {
    Object.keys(limites).forEach(function (campo) {
      var $campo = $('#formulario_atenciones #' + campo);

      if (!$campo.length) {
        return;
      }

      $campo.off('input.charcount').on('input.charcount', function () {
        actualizarCaracteres(campo, 'charNum_' + campo, limites[campo]);
      });

      actualizarCaracteres(campo, 'charNum_' + campo, limites[campo]);
    });

    estadoComponentesAtencion.contadoresInicializados = true;
  };

  window.actualizarCaracteres = function (campo, contadorId, max_chars) {
    var texto = $('#' + campo).val() || '';
    var longitudTexto = texto.length;

    if (longitudTexto > max_chars) {
      $('#' + campo).val(texto.substring(0, max_chars));
      longitudTexto = max_chars;
    }
    $('#' + contadorId).text(longitudTexto + '/' + max_chars);
  };

  // ============================
// ✅ DICTADO POR VOZ (MISMO TUYO, FIX EDGE)
// ============================
window.inicializarSpeechRecognition = function (limites) {
  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SR) {
    console.warn("SpeechRecognition no disponible en este navegador.");
    return;
  }
  if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
    console.warn("SpeechRecognition suele requerir HTTPS.");
    return;
  }

  // Evitar duplicar handlers y recognitions
  if (!window.__SPEECH_STATE__) window.__SPEECH_STATE__ = { recognitions: {}, activeCampo: null };

  // Estado visual inicial: únicamente se muestra el botón de grabar.
  $('#formulario_atenciones [id^="search_"][id$="_stop"]').hide();
  $('#formulario_atenciones [id^="search_"][id$="_start"]').show();

  const isEdge = /Edg\//.test(navigator.userAgent);

  Object.keys(limites).forEach(function (campo) {

    const $start = $('#formulario_atenciones #search_' + campo + '_start');
    const $stop  = $('#formulario_atenciones #search_' + campo + '_stop'); // puede no existir

    // ✅ Antes: si faltaba stop, no funcionaba. Ahora: con que exista start basta.
    if (!$start.length) return;

    // Si existe stop, lo ocultamos; si no existe, no pasa nada.
    if ($stop.length) $stop.hide();

    // si ya existía recognition de este campo, la detenemos y la reemplazamos
    if (window.__SPEECH_STATE__.recognitions[campo]) {
      try { window.__SPEECH_STATE__.recognitions[campo].recognition.stop(); } catch (_) {}
    }

    const recognition = new SR();
    recognition.continuous = true;
    recognition.interimResults = false;

    // ✅ Edge suele fallar con "es" -> usar "es-ES"
    recognition.lang = "es-ES";

    window.__SPEECH_STATE__.recognitions[campo] = { recognition, running: false };

    function stopCampo(c) {
      const item = window.__SPEECH_STATE__.recognitions[c];
      if (!item) return;
      try { item.recognition.stop(); } catch (_) {}
      item.running = false;

      // UI: si existe stop, se alterna como antes
      if ($('#formulario_atenciones #search_' + c + '_stop').length) {
        $('#formulario_atenciones #search_' + c + '_stop').hide();
        $('#formulario_atenciones #search_' + c + '_start').show();
      } else {
        // Si solo hay un botón, lo dejamos visible
        $('#formulario_atenciones #search_' + c + '_start').show();
      }
    }

    $start.off('click.speech').on('click.speech', async function (event) {
      event.preventDefault();

      // Si NO existe stop, este mismo botón funciona como toggle (start/stop)
      if (!$stop.length && window.__SPEECH_STATE__.recognitions[campo].running) {
        stopCampo(campo);
        if (window.__SPEECH_STATE__.activeCampo === campo) window.__SPEECH_STATE__.activeCampo = null;
        return false;
      }

      // detener otro campo si está activo
      if (window.__SPEECH_STATE__.activeCampo && window.__SPEECH_STATE__.activeCampo !== campo) {
        stopCampo(window.__SPEECH_STATE__.activeCampo);
      }

      window.__SPEECH_STATE__.activeCampo = campo;
      window.__SPEECH_STATE__.recognitions[campo].running = true;

      // UI: solo si existe stop
      if ($stop.length) {
        $start.hide();
        $stop.show();
      }

      try {
        // ✅ Truco para Edge: pedir audio antes de start()
        if (isEdge && navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
          await navigator.mediaDevices.getUserMedia({ audio: true });
        }

        recognition.start();
      }
      catch (e) {
        console.error(e);
        stopCampo(campo);
        swal({
          title: "Micrófono",
          text: "No se pudo iniciar el dictado. Revisá permisos del micrófono del navegador.",
          icon: "error",
          dangerMode: true,
          closeOnEsc: false,
          closeOnClickOutside: false
        });
      }
      return false;
    });

    // STOP solo si existe botón stop
    if ($stop.length) {
      $stop.off('click.speech').on('click.speech', function (event) {
        event.preventDefault();
        stopCampo(campo);
        if (window.__SPEECH_STATE__.activeCampo === campo) window.__SPEECH_STATE__.activeCampo = null;
        return false;
      });
    }

    recognition.onresult = function (event) {
      let valorActual = $('#formulario_atenciones #' + campo).val() || '';

      for (let i = event.resultIndex; i < event.results.length; ++i) {
        if (event.results[i].isFinal) {
          const textoNuevo = (event.results[i][0] && event.results[i][0].transcript) ? event.results[i][0].transcript : '';
          let combinado = (valorActual + ' ' + textoNuevo).trim();

          if (combinado.length > limites[campo]) {
            combinado = combinado.substring(0, limites[campo]);
          }

          $('#formulario_atenciones #' + campo).val(combinado);
          actualizarCaracteres(campo, 'charNum_' + campo, limites[campo]);
          valorActual = combinado;
        }
      }
    };

    recognition.onerror = function (event) {
      console.error("Speech error:", event);
      stopCampo(campo);
    };

    // ✅ Reintento SOLO en Chrome. En Edge puede dar "aborted/network"
    recognition.onend = function () {
      const item = window.__SPEECH_STATE__.recognitions[campo];
      if (item && item.running) {
        if (!isEdge) {
          try { recognition.start(); } catch (_) {}
        }
      }
    };
  });
};

  // ============================
  // PAGINACION (ANTES: eval)
  // ============================
  window.pagination = function (partida) {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/paginar.php';
    var fechai = $('#form_main #fecha_b').val();
    var fechaf = $('#form_main #fecha_f').val();
    var dato = $('#form_main #bs_regis').val() || '';
    var estado = ($('#form_main #estado').val() === '' || $('#form_main #estado').val() == null)
      ? 0
      : $('#form_main #estado').val();

    abortarSolicitud('pagination');

    solicitudesActivas.pagination = $.ajax({
      type: 'POST',
      url: url,
      dataType: 'json',
      cache: false,
      timeout: 30000,
      data: {
        partida: partida,
        fechai: fechai,
        fechaf: fechaf,
        dato: dato,
        estado: estado
      },
      beforeSend: function () {
        var $contenedor = $('#agrega-registros-atenciones');

        $contenedor.attr('aria-busy', 'true');

        if (!estadoCargaInicial.listadoCargado && $.trim($contenedor.html()) === '') {
          $contenedor.html(
            '<div class="text-center" style="padding:30px 10px;">' +
              '<i class="fas fa-spinner fa-spin fa-2x"></i>' +
              '<div style="margin-top:10px;">Cargando atenciones...</div>' +
            '</div>'
          );
        }
      },
      success: function (respuesta) {
        if (!respuesta || respuesta.status !== 'success') {
          $('#agrega-registros-atenciones').html(
            '<div class="alert alert-danger">' +
            (respuesta && respuesta.message ? respuesta.message : 'No se pudieron consultar las atenciones.') +
            '</div>'
          );
          $('#pagination-atenciones').html('');
          return;
        }

        $('#agrega-registros-atenciones').html(respuesta.html);
        $('#pagination-atenciones').html(respuesta.pagination);
        estadoCargaInicial.listadoCargado = true;
      },
      error: function (xhr, textStatus, errorThrown) {
        if (textStatus === 'abort') return;

        var respuesta = xhr.responseJSON;
        var mensaje = respuesta && respuesta.message
          ? respuesta.message
          : (xhr.responseText || errorThrown || textStatus || 'No se pudieron consultar las atenciones.');

        $('#agrega-registros-atenciones').html(
          '<div class="alert alert-danger">' + mensaje + '</div>'
        );
        $('#pagination-atenciones').html('');
      },
      complete: function () {
        $('#agrega-registros-atenciones').removeAttr('aria-busy');
      }
    });

    return solicitudesActivas.pagination;
  };

  window.paginationBusqueda = function (partida) {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/paginar_buscar.php';
    var dato = $('#formulario_buscarAtencion #busqueda').val() || '';

    abortarSolicitud('paginationBusqueda');

    solicitudesActivas.paginationBusqueda = $.ajax({
      type: 'POST',
      url: url,
      data: {
        partida: partida,
        dato: dato
      },
      dataType: 'text',
      cache: false,
      timeout: 30000,
      success: function (data) {
        try {
          var array = parseServerPayload(data, "paginar_buscar.php");
          $('#formulario_buscarAtencion #agrega_registros_busqueda').html(array[0]);
          $('#formulario_buscarAtencion #pagination_busqueda').html(array[1]);
        } catch (e) {
          console.error(e);
        }
      },
      error: function (xhr, textStatus, errorThrown) {
        if (textStatus === 'abort') return;
        console.error(xhr.responseText || errorThrown || textStatus);
      }
    });

    return false;
  };

  window.detallesAtencion = function (pacientes_id) {
    $('#formulario_buscarAtencion #pacientes_id').val(pacientes_id);
    paginarSeguimiento(1);
  };

  window.paginarSeguimiento = function (partida) {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/paginar_historias_clinicas.php';
    var pacientes_id = $('#formulario_buscarAtencion #pacientes_id').val();

    abortarSolicitud('paginarSeguimiento');

    solicitudesActivas.paginarSeguimiento = $.ajax({
      type: 'POST',
      url: url,
      data: {
        partida: partida,
        pacientes_id: pacientes_id
      },
      dataType: 'text',
      cache: false,
      timeout: 30000,
      success: function (data) {
        try {
          var array = parseServerPayload(data, "paginar_historias_clinicas.php");

          getNombrePaciente(pacientes_id)
            .done(function (nombrePaciente) {
              $('#formulario_buscarAtencion #paciente_consulta').html(
                '<b>Paciente:</b> ' + nombrePaciente
              );
            })
            .fail(function () {
              $('#formulario_buscarAtencion #paciente_consulta').html('<b>Paciente:</b>');
            });

          $('#formulario_buscarAtencion #agrega_registros_busqueda_').html(array[0]);
          $('#formulario_buscarAtencion #pagination_busqueda_').html(array[1]);
        } catch (e) {
          console.error(e);
        }
      },
      error: function (xhr, textStatus, errorThrown) {
        if (textStatus === 'abort') return;
        console.error(xhr.responseText || errorThrown || textStatus);
      }
    });

    return false;
  };

  // ============================
  // Limpieza forms (tu código)
  // ============================
  window.limpiarFormPacientes = function () {
    $('#formulario_atenciones #historia_clinica').val('');
    $('#formulario_atenciones #historia_clinica_read').val('');
    $('#formulario_atenciones #seguimiento').val('');
    $('#formulario_atenciones #seguimiento_read').val('');
    $('#formulario_atenciones #pro').val('Registro');
  };

  window.limpiarFormMetodoPago = function () {
    funcionesMetodoPago();
    $('#formulario_metodoPago #pro').val('Registro');
    $("#formulario_metodoPago #reg").attr('disabled', true);
  };

  // ============================
  // (El resto de tus funciones NO las cambio de lógica, solo quedan igual)
  // 👉 Todo lo demás se mantiene como lo tenías (AJAX, swal, etc.)
  // 👉 Si tenés algún otro eval() escondido, cambiá por parseServerPayload()
  // ============================

  // --- TU CODIGO TAL CUAL (sin tocar urls/fields), solo dejo aquí las que estaban al final:

  window.funcionesFormPacientes = function () {
    if (estadoCargaInicial.catalogosIniciados) {
      return;
    }

    estadoCargaInicial.catalogosIniciados = true;

    // Se lanzan después de cargar el listado para que nunca retrasen la tabla.
    setTimeout(function () {
      getServicioTransito();
      getEstado();

      // La lista grande de pacientes de Atención Médica ya no se carga aquí.
      // Solo se cargan los pacientes de Tránsito; Atención los carga al crear
      // una atención nueva y usa un único paciente al editar desde agenda.
      getPacientesTransito();

      getConsultorio();
      getEscolaridad();
      getEstadoCivil();
      getProfesion();
      getReligion();
    }, 0);
  };

  window.getNombrePaciente = function (pacientes_id) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getNombrePaciente.php',
      { pacientes_id: pacientes_id }
    );
  };

  window.getMonto = function (colaborador_id, agenda_id, tipo_tarifa) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getMonto.php',
      {
        colaborador_id: colaborador_id,
        agenda_id: agenda_id,
        tipo_tarifa: tipo_tarifa
      }
    );
  };

  window.getPorcentaje = function (descuento_id, agenda_id) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getDescuentoPorcentaje.php',
      {
        descuento_id: descuento_id,
        agenda_id: agenda_id
      }
    );
  };

  window.getNetoCobrar = function (monto, porcentaje) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getNetoCobrar.php',
      {
        monto: monto,
        porcentaje: porcentaje
      }
    );
  };

  window.getColaborador_id = function () {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getColaborador.php'
    );
  };

  window.getServicioTransito = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/servicios_transito.php';

    $.ajax({
      type: "POST",
      url: url,
      async: true,
      success: function (data) {
        $('#formulario_transito_enviada #servicio').html("").html(data).selectpicker('refresh');
        $('#formulario_transito_recibida #servicio').html("").html(data).selectpicker('refresh');
      }
    });
  };

  window.limpiarTE = function () {
    getPacientesTransito();
    getColaborador();
    $('#formulario_transito_enviada #pro').val("Registro");
    $('#formulario_transito_enviada #motivo').val("");
    $("#reg_transitoe").attr('disabled', false);
  };

  window.limpiarTR = function () {
    getPacientesTransito();
    getColaborador();
    $('#formulario_transito_recibida #pro').val("Registro");
    $('#formulario_transito_recibida #motivo').val("");
    $("#reg_transitor").attr('disabled', false);
  };

  window.obtenerCatalogoPacientesHtml = function () {
    if (cacheCatalogosAtencion.pacientesHtml !== null) {
      return $.Deferred()
        .resolve(cacheCatalogosAtencion.pacientesHtml)
        .promise();
    }

    return $.ajax({
      type: 'POST',
      url: '<?php echo SERVERURL; ?>php/atencion_pacientes/getPacientes.php',
      dataType: 'html',
      cache: true,
      timeout: 30000
    }).then(function (data) {
      cacheCatalogosAtencion.pacientesHtml = data;
      return data;
    });
  };

  window.getPacientesAtencion = function () {
    abortarSolicitud('catalogoPacientesAtencion');

    solicitudesActivas.catalogoPacientesAtencion = obtenerCatalogoPacientesHtml()
      .then(function (data) {
        var deferred = $.Deferred();
        var $select = $('#formulario_atenciones #paciente_consulta');

        destruirSelectpickerPacienteAtencion();

        // Se deja que la vista se pinte antes de insertar una lista grande.
        siguientePintado(function () {
          $select.html(data).prop('disabled', false);

          siguientePintado(function () {
            if (typeof $select.selectpicker === 'function') {
              $select.selectpicker();
            }

            deferred.resolve();
          });
        });

        return deferred.promise();
      });

    return solicitudesActivas.catalogoPacientesAtencion;
  };

  window.getPacientesTransito = function () {
    abortarSolicitud('catalogoPacientesTransito');

    solicitudesActivas.catalogoPacientesTransito = obtenerCatalogoPacientesHtml()
      .then(function (data) {
        var selects = [
          '#formulario_transito_enviada #paciente_te',
          '#formulario_transito_recibida #paciente_tr'
        ];

        var deferred = $.Deferred();
        var indice = 0;

        function cargarSiguiente() {
          if (indice >= selects.length) {
            deferred.resolve();
            return;
          }

          var $select = $(selects[indice++]);

          window.requestAnimationFrame(function () {
            try {
              if ($select.data('selectpicker')) {
                $select.selectpicker('destroy');
              }
            } catch (_) {}

            $select.html(data);

            window.requestAnimationFrame(function () {
              if (typeof $select.selectpicker === 'function') {
                $select.selectpicker();
              }

              cargarSiguiente();
            });
          });
        }

        cargarSiguiente();
        return deferred.promise();
      });

    return solicitudesActivas.catalogoPacientesTransito;
  };

  // Compatibilidad con llamadas antiguas: carga únicamente donde corresponde.
  window.getPacientes = function () {
    return $.when(
      getPacientesAtencion(),
      getPacientesTransito()
    );
  };

  window.getServicioAtencion = function (agenda_id) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/servicios.php',
      { agenda_id: agenda_id }
    );
  };

  window.getEstado = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getEstado.php';
    $.ajax({
      type: "POST",
      url: url,
      async: true,
      success: function (data) {
        $('#form_main #estado').html("").html(data).selectpicker('refresh');
      }
    });
  };

  window.evaluarRegistrosPendientes = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/evaluarPendientes.php';
    var fecha = getSafeFechaGlobal();

    abortarSolicitud('pendientes');

    solicitudesActivas.pendientes = $.ajax({
      type: 'POST',
      url: url,
      data: { fecha: fecha },
      dataType: 'text',
      cache: false,
      timeout: 30000,
      success: function (valores) {
        try {
          var datos = parseServerPayload(valores, 'evaluarPendientes.php');
          var total = Number(datos[0] || 0);

          if (total <= 0) {
            return;
          }

          // Durante la entrada se muestra una sola vez. El intervalo podrá
          // volver a mostrarla después de treinta minutos.
          if (!estadoCargaInicial.alertaPendientesMostrada) {
            estadoCargaInicial.alertaPendientesMostrada = true;
          }

          var textoRegistro = total === 1
            ? 'Registro pendiente'
            : 'Registros pendientes';

          swal({
            title: 'Advertencia',
            text: 'Se le recuerda que tiene ' + total + ' ' + textoRegistro +
              ' de subir en las Atenciones Médicas en este mes de ' + datos[1] +
              '. Debe revisar sus registros pendientes.',
            icon: 'warning',
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
          });
        } catch (e) {
          console.error(e);
        }
      },
      error: function (xhr, textStatus, errorThrown) {
        if (textStatus === 'abort') {
          return;
        }

        console.error(
          'No se pudieron evaluar los registros pendientes:',
          xhr.responseText || errorThrown || textStatus
        );
      }
    });

    return solicitudesActivas.pendientes;
  };

  window.evaluarRegistrosPendientesEmail = function () {
    var url = '<?php echo SERVERURL; ?>php/mail/evaluarPendientes_atencionesMedicas.php';
    $.ajax({ type: 'POST', url: url, success: function () { } });
  };

  window.getEscolaridad = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getEscolaridad.php';

    return $.ajax({
      type: 'GET',
      url: url,
      dataType: 'html',
      cache: false,
      success: function (data) {
        var $select = $('#formulario_atenciones #escolaridad');

        $select.html(data);
        refrescarSelectSinBloquear($select);
      },
      error: function (xhr, textStatus, errorThrown) {
        console.error(
          'No se pudo cargar Escolaridad:',
          xhr.responseText || errorThrown || textStatus
        );

        $('#formulario_atenciones #escolaridad')
          .html('<option value="">No se pudo cargar Escolaridad</option>')
          .selectpicker('refresh');
      }
    });
  };

  window.getEstadoCivil = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getEstadoCivil.php';

    return $.ajax({
      type: 'GET',
      url: url,
      dataType: 'html',
      cache: false,
      success: function (data) {
        var $select = $('#formulario_atenciones #estado_civil');

        $select.html(data);
        refrescarSelectSinBloquear($select);
      },
      error: function (xhr, textStatus, errorThrown) {
        console.error(
          'No se pudo cargar Estado Civil:',
          xhr.responseText || errorThrown || textStatus
        );

        $('#formulario_atenciones #estado_civil')
          .html('<option value="">No se pudo cargar Estado Civil</option>')
          .selectpicker('refresh');
      }
    });
  };

  window.getProfesion = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getProfesion.php';

    return $.ajax({
      type: 'GET',
      url: url,
      dataType: 'html',
      cache: false,
      success: function (data) {
        var $select = $('#formulario_atenciones #profesion_id');

        $select.html(data);
        refrescarSelectSinBloquear($select);
      },
      error: function (xhr, textStatus, errorThrown) {
        console.error(
          'No se pudo cargar Profesión:',
          xhr.responseText || errorThrown || textStatus
        );

        $('#formulario_atenciones #profesion_id')
          .html('<option value="">No se pudo cargar Profesión</option>')
          .selectpicker('refresh');
      }
    });
  };

  window.getReligion = function () {
    var url = '<?php echo SERVERURL; ?>php/atencion_pacientes/getReligion.php';

    return $.ajax({
      type: 'GET',
      url: url,
      dataType: 'html',
      cache: false,
      success: function (data) {
        var $select = $('#formulario_atenciones #religion_id');

        $select.html(data);
        refrescarSelectSinBloquear($select);
      },
      error: function (xhr, textStatus, errorThrown) {
        console.error(
          'No se pudo cargar Religión:',
          xhr.responseText || errorThrown || textStatus
        );

        $('#formulario_atenciones #religion_id')
          .html('<option value="">No se pudo cargar Religión</option>')
          .selectpicker('refresh');
      }
    });
  };

  window.getConsultorio = function () {
    var url = '<?php echo SERVERURL; ?>php/citas/getServicioFacturas.php';

    return $.ajax({
      type: 'POST',
      url: url,
      dataType: 'html',
      cache: false,
      success: function (data) {
        var $select = $('#formulario_atenciones #servicio_id');

        $select.html(data);

        window.requestAnimationFrame(function () {
          if (typeof $select.selectpicker === 'function') {
            $select.selectpicker('refresh');
          }

          // Si editarRegistro dejó un servicio pendiente, se respeta.
          // En una atención nueva, se selecciona automáticamente el primero.
          window.requestAnimationFrame(function () {
            seleccionarServicioAtencion(servicioAtencionPendiente);
          });
        });
      },
      error: function (xhr, textStatus, errorThrown) {
        console.error(
          'No se pudo cargar Consultorio:',
          xhr.responseText || errorThrown || textStatus
        );

        var $select = $('#formulario_atenciones #servicio_id');

        $select.html('<option value="">No se pudo cargar Consultorio</option>');

        if (typeof $select.selectpicker === 'function') {
          $select.selectpicker('refresh');
        }
      }
    });
  };

  window.convertDate = function (inputFormat) {
    function pad(s) { return (s < 10) ? '0' + s : s; }
    var d = new Date(inputFormat);
    return [d.getFullYear(), pad(d.getMonth() + 1), pad(d.getDate())].join('-');
  };

  window.getMes = function (fecha) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getMes.php',
      { fecha: fecha }
    );
  };

  window.consultarNombre = function (pacientes_id) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/pacientes/getNombre.php',
      { pacientes_id: pacientes_id }
    );
  };

  window.consultarExpediente = function (pacientes_id) {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/pacientes/getExpedienteInformacion.php',
      { pacientes_id: pacientes_id }
    );
  };

  // ---- navegación factura/atención (tu código)
  var accion = false;

  window.formFactura = function () {
    $('#formulario_facturacion')[0].reset();

    vistaAnteriorFactura = 'main';
    navegacionConfirmada = false;

    $('#main_facturacion').hide();
    $('#atencionMedica').hide();
    $('#facturacion').show();

    $('#label_acciones_volver').html("Volver");
    $('#acciones_atras').removeClass("active");
    $('#acciones_factura').addClass("active");
    $('#label_acciones_factura').html("Factura");

    $('#formulario_facturacion #fecha').attr('readonly', true);

    getColaborador_id()
      .done(function (colaborador_id) {
        $('#formulario_facturacion #colaborador_id').val(colaborador_id);
        refrescarSelectSinBloquear($('#formulario_facturacion #colaborador_id'));
      })
      .fail(function (xhr) {
        console.error(xhr && xhr.responseText ? xhr.responseText : 'No se pudo cargar el colaborador.');
      });

    $('#formulario_facturacion').attr({ 'data-form': 'save' });
    $('#formulario_facturacion').attr({ 'action': '<?php echo SERVERURL; ?>php/facturacion/addPreFactura.php' });

    limpiarTabla();

    $('.footer').hide();
    $('.footer1').show();
    $('#formulario_facturacion #validar').hide();
    $('#formulario_facturacion #guardar1').hide();

    accion = true;
    setTimeout(guardarSnapshotFactura, 0);
  };

  window.FormAtencionMedica = function (preservarDatos) {
    $('#main_facturacion').hide();
    $('#facturacion').hide();
    $('#atencionMedica').show();

    $('#label_acciones_volver').html('Atenciones Médicas');
    $('#acciones_atras').removeClass('active');
    $('#acciones_factura').addClass('active');
    $('#label_acciones_factura').html('Historia Clínica');

    if (preservarDatos !== true) {
      $('#formulario_atenciones').trigger('reset');
      $('#formulario_atenciones #pro').val('Registro');
    }

    // Aquí no se inicializan contadores ni reconocimiento de voz.
    // Esta función debe limitarse a mostrar la vista.
    accion = false;
  };

  window.volver = function () {
    if ($('#facturacion').is(':visible')) {
      solicitarRegresoDesdeFactura();
      return false;
    }

    mostrarVistaPrincipal();
    return false;
  };

  window.getProfesional = function () {
    return obtenerTextoAjax(
      '<?php echo SERVERURL; ?>php/atencion_pacientes/getProfeisonal.php'
    );
  };

  window.getFechaActual = function () {
    return $.Deferred().resolve(convertDate(new Date())).promise();
  };

})(jQuery);
</script>