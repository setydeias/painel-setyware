<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<section id="content">
	<section class="row">
		<section class="col-md-12 col-sm-12">
			<h3>Prêmio Adimplência</h3>
			<section class="menu-horizontal">
				<a href="/painel/list/customers"><button class="btn btn-primary" type="button">Voltar</button></a>
			</section>
		</section>
	</section>
	<hr />
	<form enctype="multipart/form-data">
		<section id="dadosiniciais">
			<section class="row">
				<section class="col-md-2 form-group">
					<label>Código:</label>
					<input type="text" class="form-control" name="codsac" id="codsac" maxlength="5" readonly />
				</section>
				<section class="col-md-2 form-group">
					<label>Sigla: </label>
					<input type="text" class="form-control" name="sigla-cliente" id="siglaCliente" maxlength="3" readonly />
				</section>
			</section>
			<section class="row">				
				<section class="col-md-4 form-group">
					<label>Nome: </label>
					<input type="text" class="form-control" name="nome-cliente" id="nomeCliente" maxlength="45" readonly />
				</section>
			</section>
			<div class="row">
				<div class="col-md-5 form-group">
					<span class="image-error"></span>
				</div>
			</div>
		</section>		
		<div class="row">
			<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-home"></span> ESTRUTURAR UNIDADES</b></h5></div> <hr />			
		</div>	
		<div id="acao-add-unidade" class="row">
			<div class="col-md-3 form-group">
				<label>Descrição: </label>
				<input type="text" class="form-control" name="unidade-descricao" id="unidade-descricao" maxlength="30" />
				</div>
					<div class="col-md-3 form-group">
						<button type="button" name="add-unidade" id="add-unidade" style="margin-top: 25px; margin-left: -20px;" class="btn btn-primary" ><span class="glyphicon glyphicon-plus-sign"></span> Adicionar</button>
					</div>
				<section class="col-md-12" id="msg-error-unidade"></section>				
			</div>	
		<div id="msg-error-unidade" class="row">
						
		</div>	
		<section class="row">
			<section class="col-md-6">
				<section class="panel panel-default">
					<section class="panel-heading">
						Lista <span class="required-alert">*</span> <b>(<span id="qtdUnidades"></span>)</b>
					</section>
					<section class="panel-body" class="col-md-12" style="padding:10px;">
						<section class="row">
							<section class="col-md-12" id="list-unidades"></section>
						</section>
					</section>
				</section>
			</section>	
			<div class="col-md-8 form-group" id="acaoLista" style="margin-top: -10px"></div>
		</section>
	</form>
	
	<!-- MODAL CONTATO -->
	<section id="modalContato" class="modal fade" tabindex="-1" role="dialog">
		<section class="modal-dialog" role="document">
			<section class="modal-content">
				<section class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Contato</h4>
				</section>
				<section class="modal-body">
					<form>
						<section class="row">
							<section class="col-md-12">
								<label>Condômino: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="descricao-condomino" placeholder="Nome do condômino." />
							</section>
							<section class="col-md-12">
								<label>E-mail: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="email" placeholder="E-mail."/>
							</section>
						</section>					
						<section class="row">
							<section class="col-md-12">
								<label>Tipo de telefone: <span class="required-alert">*</span></label>
								<select class="form-control" name="tipo-telefone" id="tipoTelefone">
									<option value="0" selected></option>
									<option value="1">Fixo</option>
									<option value="2">Celular</option>
								</select>
							</section>							
							<section class="col-md-12">
								<label>Número: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="numero-telefone" placeholder="(85) 9.9999-9999" />
							</section>
						</section>
					</form>
					<section class="row">
						<section class="col-md-12" id="msg-error-contato"></section>
					</section>
				</section>
				<section class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
					<button type="button" class="btn btn-primary" id="cadastrar-contato"><span class="glyphicon glyphicon-plus-sign"></span> Confirmar</button>
				</section>
			</section><!-- /.modal-content -->
		</section><!-- /.modal-dialog -->
	</section><!-- /.modal -->

</section>
	
<script src="/painel/dist/premioAdimplencia.js"></script>