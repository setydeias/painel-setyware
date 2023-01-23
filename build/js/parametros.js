const server = require('../api/server')
const customers = require('../api/customer')
const parametros = require('../api/parameters')
const convenios = require('../api/convenios')
const validation = require('../modules/validation')
const util = require('../modules/util')
const modal = require('../modules/modal')
const format = new Format()
toastr.options = { "positionClass": "toast-bottom-right" }

const callListItem = (data) => {
	return `
		<img src="/painel/build/images/${data.BANCO}.jpg" width="15" alt="logo banco" /> 
		<span class="numero-convenio">${data.CONVENIO}</span>
		<a><span title="Remover convênio" style="cursor:pointer;margin:0 0 0 10px" class="pull-right glyphicon glyphicon-trash delete-convenio"></span></a>
		${ data.CHECAR_ARQUIVO_REPOSICAO === 'N' 
			? '<a><span title="Checar arquivo de reposição no processamento de remessas para o banco" style="margin:0 0 0 10px;cursor:pointer;" class="check-file-reposicao pull-right glyphicon glyphicon-eye-close"></span></a>'
			: '<span title="Checagem habilitada" style="margin:0 0 0 10px;color:green;" class="pull-right glyphicon glyphicon-eye-open"></span>'
		}
		<a><span title="Editar convênio" style="cursor:pointer;margin:0" class="pull-right glyphicon glyphicon-edit edit-convenio"></span></a>
		${ data.PADRAO === 'S' 
			? '<span title="Convênio padrão" class="glyphicon glyphicon-ok-circle pull-right" style="margin: 0 10px;color: green;"></span>' 
			: '<a><span title="Tornar padrão" class="glyphicon glyphicon-transfer pull-right make-pattern" style="cursor:pointer;margin: 0 10px;"></span></a>'
		}
		<span class="pull-right badge">${data.MANTENEDOR}</span>
	`
}

/*
* Atualização do status de envio de retorno para o servidor nas nuvens
*/

const updateServer = () => {
	const status = document.querySelector('input[name=enviar-servidor]:checked').value
	
	server.sendRetorno(status)
	.then( res => {
		const msgContent = document.getElementById('msg-update-server')
		msgContent.innerHTML = ""
		try {
			const { success, status } = res.data

			msgContent.insertAdjacentHTML('beforeend', `<div class="alert alert-${success ? 'success' : 'danger'}">${status}</div>`)
		} catch (e) {
			console.log(new Error(`Houve algum erro no processamento: ${e}`))
		}
	})
	.catch( err => console.log(new Error(`Houve algum erro no processamento: ${err}`)) )
}

const btnUpServer = document.getElementById('upd-status')
EventHandler.bind(btnUpServer, 'click', updateServer)

/*
* Conversão dos arquivos na hora da transferência
*/

const updateConversorStatus = () => {
	const status = document.querySelector('input[name=convert-files]:checked').value
	
	server.updateConversorStatus(status)
	.then( res => {
		const msgContent = document.getElementById('msg-convert')
		msgContent.innerHTML = ""
		try {
			const { success, status } = res.data

			msgContent.insertAdjacentHTML('beforeend', `<div class="alert alert-${success ? 'success' : 'danger'}">${status}</div>`)
		} catch (e) {
			console.log(new Error(`Houve algum erro no processamento: ${e}`));
		}
	})
	.catch( err => console.log(err) )
}

const btnConvert = document.getElementById('upd-conversor')
EventHandler.bind(btnConvert, 'click', updateConversorStatus)

/*
* Alteração de diretórios
*/

const processamento = document.getElementById('pathtoprocessret')
const retornos_processados = document.getElementById('pathtoprocessedret')
const retornos_originais = document.getElementById('pathtooriginalret')
const pagamentos_cheque = document.getElementById('pathtocheque')
const processamento_remessa = document.getElementById('pathtoprocessrem')
const remessas_processadas = document.getElementById('pathtoprocessedrem')
const remessas_originais = document.getElementById('pathtooringial')
const remessa_banco = document.getElementById('pathrembanco')
const remessa_banco_processadas = document.getElementById('pathrembancoproc')
const remessa_banco_originais = document.getElementById('pathrembancoorig')
const pasta_backup_remessa_banco = document.getElementById('pathRemBanco')
const pasta_reposicao_base = document.getElementById('pathreplacementfiles')
const conta_transitoria = document.getElementById('contatransitoria')
const laboratorio = document.getElementById('laboratorio')
const path_clientes = document.getElementById('path_clientes')
const banco_adm = document.querySelector('#bancoadm77777')

