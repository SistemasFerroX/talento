<?php
require '../php/config.php';

// 1) Inserta las plantillas
$templates = [
  ['auto','Autoevaluación','../images/banners/auto.png'],
  ['jefe','Jefe Inmediato','../images/banners/jefe.png'],
  // ... cargos similares, personal a cargo
];
$stmt = $mysqli->prepare("INSERT INTO evaluation_templates (slug,title,banner) VALUES (?,?,?)");
foreach($templates as $tpl){
  $stmt->bind_param("sss",$tpl[0],$tpl[1],$tpl[2]);
  $stmt->execute();
}

// 2) Inserta las preguntas (en orden) para cada slug
$questionsByTpl = [
  'jefe'=> [
    'q1'=>'Establece metas con su equipo de trabajo y le realiza seguimiento',
    'q2'=>'Inspira, motiva y guía al equipo para el logro de las metas',
    // ...
  ],
  // ...
];
foreach($questionsByTpl as $slug=>$qs){
  // obtiene template_id
  $r = $mysqli->query("SELECT id FROM evaluation_templates WHERE slug='$slug'")->fetch_assoc();
  $tid = $r['id'];
  $qorder=1;
  $stmt2 = $mysqli->prepare("
    INSERT INTO evaluation_questions(template_id,qkey,text,question_type,qorder)
    VALUES(?,?,?,?,?)
  ");
  foreach($qs as $qk=>$qt){
    $stmt2->bind_param("isssi",$tid,$qk,$qt,$type='radio',$qorder);
    $stmt2->execute();
    $qorder++;
  }
}
echo "Templates y preguntas cargados.\n";
?>
