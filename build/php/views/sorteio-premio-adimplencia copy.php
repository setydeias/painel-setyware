<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<section id="content">
	<section class="row">
		<section class="col-md-12 col-sm-12">
			<h3>Prêmio Adimplência</h3>
			<section class="menu-horizontal">
				<span style="margin-right:30px">
					Link: <a href="" id="link-loteria-federal" target="_blank">Loteria Federal</a>
					<button name="editar-link-loteria-federal" id="editar-link-loteria-federal" type="button" class="btn btn-light btn-sm glyphicon glyphicon-edit" style="margin: 0 0 0 0px;" data-toggle="modal" data-target="#modal-link"></button>
				</span>
				<a href="/painel/list/customers"><button class="btn btn-primary glyphicon glyphicon-home" type="button"> Estruturar Unidades por Cliente</button></a>
			</section>
		</section>
	</section>
	<hr />
	<form enctype="multipart/form-data">	
		<section>
			<div class="row">
				<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-tags"> </span> SORTEIOS</b></h5></div> <hr />	
			</div>
			<section class="row">
				<section class="col-md-2 form-group">
					<label>Concurso: </label>
					<input type="text" class="form-control" name="concurso-numero" id="concurso-numero" maxlength="6" />
				</section>
				<section class="col-md-2 form-group">
					<label>Datra: </label>
					<input type="text" class="form-control maskData" name="concurso-data" id="concurso-data" maxlength="10" />
				</section>
				<section class="col-md-2 form-group">
					<label>Bilhete: </label>
					<input type="text" class="form-control" name="concurso-bilhete" id="concurso-bilhete" maxlength="6" />
				</section>
			</section>		
		</section>
		<section class="row">
			<section class="col-md-2 form-group">
				<button type="button" name="add-sorteio" id="add-sorteio" class="btn btn-primary" ><span class="glyphicon glyphicon-plus-sign"></span> Adicionar</button>
			</section>
		</section>
		<section class="row">
			<section class="col-md-6" id="msg-error"></section>
		</section>		

		<!-- LISTA DOS SORTEIOS -->
		<section class="row">
			<section class="col-md-6">
				<section class="panel panel-default">
					<section class="panel-heading">
						Lista <span class="required-alert">*</span> <b>(<span id="qtdSorteios"></span>)</b>
					</section>
					<section class="panel-body" class="col-md-12" style="padding:10px;">
						<section class="row">
							<section class="col-md-12" id="list-sorteios"></section>
						</section>
					</section>
				</section>
			</section>	
			<div class="col-md-8 form-group" id="acaoLista" style="margin-top: -10px"></div>
		</section>
	</form>
	
	<!-- MODAL LINK -->
	<section id="modal-link" class="modal fade" tabindex="-1" role="dialog">
		<section class="modal-dialog  modal-lg" role="document">
			<section class="modal-content">
				<section class="modal-header">
					<button id='btn-close-modal-link' type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Editar Link</h4>
				</section>
				<section class="modal-body">
					<form>
						<section class="row">
							<section class="col">
								<label>Link: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="descricao-link" placeholder="Digite o link." />
							</section>
						</section>
						<section class="row">
							<section class="col">
								<section class="col-md-12" id="msg-error-link"></section>
							</section>
						</section>
					</form>					
				</section>
				<section class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-primary" id="btn-editar-link"><span class="glyphicon glyphicon-plus-sign" ></span> Confirmar</button>
				</section>
			</section><!-- /.modal-content -->
		</section><!-- /.modal-dialog -->
	</section><!-- /.modal -->

	<!-- MODAL ADD SORTEIO -->
	<section id="modal-sorteio" class="modal fade" tabindex="-1" role="dialog">
		<section class="modal-dialog  modal-lg" role="document">
			<section class="modal-content">
				<section class="modal-header">
					<button id='btn-close-modal-sorteio' type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Editar Sorteio<span id="id-concurso" style="color: #fff"></span></h4>
				</section>
				<section class="modal-body">
					<form>
						<section class="row">
							<section class="col">
								<label>Concurso: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="concurso-edit" placeholder="Digite o concurso." maxlength="6"/>
							</section>
						</section>
						<section class="row">
							<section class="col">
								<label>Data: <span class="required-alert">*</span></label>
								<input type="text" class="form-control maskData"  id="data-edit" maxlength="10" />
							</section>							
						</section>
						<section class="row">
							<section class="col">
								<label>Bilhete: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="bilhete-edit" placeholder="Digite o bilhete."  maxlength="6" />
							</section>
						</section>
						<section class="row">
							<section class="col">
								<section class="col-md-12" id="msg-error-modalsorteio"></section>
							</section>
						</section>
					</form>					
				</section>
				<section class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-primary" id="btn-editar-sorteio" ><span class="glyphicon glyphicon-plus-sign" ></span> Confirmar</button>
				</section>
			</section><!-- /.modal-content -->
		</section><!-- /.modal-dialog -->
	</section><!-- /.modal -->

</section>
	
<script src="/painel/dist/premioAdimplenciaSorteios.js"></script>