const updateDirectories = () => {

	const directories = {
		processamento: processamento.value, 
		retornos_processados: retornos_processados.value, 
		retornos_originais: retornos_originais.value, 
		pagamentos_cheque: pagamentos_cheque.value, 
		processamento_remessa: processamento_remessa.value, 
		remessas_processadas: remessas_processadas.value, 
		remessas_originais: remessas_originais.value, 
		remessa_banco: remessa_banco.value, 
		remessa_banco_processadas: remessa_banco_processadas.value, 
		remessa_banco_originais: remessa_banco_originais.value, 
		pasta_backup_remessa_banco: pasta_backup_remessa_banco.value,
		pasta_reposicao_base: pasta_reposicao_base.value,
		conta_transitoria: conta_transitoria.value,
		laboratorio: laboratorio.value,
		clientes: path_clientes.value,
		banco_adm: banco_adm.value
	}
	
	server.updateDirectories(directories)
	.then( res => {
		const msgContent = document.getElementById('msg-dir')
		msgContent.innerHTML = ""
		try {
			const { success, status } = res.data

			msgContent.insertAdjacentHTML('beforeend', `<div class="alert alert-${success ? 'success' : 'danger'}">${status}</div>`)
		} catch (e) {
			console.log(new Error(`Houve algum erro no processamento: ${e}`))
		}	
	})
	.catch( err => console.log(new Error(`Houve algum erro no processamento: ${err}`)) )
}

const btnCadDir = document.getElementById('btncad-dir')
EventHandler.bind(btnCadDir, 'click', updateDirectories)

/*
* Atualiza o salário mínimo
*/

const mensalidade = document.getElementById('salario-param')

const updateSalario = () => {
	server.updateSalario(mensalidade.value)
	.then( res => {
		const msgContent = document.getElementById('msg-money')
		msgContent.innerHTML = ""
		try {
			const { success, status } = res.data

			msgContent.insertAdjacentHTML('beforeend', `<div class="alert alert-${success ? 'success' : 'danger'}">${status}</div>`)
		} catch (e) {
			console.log(new Error(`Erro no processamento de informações: ${e}`));
		}
	})
	.catch( err => console.log(new Error(`Erro no processamento de informações: ${err}`)) )
}

const btnUpMoney = document.getElementById('upd-money')
EventHandler.bind(btnUpMoney, 'click', updateSalario)

/*
* Parâmetros do Servidor nas Nuvens
*/

const svn_ip = document.querySelector('#svn_ip')
const svn_login_ftp = document.querySelector('#svn_login_ftp')
const password_group = document.querySelector('#password-group')
const svn_password = document.querySelector('#svn_password')
const svn_new_password = document.querySelector('#svn_new_password')
password_group.style.display = 'none'

//Toggle
const btnToggleArea = document.querySelector('a[id=toggle-password-area]')
EventHandler.bind(btnToggleArea, 'click', () => password_group.style.display = password_group.style.display === 'block' ? 'none' : 'block' )

