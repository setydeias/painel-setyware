<?php 
	error_reporting(E_ALL);
	include_once '../header.php';
?>
<div id="content">
	<section class="row">
		<section class="col-md-12 col-sm-12">
			<h3>Cadastro de clientes</h3>
		</section>
	</section>
	<hr />
	<form enctype="multipart/form-data">
		<div class="row">
			<div class="col-md-3 form-group">
				<label>Tipo de cadastro: <span class="required-alert">*</span></label>
				<select class="form-control" name="tpdoc" id="tpdoc">
					<option></option>
					<option value="1">Pessoa Física</option>
					<option value="2">Pessoa Jurídica</option>
				</select>
			</div>
			<section class="col-md-2 form-group">
				<label>Status: <span class="required-alert">*</span></label>
				<select class="form-control" name="status" id="status">
					<option value="0">ATIVO</option>
					<option value="1">DESATIVADO</option>
				</select>
			</section>
		</div>
		<div id="dadosiniciais">
			<div class="row">
				<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-user sub-title-icon"></span> Dados iniciais</b></h5><hr /></div>
			</div>
			<div class="row">
				<div class="col-md-2 form-group">
					<label>Código:</label>
					<input type="text" class="form-control" name="codsac" id="codsac" maxlength="5" readonly />
				</div>
				<div class="col-md-3 form-group">
					<label id="titulotipodoc">Documento:</label> <span class="required-alert">*</span>
					<input type="text" class="form-control" name="documento" id="documento" />
				</div>
				<div class="col-md-2 form-group">
					<label>Área de atuação:</label> <span class="required-alert">*</span>
					<select class="form-control" name="area_atuacao" id="area_atuacao">
						<option value=""></option>
						<option value="1">Alimentação</option>
						<option value="2">Assessoria</option>
						<option value="3">Associação</option>
						<option value="4">Clube</option>
						<option value="5">Contabilidade</option>
						<option value="6">Educação</option>
						<option value="7">Igreja</option>
						<option value="8">Imobiliária</option>
						<option value="9">Moradia</option>
						<option value="10">Tecnologia</option>
					</select>
				</div>
				<div class="col-md-2 form-group">
					<label>Repasse: <span class="required-alert">*</span></label>
					<select class="form-control" name="repasse" id="repasse">
						<option value="1">Sim</option>
						<option value="0">Não</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-md-2 form-group">
					<label>Sigla: <span class="required-alert">*</span></label>
					<input type="text" class="form-control" name="sigla-cliente" id="siglaCliente" maxlength="3" />
				</div>
				<div class="col-md-4 form-group">
					<label>Nome: <span class="required-alert">*</span></label>
					<input type="text" class="form-control" name="nome-cliente" id="nomeCliente" maxlength="45" />
				</div>
				<div class="col-md-3 form-group">
					<label>Responsável: <span class="required-alert">*</span></label>
					<input type="text" class="form-control" name="responsavel" id="responsavel" maxlength="45" />
				</div>
			</div>
			<div class="row">
				<div class="col-md-2 form-group">
					<label id="titulodatacliente">Dt. de constituição:</label> <span class="required-alert">*</span>
					<input type="text" class="form-control maskData" name="dt-constituicao" id="tipoData" />
				</div>
				<div class="col-md-2 form-group">
					<label>Cliente desde: <span class="required-alert">*</span></label>
					<input type="text" class="form-control maskData" name="dt-entrada-cliente" id="dtEntradaCliente" />
				</div>
				<div class="col-md-5 form-group">
					<label>Site: <span class="required-alert">*</span></label>
					<input type="text" class="form-control" name="site" id="site" maxlength="50" placeholder="http://cliente.setydeias.com" />
				</div>
			</div>
			<div class="row">
				<div class="col-md-2 form-group">
					<label>Imagem do cliente: <span class="required-alert">*</span></label>
					<input type="file" name="logo-customer" id="logo-customer" style="display:none;" />
					<button type="button" class="btn btn-warning" name="btn-add-image"><span class="glyphicon glyphicon-cloud-upload"></span> Clique aqui para selecionar a imagem</button>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5 form-group">
					<span class="image-error"></span>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-globe sub-title-icon"></span> Dados de Localização<hr /></b></h5></div>
		</div>
		<div class="row">
			<div class="col-md-2 form-group">
				<label>CEP: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="cep" id="cep" maxlength="8" />
			</div>
			<div class="col-md-6 form-group" style="margin: 20px 0 0 0;" id="cep-msg"></div>
		</div>
		<div class="row">
			<div class="col-md-4 form-group">
				<label>Logradouro: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="endereco" id="endereco" maxlength="35" />
			</div>
			<div class="col-md-2 form-group">
				<label>Número: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="numero" id="numero" maxlength="5" />
			</div>
			<div class="col-md-3 form-group">
				<label>Complemento:</label>
				<input type="text" class="form-control" name="complemento" id="complemento"  maxlength="10" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 form-group">
				<label>Bairro: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="bairro" id="bairro" maxlength="20" />
			</div>
			<div class="col-md-3 form-group">
				<label>Cidade: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="cidade" id="cidade" maxlength="20" />
			</div>
			<div class="col-md-1 form-group">
				<label>UF: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="uf" id="uf" maxlength="5" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-4 form-group">
				<label>Ponto de referência:</label>
				<input type="text" class="form-control" name="ponto-referencia" id="pontoReferencia" maxlength="40" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-usd sub-title-icon"></span> Informações financeiras<hr /></b></h5></div>
		</div>
		<div class="row">
			<div class="col-md-3 form-group">
				<label>Banco da conta crédito: <span class="required-alert">*</span></label>
				<select class="form-control" name="banco" id="banco">
					<option value="001">001 - Banco do Brasil</option>
					<option value="104">104 - Caixa Econômica Federal</option>
					<option value="237">237 - Bradesco S.A</option>
					<option value="341">341 - Banco Itaú S.A</option>
				</select>
			</div>
			<div class="col-md-2 form-group">
				<label>Agência: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="agencia" id="agencia" maxlength="8" />
			</div>
			<div class="col-md-2 form-group" id="op" style="display:none;"></div>
			<div class="col-md-2 form-group">
				<label>Número da conta: <span class="required-alert">*</span></label>
				<input type="text" class="form-control" name="conta" id="conta" maxlength="12" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 form-group">
				<label>Isento de mensalidade: <span class="required-alert">*</span></label><br/>
				<label class="radio-inline">
					<input type="radio" name="isento-mensalidade" id="isentoMensalidade" value="1"> SIM
				</label>
				<label class="radio-inline">
					<input type="radio" name="isento-mensalidade" id="isentoMensalidade" value="0" checked> NÃO
				</label>
			</div>
			<div class="col-md-4 form-group">
				<label>Isento da tarifa de Débito Automático: <span class="required-alert">*</span></label><br/>
				<label class="radio-inline">
					<input type="radio" name="isento-debito-automatico" id="isentoDebitoAutomatico" value="1" checked> SIM
				</label>
				<label class="radio-inline">
					<input type="radio" name="isento-debito-automatico" id="isentoDebitoAutomatico" value="0"> NÃO
				</label>
			</div>
			<div class="col-md-3 form-group">
				<label>Isento de Subst. Tributário: <span class="required-alert">*</span></label><br/>
				<label class="radio-inline">
					<input type="radio" name="isento-sub-trib" id="isentoSubTrib" value="0" checked> SIM
				</label>
				<label class="radio-inline">
					<input type="radio" name="isento-sub-trib" id="isentoSubTrib" value="1"> NÃO
				</label>
			</div>
		</div>
		<div class="row">		
			<div class="col-md-4 form-group">
				<label style="display:block;">Mensalidade (%): <span class="required-alert">*</span> </label>
				<div style="margin:10px 0;">
					<label class="radio-inline">
						<input type="radio" name="tipoMensalidade" value="1" checked /> (%) Salário Mínimo
					</label>
					<label class="radio-inline">
						<input type="radio" name="tipoMensalidade" value="2" /> (R$) Valor Fixo
					</label>
				</div>
				<input type="text" class="form-control" name="mensalidade" id="mensalidade" value="50" maxlength="3" />
				<span>(equivalente a R$ <span id="valorMensalidade"></span>)</span>
				<span style="display:none;" name="valor_mensalidade_cliente"></span>
			</div>
		</div>
		<div class="row">
			<div class="col-md-2 form-group">
				<label>Tipo de tarifa: <span class="required-alert">*</span></label>
				<select class="form-control" name="tipo-tarifa" id="tipoTarifa">
					<option value="1" checked>Padrão</option>
					<option value="2">Personalizada</option>
				</select>
			</div>
		</div>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="#bancobrasil" aria-controls="bancobrasil" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-tag"></span> Banco do Brasil</a></li>
			<li role="presentation"><a href="#cef" aria-controls="cef" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-tag"></span> Caixa Econômica Federal</a></li>
			<li role="presentation"><a href="#brd" aria-controls="brd" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-tag"></span> Bradesco</a></li>
		</ul>
		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="bancobrasil" style="margin:10px 0;">
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon"><b>Carteira 17-04</b> (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="bb17" name="bb17" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon"><b>Cobrança LQR</b> (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="bblqr" name="bblqr" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon"><b>Carteira 17-11</b> (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="bb1711" name="bb1711" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon"><b>Carteira 17-05</b> (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="bb1705" name="bb1705" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon"><b>Carteira 18</b> (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="bb" name="bb" />
						</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="cef" style="margin:10px 0;">
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon">AUTO AT. (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="cefint" name="cefint" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon">AGÊNCIA (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="cefagn" name="cefagn" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon">COMPENSAÇÃO (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="cefcomp" name="cefcomp" />
						</div>
					</div>
				</div>
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon">LOTERIAS (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="ceflot" name="ceflot" />
						</div>
					</div>
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon">Conta Transitória (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="cefct" name="cefct" />
						</div>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="brd" style="margin:10px 0;">
				<div class="row" style="padding:0 20px;">
					<div class="form-group">
						<div class="input-group col-md-5">
							<div class="input-group-addon">Conta Transitória (R$)</div>
							<input type="text" class="form-control tarifas" style="text-align:right;" id="brdct" name="brdct" />
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-phone sub-title-icon"></span> Dados de contato<hr /></b></h5></div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						Telefones <span class="required-alert">*</span> <b>(<span id="qtdeDeTelefones"></span>)</b>
						<span class="pull-right">
							<button type="button" class="btn btn-primary btn-sm" style="margin: -10px 0;" id="add-telefone" data-toggle="modal" data-target="#modalCadastroTelefone"><span class="glyphicon glyphicon-plus-sign"></span></button>
						</span>
					</div>
					<div class="panel-body" class="col-md-12" style="padding:10px;">
						<div class="row">
							<div class="col-md-12" id="list-telefones"></div>
						</div>
					</div>
				</div>
			</div>	
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						Emails <span class="required-alert">*</span> <b>(<span id="qtdeDeEmails"></span>)</b>
						<span class="pull-right">
							<button type="button" class="btn btn-primary btn-sm" style="margin: -10px 0;" id="add-email" data-toggle="modal" data-target="#modalCadastroEmail"><span class="glyphicon glyphicon-plus-sign"></span></button>
						</span>
					</div>
					<div class="panel-body" class="col-md-12" style="padding:10px;">
						<div class="row">
							<div class="col-md-12" id="list-emails"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12"><h5 class="sub-title"><b><span class="glyphicon glyphicon-send sub-title-icon"></span> Opções de envio<hr /></b></h5></div>
		</div>
		<section class="row col-md-12">
			<section>
				<label for="retorno-por-email">
					<input type="checkbox" name="retorno-por-email" id="retorno-por-email" /> Receber retorno por email
				</label>
			</section>
			<section class="row col-md-12">
				<label for="retorno-cnab240">
					<input type="checkbox" name="retorno-cnab240" id="retorno-cnab240" /> Arquivo padrão CNAB240
				</label>
			</section>
		</section>
		<hr />
		<section class="row">
		<hr />
			<section class="col-md-12">
				<span><p><span class="required-alert">*</span> Dados obrigatórios</p></span>
			</section><br/><br/>
			<section class="col-md-12">
				<section id="geral-alert"></section>
			</section>
			<section class="col-md-12">
				<button class="btn btn-primary" type="button" id="cadastrar-cliente"><span class="glyphicon glyphicon-plus-sign"></span> Cadastrar</button>
				<button class="btn btn-danger" type="reset"><span class="glyphicon glyphicon-trash"></span> Limpar tudo</button>
			</section>
		</section>
	</form>
	<!-- TELEFONE -->
	<div id="modalCadastroTelefone" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Cadastrar telefone</h4>
				</div>
				<div class="modal-body">
					<form>
						<div class="row">
							<div class="col-md-12">
								<label>Descrição: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="descricao-telefone" placeholder="Fixo, celular, etc" />
							</div>
						</div><br/>
						<div class="row">
							<div class="col-md-12">
								<label>Tipo de telefone: <span class="required-alert">*</span></label>
								<select class="form-control" name="tipo-telefone" id="tipoTelefone">
									<option></option>
									<option value="1">Fixo</option>
									<option value="2">Celular</option>
								</select>
							</div>
							<div class="col-md-12">
								<label>Número: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="numero-telefone" />
							</div>
						</div>
					</form>
					<div class="row">
						<div class="col-md-12" id="msg-error-telefone"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
					<button type="button" class="btn btn-primary" id="cadastrar-telefone"><span class="glyphicon glyphicon-plus-sign"></span> Cadastrar</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- ATUALIZAR TELEFONE -->
	<div id="modalUpdateTelefone" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Atualizar telefone</h4>
				</div>
				<div class="modal-body">
					<form>
						<div class="row">
							<div class="col-md-12">
								<label>Descrição: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="update-descricao-telefone" placeholder="Fixo, celular, etc" />
							</div>
						</div><br/>
						<div class="row">
							<div class="col-md-12">
								<label>Tipo de telefone: <span class="required-alert">*</span></label>
								<select class="form-control" name="tipo-telefone" id="update-tipoTelefone">
									<option></option>
									<option value="1">Fixo</option>
									<option value="2">Celular</option>
								</select>
							</div>
							<div class="col-md-12">
								<label>Número: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="update-numero-telefone" />
							</div>	
						</div>
					</form>
					<div class="row">
						<div class="col-md-12" id="msg-error-telefone"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
					<button type="button" class="btn btn-primary" id="update-telefone-btn"><span class="glyphicon glyphicon-refresh"></span> Atualizar</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- EMAIL -->
	<div id="modalCadastroEmail" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Cadastrar email</h4>
				</div>
				<div class="modal-body">
					<form>
						<div class="row">
							<div class="col-md-12">
								<label>Descrição: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="descricao-email" />
							</div>
							<div class="col-md-12">
								<label>Email: <span class="required-alert">*</span></label>
								<input type="email" class="form-control" id="email" placeholder="nome@dominio.com.br" />
							</div>
						</div>
					</form>
					<div class="row">
						<div class="col-md-12" id="msg-error-email"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
					<button type="button" class="btn btn-primary" id="cadastrar-email"><span class="glyphicon glyphicon-plus-sign"></span> Cadastrar</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<!-- ATUALIZAR EMAIL -->
	<div id="modalUpdateEmail" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Atualizar email</h4>
				</div>
				<div class="modal-body">
					<form>
						<div class="row">
							<div class="col-md-12">
								<label>Descrição: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="update-descricao-email" />
							</div>
							<div class="col-md-12">
								<label>Email: <span class="required-alert">*</span></label>
								<input type="text" class="form-control" id="update-email" placeholder="nome@dominio.com.br" />
							</div>
						</div>
					</form>
					<div class="row">
						<div class="col-md-12" id="msg-error-email"></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
					<button type="button" class="btn btn-primary" id="update-email-btn"><span class="glyphicon glyphicon-refresh"></span> Atualizar</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
</div>
<script src="/painel/dist/cadastroCliente.js"></script>