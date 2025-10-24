<script>
	function guardado(){
		alert('Cliente registrado correctamente');
		window.location ='../clientes.php?buscar=';
	}
</script>
<?php
// Redirigir al API mejorado
$_POST['accion'] = 'guardar';

// Headers para JSON
header('Content-Type: application/json');

// Incluir el API actualizado
include('ClienteControllerAPI.php');
?>