const btnUpdateSVNParams = document.querySelector('button[id=btn-update-svn-params]')
EventHandler.bind(btnUpdateSVNParams, 'click', () => {
	const errorArea = document.querySelector('#error-area-svn-params')
	errorArea.innerHTML = ""

	if ( svn_ip.value === '' ) {
		return errorArea.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">IP do Servidor é obrigatório</section>')
	}

	if ( svn_login_ftp.value === '' ) {
		return errorArea.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">Login FTP é obrigatório</section>')
	}
	
	if ( (svn_password.value !== '' && svn_new_password.value === '') || (svn_password.value === '' && svn_new_password.value !== '') ) {
		return errorArea.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">Ao informar uma senha, a outra é obrigatória</section>')
	}

	const obj = {
		svn_ip: svn_ip.value,
		svn_login_ftp: svn_login_ftp.value,
		svn_password: svn_password.value,
		svn_new_password: svn_new_password.value
	}
	
	server.updateSVNparams(obj)
	.then( res => errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-success">Parâmetros atualizados com sucesso</section>`) )
	.catch( err => {
		const { error } = err.response.data
		errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error}</section>`)
	})
})

/*
* Parâmetros de email
*/

const mail_sty_name = document.querySelector('#mail_sty_name')
const mail_host_smtp = document.querySelector('#mail_host_smtp')
const mail_email = document.querySelector('#mail_email')
const mail_port = document.querySelector('#mail_port')
const mail_password = document.querySelector('#mail_password')
const mail_new_password = document.querySelector('#mail_new_password')

const btnUpdateMailParams = document.querySelector('#btn-update-mail-params')
EventHandler.bind(btnUpdateMailParams, 'click', () => {
	const errorArea = document.querySelector('#error-area-mail-params')
	errorArea.innerHTML = ""
	const mail_obj = {
		mail_sty_name: mail_sty_name.value,
		mail_host_smtp: mail_host_smtp.value,
		mail_email: mail_email.value,
		mail_port: mail_port.value,
		mail_password: mail_password.value,
		mail_new_password: mail_new_password.value
	}

	server.updateMailParams(mail_obj)
	.then( res => errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-success">${res.data.message}</section>`) )
	.catch( err => errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err.response.data.error}</section>`))
})

//Preenchendo os campos
server.getParameters()
.then( response => {
	try {
		const result = response.data
		
		/*
		* TARIFAS BANCÁRIAS
		*/
		const bb = document.getElementById('bb')
		const bb17 = document.getElementById('bb17')
		const bb1705 = document.getElementById('bb1705')
		const bb1711 = document.getElementById('bb1711')
		const bblqr = document.getElementById('bblqr')
		const brd = document.getElementById('brd')
		const cefint = document.getElementById('cefint')
		const cefagn = document.getElementById('cefagn')
		const cefcomp = document.getElementById('cefcomp')
		const ceflot = document.getElementById('ceflot')
		const cefct = document.getElementById('cefct')
		const debito_conta = document.getElementById('debito_conta')
		const impressao_grafica = document.getElementById('impressao_grafica')
		const impressao = document.getElementById('impressao')
		const entrega_individual = document.getElementById('entrega_individual')
		const entrega_unica = document.getElementById('entrega_unica')

		//Máscara
		VMasker(document.querySelectorAll(".fmt-money")).maskMoney()

		const tarifas_anteriores = {
			'bb18': result.tarifas.BB18[0] * 1,
			'bb17': result.tarifas.BB17_04[0] * 1,
			'bb1705': result.tarifas.BB17_05[0] * 1,
			'bb1711': result.tarifas.BB17_11[0] * 1,
			'bblqr': result.tarifas.BB_LQR[0] * 1,
			'cef_autoat': result.tarifas.CEF_AUTOAT[0] * 1,
			'cef_agencia': result.tarifas.CEF_AGENCIA[0] * 1,
			'cef_loterias': result.tarifas.CEF_LOTERIAS[0] * 1,
			'cef_compensacao': result.tarifas.CEF_COMPENSACAO[0] * 1,
			'cef_ct': result.tarifas.CEF_CT[0] * 1,
			'brd': result.tarifas.BRD[0] * 1
		}

		bb.value = format.FormatMoney(result.tarifas.BB18[0] * 1, 'BRL')
		bb17.value = format.FormatMoney(result.tarifas.BB17_04[0] * 1, 'BRL')
		bb1705.value = format.FormatMoney(result.tarifas.BB17_05[0] * 1, 'BRL')
		bb1711.value = format.FormatMoney(result.tarifas.BB17_11[0] * 1, 'BRL')
		bblqr.value = format.FormatMoney(result.tarifas.BB_LQR[0] * 1, 'BRL')
		brd.value = format.FormatMoney(result.tarifas.BRD[0] * 1, 'BRL')
		cefint.value = format.FormatMoney(result.tarifas.CEF_AUTOAT[0] * 1, 'BRL')
		cefagn.value = format.FormatMoney(result.tarifas.CEF_AGENCIA[0] * 1, 'BRL')
		cefcomp.value = format.FormatMoney(result.tarifas.CEF_COMPENSACAO[0] * 1, 'BRL')
		ceflot.value = format.FormatMoney(result.tarifas.CEF_LOTERIAS[0] * 1, 'BRL')
		cefct.value = format.FormatMoney(result.tarifas.CEF_CT[0] * 1, 'BRL')
		debito_conta.value = format.FormatMoney(result.tarifas.DEBITO_CONTA[0] * 1, 'BRL')
		impressao.value = format.FormatMoney(result.tarifas.IMPRESSAO[0] * 1, 'BRL')
		impressao_grafica.value = format.FormatMoney(result.tarifas.IMPRESSAO_GRAFICA[0] * 1, 'BRL')
		entrega_individual.value = format.FormatMoney(result.tarifas.ENTREGA_INDIVIDUAL[0] * 1, 'BRL')
		entrega_unica.value = format.FormatMoney(result.tarifas.ENTREGA_UNICA[0] * 1, 'BRL')

		/*
		* ATUALIZAÇÃO DE TARIFAS
		*/

		const updateTaxes = () => {
			//Área de mensagens
			const msgContent = document.getElementById('msg-update-tax')
			
			const taxes = {
				bb: bb.value || 0,
				bb17: bb17.value || 0,
				bb1705: bb1705.value || 0,
				bb1711: bb1711.value || 0,
				bblqr: bblqr.value || 0,
				brd: brd.value || 0,
				cefint: cefint.value || 0,
				cefagn: cefagn.value || 0,
				cefcomp: cefcomp.value || 0,
				ceflot: ceflot.value || 0,
				cefct: cefct.value || 0,
				tarifas_originais: tarifas_anteriores,
				debito_conta: debito_conta.value || 0,
				impressao: impressao.value || 0,
				impressao_grafica: impressao_grafica.value || 0,
				entrega_individual: entrega_individual.value || 0,
				entrega_unica: entrega_unica.value || 0
			}

			server.updateTaxes(taxes)
			.then( res => {
				try {
					const { success, status } = res.data

					msgContent.innerHTML = ""
					msgContent.insertAdjacentHTML('beforeend', `<div class="alert alert-${success ? 'success' : 'danger'}">${status}</div>`)
				} catch (e) {
					console.log(new Error(`Erro no processamento de informações: ${e}`))	
				}
			})
			.catch( err => console.log(new Error(`Erro no processamento de informações: ${err}`)) )
		}

		const btnUpTar = document.getElementById('update-tax')
		EventHandler.bind(btnUpTar, 'click', updateTaxes)
		
		/*
		* STATUS ENVIO PARA SERVIDOR
		*/

		Array.from(document.querySelectorAll(`input[name=enviar-servidor]`))
						.map( radio => radio.checked = radio.value == result.sendStatus.ENVIAR_SERVIDOR[0] )

		/*
		* STATUS DA CONVERSÃO DO ARQUIVO
		*/

		Array.from(document.querySelectorAll(`input[name=convert-files]`))
						.map( radio => radio.checked = radio.value == result.convertStatus.CONVERTER_ARQUIVOS[0] )

		/*
		* MENSALIDADES
		*/
		let salario_minimo = result.salario_minimo.SALARIO_MINIMO[0] * 1
		VMasker(mensalidade).maskMoney()
		
		mensalidade.value = format.FormatMoney(salario_minimo, 'BRL')

		/*
		* DIRETORIOS
		*/
		const { dir } = result
		processamento.value = dir.PROCESSAMENTO_RETORNOS[0]
		retornos_processados.value = dir.RETORNOS_PROCESSADOS[0]
		retornos_originais.value = dir.RETORNOS_ORIGINAIS[0]
		pagamentos_cheque.value = dir.PAGAMENTOS_EM_CHEQUE[0]
		processamento_remessa.value = dir.PROCESSAMENTO_REMESSA_GRAFICA[0]
		remessas_processadas.value = dir.REMESSA_PROCESSADA_GRAFICA[0]
		remessas_originais.value = dir.REMESSA_ORIGINAL_GRAFICA[0]
		remessa_banco.value = dir.PROCESSAMENTO_REMESSA_BANCO[0]
		remessa_banco_processadas.value = dir.REMESSA_PROCESSADA_BANCO[0]
		remessa_banco_originais.value = dir.REMESSA_ORIGINAL_BANCO[0]
		pasta_backup_remessa_banco.value = dir.PASTA_BACKUP_REMESSA_BANCO[0]
		pasta_reposicao_base.value = dir.PASTA_ARQUIVOS_REPOSICAO_BASE[0]
		conta_transitoria.value = dir.CONTA_TRANSITORIA[0]
		laboratorio.value = dir.LABORATORIO[0]
		path_clientes.value = dir.CLIENTES[0]
		banco_adm.value = dir.BANCO_ADM77777[0]

		/*
		* Parâmetros SVN
		*/
		svn_ip.value = result.svn_params.IP_SERVER[0]
		svn_login_ftp.value = result.svn_params.FTP_LOGIN[0]

		/*
		* Parâmetros Email
		*/
		mail_sty_name.value = result.mail_params.NAME
		mail_host_smtp.value = result.mail_params.SMTP_HOST
		mail_email.value = result.mail_params.EMAIL
		mail_port.value = result.mail_params.PORT
	} catch (e) {
		console.log(new Error(`Erro no processamento de informações: ${e}`))
	}
})
.catch( err => console.log(new Error(`Erro no processamento de informações: ${err}`)) )

/*
* Convênios de cobranca
*/

//Listar convênios cadastrados
const convenioList = document.querySelector('ul[name=convenio-list]')
const getConvenios = () => {
	convenioList.innerHTML = ""

	parametros.getConvenios().then( res => {
		const { data } = res
		const listItems = data.map( item => `<li class="list-group-item">${callListItem(item)}<li>` )
		convenioList.insertAdjacentHTML('beforeend', listItems.join(''))
	})
	.catch( err => console.log(err) )
}

getConvenios()
const customer_list_data = []
//Carregando lista de clientes
const loadCustomers = () => {
	customers.get()
	.then( res => {
		const customerList = document.querySelector('select[name=customer-pathname]')
		const { data } = res
		
		const list = data
			.filter( customer => customer.STATUS === '0' )
			.map( customer => `<option value="${customer.CLI_SIGLA}">${customer.CLI_SIGLA} - ${customer.NOMSAC}</option>` )
		customerList.innerHTML = ""
		sorted_list = `<option></option>${list.sort().join('')}`
		customerList.insertAdjacentHTML('beforeend', sorted_list)
		customer_list_data.push(sorted_list)
	})
	.catch( err => console.log(err) )
}

loadCustomers()

const form = document.querySelector('form[name=add-convenio-cobranca]')
const banco = form.querySelector('select[name=banco]')
const agencia = form.querySelector('input[name=agencia-convenio]')
const conta = form.querySelector('input[name=conta-convenio]')
const operacao = form.querySelector('select[name=op-convenio]')
const convenio = form.querySelector('input[name=convenio]')
const carteira = form.querySelector('input[name=carteira-convenio]')
const variacao = form.querySelector('input[name=variacao-convenio]')
const customer = form.querySelector('select[name=customer-pathname]')
const padrao = form.querySelector('input[name=make-this-pattern]')
const checkFile = form.querySelector('input[name=check-file]')

/*
* Capturando a mudança do tipo de convênio
* para exibir a lista de clientes
*/
const toggleTipoConvenio = (event) => {
	const { target } = event
	const { name, value } = target
	const customerList = document.querySelector('select[name=customer-pathname]')

	if ( name === 'tipo-convenio' ) {
		customerList.parentNode.style.display = value === '2' ? 'block' : 'none'
	}
}

EventHandler.bind(document, 'click', toggleTipoConvenio)

/*
* Libera o campo OPERAÇÃO caso o banco selecionado seja CAIXA
*/
const toggleOpField = (event, edit = false) => {
	const { target } = event
	const { tagName, name } = target
	let opField, variacaoField

	if ( tagName === 'SELECT' && (name === 'banco' || name === 'banco_convenio') ) {
		if ( name === 'banco_convenio' ) {
			opField = 'op_convenio_edit'
			variacaoField = 'variacao_edit'
		} else {
			opField = 'op-convenio'
			variacaoField = 'variacao-convenio'
		}
		
		const op = document.querySelector(`select[name=${opField}]`) //Operação
		const variacao = document.querySelector(`input[name=${variacaoField}]`) //Variação
		const visibility = target.value !== '104' ? 'none' : 'block'
		
		op.parentNode.style.display = visibility
		variacao.parentNode.style.display = visibility === 'block' ? 'none' : 'block'
	}
}

EventHandler.bind(document, 'change', toggleOpField)

//Cadastrar convênio
const addConvenio = () => {
	const errorArea = document.querySelector('.error-area')
	const removeError = () => errorArea.innerHTML = ""
	const addError = (error) => {
		removeError()
		errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error}</section>`)
	}

	removeError()
	
	const fields = {
		banco: banco.value,
		agencia: agencia.value,
		conta: conta.value,
		convenio: convenio.value,
		carteira: carteira.value,
		variacao: variacao.value,
		op: operacao.value,
		tipo_convenio: form.querySelector('input[name=tipo-convenio]:checked').value,
		customer: customer.value,
		padrao: padrao.checked,
		checkFile: checkFile.checked
	}

	validation.convenio(fields)
	.then( res => {
		convenios.addConvenioProcessing(fields)
		.then( res => {
			const customerList = document.querySelector('select[name=customer-pathname]')
			customerList.parentNode.style.display = 'none'
			document.getElementsByName('tipo-convenio')[0].checked = true
			convenio.value = ''
			customer.value = ''
			padrao.checked = false
			checkFile.checked = false
			getConvenios()
		})
		.catch( err => addError(err.response.data.error) )
	})
	.catch( error => addError(error) )
}

