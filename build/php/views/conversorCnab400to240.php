<br /><br />
<div>
	<form>
		<div class="row">
			<div class="col-md-5">
				<div class="row">
					<label class="col-md-12">
						Caminho do arquivo gerado:
						<input type="text" class="form-control" id="path-to" value="C:\COBPOP\Arquivos\Remessas\Registrar\Processadas\" />
					</label>
				</div>
				<br />
				<div class="row">
					<label class="col-md-12">
						<button class="btn btn-warning" type="button" id="select-file"><span class="glyphicon glyphicon-open-file"></span> Clique aqui para selecionar o arquivo para a conversão</button>
						<input type="file" name="arquivocnab" class="form-control" id="file" />
					</label>
				</div>
				<br />
				<div class="row">
					<div class="col-md-12">
						<label>Tipo de convênio:</label>
						<div class="radio">
							<label>
								<input type="radio" name="tipoconvenio" id="tipoconvenio" value="6" disabled="disabled">
								6 Posições
							</label>
						</div>
						<div class="radio">
							<label>
								<input type="radio" name="tipoconvenio" id="tipoconvenio" value="7" checked disabled="disabled">
								7 Posições
							</label>
						</div>
					</div>
				</div>
				<p>
					<br /><button class="btn btn-primary" type="button" id="btn-converter"><span class="glyphicon glyphicon-retweet"></span> &nbsp;Converter arquivo</button>
				</p>
				<p id="error-area"></p>
			</div>
			<div class="col-md-6 col-md-offset-1" id="info-panel">
				<div class="panel panel-default">
					<div class="panel-heading"><span class="glyphicon glyphicon-info-sign"></span> Informações do arquivo selecionado</div>
					<div class="panel-body" style="padding:10px;">
						<p>Nome: <b><span class="pull-right" id="nome-arquivo"></span></b></p>
						<p>Tamanho: <b><span class="pull-right" id="tamanho-arquivo"></span></b></p>
						<p>Qtde. de títulos: <b><span class="pull-right" id="qtde-titulos"></span></b></p>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>