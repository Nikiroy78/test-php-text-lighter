<!doctype html>
<html>
	<head>
		<title>Тестовое задание</title>
		<meta charset="utf-8">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
		<style>
			body {
				margin-top : 2.5%;
			}
		</style>
	</head>
	<body>
		<center><h1>Подсветка латинских символов</h1></center>
		
		<form>
			<div class="mb-3">
				<label for="textArea" class="form-label">Введите текст здесь</label>
				<textarea type="text" class="form-control" id="textArea"></textarea>
				<hr>
				<p id="outputArea"></p>
				<hr>
			</div>
			
			<div class="mb-3">
				<label for="textArea" class="form-label">История запросов</label>
				<textarea type="text" class="form-control" id="historyArea" readonly><?php
					include "api/components/get-history.php";
					echo getHistory();
				?></textarea>
			</div>
			
			<center><button type="button" class="btn btn-primary" onclick="formHandler();">Отправить</button></center>
		</form>
		
		<script>
			var textInputed = document.getElementById('textArea').innerHTML;
			
			function escapeRegExp(string) {
				return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
			}
			
			function replaceAll(str, find, replace) {
				return str.replace(new RegExp(escapeRegExp(find), 'g'), replace);
			}
			
			var autoLightMode = false;
			
			function syncHistroy () {
				let request = new XMLHttpRequest(); 
				request.open("GET", "/api/get_history.php", false);
				request.send();
				if (request.status == 200) {
					return request.responseText;
				}
			}
			
			const formHandler = () => {
				let request = new XMLHttpRequest();
				/*
				Получаем из метода список индексов, которые мы "подсветим".
				*/
				request.open("GET", `/api/wrong_symbols.find.php?text=${textInputed}`, false);
				request.send();
				
				console.log(request.status);
				if (request.status == 200) {
					let result = JSON.parse(request.responseText);
					let index;
					let bufferStr = textInputed;
					let lightingLetters = [];
					for (let i in result) {
						index = result[i];
						lightingLetters.push(`<strong>${bufferStr[index]}</strong>`);
					}
					
					for (let i in result) {
						index = result[i];
						bufferStr = replaceAll(bufferStr, bufferStr[index], "জ");
					}
					
					bufferStr = bufferStr.split("জ");
					
					let lightingLettersId = 0;
					let endlessStr = "";
					for (let i in bufferStr) {
						endlessStr += bufferStr[i];
						if (lightingLettersId < lightingLetters.length) {
							endlessStr += lightingLetters[lightingLettersId];
							lightingLettersId++;
						}
					}
					
					document.getElementById('outputArea').innerHTML = endlessStr;
					document.getElementById('historyArea').innerHTML = syncHistroy();
					
					if (!autoLightMode) autoLightMode = true;
				}
			}
			
			function realTimeTyping () {
				// console.log(123);
				if (textInputed != document.getElementById('textArea').value) {
					textInputed = document.getElementById('textArea').value;
					if (!autoLightMode) {
						document.getElementById('outputArea').innerHTML = textInputed;
					}
					else {
						formHandler();
					}
				}
			}
			
			setInterval(realTimeTyping, 1);
		</script>
	</body>
</html>