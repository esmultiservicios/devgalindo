<script>
$(document).ready(pagination(1));
 $(function(){
	  listar_colaboradores_buscar();
	  $('#nuevo-registro').on('click',function(e){
		e.preventDefault();
		if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){
	      clean();
		  $('#formulario')[0].reset();
     	  $('#formulario #pro').val('Registro');
		  $('#grupo_atas_localidades').hide();
		  $('#edi').hide();
		  $('#reg_usuarios').show();
		  $('#formulario').attr({ 'data-form': 'save' }); 
		  $('#formulario').attr({ 'action': '<?php echo SERVERURL; ?>php/users/agregar.php' });
		  
		  $('#registrar').modal({
			show:true,
			keyboard: false,
			backdrop:'static'
		  });
		 }else{
			swal({
				title: "Acceso Denegado", 
				text: "No tiene permisos para ejecutar esta acción",
				icon: "error", 
				dangerMode: true
			});			 
         }
	   });
	
	   $('#main_form #bs-regis').on('keyup',function(){
		  pagination(1);
       });
	   
	   $('#main_form #status').on('change',function(){
		  pagination(1);
       });	   
	   clean();
});

function clean(){
	getColaborador();
	getStatus();
	getEmpresa();
	getTipo();
	getEstatus();
	listar_colaboradores_buscar();
}
/*INICIO DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/
$(document).ready(function(){
    $("#registrar").on('shown.bs.modal', function(){
        $(this).find('#formulario #username').focus();
    });
});
/*FIN DE FUNCIONES PARA ESTABLECER EL FOCUS PARA LAS VENTANAS MODALES*/

function modificarContra(id){
if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){	
	$('#modificar_contra #dato').val(id);
	swal({
		title: "¿Esta seguro?",
		text: "¿Desea resetear la contraseña al usuario: " + consultarNombre(id) + "?",
		icon: "warning",
		buttons: {
			cancel: {
				text: "Cancelar",
				visible: true
			},
			confirm: {
				text: "¡Sí, modificar la contraseña!",
			}
		},
		closeOnClickOutside: false
	}).then((willConfirm) => {
		if (willConfirm === true) {
			resetearContra(id);
		}
	});
}else{
	swal({
		title: "Acceso Denegado", 
		text: "No tiene permisos para ejecutar esta acción",
		icon: "error", 
		dangerMode: true
	});					 
}	
}

function resetearContra(id){	
	var url = '<?php echo SERVERURL; ?>php/users/resetear.php';

	$.ajax({
		type:'POST',
		url:url,
		data:'id='+id,
		success: function(registro){		
			if(registro == 1){
			   pagination(1);
			   $('#main_form #bs-regis').val("");
				swal({
					title: "Success", 
					text: "Contraseña cambiada correctamente",
					icon: "success",
					timer: 3000, //timeOut for auto-close					
				});		
			    return false;					
			}else{
				pagination(1);
				$('#bs-regis').val("");
				swal({
					title: "Error", 
					text: "Error al resetear la contraseña",
					icon: "error", 
					dangerMode: true
				});				   
			   return false;				
			}						
  		}
	}); 
	return false;
}

function getColaborador(){
	var url = '<?php echo SERVERURL; ?>php/users/getMedico.php';
	
	$.ajax({
		type:'POST',
		url:url,		
		success: function(data){
			$('#formulario #colaborador').html("");
			$('#formulario #colaborador').html(data);
			$('#formulario #colaborador').selectpicker('refresh');
			
			$('#formulario_editar #colaborador1').html("");
			$('#formulario_editar #colaborador1').html(data);
			$('#formulario_editar #colaborador1').selectpicker('refresh');			
		}
	});
	return false;	
}

function getStatus(){
	var url = '<?php echo SERVERURL; ?>php/users/getStatus.php';
	
	$.ajax({
		type:'POST',
		url:url,		
		success: function(data){
			$('#formulario #estatus').html("");
			$('#formulario #estatus').html(data);
			$('#formulario #estatus').selectpicker('refresh');
			
			$('#formulario_editar #estatus1').html("");
			$('#formulario_editar #estatus1').html(data);
			$('#formulario_editar #estatus1').selectpicker('refresh');			
		}
	});
	return false;	
}

function getEmpresa(){
	var url = '<?php echo SERVERURL; ?>php/selects/empresa.php';
	
	$.ajax({
		type:'POST',
		url:url,		
		success: function(data){
			$('#formulario #empresa').html("");
			$('#formulario #empresa').html(data);	
			$('#formulario #empresa').selectpicker('refresh');
			
			$('#formulario_editar #empresa1').html("");
			$('#formulario_editar #empresa1').html(data);
			$('#formulario_editar #empresa1').selectpicker('refresh');			
		}
	});
	return false;	
}

