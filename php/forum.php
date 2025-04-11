<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',       // O 'localhost' si lo prefieres
    'secure'   => false,    // false, porque usas HTTP y no HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit;
}
require 'config.php';

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['rol']; // "estudiante", "profesor", etc.
$mensaje   = "";

// Generar enlaces dinámicos según el rol
$dashboardLink = ($user_role === 'profesor') ? 'dashboard_profesor.php' : 'dashboard_estudiante.php';
$perfilLink    = ($user_role === 'profesor') ? 'perfil_profesor.php'    : 'perfil_estudiante.php';

// Procesar "like" (toggle: si ya dio like, se quita; de lo contrario, se agrega)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['like_question'])) {
    $question_id = (int)$_GET['like_question'];
    // Verificar si el usuario ya dio like a esta pregunta
    $checkQuery  = "SELECT * FROM forum_question_likes WHERE question_id = $question_id AND user_id = $user_id";
    $checkResult = $mysqli->query($checkQuery);
    
    if ($checkResult->num_rows > 0) {
         // Ya dio like, se quita
         $deleteQuery = "DELETE FROM forum_question_likes WHERE question_id = $question_id AND user_id = $user_id";
         $mysqli->query($deleteQuery);
    } else {
         // No ha dado like, se agrega
         $insertQuery = "INSERT INTO forum_question_likes (question_id, user_id) VALUES ($question_id, $user_id)";
         $mysqli->query($insertQuery);
    }
    header("Location: forum.php");
    exit;
}

// Procesar publicación de una nueva pregunta (solo estudiantes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'post_question') {
    if ($user_role != 'estudiante') {
        $mensaje = "Solo los estudiantes pueden publicar preguntas.";
    } else {
        $title   = $mysqli->real_escape_string($_POST['title']);
        $content = $mysqli->real_escape_string($_POST['content']);
        // Procesar imágenes adjuntas (opcional)
        $imageNames = array();
        $uploadDir = __DIR__ . "/../uploads/forum/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // Verificar que se haya seleccionado al menos un archivo
        if (isset($_FILES['question_images']) && !empty($_FILES['question_images']['name'][0])) {
            for ($i = 0; $i < count($_FILES['question_images']['name']); $i++) {
                if ($_FILES['question_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileTmpPath   = $_FILES['question_images']['tmp_name'][$i];
                    $fileName      = $_FILES['question_images']['name'][$i];
                    $fileNameCmps  = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        // Para que cada imagen tenga un nombre único
                        $newImageName = $user_id . "_" . time() . "_" . $i . "." . $fileExtension;
                        $dest_path = $uploadDir . $newImageName;
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $imageNames[] = $newImageName;
                        } else {
                            $mensaje .= "Error al subir la imagen $fileName. ";
                        }
                    } else {
                        $mensaje .= "Extensión de archivo no permitida para la imagen $fileName. ";
                    }
                }
            }
        }
        // Convertir el array de imágenes a una cadena separada por comas (si no se subió ninguna, será NULL)
        $imagesStr = count($imageNames) > 0 ? "'" . $mysqli->real_escape_string(implode(",", $imageNames)) . "'" : "NULL";
        
        // Insertar la pregunta (los likes se contarán dinámicamente)
        $query = "INSERT INTO forum_questions (user_id, title, content, image) 
                  VALUES ($user_id, '$title', '$content', $imagesStr)";
        if ($mysqli->query($query)) {
            $mensaje .= "Pregunta publicada exitosamente.";
        } else {
            $mensaje .= "Error al publicar la pregunta: " . $mysqli->error;
        }
    }
}

// Procesar publicación de una respuesta (solo profesores)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'post_answer') {
    if ($user_role != 'profesor') {
        $mensaje = "Solo los profesores pueden responder preguntas.";
    } else {
        $question_id    = (int)$_POST['question_id'];
        $answer_content = $mysqli->real_escape_string($_POST['answer_content']);
        $query = "INSERT INTO forum_answers (question_id, user_id, content) 
                  VALUES ($question_id, $user_id, '$answer_content')";
        if ($mysqli->query($query)) {
            $mensaje = "Respuesta publicada exitosamente.";
        } else {
            $mensaje = "Error al publicar la respuesta: " . $mysqli->error;
        }
    }
}

