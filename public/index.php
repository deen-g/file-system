<?php

use Src\Controller\Manager;

require "../bootstrap.php";
$manager = Manager::getInstance();
$manager->loadData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        filesystem
    </title>
</head>
<body style="text-align: center; padding: 200px">
<hr/>
<h5>Search</h5>
<form action="<?php $manager->search(); ?>" method="post">
    <input type="search" name="search">
    <br/>
    <br/>
    <input type="submit" name="submit">
</form>
<?php
$manager = Manager::getInstance();
$outputs = $manager->getSearchResult();
$error = $manager->getSearchError();
if ($error) print_r($error);
if ($outputs) {
    foreach ($outputs as $out) {
        echo $out;
        echo "<br/>";
    }
}

?>
</body>
</html>