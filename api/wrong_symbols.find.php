<?php
include "components/database-controller.php";
include "components/api-out.php";

error_reporting(E_ERROR | E_PARSE);

function toMd5 ($str) {  // Возможность своей реализации алгоритма md5 при необходимости
	return md5($str);
}

function detectLang ($text) {
	$ruSymbols = selectRuIndex($text);
	
	$text = iconv("UTF-8", "windows-1251", $text);
	$textArr = str_split($text, 1);
	return (float)count($ruSymbols) / (float)count($textArr) >= 0.5 || count(selectRuIndex($text)) == strlen($text) ? "ru_RU" : "en_US";
}

function selectEnIndex ($text) {
	$enSymbols = array();
	$text = iconv("UTF-8", "windows-1251", $text);
	$textArr = str_split($text, 1);
	$enDict = str_split('qwertyuiopasdfghjklzxcvbnm', 1);
	
	for ($i=0; $i <= count($textArr); $i++) {
		if (in_array(mb_strtolower($textArr[$i]), $enDict)) {
			$enSymbols[] = $i;
		}
	}
	return $enSymbols;
}

function selectRuIndex ($text) {
	$enSymbols = selectEnIndex($text);
	$result = array();
	
	$text = iconv("UTF-8", "windows-1251", $text);
	$textArr = str_split($text, 1);
	
	for ($i=0; $i < count($textArr); $i++) {
		if (!in_array($i, $enSymbols)) {
			$result[] = $i;
		}
	}
	
	return $result;
}

/*
В функции wrong_symbol я решил использовать рекурсию: если нам не передаётся язык, то он определяется автоматически.
Результаты алгоритм аавтоподсветки необходимых элементов записываются в базу данных, после чего они могут быть импортированы
из базы данных, что в свою очередь экономит рантайм метода.

Возвращает список индексов в формате json, которые необходимо "подсветить" на уровне клиента.
*/

function wrong_symbol ($text, $lang) {
	switch ($lang) {
		case "ru_RU":
			$cacheResult = getResultFromMd5(toMd5($text), "ru_RU");
			if (
				$cacheResult === "NO"
			) {
				$enSymbols = selectEnIndex($text);
				$text = iconv("UTF-8", "windows-1251", $text);
				$textArr = str_split($text, 1);
				
				$recheckSymbols = array();
				
				$enDict = str_split('eopakxcyEOPAKXCBMHT', 1);
				for ($i=0; $i <= count($textArr); $i++) {
					if (in_array($textArr[$i], $enDict)) {
						$recheckSymbols[] = $i;
					}
				}
				
				saveResult(
					toMd5($text),
					$text,
					$recheckSymbols
				);
				return $recheckSymbols;
			}
			else {
				saveHistory(toMd5($text), $text);
				return $cacheResult;
			}
		
		case "en_US":
			$cacheResult = getResultFromMd5(toMd5($text), "en_US");
			if (
				$cacheResult === "NO"
			) {
				$ruSymbols = selectRuIndex($text);
				$text = iconv("UTF-8", "windows-1251", $text);
				$textArr = str_split($text, 1);
				
				$recheckSymbols = array();
				
				$ruDict = str_split(iconv("UTF-8", "windows-1251", "еоракхсуЕОРАКХСВМНТ"), 1);
				
				for ($i=0; $i <= count($textArr); $i++) {
					if (in_array($textArr[$i], $ruDict)) {
						$recheckSymbols[] = $i;
					}
				}
				
				saveResult(
					toMd5($text),
					$text,
					$recheckSymbols
				);
				return $recheckSymbols;
			}
			else {
				saveHistory(toMd5($text), $text);
				return $cacheResult;
			}
		
		default : // Определим язык и применим рекурсию
			return wrong_symbol($text, detectLang($text));
	}
}

die(
	apiOut(
		wrong_symbol($_GET['text'], $_GET['lang'])
	)
);

?>