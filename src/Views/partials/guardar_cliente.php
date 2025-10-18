<?php
include('../global/conexion.php'); // Asegúrate de que la ruta sea correcta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $con->real_escape_string($_POST['nombre_completo']);
    // Recoger otros campos...
    
    $sql = "INSERT INTO fs_clientes (...) VALUES (...)";
    $stmt = $con->prepare($sql);
    
    if ($stmt->execute()) {
        header("Location: ../scripts/clientes.php?exito=1");
    } else {
        header("Location: ../scripts/clientes.php?error=1");
    }
}

// Validar datos obligatorios
if (empty($_POST['nombre_completo']) || empty($_POST['telefono']) || empty($_POST['localidad'])) {
    die("Error: Nombre, teléfono y localidad son campos obligatorios.");
}

// Recoger datos del formulario
$nombre = $_POST['nombre_completo'];
$direccion = $_POST['direccion'] ?? ''; // Usar valor por defecto si no existe
$ciudad = "Bogotá"; // Fijar ciudad como Bogotá
$localidad = $_POST['localidad'];
$codigo_postal = $_POST['cod_postal'] ?? '';
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'];
$cc = $_POST['numero_identificacion'] ?? '';
$descuento = $_POST['descuento'] ?? 0;
$revendedora = $_POST['revendedora'] ?? 0;

// Validar localidad (solo permitir localidades de Bogotá)
$localidades_validas = [
    "Usaquén", "Chapinero", "Santa Fe", "San Cristóbal", "Usme", 
    "Tunjuelito", "Bosa", "Kennedy", "Fontibón", "Engativá", 
    "Suba", "Barrios Unidos", "Teusaquillo", "Los Mártires", 
    "Antonio Nariño", "Puente Aranda", "La Candelaria", 
    "Rafael Uribe Uribe", "Ciudad Bolívar", "Sumapaz"
];

if (!in_array($localidad, $localidades_validas)) {
    die("Error: Localidad no válida para Bogotá.");
}

