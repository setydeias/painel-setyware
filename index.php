<?php 
	error_reporting(E_ALL);
	include_once 'build/php/header.php';
?>
<div id="content">
	<h3>Processamento de Retornos</h3>
	<hr />
	<form name="process" id="processing" method="post" action="/painel/processamento-retornos">
		<ul class="list-group" id="retornos"></ul>
		<h5><b>Deseja verificar os registros rejeitados?</b></h5>
		<input type="radio" name="insert_record_db" value="01" /> Sim&nbsp;&nbsp;
		<input type="radio" name="insert_record_db" value="02" checked /> Não<br/>
		<hr />
		<h5><b>Atualizar Contas Transitórias?</b></h5>
		<input type="radio" name="ct" value="01" /> Sim&nbsp;&nbsp;
		<input type="radio" name="ct" value="02" checked /> Não<br/>
		<div id="input-ct" style="margin:10px 0;"></div>
		<hr />
		<h5><b>Checar duplicidades?</b></h5>
		<input type="radio" name="duplicidades" value="1" /> Sim&nbsp;&nbsp;
		<input type="radio" name="duplicidades" value="2" checked /> Não<br/><br/>
		<div id="input-duplicidade"></div>
		<hr />
		<button type="submit" class="btn btn-primary" name="processnow" style="margin:10px 0;" id="btn-processing"><span class="glyphicon glyphicon-refresh"></span> Processar Retornos</button>
		<div id="msg-error"></div>
	</form>
</div>
<script src="/painel/dist/main.js"></script>