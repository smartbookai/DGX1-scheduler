<?php
include(__BASE_PATH__ . '/database_connection.php');

$sql = "SELECT  
            c.`container_ID` 'id',
            c.`name` 'name',
            c.`description` 'description'
        FROM `containers` c
        WHERE c.`isActive` = 1
        ORDER BY c.`container_ID`";

$stmt = $conn->prepare($sql);
$stmt->execute();
$containers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$tmp = array();
foreach ($containers as &$container) {
    echo "<option value='" . json_encode(array("id" => $container["id"], "description" => $container["description"])) . "'>" . $container["name"] . "</option>";
}
?>