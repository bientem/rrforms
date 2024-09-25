<?php 

    require 'ldap_conf_logic.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="setup.css">
    <title>Directorio Activo</title>
    <script>
        function toggleSubOU(element) {
            element.classList.toggle("active");
            let subOU = element.nextElementSibling;
            if (subOU) {
                subOU.classList.toggle("active");
            }
        }
    </script>
</head>
<body>

<h1>Directorio Activo</h1>
<ul>
    <?php foreach ($tree_structure as $ou_name => $sub_ous): ?>
        <li class="toggle" onclick="toggleSubOU(this)"><?php echo htmlspecialchars($ou_name); ?></li>
        <ul class="sub-ou">
            <?php foreach ($sub_ous as $sub_ou_name => $users): ?>
                <li class="toggle" onclick="toggleSubOU(this)"><?php echo htmlspecialchars($sub_ou_name); ?></li>
                <ul class="sub-ou">
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <li>
                                <ul>
                                    <li>Nombre Común: <?php echo htmlspecialchars($user['Common Nombre']); ?></li>
                                    <li>Nombre: <?php echo htmlspecialchars($user['Nombre']); ?></li>
                                    <li>Apellido: <?php echo htmlspecialchars($user['Apellido']); ?></li>
                                    <li>Email: <?php echo htmlspecialchars($user['Email']); ?></li>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No se encontraron usuarios o equipos.</li>
                    <?php endif; ?>
                </ul>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</ul>

</body>
</html>
