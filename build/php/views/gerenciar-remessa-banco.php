<?php
	include_once '../header.php';
	include_once '../functions.php';
?>
<div id="content">
	<h3>
		Gerenciar remessas para banco
	</h3>
	<hr />
	<div>
		<div class="col-md-12">
			<div class="row">
				<label>
					Selecione o cliente:
					<select name="selected-customer" class="form-control"></select>
				</label>
			</div>
			<div class="row">
				<label>
					De <input type="text" name="de" class="form-control date" placeholder="DD/MM/AAAA" />
				</label>
				<label>
					Até <input type="text" name="ate" class="form-control date" placeholder="DD/MM/AAAA" />
				</label>
			</div>
			<div class="row">
				<div class="col-md-5 error-area" style="padding:10px 0 0 0;"></div>
			</div>
			<div class="row">
				<button type="button" name="btn-filter" class="btn btn-primary"><span class="glyphicon glyphicon-filter"></span> Filtrar remessas</button>
			</div>
		</div>
		<div class="col-md-12">
			<div class="row" id="filter-content"></div>
		</div>
	</div>
</div>
<script src="/painel/dist/gerenciarRemessaRegistro.js"></script>