// Obtener todas las preguntas (más recientes primero)
// Se utiliza una subconsulta para contar los likes y otra para saber si el usuario ya dio like.
$query_questions = "
    SELECT fq.id, fq.title, fq.content, fq.created_at, fq.image,
           u.nombre_completo AS author,
           (SELECT COUNT(*) FROM forum_question_likes fql WHERE fql.question_id = fq.id) AS likes,
           (SELECT COUNT(*) FROM forum_question_likes fql WHERE fql.question_id = fq.id AND fql.user_id = $user_id) AS liked_by_user
    FROM forum_questions fq 
    JOIN users u ON fq.user_id = u.id 
    ORDER BY fq.created_at DESC
";
$result_questions = $mysqli->query($query_questions);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foro de Preguntas y Respuestas</title>
    <!-- Font Awesome para iconos (corazones) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Enlaces a tus CSS (dashboard y forum) -->
    <link rel="stylesheet" href="../css/dashboard_estudiante.css">
    <link rel="stylesheet" href="../css/forum.css">
    <style>
        /* Estilos adicionales para el foro */
        .forum-container {
            padding: 20px;
            background: #f8f9fa;
        }
        .back-dashboard-btn {
            display: inline-block;
            padding: 8px 12px;
            background: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background 0.3s;
            margin-bottom: 20px;
        }
        .back-dashboard-btn:hover {
            background: #0056b3;
        }
        .mensaje {
            background: #e9f7ef;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .like-btn {
            text-decoration: none;
            font-size: 24px;
            transition: color 0.3s ease;
        }
        .like-btn i {
            color: #999;
        }
        .like-btn.liked i {
            color: #007BFF;
        }
        .like-count {
            font-size: 1em;
            margin-left: 8px;
            color: #333;
            vertical-align: middle;
        }
        .question {
            background: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .question h3 {
            margin-bottom: 10px;
            color: #333;
        }
        .question p {
            margin-bottom: 10px;
            line-height: 1.5;
            color: #555;
        }
        /* Nueva clase para imágenes adjuntas más pequeñas */
        .question-img {
            max-width: 400px; /* Ajusta este valor según lo que necesites */
            height: auto;
            display: inline-block;
            margin: 10px;
            border-radius: 4px;
        }
        .question .info {
            font-size: 0.85em;
            color: #777;
        }
        .answer {
            background: #f0f8ff;
            padding: 15px;
            margin: 15px 0 0 30px;
            border-left: 4px solid #007BFF;
            border-radius: 4px;
        }
        .answer .info {
            font-size: 0.8em;
            color: #555;
        }
        .new-answer {
            margin-top: 20px;
            padding: 15px;
            background: #fafafa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .new-answer textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .new-answer button {
            padding: 10px 15px;
            background: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .new-answer button:hover {
            background: #218838;
        }
        .new-question {
            background: #fff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .new-question h2 {
            color: #003366;
            margin-bottom: 15px;
        }
        .new-question input[type="text"],
        .new-question textarea,
        .new-question input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .new-question button {
            padding: 12px 20px;
            background: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .new-question button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Barra Superior -->
    <header class="top-bar">
        <div class="top-bar-left">
            <img src="../images/logo.png" alt="Logo" class="logo">
            <span class="site-name">Plataforma de Cursos</span>
        </div>
        <div class="top-bar-right">
            <span class="username">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="<?php echo $perfilLink; ?>" class="profile-btn">Mi Perfil</a>
            <a href="forum.php" class="forum-btn">Foro</a>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </header>

    <!-- Banner principal -->
    <div class="banner">
        <img src="../images/banner.png" alt="Banner <?php echo ($user_role=='profesor') ? 'Profesor' : 'Estudiante'; ?>">
    </div>

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <ul>
            <li><a href="<?php echo $dashboardLink; ?>">Inicio</a></li>
            <li>Foro</li>
        </ul>
    </nav>

    <!-- Contenedor principal del foro -->
    <main class="forum-container">
        <!-- Botón para volver al Dashboard -->
        <a href="<?php echo $dashboardLink; ?>" class="back-dashboard-btn">Volver al Dashboard</a>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Publicar nueva pregunta (solo estudiantes) -->
        <?php if ($user_role == 'estudiante'): ?>
            <section class="new-question">
                <h2>Publica tu pregunta</h2>
                <form action="forum.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="post_question">
                    <label for="title">Título:</label>
                    <input type="text" name="title" id="title" required>
                    <label for="content">Pregunta:</label>
                    <textarea name="content" id="content" rows="4" required></textarea>
                    <label for="question_images">Adjuntar imágenes (opcional, puedes seleccionar varias):</label>
                    <input type="file" name="question_images[]" id="question_images" accept="image/*" multiple>
                    <button type="submit">Publicar Pregunta</button>
                </form>
            </section>
        <?php endif; ?>

        <!-- Listado de preguntas y respuestas -->
        <section class="questions">
            <?php if ($result_questions && $result_questions->num_rows > 0): ?>
                <?php while ($question = $result_questions->fetch_assoc()): ?>
                    <div class="question">
                        <h3><?php echo htmlspecialchars($question['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($question['content'])); ?></p>
                        <?php if (!empty($question['image'])): ?>
                            <?php 
                                // Suponemos que se almacenaron múltiples imágenes separadas por comas
                                $images = explode(",", $question['image']);
                                foreach ($images as $img): 
                            ?>
                                <img src="../uploads/forum/<?php echo htmlspecialchars($img); ?>" alt="Imagen adjunta" class="question-img">
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <p class="info">
                            Publicado por <?php echo htmlspecialchars($question['author']); ?> 
                            el <?php echo $question['created_at']; ?>
                        </p>
                        <!-- Botón de Like (toggle) usando icono de corazón -->
                        <a href="forum.php?like_question=<?php echo $question['id']; ?>" 
                           class="like-btn <?php echo ($question['liked_by_user'] > 0) ? 'liked' : ''; ?>">
                           <?php if ($question['liked_by_user'] > 0): ?>
                               <i class="fa fa-heart"></i>
                           <?php else: ?>
                               <i class="fa fa-heart-o"></i>
                           <?php endif; ?>
                        </a>
                        <span class="like-count"><?php echo $question['likes']; ?> likes</span>
                        
                        <!-- Mostrar respuestas -->
                        <?php 
                            $question_id = $question['id'];
                            $query_answers = "
                                SELECT fa.content, fa.created_at, u.nombre_completo AS answerer 
                                FROM forum_answers fa 
                                JOIN users u ON fa.user_id = u.id 
                                WHERE fa.question_id = $question_id 
                                ORDER BY fa.created_at ASC
                            ";
                            $result_answers = $mysqli->query($query_answers);
                        ?>
                        <?php if ($result_answers && $result_answers->num_rows > 0): ?>
                            <?php while ($answer = $result_answers->fetch_assoc()): ?>
                                <div class="answer">
                                    <p><?php echo nl2br(htmlspecialchars($answer['content'])); ?></p>
                                    <p class="info">
                                        Respuesta por <?php echo htmlspecialchars($answer['answerer']); ?> 
                                        el <?php echo $answer['created_at']; ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p><em>No hay respuestas aún.</em></p>
                        <?php endif; ?>

                        <!-- Formulario para responder (solo profesores) -->
                        <?php if ($user_role == 'profesor'): ?>
                            <div class="new-answer">
                                <form action="forum.php" method="POST">
                                    <input type="hidden" name="action" value="post_answer">
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                    <textarea name="answer_content" rows="3" placeholder="Escribe tu respuesta..." required></textarea>
                                    <button type="submit">Responder</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p><em>No se han publicado preguntas aún.</em></p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
