<script>
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#modal_movimientos").on('shown.bs.modal', function(){
        $(this).find('#formularioMovimientos #movimiento_categoria').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

$(document).ready(function() {
	funciones();

	$('#form_main #categoria_id').on('change', function(){
		listar_movimientos();
	});

	$('#form_main #fechai').on('change', function(){
		listar_movimientos();
	});

	$('#form_main #fechaf').on('change', function(){
		listar_movimientos();
	});
});

$('#form_main #registrar').on('click', function(e){
    e.preventDefault();
    agregarMovimientos();
    getCategoriaProductosMovimientos();
    getCategoriaProductos();
    getCategoriaOperacion();
    getProductos(1);
});

$('#formularioMovimientos #buscar_productos').on('click', function(e){
	e.preventDefault();
	listar_productos_buscar();
	 $('#modal_busqueda_productos_facturas').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
});

function funciones(){
    listar_movimientos();
  	getCategoriaProductosMovimientos();
  	getCategoriaProductos();
  	getCategoriaOperacion();
    getProductos(1);
}

function agregarMovimientos(){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3 || getUsuarioSistema() == 5){
		funciones();
		$('#formularioMovimientos').attr({ 'data-form': 'save' });
		$('#formularioMovimientos').attr({ 'action': '<?php echo SERVERURL; ?>php/movimientos/agregarMovimientos.php' });
		$('#modal_movimientos #pro').val("Proceso");
		$('#modal_movimientos').show();

		 $('#modal_movimientos').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		});
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
	}
}

var listar_movimientos = function(){
	var categoria;

	if ($('#form_main #categoria_id').val() == "" || $('#form_main #categoria_id').val() == null){
	  categoria = 1;
	}else{
	  categoria = $('#form_main #categoria_id').val();
	}

	var fechai = $("#form_main #fechai").val();
	var fechaf = $("#form_main #fechaf").val();

	var table_movimientos  = $("#dataTablaMovimientos").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/movimientos/getMovimientosTabla.php",
			"data":{
				"categoria":categoria,
				"fechai":fechai,
				"fechaf":fechaf
			}
		},
		"columns":[
			{"data":"fecha_registro"},
			{"data":"producto"},
			{"data":"concentracion"},
			{"data":"medida"},
			{"data":"entrada"},
			{"data":"salida"},
			{"data":"saldo"},
      {"data":"comentario"}
		],
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español//esta se encuenta en el archivo main.js
	});
	table_movimientos.search('').draw();
	$('#buscar').focus();
}

function getCategoriaProductos(){
    var url = '<?php echo SERVERURL; ?>php/movimientos/getCategoriaProducto.php';

    $.ajax({
      type: "POST",
      url: url,
      success: function(data){
        $('#form_main #categoria_id').html("");
        $('#form_main #categoria_id').html(data);
        $('#form_main #categoria_id').selectpicker('refresh');
      }
    });
}

function getCategoriaProductosMovimientos(){
    var url = '<?php echo SERVERURL; ?>php/movimientos/getCategoriaProducto.php';

	   $.ajax({
      type: "POST",
      url: url,
      success: function(data){
        $('#formularioMovimientos #movimiento_categoria').html("");
        $('#formularioMovimientos #movimiento_categoria').html(data);
        $('#formularioMovimientos #movimiento_categoria').selectpicker('refresh');
      }
     });
}

function getCategoriaOperacion(){
    var url = '<?php echo SERVERURL; ?>php/movimientos/getOperacion.php';

    $.ajax({
        type: "POST",
        url: url,
        success: function(data){
          $('#formularioMovimientos #movimiento_operacion').html("");
          $('#formularioMovimientos #movimiento_operacion').html(data);
          $('#formularioMovimientos #movimiento_operacion').selectpicker('refresh');
    	}
    });
}

$(document).ready(function() {
	$('#formularioMovimientos #movimiento_categoria').on('change', function(){
		var categoria_producto_id;

		if ($('#formularioMovimientos #movimiento_categoria').val() == "" || $('#formularioMovimientos #movimiento_categoria').val() == null){
		  categoria_producto_id = 1;
		}else{
		  categoria_producto_id = $('#formularioMovimientos #movimiento_categoria').val();
		}

		getProductos(categoria_producto_id);
	  return false;
    });
});


function getProductos(categoria_producto_id){
    var url = '<?php echo SERVERURL; ?>php/movimientos/getProductos.php';

  	$.ajax({
        type: "POST",
        url: url,
    		data:'categoria_producto_id='+categoria_producto_id,
        success: function(data){
          $('#formularioMovimientos #movimiento_producto').html("");
          $('#formularioMovimientos #movimiento_producto').html(data);
          $('#formularioMovimientos #movimiento_producto').selectpicker('refresh');
    		}
    });
}

