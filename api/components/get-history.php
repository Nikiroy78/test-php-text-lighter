<?php
error_reporting(E_ERROR | E_PARSE);
include "database-controller.php";

function getHistory () {
	$result = getRequestHistory();
	
	if (count($result) == 0) {
		return "Записи отсутствуют";
	}
	else {
		return "Запросы: \n". implode("\n", $result);
	}
}

?>