function pagination(partida){
	var dato = $('#main_form #bs-regis').val();
	var status_valor = "";
	
	if($('#main_form #status').val() == "" || $('#main_form #status').val() == null){
		status_valor = 1;
	}else{
		status_valor = $('#main_form #status').val();
	}
	
	var url = '<?php echo SERVERURL; ?>php/users/paginar.php';
	
	$.ajax({
		type:'POST',
		url:url,
		data:'partida='+partida+'&dato='+dato+'&status_valor='+status_valor,
		success:function(data){
			var array = eval(data);
			$('#agrega-registros').html(array[0]);
			$('#pagination').html(array[1]);
		}
	});
	return false;
}

function modal_eliminar(colaborador_id, id){
	if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){	
		$('#dato').val(id);
		swal({
			title: "¿Esta seguro?",
			text: "¿Desea eliminar el usuario " + consultarNombre(id) + "",
			icon: "warning",
			buttons: {
				cancel: {
					text: "Cancelar",
					visible: true
				},
				confirm: {
					text: "¡Sí, Eliminar el usuario!",
				}
			},
			closeOnClickOutside: false
		}).then((willConfirm) => {
			if (willConfirm === true) {
				eliminarRegistro(colaborador_id);
			}
		});
	}else{
		swal({
			title: "Acceso Denegado", 
			text: "No tiene permisos para ejecutar esta acción",
			icon: "error", 
			dangerMode: true
		});				 
	}		
}

function eliminarRegistro(colaborador_id){
	var url = '<?php echo SERVERURL; ?>php/users/eliminar.php';

	$.ajax({
		type:'POST',
		url:url,
		data:'colaborador_id='+colaborador_id,
		success: function(registro){
		   if(registro == 1){
				pagination(1);
				$('#bs-regis').val("");
				swal({
					title: "Success", 
					text: "Registro eliminado correctamente",
					icon: "success",
					timer: 3000, //timeOut for auto-close
				});			 			
				return false;			   
		   }else if(registro == 2){
				swal({
					title: "Error", 
					text: "Error al eliminar este usuario",
					icon: "error", 
					dangerMode: true
				});	
			 return false;			   
		   }else if(registro == 3){	
				swal({
					title: "Error", 
					text: "No se puede realizar esta operación con su propio usuario",
					icon: "error", 
					dangerMode: true
				});		
			 return false;			   
		   }else if(registro == 4){	
				swal({
					title: "Error", 
					text: "Este registro cuenta con información almacenada no se puede eliminar",
					icon: "error", 
					dangerMode: true
				});		
			 return false;			   
		   }else{
				$('#bs-regis').val("");	
				swal({
					title: "Error", 
					text: "No se puede eliminar el registro",
					icon: "error", 
					dangerMode: true
				}); 			 
			 return false;			   
		   }
		}
	});
	return false;
}

function editarRegistro(id){	
if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){	
	$('#formulario_editar')[0].reset();
	var url = '<?php echo SERVERURL; ?>php/users/editar.php';
		$.ajax({
		type:'POST',
		url:url,
		data:'id='+id,
		success: function(valores){			    
				var datos = eval(valores);
				$('#reg').hide();
				$('#editar_usuarios').show();
				listar_colaboradores_buscar();
				$('#formulario_editar #pro').val('Edicion');
				$('#formulario_editar #id-registro1').val(id);
			    $('#formulario_editar #colaborador1').val(datos[0]);
				$('#formulario_editar #colaborador1').selectpicker('refresh');				
				$('#formulario_editar #email1').val(datos[3]);
				$('#formulario_editar #empresa1').val(datos[4]);
				$('#formulario_editar #empresa1').selectpicker('refresh');			
			    $('#formulario_editar #tipo1').val(datos[5]);
				$('#formulario_editar #tipo1').selectpicker('refresh');				
				$('#formulario_editar #estatus1').val(datos[6]);
				$('#formulario_editar #estatus1').selectpicker('refresh');
				
				$('#formulario_editar').attr({ 'data-form': 'save' }); 
				$('#formulario_editar').attr({ 'action': '<?php echo SERVERURL; ?>php/users/agregar_edicion.php' });
		  
		        $("#formulario_editar #colaborador1").attr('disabled', true);			
	            $('#registrar_editar').modal({
					show:true,
					keyboard: false,
					backdrop:'static'
	            });
			return false;
		}
	});
	return false;
}else{
	swal({
		title: "Acceso Denegado", 
		text: "No tiene permisos para ejecutar esta acción",
		icon: "error", 
		dangerMode: true
	});					 
}	
}