const btnAddConvenio = document.querySelector('button[name=add-convenio-cobranca]')
EventHandler.bind(btnAddConvenio, 'click', addConvenio)

//Tornar convênio padrão
const doEvent = (event) => {
	const { target } = event
	const { classList } = target

	if ( classList.contains('make-pattern') || classList.contains('delete-convenio') || classList.contains('check-file-reposicao') ) {
		const convenio = target.parentNode.parentNode.querySelector('span[class=numero-convenio]')
		let modalContent
		
		if ( classList.contains('make-pattern') ) {
			modalContent = `
				<h4>Deseja tornar o convênio <span class="numero-convenio"><b>${convenio.innerHTML}</b></span> padrão?</h4>
				<hr />
				<button type="button" class="sure-action make-pattern-sure btn btn-sm btn-primary"><span class="glyphicon glyphicon-ok-sign"></span> Confirmar</button>
				<button type="button" class="exit-action btn btn-sm btn-danger"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
			`
		} else if ( classList.contains('delete-convenio') ) { 
			modalContent = `
				<h4>Deseja remover o convênio <span class="numero-convenio"><b>${convenio.innerHTML}</b></span>?</h4>
				<hr />
				<button type="button" class="sure-action delete-convenio-sure btn btn-sm btn-primary"><span class="glyphicon glyphicon-ok-sign"></span> Confirmar</button>
				<button type="button" class="exit-action btn btn-sm btn-danger"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
			`
		} else {
			modalContent = `
				<h4>Deseja verificar o arquivo de reposição do convênio <span class="numero-convenio"><b>${convenio.innerHTML}</b></span>?</h4>
				<h5 style="color:#069;">Ao fazer esta verificação os registros podem ser modificados de acordo com o arquivo no momento de processar as remessas para o banco.</h5>
				<hr />
				<button type="button" class="sure-action check-file-reposicao-sure btn btn-sm btn-primary"><span class="glyphicon glyphicon-ok-sign"></span> Confirmar</button>
				<button type="button" class="exit-action btn btn-sm btn-danger"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
			`
		}
		
		document.body.insertAdjacentHTML('beforeend', modal.open(modalContent))
	}
}

