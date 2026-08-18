<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1><?php echo $nombre ?></h1>


    <h3>Asunto:</h3>
    <p>
        "<?php echo $mensaje ?>"
    </p>

    <strong>Contactar por: <?php echo $tipo_contacto ?></strong>

<?php switch($tipo_contacto): ?>
<?php case 'telefono': ?>
        <p><strong>Cita:</strong><?php echo " $hora - $fecha - $telefono"?></p>
<?php break; ?>
        
        <?php case 'correo': ?>
            <p><strong>Cita:</strong><?php echo $email?></p>
        <?php break; ?>
        
        <?php default ?>
            
        <?php break; ?>
<?php endswitch; ?>
</body>
</html>