function reporteEXCEL(){
   if (getUsuarioSistema() == 1 || getUsuarioSistema() == 2){	
       var dato = $('#bs-regis').val();
	   var status_valor = "";
	
	   if($('#main_form #status').val() == "" || $('#main_form #status').val() == null){
		  status_valor = 1;
	   }else{
		  status_valor = $('#main_form #status').val();
	   }
	   
	   var url = '<?php echo SERVERURL; ?>php/users/buscar_usuarios_excel.php?dato='+dato+'&status_valor='+status_valor;
       window.open(url);
   }else{
	swal({
		title: "Acceso Denegado", 
		text: "No tiene permisos para ejecutar esta acción",
		icon: "error", 
		dangerMode: true
	});					 
   }	
}

function getTipo(){
    var url = '<?php echo SERVERURL; ?>php/users/getTipo.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){		
		    $('#formulario #tipo').html("");
			$('#formulario #tipo').html(data);
			$('#formulario #tipo').selectpicker('refresh');
			
		    $('#formulario_editar #tipo1').html("");			
			$('#formulario_editar #tipo1').html(data);		
			$('#formulario_editar #tipo1').selectpicker('refresh');
		}			
     });		
}

function getEstatus(){
    var url = '<?php echo SERVERURL; ?>php/users/getStatus.php';		
		
	$.ajax({
        type: "POST",
        url: url,
	    async: true,
        success: function(data){		
		    $('#main_form #status').html("");
			$('#main_form #status').html(data);
			$('#main_form #status').selectpicker('refresh');			
		}			
     });		
}

function consultarNombre(id){	
    var url = '<?php echo SERVERURL; ?>php/users/getNombre.php';
	var resp;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'id='+id,
		async: false,
		success:function(data){	
          resp = data;			  		  		  			  
		}
	});
	return resp;		
}


var tiempo;
function ini() {
  tiempo = setTimeout('location="<?php echo SERVERURL; ?>php/signin_out/signinout.php"',14400000); // 4 horas
}

function parar() {
  clearTimeout(tiempo);
  tiempo = setTimeout('location="<?php echo SERVERURL; ?>php/signin_out/signinout.php"',14400000); // 4 horas
}

$('#main_form #reporte').on('click', function(e){
    e.preventDefault();
    reporteEXCEL();
});

var listar_colaboradores_buscar = function(){
	var table_colaboradores_buscar = $("#dataTableColaboradores").DataTable({		
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/users/getColaboradoresTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"colaborador"},
			{"data":"identidad"},
			{"data":"puesto"}			
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,	
	});	 
	table_colaboradores_buscar.search('').draw();
	$('#buscar').focus();
	
	view_colaboradores_busqueda_dataTable("#dataTableColaboradores tbody", table_colaboradores_buscar);
}

var view_colaboradores_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");		
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario #colaborador').val(data.colaborador_id);
		$('#modal_busqueda_colaboradores').modal('hide');
	});
}

$('#formulario #buscar_colaboradores').on('click', function(e){
	e.preventDefault();
	listar_colaboradores_buscar();
	$('#modal_busqueda_colaboradores').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});		 
});

var listar_empresas_buscar = function(){
	var table_empresas_buscar = $("#dataTableEmpresa").DataTable({		
		"destroy":true,	
		"ajax":{
			"method":"POST",
			"url":"<?php echo SERVERURL; ?>php/users/getEmpresaTabla.php"
		},
		"columns":[
			{"defaultContent":"<button class='view btn btn-primary'><span class='fas fa-copy'></span></button>"},
			{"data":"nombre"},
			{"data":"rtn"},
			{"data":"ubicacion"}			
		],
		"pageLength" : 5,
        "lengthMenu": lengthMenu,
		"stateSave": true,
		"bDestroy": true,
		"language": idioma_español,	
	});	 
	table_empresas_buscar.search('').draw();
	$('#buscar').focus();
	
	view_empresa_busqueda_dataTable("#dataTableEmpresa tbody", table_empresas_buscar);
}

var view_empresa_busqueda_dataTable = function(tbody, table){
	$(tbody).off("click", "button.view");		
	$(tbody).on("click", "button.view", function(e){
		e.preventDefault();
		var data = table.row( $(this).parents("tr") ).data();
		$('#formulario #empresa').val(data.empresa_id);
		$('#modal_busqueda_empresa').modal('hide');
	});
}

$('#formulario #buscar_empresa').on('click', function(e){
	e.preventDefault();
	listar_empresas_buscar();
	$('#modal_busqueda_empresa').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});		 
});

$(document).ready(function(){
// Prepare the preview for profile picture
    $("#wizard-picture").change(function(){
        readURL(this);
    });
});
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#wizardPicturePreview').attr('src', e.target.result).fadeIn('slow');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>