EventHandler.bind(document, 'click', doEvent, true)

//Executa a função de acordo com o modal selecionado
const executeContext = (event) => {
	const { target } = event
	const { classList } = target
	const loading = '<img src="/painel/build/images/loading.gif" alt="loading" width="15" />';
	if ( classList.contains('sure-action') ) {
		const convenio = target.parentNode.querySelector('span[class="numero-convenio"] b').innerHTML
		let action, btnText, context
		//Obtém a class da ação e o texto para aparecer quando a função estiver sendo executada
		if ( classList.contains('make-pattern-sure') ) {
			action = 'makePattern'
			btnText = 'Tornando padrão'
			context = convenio
		} else if ( classList.contains('delete-convenio-sure') ) {
			action = 'remove'
			btnText = 'Removendo convênio'
			context = convenio
		} else if ( classList.contains('check-file-reposicao-sure') ) {
			action = 'checkFileReplacement'
			btnText = 'Habilitando verificação'
			context = { convenio, checkFile: true }
		}
		
		target.innerHTML = ""
		target.insertAdjacentHTML('beforeend', `${loading} ${btnText}`)
		convenios[action](context)
		.then( res => {
			modal.close()
			getConvenios()
		})
		.catch( err => {
			toastr.error(err) 
			getConvenios()
		})
	} else if ( classList.contains('exit-action') ) {
		modal.close()
	}
}

