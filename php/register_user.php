<?php
session_start();

require '../php/config.php'; // Asegúrate de que la ruta sea correcta

$mensaje = ""; // Variable para mostrar mensajes

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y sanear los datos enviados utilizando la conexión $mysqli
    $cedula          = $mysqli->real_escape_string($_POST['cedula']);
    $nombre          = $mysqli->real_escape_string($_POST['nombre']);
    $apellidos       = $mysqli->real_escape_string($_POST['apellidos']);
    $correo          = isset($_POST['correo']) ? $mysqli->real_escape_string($_POST['correo']) : "";
    $rol             = $mysqli->real_escape_string($_POST['rol']);
    // Los campos de empresa
    $empresa         = $mysqli->real_escape_string($_POST['empresa']);
    $sub_empresa     = isset($_POST['sub_empresa']) ? $mysqli->real_escape_string($_POST['sub_empresa']) : "";
    $sub_sub_empresa = isset($_POST['sub_sub_empresa']) ? $mysqli->real_escape_string($_POST['sub_sub_empresa']) : "";
    $cargo           = $mysqli->real_escape_string($_POST['cargo']);
    // Nuevo campo: número de celular (opcional)
    $celular         = isset($_POST['celular']) ? $mysqli->real_escape_string($_POST['celular']) : "";
    
    // La contraseña se establece igual a la cédula y se cifra
    $hashed_password = password_hash($cedula, PASSWORD_DEFAULT);
    
    // Inserta los datos; se agregan los campos opcionales sub_empresa y sub_sub_empresa
    $query = "INSERT INTO users (cedula, nombre_completo, apellidos, correo, password, rol, empresa, cargo, celular) 
              VALUES ('$cedula', '$nombre', '$apellidos', '$correo', '$hashed_password', '$rol', '$empresa', '$cargo', '$celular')";
    
    if ($mysqli->query($query)) {
        $mensaje = "Usuario creado exitosamente.";
    } else {
        $mensaje = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Usuario</title>
  <!-- Fuente de Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Enlace al CSS -->
  <link rel="stylesheet" href="../css/registro.css">
  <style>
    /* Ejemplo de estilos para los selects en cascada */
    .form-group select {
      width: 100%;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <!-- Botón fijo para volver al dashboard -->
  <a href="dashboard_admin.php" class="back-button">Volver al Dashboard</a>

  <div class="registro-container">
    <div class="card">
      <header>
        <h1>Registrar Usuario</h1>
      </header>
      <?php if(!empty($mensaje)): ?>
        <p class="mensaje"><?php echo $mensaje; ?></p>
      <?php endif; ?>
      <form action="register_user.php" method="POST">
        <div class="form-group">
          <label for="cedula">Cédula:</label>
          <input type="text" name="cedula" id="cedula" placeholder="Ingresa tu cédula" required>
        </div>
        <div class="form-group">
          <label for="nombre">Nombre Completo:</label>
          <input type="text" name="nombre" id="nombre" placeholder="Ingresa tu nombre" required>
        </div>
        <div class="form-group">
          <label for="apellidos">Apellidos:</label>
          <input type="text" name="apellidos" id="apellidos" placeholder="Ingresa tus apellidos" required>
        </div>
        <div class="form-group">
          <label for="correo">Correo (opcional):</label>
          <input type="email" name="correo" id="correo" placeholder="correo@ejemplo.com">
        </div>
        <div class="form-group">
          <label for="rol">Rol:</label>
          <select name="rol" id="rol" required>
            <option value="" disabled selected>Selecciona un rol</option>
            <option value="estudiante">Estudiante</option>
            <option value="profesor">Profesor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <!-- Selección de la Empresa con select cascada -->
        <div class="form-group">
          <label for="empresa">Empresa a la que pertenece:</label>
          <select name="empresa" id="empresa" required>
            <option value="" disabled selected>Selecciona una empresa</option>
            <option value="Ferbienes">Inversiones Ferbienes</option>
            <option value="Agrosigo">Comercializadora Agrosigo</option>
            <option value="Tribilin">Inversiones Tribilin</option>
            <option value="Agrosigo2">Agrosigo</option>
          </select>
        </div>
        <!-- Los siguientes selects son opcionales; por ello, se elimina el atributo "required" -->
        <div class="form-group">
          <label for="sub_empresa" style="display:none;">Seleccione la Sub-Opción (opcional):</label>
          <select name="sub_empresa" id="sub_empresa" style="display:none;">
            <!-- Se rellenará dinámicamente -->
          </select>
        </div>
        <div class="form-group">
          <label for="sub_sub_empresa" style="display:none;">Seleccione la Sub-Sub-Opción (opcional):</label>
          <select name="sub_sub_empresa" id="sub_sub_empresa" style="display:none;">
            <!-- Se rellenará dinámicamente -->
          </select>
        </div>
        <div class="form-group">
          <label for="cargo">Cargo:</label>
          <input type="text" name="cargo" id="cargo" placeholder="Ej. Gerente de Ventas" required>
        </div>
        <!-- Campo opcional: Número de celular -->
        <div class="form-group">
          <label for="celular">Número de Celular (opcional):</label>
          <input type="text" name="celular" id="celular" placeholder="Ingresa tu número de celular">
        </div>
        <p class="info">
          La contraseña se establecerá igual a la cédula y se cifrará automáticamente.
        </p>
        <button type="submit">Crear Usuario</button>
      </form>
    </div>
  </div>

  <!-- Script para el select cascada -->
  <script>
    // Definición de los datos para cada empresa con sus sub-opciones (estos pueden usarse si lo deseas)
    const empresaData = {
      "Ferbienes": {
        "Opción 1": ["Sub1-1", "Sub1-2", "Sub1-3"],
        "Opción 2": ["Sub2-1", "Sub2-2", "Sub2-3"],
        "Opción 3": ["Sub3-1", "Sub3-2", "Sub3-3"],
        "Opción 4": ["Sub4-1", "Sub4-2", "Sub4-3"]
      },
      "Agrosigo": {
        "Opción A": ["A1", "A2", "A3"],
        "Opción B": ["B1", "B2", "B3"],
        "Opción C": ["C1", "C2", "C3"],
        "Opción D": ["D1", "D2", "D3"]
      },
      "Tribilin": {
        "Sub A": ["TA1", "TA2", "TA3"],
        "Sub B": ["TB1", "TB2", "TB3"],
        "Sub C": ["TC1", "TC2", "TC3"],
        "Sub D": ["TD1", "TD2", "TD3"]
      },
      "Agrosigo2": {
        "Opción X": ["X1", "X2", "X3"],
        "Opción Y": ["Y1", "Y2", "Y3"],
        "Opción Z": ["Z1", "Z2", "Z3"],
        "Opción W": ["W1", "W2", "W3"]
      }
    };

    const selectEmpresa = document.getElementById("empresa");
    const selectSubEmpresa = document.getElementById("sub_empresa");
    const selectSubSubEmpresa = document.getElementById("sub_sub_empresa");

    // Cuando cambia el select de Empresa
    selectEmpresa.addEventListener("change", function() {
      // Limpiar y actualizar la visibilidad de los selects
      selectSubEmpresa.innerHTML = "";
      selectSubSubEmpresa.innerHTML = "";
      document.querySelector("label[for='sub_empresa']").style.display = "block";
      selectSubEmpresa.style.display = "block";
      document.querySelector("label[for='sub_sub_empresa']").style.display = "none";
      selectSubSubEmpresa.style.display = "none";
      
      const empresaElegida = this.value;
      if (empresaElegida && empresaData[empresaElegida]) {
        // Agregar opción predeterminada
        let defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Seleccione una opción (opcional)";
        defaultOption.disabled = true;
        defaultOption.selected = true;
        selectSubEmpresa.appendChild(defaultOption);
        
        // Rellenar el select de sub-empresa
        const subOpciones = Object.keys(empresaData[empresaElegida]);
        subOpciones.forEach(function(subOp) {
          let option = document.createElement("option");
          option.value = subOp;
          option.textContent = subOp;
          selectSubEmpresa.appendChild(option);
        });
      }
    });

    // Cuando cambia el select de Sub Empresa
    selectSubEmpresa.addEventListener("change", function() {
      // Limpiar y mostrar el select de sub-sub-empresa
      selectSubSubEmpresa.innerHTML = "";
      document.querySelector("label[for='sub_sub_empresa']").style.display = "block";
      selectSubSubEmpresa.style.display = "block";
      
      const empresaElegida = selectEmpresa.value;
      const subElegida = this.value;
      if (empresaElegida && subElegida && empresaData[empresaElegida][subElegida]) {
        let defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.textContent = "Seleccione una opción (opcional)";
        defaultOption.disabled = true;
        defaultOption.selected = true;
        selectSubSubEmpresa.appendChild(defaultOption);
        
        // Rellenar el select de sub-sub-empresa
        empresaData[empresaElegida][subElegida].forEach(function(subSubOp) {
          let option = document.createElement("option");
          option.value = subSubOp;
          option.textContent = subSubOp;
          selectSubSubEmpresa.appendChild(option);
        });
      }
    });
  </script>
</body>
</html>