// Preparar y ejecutar el INSERT
$stmt = $con->prepare("
    INSERT INTO fs_clientes (
        nombre_completo, direccion, ciudad, localidad, codigo_postal, 
        email, telefono, CC, descuento, revendedora
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssssii", 
    $nombre, $direccion, $ciudad, $localidad, $codigo_postal,
    $email, $telefono, $cc, $descuento, $revendedora
);

if ($stmt->execute()) {
    echo "Cliente guardado correctamente. ID: " . $stmt->insert_id;
    // Redirigir o mostrar mensaje de éxito
} else {
    echo "Error al guardar: " . $stmt->error;
}

$stmt->close();
$con->close();
?><!-- ////////// SECCION CLIENTE NUEVO REGISTRO ////////// -->
<section id="ingresar_cliente" style="display: none;">
    <div class="boton">
        <button id="boton_cliente">Cancelar</button>
    </div>
    
    <div id="form_cliente">
        <form action="scripts/guardar_cliente.php" method="POST" id="formulario-cliente" onsubmit="return validarFormularioCliente()">
            <!-- Grupo 1: Información Básica -->
            <div class="grupo-formulario">
                <label for="nombre_completo">Nombre y Apellido *</label>        
                <input type="text" id="nombre_completo" name="nombre_completo" maxlength="80" required
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{5,}" 
                       title="Mínimo 5 caracteres alfabéticos">
                <span class="pista">Mínimo 5 caracteres. Obligatorio.</span>

                <label for="c.c">C.C *</label>
                <input type="text" id="dni" name="dni" maxlength="11" required
                       pattern="[0-9]{7,11}" title="Entre 7 y 11 dígitos">
                <span class="pista">Solo números. Obligatorio.</span>
            </div>

            <!-- Grupo 2: Contacto -->
            <div class="grupo-formulario">
                <label for="telefono">Teléfono *</label>
                <input type="tel" id="telefono" name="telefono" required
                       pattern="[0-9]{7,15}" title="Número telefónico válido">
                <span class="pista">Ej: 3222346162. Obligatorio.</span>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" maxlength="150">
                <span class="pista">Ej: cliente@ejemplo.com</span>
            </div>

            <!-- Grupo 3: Ubicación -->
            <div class="grupo-formulario">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" maxlength="200">
                <span class="pista">Máximo 200 caracteres</span>

                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" maxlength="100">
                <span class="pista">Máximo 100 caracteres</span>
            </div>

            <!-- Grupo 4: Provincia y Código Postal -->
            <div class="grupo-formulario">
                <label for="Localidad">Provincia</label>        
                <select id="Localidad" name="Localidad">
                    <option value="">Seleccione Localidad</option>
                    <option value="Bogotá">Bogotá</option>
                            <option value="Bogotá">Bogotá</option>
                            <option value="Kennedy">Kennedy</option>
							<option value="Antonio Nariño">Antonio Nariño</option>
							<option value="Barrios Unidos">Barrios Unidos</option>
							<option value="Bosa">Bosa</option>
							<option value="Chapinero">Chapinero</option>
							<option value="Ciudad Bolivar">Ciudad Bolivar</option>
							<option value="Engativa">Engativa</option>
							<option value="Fontibón">Fontibón</option>
							<option value="La Candelaria">La Candelaria</option>
							<option value="Los Mártires">Los Mártires</option>
							<option value="Puente Aranda">Puente Aranda</option>
							<option value="Rafael Uribe Uribe">Rafael Uribe Uribe</option>
							<option value="Suba">Suba</option>
							<option value="Santa Fe">Santa Fe</option>
							<option value="San Cristóbal">San Cristóbal</option>
							<option value="Sumapaz">Sumapaz</option>
							<option value="Teusaquillo">Teusaquillo</option>
							<option value="Tunjuelito">Tunjuelito</option>
							<option value="Usaquén">Usaquén</option>
							<option value="Usme"> Usme</option>
							
                            <!-- Otras Localidades -->
                        </select>
                    </div>

                <label for="cod_postal">Código Postal</label>
                <input type="text" id="cod_postal" name="cod_postal" maxlength="8" 
                       pattern="[0-9]{4,8}">
                <span class="pista">Ej: 110111</span>
            </div>

            <!-- Grupo 5: Tipo de Cliente -->
            <div class="campo-formulario">
                        <label>Tipo de cliente</label>
                        <div class="opciones-radio">
                            <label>
                                <input type="radio" name="Cliente nuevo/Primera compra" value="0" checked> Cliente nuevo/Primera compra
                            </label>
                            <label>
                                <input type="radio" name="Cliente ya registrado" value="1"> Cliente ya registrado
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="campo-formulario" id="campo-descuento">
                    <label for="descuento">Descuento (%)</label>
                    <input type="number" id="descuento" name="descuento" min="0" max="50" value="0">
                </div>
                
                <div class="acciones-formulario">
                    <button type="submit" class="btn-lilipink btn-grande">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                    <button type="button" class="btn-cancelar" onclick="toggleFormulario()">
                        Cancelar
                    </button>
                </div>
            </form>
        </section>
    </div>

            <!-- Botón de Envío -->
            <div class="grupo-formulario">
                <input type="submit" value="Guardar Registro" class="btn-guardar">
            </div>
        </form>
    </div>
</section>

<script>
// Validación del formulario
function validarFormularioCliente() {
    // Validar DNI
    const dni = document.getElementById('dni').value;
    if (!/^\d{7,11}$/.test(dni)) {
        alert('El DNI debe contener entre 7 y 11 dígitos numéricos');
        return false;
    }

    // Validar teléfono
    const telefono = document.getElementById('telefono').value;
    if (!/^\d{7,15}$/.test(telefono)) {
        alert('El teléfono debe contener entre 7 y 15 dígitos');
        return false;
    }

    // Validar email si se ingresó
    const email = document.getElementById('email').value;
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Por favor ingrese un email válido');
        return false;
    }

    // Validación para revendedores
    if (document.querySelector('input[name="revendedora"]:checked').value === '1') {
        const descuento = parseInt(document.getElementById('descuento').value);
        if (isNaN(descuento) || descuento < 0 || descuento > 50) {
            alert('El descuento para revendedores debe ser entre 0% y 50%');
            return false;
        }
    }

    return true;
}

// Mostrar/ocultar campo de descuento
document.querySelectorAll('input[name="revendedora"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const campoDescuento = document.getElementById('campo