EventHandler.bind(document, 'click', executeContext)

/*
* Editar convenio
*/ 

const createForm = (data, el) => {
	convenios.getConvenio(data.toString())
	.then( res => {
		const { data } = res
		const { BANCO, AGENCIA, CONTA, OPERACAO, TIPO, MANTENEDOR, CONVENIO, CARTEIRA, VARIACAO, PADRAO, CHECAR_ARQUIVO_REPOSICAO } = data[0]
		
		const bancos = [{'001': 'Banco do Brasil'}, {'104': 'Caixa Econômica Federal'}, {'237': 'Bradesco'}, {'341': 'Itaú'}]
		
		const formEdit = `
			<section class="form-group">
				<h4>Editar convênio</h4>
				<section>
					<span style="position: absolute;right:20px;top:20px;color:#c9c9c9;cursor:pointer;" class="glyphicon glyphicon-remove pull-right close-form-edit"></span>
				</section>
			</section>
			<hr />
			<form class="form-horizontal" style="padding: 0 10px;">
				<section class="form-group">
					<label>
						Banco
						<select name="banco_convenio" class="form-control">
							${bancos.map( banco => {
								const key = Object.keys(banco)[0]
								return `<option ${BANCO === key ? 'selected="selected"' : ''} value="${key}">${key} - ${banco[key]}</option>`
							})}
						</select>
					</label>
				</section>
				<section class="row">
					<section class="form-group">
						<label class="col-md-3">
							Agência
							<input type="text" class="form-control" name="agencia_edit" maxlength="6" value="${AGENCIA}" />
						</label>
						<label class="col-md-4">
							Conta
							<input type="text" class="form-control" name="conta_edit" maxlength="13" value="${CONTA}" />
						</label>
					</section>
				</section>
				<section class="row" >
					<section class="col-md-3 form-group" style="display:${OPERACAO ? 'block' : 'none'};">
						<b>Operação</b>
						<select class="form-control" name="op_convenio_edit" value="${OPERACAO}">
							${util.cefAllowedOperations().map( op => `<option ${OPERACAO === op ? 'selected="selected"' : ''} value="${op}">${op}</option>`).join('')}
							
						</select>
					</section>
				</section>
				<section class="row">
					<section class="form-group">
						<label class="col-md-4">
							Convênio	
							<input type="text" class="form-control" name="numero_convenio" value="${CONVENIO}" maxlength="7" placeholder="Número do convênio" />
							<input type="hidden" name="numero_original_convenio" value="${CONVENIO}" />
						</label>
					</section>
				</section>
				<section class="row">
					<section class="form-group">
						<label class="col-md-2">
							Carteira
							<input type="text" class="form-control" name="carteira_edit" maxlength="2" value="${CARTEIRA}" />
						</label>
						<label class="col-md-3" style="display:${BANCO !== '104' ? 'block' : 'none'}">
							Variação
							<input type="text" class="form-control" name="variacao_edit" maxlength="3" value="${VARIACAO || ''}" />
						</label>
					</section>
				</section>
				<section class="form-group">
					<label>
						Tipo de convênio: 
						<label class="radio-inline">
							<input type="radio" name="tipo_convenio_edit" value="1" ${ TIPO === '1' ? 'checked="checked"' : ''} /> Setydeias
						</label>
						<label class="radio-inline">
							<input type="radio" name="tipo_convenio_edit" value="2" ${ TIPO === '2' ? 'checked="checked"' : ''} /> Próprio
						</label>
					</label>
				</section>
				<section class="form-group" style="display:${TIPO === '1' ? 'none' : 'block'};">
					<select name="customer_edit" class="form-control">
						${customers.get().then( customer => {
							const { data } = customer
							const itens = data.map( customer => 
								`<option ${ MANTENEDOR === customer.CLI_SIGLA ? 'selected="selected"' : ''} value="${customer.CLI_SIGLA}">${customer.CLI_SIGLA} - ${customer.NOMSAC}</option>` )
							document.querySelector('select[name=customer_edit]')
								.insertAdjacentHTML('beforeend', `<option></option>${itens.sort()}`)
						})}
					</select>
				</section>
				<section class="form-group">
					<label><input name="padrao" type="checkbox" ${PADRAO === 'S' ? 'checked="checked"' : ''} /> Tornar padrão</label>
					<label style="display:block;"><input name="checar_arquivo_reposicao_edit" type="checkbox" ${CHECAR_ARQUIVO_REPOSICAO === 'S' ? 'checked="checked"' : ''} /> Checar arquivo de reposição</label>
				</section>
				<section class="form-group">
					<button type="button" class="pull-right btn btn-danger btn-sm close-form-edit"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
					<button type="button" name="btn-edit-convenio" style="margin: 0 5px 0 0;" class="pull-right btn btn-success btn-sm btn-edit-convenio"><span class="glyphicon glyphicon-edit"></span> Salvar</button>
				</section>
				<section class="form-group error-edit-form"></section>
				<section style="clear:both"></section>
			</form>
		`
		const hasFormAlready = (el.parentNode.querySelector('li form') || 0) !== 0
		
		if ( !hasFormAlready ) {
			el.innerHTML = ""
			el.style.border = "1px dashed #ccc"
			el.insertAdjacentHTML('beforeend', formEdit)
		} else {
			toastr.error('Edição em progresso, conclua e tente novamente')
		}
	})
	.catch( err => console.log(err) )
}