$('#form_main #actualizar').on('click', function(e){
	e.preventDefault();
	var categoria = $('#formularioMovimientos #movimiento_categoria').val();
	listar_movimientos();
	$('#formularioMovimientos #movimiento_categoria').val(categoria);
});

$('#form_main #reporte').on('click', function(e){
	e.preventDefault();
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2 || getUsuarioSistema() == 3){
		reporteEXCEL();
	}else{
		swal({
			title: "Acceso Denegado",
			text: "No tiene permisos para ejecutar esta acción",
			icon: "error",
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});
	}
});

function reporteEXCEL(){
	var fecha = $('#form_main #fechai').val();
	var fechaf = $('#form_main #fechaf').val();
	var categoria;

	if ($('#form_main #categoria_id').val() == "" || $('#form_main #categoria_id').val() == null){
	  categoria = 1;
	}else{
	  categoria = $('#form_main #categoria_id').val();
	}

	var url = '<?php echo SERVERURL; ?>php/movimientos/reporte.php?fecha='+fecha+'&fechaf='+fechaf+'&categoria='+categoria;
    window.open(url);
}

var listar_productos_buscar = function(){
	var categoria_producto_id;

	if ($('#formularioMovimientos #movimiento_categoria').val() == "" || $('#formularioMovimientos #movimiento_categoria').val() == null){
	  categoria_producto_id = 1;
	}else{
	  categoria_producto_id = $('#formularioMovimientos #movimiento_categoria').val();
	}

	var table_productos_buscar = $("#dataTableProductosFacturas").DataTable({
		"destroy":true,
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/movimientos/getProductosTabla.php",
			"data":{
				"categoria":categoria_producto_id
			}
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"producto"},
			{"data":"descripcion"},
			{"data":"concentracion"},
			{"data":"medida"},
			{"data":"cantidad"},
			{"data":"precio_venta"}
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,
	});
	table_productos_buscar.search('').draw();
	$('#buscar').focus();

	view_productos_busqueda_dataTable("#dataTableProductosFacturas tbody", table_productos_buscar);
}

var view_productos_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formularioMovimientos #movimiento_producto').val(data.productos_id);
		$('#modal_busqueda_productos_facturas').modal('hide');
	});
}

//INICIO TEXTAREA CON AUDIO
$('#formularioMovimientos #comentario').keyup(function() {
	    var max_chars = 1000;
        var chars = $(this).val().length;
        var diff = max_chars - chars;

		$('#formularioMovimientos #charNum_comentario').html(diff + ' Caracteres');

		if(diff == 0){
			return false;
		}
});

function caracteresComentarioMovimientos(){
	var max_chars = 1000;
	var chars = $('#formularioMovimientos #comentario').val().length;
	var diff = max_chars - chars;

	$('#formularioMovimientos #charNum_comentario').html(diff + ' Caracteres');

	if(diff == 0){
		return false;
	}
}

$(document).ready(function() {
	//INICIO FORMULARIO ATENCIONES EXPEDIENTE CLINICO
	$('#formularioMovimientos #search_comentario_movimientos_stop  ').hide();

    var recognition = new webkitSpeechRecognition();
    recognition.continuous = true;
    recognition.lang = "es";

    $('#formularioMovimientos #search_comentario_movimientos_start').on('click',function(event){
		$('#formularioMovimientos #search_comentario_movimientos_start').hide();
		$('#formularioMovimientos #search_comentario_movimientos_stop').show();
		recognition.start();

		recognition.onresult = function (event) {
			finalResult = '';
			var valor_anterior  = $('#formularioMovimientos #comentario').val();
			for (var i = event.resultIndex; i < event.results.length; ++i) {
				if (event.results[i].isFinal) {
					finalResult = event.results[i][0].transcript;
					if(valor_anterior != ""){
						$('#formularioMovimientos #comentario').val(valor_anterior + ' ' + finalResult);
						caracteresComentarioMovimientos();
					}else{
						$('#formularioMovimientos #comentario').val(finalResult);
						caracteresComentarioMovimientos();
					}
				}
			}
		};
		return false;
    });

	  $('#formularioMovimientos #search_comentario_movimientos_stop').on("click", function(event){
		$('#formularioMovimientos #search_comentario_movimientos_start').show();
		$('#formularioMovimientos #search_comentario_movimientos_stop').hide();
		recognition.stop();
	});
});
//FIN TEXTAREA CON AUDIO
</script>
