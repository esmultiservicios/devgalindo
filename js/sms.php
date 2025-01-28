<script>
//INICIO MODULO AGENDA
$(document).ready(function(){
    $("#enviar_sms").on('shown.bs.modal', function(){
        $(this).find('#formulario_enviar_sms #text').focus();
    });
});

$(document).ready(function() {
  $('#form_agenda_main #send_sms').on('click', function(e){	  
      //mensajeMantenimiento("En Desarrollo","Estamos trabajando para que su experiencia sea más placentera, pronto estará disponible el envió de <b>SMS</b> de forma masiva");
	  e.preventDefault();
	  if (getUsuarioSistema() == 1 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4 || getUsuarioSistema() == 18){
         if($('#form_agenda_main #fecha').val() == $('#form_agenda_main #fechaf').val()){
		     if(consultarFecha($('#form_agenda_main #fecha').val()) == 1){	
				swal({
					title: "¿Estás seguro?",
					content: {
						element: "span",
						attributes: {
							innerHTML: "¿Desea enviar los <b>SMS</b> de forma masiva?"
						}
					},
					icon: "warning",
					buttons: {
						cancel: {
							text: "Cancelar",
							visible: true
						},
						confirm: {
							text: "¡Sí, deseo enviar!",
						}
					},
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				}).then((willConfirm) => {
					if (willConfirm) {
						sendMultipleSMSUnDiaAntes($('#form_agenda_main #fecha').val(), $('#form_agenda_main #servicio').val());
					}
				});				
		     }else if(consultarFecha($('#form_agenda_main #fecha').val()) == 5){
				mensajeConfirmacionCicoDias("Confirmación","");
				
				swal({
					title: "¿Estás seguro?",
					content: {
						element: "span",
						attributes: {
							innerHTML: "¿Desea enviar los SMS de forma masiva?",
						}
					},
					icon: "warning",
					buttons: {
						cancel: {
							text: "Cancelar",
							visible: true
						},
						confirm: {
							text: "¡Sí, deseo enviar!",
						}
					},
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				}).then((willConfirm) => {
					if (willConfirm) {
						sendMultipleSMSDiasDespues($('#form_agenda_main #fecha').val(), $('#form_agenda_main #servicio').val());
					}
				});			 
		     }else if(consultarFecha($('#form_agenda_main #fecha').val()) == 4){
				swal({
					title: "Error", 
					text: "No se puede enviar SMS a los usuarios el dia actual de la consulta",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});  
		     }else{
				swal({
					title: "Error", 
					text: "No se puede ejecutar esta acción, por favor verifique los datos e intentelo de nuevo mas tarde",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
				});					
		     }
	    }else{
			swal({
				title: "Error", 
				text: "Lo sentimos las fechas seleccionadas no son correctas, por favor corregir",
				icon: "error", 
				dangerMode: true,
				closeOnEsc: false, // Desactiva el cierre con la tecla Esc
				closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
			});			
	    }		 
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
});

function consultarFecha(fecha){
    var url = '<?php echo SERVERURL; ?>php/agenda_pacientes/getFecha.php';
	var fecha;
	$.ajax({
	    type:'POST',
		url:url,
		data:'fecha='+fecha,
		async: false,
		success:function(data){	
          fecha = data;			  		  		  			  
		}
	});
	return fecha;	
}

$('#formulario_enviar_sms #sms_send').on('click', function(e){
	if($('#formulario_enviar_sms #text').val() != ""){
	   	e.preventDefault();
        sendSMS();	
	}else{
		swal({
			title: "Error", 
			text: "Lo sentimos el mensaje no puede quedar en blanco",
			icon: "error", 
			dangerMode: true,
			closeOnEsc: false, // Desactiva el cierre con la tecla Esc
			closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera
		});	
	}
});

$('#formulario_enviar_sms #sms_clean').on('click', function(e){
	e.preventDefault();
    clean();
});

function getTelefono(pacientes_id){
    var url = '<?php echo SERVERURL; ?>php/agenda_pacientes/getTelefono.php';
	var telefono;
	$.ajax({
	    type:'POST',
		url:url,
		data:'pacientes_id='+pacientes_id,
		async: false,
		success:function(data){	
          telefono = data;			  		  		  			  
		}
	});
	return telefono;	
}

function sendOneSMS(pacientes_id, agenda_id){	   
    if (getUsuarioSistema() == 1 || getUsuarioSistema() == 3 || getUsuarioSistema() == 4 || getUsuarioSistema() == 18){
	     $('#formulario_enviar_sms #to').val(getTelefono(pacientes_id));
         $('#formulario_enviar_sms #pacientes_id').val(pacientes_id);
         $('#formulario_enviar_sms #agenda_id').val(agenda_id);
   
         $('#enviar_sms').modal({
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

$('#formulario_enviar_sms #text').keyup(function() {
	    var max_chars = 160;
        var chars = $(this).val().length;
        var diff = max_chars - chars;
		
		$('#charNum').html(diff + ' Caracteres'); 
		
		if(diff == 0){
			return false;
		}
});

//MENSAJE ENVIADO DE FORMA AUTOMATICA SEGUN LOS VALORES DE LA CITA DEL USUARIO
//FUNCION QUE PERMITE REALIZAR EL ENVIO DE SMS A LOS USUARIOS DE UN DIA ANTES DE LA FECHA CONSULTADA
function sendMultipleSMSUnDiaAntes(fecha, servicio){
    var url = '<?php echo SERVERURL; ?>php/sms/sendMultipleSMSUnDiaAntes.php';
	var resp;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'fecha='+fecha+'&servicio='+servicio,
		beforeSend: function(){
           $('#myPleaseWait').modal('show');
        },
		success:function(data){	
          if(data == 1){	
			swal({
				title: "Success", 
				text: "Mensaje enviado correctamente",
				icon: "success", 
				timer: 3000, //timeOut for auto-close
				closeOnEsc: false, // Desactiva el cierre con la tecla Esc
				closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera				
			});				 
		  }else if(data == 2){
				swal({
					title: "Error", 
					text: "Verifique su conexión a Internet",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});
		  }else if(data == 3){
				swal({
					title: "Error", 
					text: "No existen SMS que enviar, por favor seleccione un Servicio o verifique la información",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});  
		  }else if(data == 4){
				swal({
					title: "Error", 
					text: "Lo sentimos ya había enviado los SMS para esta fecha",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				}); 
		  }else if(data == 5){
				swal({
					title: "Error", 
					text: "Lo sentimos no hay suficiente balance para enviar los SMS",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});  
		  }else{
				swal({
					title: "Error", 
					text: "Lo sentimos no se puede procesar su solicitud, por favor intentelo de nuevo más tarde",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});	
		  }  		  		  			  
		},complete:function(){
            $('#myPleaseWait').modal('hide');			
		}
	});
	return resp;	
}


//FUNCION QUE PERMITE REALIZAR EL ENVIO DE SMS A LOS USUARIOS CONSULTADOS DE MAS DE UN DIA CALENDARIO
function sendMultipleSMSDiasDespues(fecha, servicio){
    var url = '<?php echo SERVERURL; ?>php/sms/sendMultipleSMSDiasDespues.php';
	var resp;
		
	$.ajax({
	    type:'POST',
		url:url,
		data:'fecha='+fecha+'&servicio='+servicio,
		beforeSend: function(){
           $('#myPleaseWait').modal('show');
        },
		success:function(data){	
          if(data == 1){	
				swal({
					title: "Success", 
					text: "Mensaje enviado correctamente",
					icon: "success",
					timer: 3000, //timeOut for auto-close
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera						
				});				 
		  }else if(data == 2){
				swal({
					title: "Error", 
					text: "Verifique su conexión a Internet",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});  
		  }else if(data == 3){
				swal({
					title: "Error", 
					text: "No existen SMS que enviar, por favor seleccione un Servicio o verifique la información",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});  
		  }else if(data == 4){
				swal({
					title: "Error", 
					text: "Lo sentimos ya había enviado los SMS para esta fecha",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				}); 
		  }else if(data == 5){
				swal({
					title: "Error", 
					text: "Lo sentimos no hay suficiente balance para enviar los SMS",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});  
		  }else{
				swal({
					title: "Error", 
					text: "Lo sentimos no se puede procesar su solicitud, por favor intentelo de nuevo más tarde",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});
		  }	 	  		  		  			  
		},complete:function(){
            $('#myPleaseWait').modal('hide');			
		}
	});
	return resp;	
}

//MENSAJE ENVIADO SEGUN FORMULARIO DE ENVIO DE SMS
function sendSMS(){
    var url = '<?php echo SERVERURL; ?>php/sms/sendSMS.php';
 	
	$.ajax({
	    type:'POST',
		url:url,
		data:$('#formulario_enviar_sms').serialize(),
		success:function(data){	
          if(data == 1){
				swal({
					title: "Success", 
					text: "Mensaje enviado correctamente",
					icon: "success",
					timer: 3000, //timeOut for auto-close
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera						
				});	  
		  }else if(data == 2){
				swal({
					title: "Error", 
					text: "Verifique su conexión a Internet",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});			 
		  }else if(data == 3){
				swal({
					title: "Error", 
					text: "Lo sentimos ya había enviado este SMS para esta registro",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});;			 
		  }else if(data == 4){
				swal({
					title: "Error", 
					text: "Lo sentimos no hay suficiente balance para enviar los SMS",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});			 
		  }else{
				swal({
					title: "Error", 
					text: "El Mensaje no se pudo enviar, por favor verifique la información",
					icon: "error", 
					dangerMode: true,
					closeOnEsc: false, // Desactiva el cierre con la tecla Esc
					closeOnClickOutside: false // Desactiva el cierre al hacer clic fuera	
				});			  
		  }  		  		  			  
		}
	});	
}

function clean(){
	$('#formulario_enviar_sms #text').val('');
	$('#formulario_enviar_sms #text').focus();
}

//VENTANAS EMERGENTES
function mensajeMantenimiento(titulo,mensaje){
	imagen = "<img src='<?php echo SERVERURL; ?>img/construccion.png' width='100%' height='50%'>";
	
	$('#mensaje').modal({
		show:true,
		keyboard: false,
		backdrop:'static'
	});
	$('#mensaje #mensaje_mensaje').html("<span class='fas fa-toolbox'> " + titulo + "</span><br/><hr><center>"  + imagen 
	    + "</center><br/> " + mensaje);
	$('#mensaje #bad').hide();
	$('#mensaje #okay').show();	
}
</script>