//Obtém os dados do convênio e monta o form
const getConvenioData = (event) => {
	const { target } = event
	const { classList, parentNode } = target

	if ( classList.contains('edit-convenio') ) {
		const convenio = target.parentNode.parentNode.querySelector('span[class=numero-convenio]')
		createForm(convenio.innerHTML.toString(), parentNode.parentNode)
	}
}

EventHandler.bind(document, 'click', getConvenioData)

//Fechar formulário de edição
const closeForm = (event) => {
	const { target } = event
	const { classList, parentNode } = target
	
	const setList = (el, html) => {
		el.innerHTML = ""
		el.style.border = "1px solid #ccc"
		el.insertAdjacentHTML('beforeend', html)
	}
	
	if ( classList.contains('close-form-edit') ) {
		const convenio = target.parentNode.parentNode.parentNode.querySelector('form input[name=numero_convenio]')
		convenios.getConvenio(convenio.value)
		.then( res =>  {
			const li = parentNode.parentNode.parentNode
			setList(li, callListItem(res.data[0]))
		})
		.catch( err => console.log(err) )
	}
}

EventHandler.bind(document, 'click', closeForm)

//Editar convênio
const editConvenio = (data) => {
	const btnEditConvenio = document.querySelector('button[name=btn-edit-convenio]')
	const { banco_convenio, agencia_edit, conta_edit, op_convenio_edit, numero_convenio, numero_original_convenio, carteira_edit, variacao_edit, tipo_convenio_edit, padrao, checar_arquivo_reposicao_edit } = data
	
	const errorArea = document.querySelector('.error-edit-form')
	const removeError = () => errorArea.innerHTML = ""
	const addError = (error) => {
		removeError()
		errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error.response.data.error || error}</section>`)
	}

	removeError()
	
	const fields = {
		banco: banco_convenio,
		agencia: agencia_edit,
		conta: conta_edit,
		op: op_convenio_edit,
		convenio: numero_convenio,
		carteira: carteira_edit,
		variacao: variacao_edit,
		convenio_original: numero_original_convenio,
		tipo_convenio: tipo_convenio_edit,
		customer: document.querySelector('select[name=customer_edit]').value,
		padrao: padrao,
		checkFile: checar_arquivo_reposicao_edit
	}
	
	validation.convenio(fields)
	.then( res => {
		convenios.editConvenioProcessing(fields)
		.then( res => {
			toastr.success('Salvo com sucesso')
			getConvenios()
		})
		.catch( err => {
			btnEditConvenio.innerHTML = ""
			btnEditConvenio.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-edit"></span> Salvar')
			addError(err) 
		})
	})
	.catch( error => {
		btnEditConvenio.innerHTML = ""
		btnEditConvenio.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-edit"></span> Salvar')
		addError(error) 
	})
}

//Toggle (tipo de convênio / form de edição)
const toggleTipoConvenioEdit = (event) => {
	const { target } = event
	const { name, value } = target
	
	if ( name === 'tipo_convenio_edit' ) {
		const customerArea = target.parentNode.parentNode.parentNode.nextElementSibling
		customerArea.style.display = value === '2' ? 'block' : 'none'
	}
}

EventHandler.bind(document, 'click', toggleTipoConvenioEdit)

const doEdit = (event) => {
	const { target } = event
	const { name } = target
		
	if ( name === 'btn-edit-convenio' ) {
		target.innerHTML = ""
		//target.disabled = true
		target.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="15" /> Salvando')
		const editForm = target.parentNode.parentNode
		const inputs = editForm.querySelectorAll('select, input')
		const data = {}
		//Montando o form
		Array.from(inputs).map( field => {
			const { type, name, value, checked } = field
			const types = ['text', 'hidden', 'select-one']
			
			if ( types.includes(type) || (type === 'radio' && field.checked) ) {
				data[name] = value
			} else if ( type === 'checkbox' ) {
				data[name] = checked
			}
		})
		
		editConvenio(data)
	}
}

EventHandler.bind(document, 'click', doEdit)

// Senha Padrão
const masterPass = document.querySelector('#master_pass')
const newMasterPass = document.querySelector('#new_master_pass')

server.getMasterPassword()
.then(res => {
	const password = res.data.password
	masterPass.value = password
})
.catch(error => console.log(error))

const btnUpdateMasterPass = document.querySelector('[name=update-master-pass]')
EventHandler.bind(btnUpdateMasterPass, 'click', () => {	
	server.updateMasterPassword(newMasterPass.value)
	.then(res => {
		if (res.data.success) {
			masterPass.value = newMasterPass.value
			newMasterPass.value = ""
			toastr.success(res.data.status)
			return
		}
		toastr.error("Erro ao alterar senha padrão")
	})
	.catch(error => toastr.error(error))
})