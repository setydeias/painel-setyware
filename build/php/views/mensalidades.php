<?php 
	include_once '../header.php';
?>
<div id="content">
	<h3>Processamento de Mensalidades</h3>
	<hr />
	<form>
		<div class="row">
			<div class="col-md-6 form-group">
				<h4>Você deseja lançar as mensalidades para qual data?</h4>
				<input class="form-control" maxlength="10" style="width:180px;" id="data-mensalidade" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-12" id="msg"></div>
		</div>
		<div class="row">
			<div class="col-md-5 form-group">
				<label>
					<input style="margin:15px 0" type="checkbox" name="write-ct" /> 
					Escrever mensalidade nas Contas Transitórias
				</label>
				<label>
					<input style="margin:15px 0" type="checkbox" name="create-recibo" /> 
					Gerar recibos de prestação de serviço
				</label>
				<label id="send-mail-area">
					<input style="margin:15px 0" type="checkbox" name="enviar-anexo" /> 
					Enviar recibo em anexo por email
				</label>
				<hr />
				<div id="customer-area">
					<span><span class="glyphicon glyphicon-exclamation-sign"></span> As ações selecionadas serão aplicadas aos clientes selecionados abaixo:</span>
					<span style="display:block;margin:10px 0;"></span>
					<ul class="list-group" id="customer-list"></ul>
				</div>
				<button class="btn btn-primary" id="edit-ct" type="button">
					<span class="glyphicon glyphicon-refresh"></span> 
					Processar
				</button>
			</div>
		</div>
		<div class="row">
			<div class="col-md-5" id="progress-bar"></div>
		</div>
	</form>
</div>
<script src="/painel/dist/mensalidade.js"></script>