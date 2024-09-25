<?php
// Datos de conexión LDAP
$ldap_server = "10.232.2.12"; // Servidor LDAP
$ldap_port = 389; // Puerto (normalmente 389 para LDAP no seguro, 636 para LDAP seguro)
$ldap_dn_admin = "CN=LDAP RRHHFORMS,OU=Non-users,DC=miambiente,DC=interno"; // DN del administrador
$ldap_password_admin = "xbZ605faAck7"; // Contraseña del administrador

// Conectar al servidor LDAP
$ldap_connection = ldap_connect($ldap_server, $ldap_port);

if (!$ldap_connection) {
    echo "No se pudo conectar al servidor LDAP.";
    exit;
}

// Autenticarse con un usuario administrador
$ldap_bind = ldap_bind($ldap_connection, $ldap_dn_admin, $ldap_password_admin);

if (!$ldap_bind) {
    echo "No se pudo autenticar con el servidor LDAP.";
    exit;
}

// Definir la base de búsqueda principal
$base_main = "DC=miambiente,DC=interno";

// Lista de OUs principales
$ous = array(
    "Domain Controllers",
    "Direcciones",
    "Administrativos",
    "Apoyo",
    "Despacho Superior",
    "REGIONAL OFFICES",
    "Informatica",
    "GROUPS",
    "Non-users",
    "Usuarios Desactivados",
    "SERVERS",
    "Panel-Soporte",
    "Equipos Desactivados",
    "Phones",
    "Temporal"
);

// Filtro para obtener sub-OUs dentro de cada OU principal
$filter_sub_ou = "(objectClass=organizationalUnit)";

// Filtro para obtener usuarios o equipos dentro de las sub-OUs
$filter_users = "(|(objectClass=user)(objectClass=computer))"; // Este filtro obtiene usuarios y equipos

// Atributos que deseas obtener para los usuarios o equipos
$attributes_users = array("cn", "sn", "givenname", "mail");

$tree_structure = [];

// Recorrer las OUs principales y realizar la búsqueda en cada una
foreach ($ous as $ou) {
    // Construir el DN de la OU principal actual
    $base_dn = "OU=" . $ou . "," . $base_main;

    // Buscar sub-OUs dentro de la OU principal
    $search_sub_ou = ldap_search($ldap_connection, $base_dn, $filter_sub_ou, array("ou"));

    if ($search_sub_ou) {
        $sub_ous = ldap_get_entries($ldap_connection, $search_sub_ou);

        if ($sub_ous["count"] > 0) {

            $tree_structure[$ou] = [];

            for ($j = 0; $j < $sub_ous["count"]; $j++) {
                $sub_ou_name = $sub_ous[$j]["ou"][0];
                $tree_structure[$ou][$sub_ou_name] = [];

                // Realizar la búsqueda de usuarios o equipos dentro de la sub-OU actual
                $sub_ou_dn = $sub_ous[$j]["dn"];
                $search_users = ldap_search($ldap_connection, $sub_ou_dn, $filter_users, $attributes_users);

                if ($search_users) {
                    $users = ldap_get_entries($ldap_connection, $search_users);

                    if ($users["count"] > 0) {
                        foreach ($users as $user) {
                            if (isset($user['cn'][0])) {
                                $tree_structure[$ou][$sub_ou_name][] = [ 
                                    "Common Nombre" => isset($user["cn"][0]) ? $user["cn"][0] : "N/A",
                                    "Nombre" => isset($user["givenname"][0]) ? $user["givenname"][0] : "N/A",
                                    "Apellido" => isset($user["sn"][0]) ? $user["sn"][0] : "N/A",
                                    "Email" => isset($user["mail"][0]) ? $user["mail"][0] : "N/A"
                                ];
                            }
                        }
                    }
                }
            }

        }

    }
    
}

// Cerrar la conexión LDAP
ldap_close($ldap_connection);

// Ahora generamos el HTML para el árbol
?>