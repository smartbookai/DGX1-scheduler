<?php include('database_connection.php') ?>

<?php
$sql = "
    SELECT `a`.*
    FROM `affiliation` as `a`
    ORDER BY affiliation_ID
    ";

$result = mysqli_query($conn, $sql);
$affiliations = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

$tmp = array();
foreach ($affiliations as &$affiliation) {
    echo "<option value=\"" . $affiliation["affiliation_ID"] . "\">" . $affiliation["name"] . "</option>";
}
?>