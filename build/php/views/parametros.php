<?php 
	include_once '../header.php';
	include_once '../functions.php';
?>
<div id="content">
	<h3>Gerenciar Parâmetros</h3>
	<hr />
	<div>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#tarifas" aria-controls="tarifas" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-usd"></span> Tarifas</a></li>
			<li role="presentation"><a href="#server" aria-controls="server" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-send"></span> Envio de arquivos para o servidor</a></li>
			<li role="presentation"><a href="#convert" aria-controls="convert" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-refresh"></span> Conversão de Arquivos</a></li>
			<li role="presentation"><a href="#path" aria-controls="path" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-folder-open"></span> Diretórios</a></li>
			<li role="presentation"><a href="#salario" aria-controls="salario" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-usd"></span> Salário Mínimo</a></li>
			<li role="presentation" class="active"><a href="#conveniocobranca" aria-controls="conveniocobranca" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-briefcase"></span> Convênios de Cobrança</a></li>
			<li role="presentation"><a href="#serverparams" aria-controls="server" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-cloud-upload"></span> Servidor nas Nuvens</a></li>
			<li role="presentation"><a href="#emailparams" aria-controls="emailparams" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-envelope"></span> Configurações de email</a></li>
			<li role="presentation"><a href="#passwordparams" aria-controls="passwordparams" role="tab" data-toggle="tab"><span class="glyphicon glyphicon-lock"></span> Senha Padrão</a></li>
		</ul>
		<!-- Tab panes -->
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane" id="tarifas">
				<form>
					<h4>Tarifas</h4>
					<div class="row" style="padding:10px 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Banco do Brasil - <b>Carteira 17-04</b> (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="bb17" name="bb17" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Banco do Brasil - <b>Carteira 17-05</b> (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="bb1705" name="bb1705" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Banco do Brasil - <b>Carteira 17-11</b> (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="bb1711" name="bb1711" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Banco do Brasil - <b>Carteira 18</b> (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="bb" name="bb" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Banco do Brasil - <b>Cobrança LQR (R$)</b></div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="bblqr" name="bblqr" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Bradesco (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="brd" name="brd" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">CEF - AUTO AT. (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="cefint" name="cefint" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">CEF - AGÊNCIA (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="cefagn" name="cefagn" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">CEF - COMPENSAÇÃO (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="cefcomp" name="cefcomp" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">CEF - LOTERIAS (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="ceflot" name="ceflot" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">CEF - Conta Transitória (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="cefct" name="cefct" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Tarifa Débito em Conta (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="debito_conta" name="debito_conta" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Impressão gráfica (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="impressao_grafica" name="impressao_grafica" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Impressão gráfica <b>cliente</b> (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="impressao" name="impressao" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Entrega individual (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="entrega_individual" name="entrega_individual" />
							</div>
						</div>
					</div>
					<div class="row" style="padding:0 20px;">
						<div class="form-group">
							<div class="input-group col-md-5">
								<div class="input-group-addon">Entrega de pacote único (R$)</div>
								<input type="text" class="form-control fmt-money" style="text-align:right;" id="entrega_unica" name="entrega_unica" />
							</div>
						</div>
					</div>
					<div id="msg-update-tax"></div>
					<div class="row" style="padding:0 20px;">
						<button type="button" class="btn btn-primary btn-sm" name="update-tax" id="update-tax">Atualizar Tarifas</button>
					</div>
				</form>
			</div>
			<div role="tabpanel" class="tab-pane" id="convert">
				<div class="row">
					<div class="col-md-6">
						<h4>Conversão de arquivos na transferência para o servidor</h4>
						<form>
							<label class="radio-inline">
							<input type="radio" name="convert-files" value="1"> Ativado
						</label>
						<label class="radio-inline">
							<input type="radio" name="convert-files" value="0"> Desativado
						</label>
						<div id="msg-convert" style="margin: 10px 0 0 0;"></div>
						<div class="row">
							<div class="col-md-5">
								<button type="button" class="btn btn-primary btn-sm" id="upd-conversor">Atualizar Status</button>
							</div>
						</div>
					</form>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="salario">
				<div class="row">
					<div class="col-md-5">
						<form>
							<h4>Salário mínimo (atual):</h4>
							<label>
								<input type="text" id="salario-param" class="form-control" maxlength="8" />
							</label>
							<div id="msg-money"></div>
							<div class="row">
							<div class="col-md-5">
								<button type="button" class="btn btn-primary btn-sm" id="upd-money">Atualizar Salário</button>
							</div>
						</div>
					</form>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="emailparams">
				<div class="row">
					<div class="col-md-6">
						<h4>Configurações de email</h4>
						<hr />
						<form id="form-update-mail-params">
							<div class="form-group">
								<label>
										Nome da empresa
										<input type="text" class="form-control" id="mail_sty_name" maxlength="60" />
								</label>
							</div>
							<div class="form-group">
								<label>
										Host SMTP
										<input type="text" class="form-control" id="mail_host_smtp" maxlength="60" />
								</label>
							</div>
							<div class="form-group">
								<label>
										Porta SMTP
										<input type="text" class="form-control" id="mail_port" maxlength="5" />
								</label>
							</div>
							<div class="form-group">
								<label>
										Email
										<input type="text" class="form-control" id="mail_email" maxlength="60" />
								</label>
							</div>
							<div class="form-group">
								<a data-target="#" id="toggle-password-area">Trocar senha</a>
							</div>
							<div class="form-group" id="password-group">
								<label>
										Senha atual
										<input type="password" class="form-control" id="mail_password" maxlength="30" />
								</label>
								<label>
										Nova senha
										<input type="password" class="form-control" id="mail_new_password" maxlength="30" />
								</label>
							</div>
							<div id="error-area-mail-params"></div>
							<div>
								<button id="btn-update-mail-params" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span> Salvar alterações</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="path">
				<div class="col-md-10">
					<form>
						<h4>Diretórios</h4>
						<div class="alert alert-info"><b>AVISO:</b> Utilize <b>"\"</b> para separar diretórios!</div>
						<p><b>Gerenciamento de pastas de Retornos</b></p>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta para processamento de retornos</div>
									<input type="text" class="form-control" id="pathtoprocessret" name="pathtoprocessret" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta de retornos processados</div>
									<input type="text" class="form-control" id="pathtoprocessedret" name="pathtoprocessedret" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta para retornos originais</div>
									<input type="text" class="form-control" id="pathtooriginalret" name="pathtooriginalret" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta de arquivos pagos em cheque</div>
									<input type="text" class="form-control" id="pathtocheque" name="pathtocheque" />
							</div>
						</div>
						<p><b>Gerenciamento de pastas de Remessas</b></p>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta para processamento de remessas para gráfica</div>
									<input type="text" class="form-control" id="pathtoprocessrem" name="pathtoprocessrem" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta de remessas processadas para gráfica</div>
									<input type="text" class="form-control" id="pathtoprocessedrem" name="pathtoprocessedrem" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta de remessas originais para gráfica</div>
									<input type="text" class="form-control" id="pathtooringial" name="pathtooringial" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta para processamento de remessas para o banco</div>
									<input type="text" class="form-control" id="pathrembanco" name="pathrembanco" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta backup de remessas para banco a processar</div>
									<input type="text" class="form-control" id="pathRemBanco" name="pathRemBanco" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta de remessas para o banco processadas</div>
									<input type="text" class="form-control" id="pathrembancoproc" name="pathrembancoproc" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta de remessas para o banco originais</div>
									<input type="text" class="form-control" id="pathrembancoorig" name="pathrembancoorig" />
							</div>
						</div>
						<p><b>Gerenciamento de pastas de processos auxiliares</b></p>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Banco ADM77777</div>
									<input type="text" class="form-control" id="bancoadm77777" name="bancoadm77777" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta dos arquivos de reposição da base</div>
									<input type="text" class="form-control" id="pathreplacementfiles" name="pathreplacementfiles" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Conta Transitória</div>
									<input type="text" class="form-control" id="contatransitoria" name="contatransitoria" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Laboratório</div>
									<input type="text" class="form-control" id="laboratorio" name="laboratorio" />
							</div>
						</div>
						<div class="form-group">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Clientes</div>
									<input type="text" class="form-control" id="path_clientes" name="path_clientes" />
							</div>
						</div>
						<div id="msg-dir"></div>
						<div class="row">
							<div class="col-md-12" style="margin: 0 0 10px 0;">
								<button type="button" class="btn btn-primary btn-sm" id="btncad-dir" name="btnPaths">Atualizar Diretórios</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="server">
				<div class="row">
					<div class="col-md-5">
						<h4>Status de envio para o Servidor nas Nuvens</h4>
						<form>
							<div class="row">
								<div class="col-md-12" style="margin: 0 0 15px 0;">
									<label class="radio-inline">
									<input type="radio" name="enviar-servidor" value="1"> Ativado
								</label>
								<label class="radio-inline">
									<input type="radio" name="enviar-servidor" value="0"> Desativado
								</label>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div id="msg-update-server"></div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-5">
								<button type="button" class="btn btn-primary btn-sm" id="upd-status">Atualizar Status</button>
							</div>
						</div>
					</form>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane active" id="conveniocobranca">
				<div class="row">
					<div class="col-md-6">
						<h4>Convênios de Cobrança</h4>
						<hr />
						<form name="add-convenio-cobranca">
							<div class="form-group">
								<label>
									Banco
									<select name="banco" class="form-control">
										<option value="001">001 - Banco do Brasil</option>
										<option value="104">104 - Caixa Econômica Federal</option>
										<option value="237">237 - Bradesco</option>
										<option value="341">341 - Itaú</option>
									</select>
								</label>
							</div>
							<div class="form-group row">
								<label class="col-md-4">
									Agência
									<input type="text" name="agencia-convenio" class="form-control" maxlength="6" />
								</label>
							</div>
							<div class="form-group row">
								<label class="col-md-4">
									Conta
									<input type="text" name="conta-convenio" class="form-control" maxlength="13" />
								</label>
								<label class="col-md-3" style="display:none;">
									Operação
									<select name="op-convenio" class="form-control">
										<option>--</option>
										<option value="001">001</option>
										<option value="002">002</option>
										<option value="003">003</option>
										<option value="006">006</option>
										<option value="007">007</option>
										<option value="013">013</option>
										<option value="022">022</option>
									</select>
								</label>
							</div>
							<div class="form-group row">
								<label class="col-md-4">
									Nº do convênio
									<input type="text" name="convenio" class="form-control" maxlength="7" />
								</label>
							</div>
							<div class="row">
								<label class="form-group col-md-2">
									Carteira
									<input type="text" name="carteira-convenio" class="form-control" maxlength="2" />
								</label>
								<label class="form-group col-md-2">
									Variação
									<input type="text" name="variacao-convenio" class="form-control" maxlength="3" />
								</label>
							</div>
							<div class="form-group">
								<label>
									Tipo de convênio: 
									<label class="radio-inline">
										<input type="radio" name="tipo-convenio" value="1" checked="checked" /> Setydeias
									</label>
									<label class="radio-inline">
										<input type="radio" name="tipo-convenio" value="2" /> Próprio
									</label>
								</label>
							</div>
							<div class="row">
								<div class="form-group col-md-6" id="customer-group" style="display:none;">
									Cliente 
									<select name="customer-pathname" class="form-control"></select></label>
								</div>
							</div>
							<div class="form-group">
								<label>
									<input type="checkbox" name="make-this-pattern" />
									Tornar este convênio padrão
								</label>
							</div>
							<div class="form-group">
								<label>
									<input type="checkbox" name="check-file" />
									Checar arquivo de reposição
								</label>
							</div>
							<div class="error-area"></div>
							<button class="btn btn-primary" type="button" name="add-convenio-cobranca"><span class="glyphicon glyphicon-plus-sign"></span> Cadastrar</button>
						</form>
					</div>
					<div class="col-md-6" style="margin: 48px 0">
						<ul class="list-group" name="convenio-list"></ul>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="serverparams">
				<div class="row">
					<div class="col-md-6">
						<h4>Parâmetros de conexão com o servidor nas nuvens</h4>
						<hr />
						<form id="form-update-svn-params">
							<div class="form-group">
								<label>
										IP do Servidor
										<input type="text" class="form-control" id="svn_ip" maxlength="20" />
								</label>
							</div>
							<div class="form-group">
								<label>
										Login FTP
										<input type="text" class="form-control" id="svn_login_ftp" maxlength="20" />
								</label>
							</div>
							<div class="form-group">
								<a data-target="#" id="toggle-password-area">Trocar senha</a>
							</div>
							<div class="form-group" id="password-group">
								<label>
										Senha atual
										<input type="password" class="form-control" id="svn_password" maxlength="30" />
								</label>
								<label>
										Nova senha
										<input type="password" class="form-control" id="svn_new_password" maxlength="30" />
								</label>
							</div>
							<div id="error-area-svn-params"></div>
							<div>
								<button id="btn-update-svn-params" type="button" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span> Salvar alterações</button>
							</div>
							</form>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane" id="passwordparams">
					<div class="row">
						<div class="col-md-6">
							<h4>Senha Padrão</h4>
							<hr />
							<div class="form-group" id="password-group">
								<label>
									Senha atual
									<input type="text" class="form-control" disabled="disabled" id="master_pass" maxlength="32" />
								</label>
								<label>
									Nova senha
									<input type="text" class="form-control" id="new_master_pass" maxlength="32" />
								</label>
							</div>
							<button class="btn btn-primary" type="button" name="update-master-pass">
								<span class="glyphicon glyphicon-refresh"></span> Alterar
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-5" id="clientes" style="padding: 0 30px 0 0;float:right;"></div>
			</div>
		</div>
	</div>
</div>
<script src="/painel/dist/parametros.js"></script>