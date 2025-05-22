<?php
session_start();
require '../php/config.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Sanitizar entradas
    $cedula          = $mysqli->real_escape_string($_POST['cedula']);
    $nombre          = $mysqli->real_escape_string($_POST['nombre']);
    $apellidos       = $mysqli->real_escape_string($_POST['apellidos']);
    $correo          = $mysqli->real_escape_string($_POST['correo'] ?? '');
    $rol             = $mysqli->real_escape_string($_POST['rol']);
    $empresa         = $mysqli->real_escape_string($_POST['empresa']);
    $sub_empresa     = $mysqli->real_escape_string($_POST['proceso'] ?? '');
    $sub_sub_empresa = $mysqli->real_escape_string($_POST['subproceso'] ?? '');
    $cargo           = $mysqli->real_escape_string($_POST['cargo']);
    $celular         = $mysqli->real_escape_string($_POST['celular'] ?? '');

    // 2) La contraseña será igual a la cédula
    $hashed_password = password_hash($cedula, PASSWORD_DEFAULT);

    // 3) Insertar en la base de datos
    $sql = "
      INSERT INTO users 
        (cedula,
         nombre_completo,
         apellidos,
         correo,
         password,
         rol,
         empresa,
         sub_empresa,
         sub_sub_empresa,
         cargo,
         celular)
      VALUES
        (
          '$cedula',
          '$nombre',
          '$apellidos',
          '$correo',
          '$hashed_password',
          '$rol',
          '$empresa',
          '$sub_empresa',
          '$sub_sub_empresa',
          '$cargo',
          '$celular'
        )
    ";

    if ($mysqli->query($sql)) {
        $mensaje = "Usuario creado exitosamente.";
    } else {
        $mensaje = "Error al crear usuario: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Usuario</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Roboto,Arial,sans-serif; background:#F1F3F5; color:#333; }
    .back-button {
      display:inline-block; margin:20px;
      background:#0066CC; color:#fff; padding:8px 12px;
      text-decoration:none; border-radius:4px;
    }
    .registro-container {
      display:flex; justify-content:center; padding:20px;
    }
    .card {
      background:#fff; padding:20px; border-radius:8px;
      box-shadow:0 2px 6px rgba(0,0,0,.1); width:400px;
    }
    .card h1 { color:#0066CC; margin-bottom:16px; }
    .mensaje {
      background:#e9f7ef; border:1px solid #c3e6cb;
      color:#155724; padding:10px; border-radius:4px;
      margin-bottom:16px;
    }
    .form-group { margin-bottom:12px; }
    .form-group label { display:block; margin-bottom:4px; }
    .form-group input,
    .form-group select {
      width:100%; padding:8px; font-size:14px;
      border:1px solid #ccc; border-radius:4px;
    }
    button {
      width:100%; padding:10px; background:#0066CC;
      color:#fff; border:none; border-radius:4px;
      font-size:16px; cursor:pointer;
    }
  </style>
</head>
<body>

  <a href="dashboard_admin.php" class="back-button">← Volver al Dashboard</a>

  <div class="registro-container">
    <div class="card">
      <h1>Registrar Usuario</h1>

      <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
      <?php endif; ?>

      <form method="POST" action="register_user.php">
        <div class="form-group">
          <label for="cedula">Cédula *</label>
          <input type="text" id="cedula" name="cedula" required>
        </div>

        <div class="form-group">
          <label for="nombre">Nombre Completo *</label>
          <input type="text" id="nombre" name="nombre" required>
        </div>

        <div class="form-group">
          <label for="apellidos">Apellidos *</label>
          <input type="text" id="apellidos" name="apellidos" required>
        </div>

        <div class="form-group">
          <label for="correo">Correo (opcional)</label>
          <input type="email" id="correo" name="correo">
        </div>

        <div class="form-group">
          <label for="rol">Rol *</label>
          <select id="rol" name="rol" required>
            <option value="" disabled selected>— Selecciona —</option>
            <option value="estudiante">Estudiante</option>
            <option value="profesor">Profesor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>

        <div class="form-group">
          <label for="empresa">Empresa *</label>
          <select id="empresa" name="empresa" required>
            <option value="" disabled selected>— Selecciona —</option>
            <option value="Inversiones Ferbienes">Inversiones Ferbienes</option>
            <option value="Comercializadora Agrosigo">Comercializadora Agrosigo</option>
            <option value="Inversiones Tribilin">Inversiones Tribilin</option>
            <option value="Agrosigo">Agrosigo</option>
          </select>
        </div>

        <div class="form-group" id="group-proceso" style="display:none;">
          <label for="proceso">Proceso *</label>
          <select id="proceso" name="proceso" required></select>
        </div>

        <div class="form-group" id="group-subproceso" style="display:none;">
          <label for="subproceso">Subproceso (opcional)</label>
          <select id="subproceso" name="subproceso"></select>
        </div>

        <div class="form-group">
          <label for="cargo">Cargo *</label>
          <input type="text" id="cargo" name="cargo" required>
        </div>

        <div class="form-group">
          <label for="celular">Celular (opcional)</label>
          <input type="text" id="celular" name="celular">
        </div>

        <p>La contraseña será igual a la cédula y se cifrará automáticamente.</p>
        <button type="submit">Crear Usuario</button>
      </form>
    </div>
  </div>

  <script>
    // Define aquí los procesos y subprocesos para cada empresa
    const data = {
      "Inversiones Ferbienes": {
        "Finanzas": ["Cuentas por pagar","Cuentas por cobrar"],
        "Recursos Humanos": ["Selección","Capacitación"]
      },
      "Comercializadora Agrosigo": {
        "Director de operaciones": [],
        "Auxiliar logístico - conductor": [],
        "Auxiliar logístico - despacho derivados cárnicos": [],
        "Auxiliar logístico - despacho desposte/porcionado": [],
        "Auxiliar de inventarios": [],
        "Auxiliar de mantenimiento": [],
        "Supervisor de mantenimiento": [],
        "Oficios varios": [],
        "Servicios generales": [],
        "Jefe de innovación y desarrollo": [],
        "Asistente administrativo": [],
        "Auxiliar de servicio al cliente - Restaurante Le'mont": [],
        "Auxiliar de servicio al cliente - Restaurante Sabaneta": [],
        "Auxiliar de servicio al cliente - Pal'asado Ferbienes": [],
        "Auxiliar de servicio al cliente - Pal'asado Sabana Sur": [],
        "Auxiliar de servicio al cliente - Pal'asado La Unión": [],
        "Auxiliar de servicio al cliente - Recepción": [],
        "Auxiliar de transporte": [],
        "Conductor TAT": [],
        "Auxiliar de cocina - Restaurante Sabaneta": [],
        "Mercaderistas": [],
        "Ejecutivo comercial": [],
        "Asesor comercial": [],
        "Auxiliar de desarrollo organizacional": [],
        "Analista de desarrollo organizacional": [],
        "Auxiliar de calidad": [],
        "Coordinador de calidad": [],
        "Director de calidad": [],
        "Vendedor": [],
        "Auxiliar de facturación": [],
        "Auxiliar de tesoreria": [],
        "Aprendiz": [],
        "Auxiliar de contabilidad": [],
        "Administrador punto de venta - Restaurante Sabaneta": [],
        "Administrador punto de venta - Restaurante Le'mont": [],
        "Líder de mercaderista": [],
        "Asistente logística": [],
        "Director comercial": [],
        "Auxiliar de cartera": []
      },
      "Inversiones Tribilin": {
        "Proyectos": ["Evaluación","Ejecución"],
        "Legal": []
      },
      "Agrosigo": {
        "Producción": ["Procesamiento","Empaque"],
        "Calidad": []
      }
    };

    const selEmpresa = document.getElementById('empresa');
    const grpProc    = document.getElementById('group-proceso');
    const selProc    = document.getElementById('proceso');
    const grpSub     = document.getElementById('group-subproceso');
    const selSub     = document.getElementById('subproceso');

    selEmpresa.addEventListener('change', () => {
      const procesos = data[ selEmpresa.value ] || null;
      selProc.innerHTML    = "";
      selSub.innerHTML     = "";
      grpSub.style.display = "none";

      if (procesos) {
        grpProc.style.display = "block";
        selProc.appendChild(new Option("— Selecciona proceso —","",true,true))
               .disabled = true;
        Object.keys(procesos).forEach(p => {
          selProc.appendChild(new Option(p,p));
        });
      } else {
        grpProc.style.display = "none";
      }
    });

    selProc.addEventListener('change', () => {
      const subs = data[ selEmpresa.value ][ selProc.value ] || [];
      selSub.innerHTML = "";
      if (subs.length) {
        grpSub.style.display = "block";
        selSub.appendChild(new Option("— Selecciona subproceso —","",true,true))
              .disabled = true;
        subs.forEach(sp => selSub.appendChild(new Option(sp,sp)));
      } else {
        grpSub.style.display = "none";
      }
    });
  </script>

</body>
</html>
