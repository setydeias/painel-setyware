<?php
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
	<h3>Adicionar convênios de cobrança</h3>
	<h5><span class="error">*</span> Adiciona um novo convênio no banco de dados do cliente selecionado</h5>
	<hr />
	<form>
		<div class="row">
			<div class="col-md-2">
				<div class="form-group">
					<label for="cliente">
						Selecione o cliente
						<select class="form-control" name="cliente"></select>
						<div class="error-handler-area"></div>
					</label>
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-group">
					<label for="banco">
						Banco
						<select class="form-control" name="banco">
							<option></option>
							<option value="001">001 - Banco do Brasil</option>
							<option value="104">104 - Caixa Econômica Federal</option>
							<option value="237">237 - Bradesco</option>
						</select>
						<div class="error-handler-area"></div>
					</label>
				</div>
			</div>
		</div>
		<section id="convenio-form"></section>
	</form>
</div>
<script src="/painel/dist/convenioCobranca.js"></script>