(() => {
	'use strict'
	const database = require('../api/database')
	const customer = require('../api/customer')
	const parameters = require('../api/parameters')
	const { isValidImage } = require('../modules/validation')
	const util = require('../modules/util')
	const input = require('../modules/input')
	const toastr = require('toastr')
	const format = new Format()
	const errorArea = document.querySelector('#geral-alert')
	const { pathname } = window.location
	const isEditScreen = pathname.indexOf('/painel/edit/customer') !== -1
	const valor_mensalidade_cliente = document.querySelector('span[name=valor_mensalidade_cliente]')

	/*
	* Menu horizontal
	*/

	const toggleMenu = (e) => {
		const { target } = e
		const { tagName, parentNode, nextElementSibling } = target
		
		if ( tagName == "BUTTON" && parentNode.classList.contains('menu-horizontal') ) {
			nextElementSibling.style.display = nextElementSibling.style.display == 'block' ? 'none' : 'block'
		}
	}
	
	EventHandler.bind(document, 'click', toggleMenu)

	// Gerar banco de dados do cliente
	const CreateDatabase = (e) => {
		const { target } = e
		const { tagName, classList } = target

		if ( tagName == "LI" && classList.contains('create-database') ) {
			const codsac = window.location.href.split('/').pop() //Captura o código do sacado através da URL
			target.innerHTML = ""
			target.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="15" /> Gerando banco')
			database.create(codsac)
			.then( res => {
				target.innerHTML = ""
				target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-file"></span> Gerar banco de dados')
				const { data } = res
				toastr.options = { "positionClass": "toast-bottom-right" }
				toastr.success(data.status)
			})
			.catch(err => {
				target.innerHTML = ""
				target.insertAdjacentHTML('beforeend', '<span style="color:#d9534f;"><span class="glyphicon glyphicon-remove"></span> Erro ao gerar banco, tente novamente</span>')
				setTimeout(() => {
					target.innerHTML = ""
					target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-file"></span> Gerar banco de dados')
				}, 1500)
			})
		}

		if ( tagName == "LI" && classList.contains('remove-customer-image') ) {
			const codsac = window.location.href.split('/').pop()
			target.innerHTML = ""
			target.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="15" /> Removendo imagem')
			
			customer.removeImage(codsac)
			.then( res => {
				target.innerHTML = ""
				target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-trash"></span> Remover imagem')
				toastr.options = { "positionClass": "toast-bottom-right" }
				toastr.success('Imagem removida com sucesso')
			})
			.catch( err => {
				target.innerHTML = ""
				target.insertAdjacentHTML('beforeend', '<span style="color:#d9534f;"><span class="glyphicon glyphicon-remove"></span> Erro ao remover imagem, tente novamente</span>')
				setTimeout(() => {
					target.innerHTML = ""
					target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-trash"></span> Remover imagem')
				}, 1500)
			})
		}

		if ( tagName == "LI" && classList.contains('reset-password') ) {
			const codsac = window.location.href.split('/').pop()
			const sigla = document.querySelector('#siglaCliente').value
			target.innerHTML = ""
			target.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="15" /> Resetando senha')
			
			customer.resetPassword(sigla, codsac)
			.then( res => {
				target.innerHTML = ""
				target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-refresh"></span> Resetar senha')
				toastr.options = { "positionClass": "toast-bottom-right" }
				toastr.success(res.data.success)
			})
			.catch( err => {
				target.innerHTML = ""
				target.insertAdjacentHTML('beforeend', '<span style="color:#d9534f;"><span class="glyphicon glyphicon-refresh"></span> Erro ao resetar senha, tente novamente</span>')
				setTimeout(() => {
					target.innerHTML = ""
					target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-refresh"></span> Resetar senha')
				}, 1500)
			})
		}
	}

	EventHandler.bind(document, 'click', CreateDatabase)

	// ===============================> (INÍCIO) DADOS INICIAS <===============================

	//Máscara campo data
	const tipoData = document.getElementById('tipoData')
	const clienteDesde = document.getElementById('dtEntradaCliente')

	VMasker(document.querySelectorAll('.maskData')).maskPattern("99/99/9999")
	//Validação de data
	//Data de nascimento/constituição
	EventHandler.bind(tipoData, 'blur', () => input.format(tipoData, util.validarData(tipoData.value)) )
	//Cliente desde
	clienteDesde.value = new Date().toLocaleDateString('pt-BR');
	EventHandler.bind(clienteDesde, 'blur', () => input.format(clienteDesde, util.validarData(clienteDesde.value)))
	//Código do cliente
	const codsac = document.getElementById('codsac')

	setTimeout(() => {
		if ( !isEditScreen ) {
			parameters.getGenID('SACADOS')
			.then( res => codsac.value = res.data.CODSAC )
			.catch( err => codsac.value = `error: ${err}`)
		}
	}, 900)
	/*
	* Seleciona o tipo de pessoa (PF/PJ)
	*/
	const tipodoc = document.querySelector('#tpdoc')
	const dadosiniciais = document.querySelector('#dadosiniciais')
	const titulodatacliente = document.querySelector('#titulodatacliente')
	const titulotipodocumento = document.querySelector('#titulotipodoc')
	const documento = document.getElementById('documento')
	dadosiniciais.style.display = 'none'

	EventHandler.bind(tipodoc, 'change', () => {
		dadosiniciais.style.display = 'block'
		titulotipodocumento.innerHTML = tipodoc.value === "1" ? 'CPF:' : 'CNPJ:'
		titulodatacliente.innerHTML = tipodoc.value === "1" ? 'Dt. de nascimento:' : 'Dt. de constituição:'
		documento.maxLength = tipodoc.value === "1" ? 14 : 18
		VMasker(documento).maskPattern(tipodoc.value === "1" ? "999.999.999-99" : "99.999.999/9999-99")
	})

	EventHandler.bind(documento, 'blur', () => {
		input.format(documento, util[tipodoc.value === "1" ? 'validarCPF' : 'validarCNPJ'](documento.value)) 
	})

	const area_atuacao = document.querySelector('#area_atuacao')
	EventHandler.bind(area_atuacao, 'blur', () => input.format(area_atuacao, area_atuacao.value !== ''))

	/*
	* Sigla (uppercase)
	*/
	const siglaCliente = document.getElementById('siglaCliente')

	EventHandler.bind(siglaCliente, 'keyup', () => {
		input.only('string', siglaCliente)
		siglaCliente.value = siglaCliente.value.toUpperCase()
	}, false)

	EventHandler.bind(siglaCliente, 'blur', () => input.format(siglaCliente, /^[A-Z]{3}$/))

	/*
	* Imagem do cliente
	*/
	const inputImage = document.querySelector('input[name=logo-customer]')
	const btnAddImage = document.querySelector('button[name=btn-add-image]')
	const imageError = document.querySelector('span[class=image-error]')

	EventHandler.bind(btnAddImage, 'click', () => inputImage.click())

	//Atualização do formato do botão de adicionar imagens
	const updateBtn = (el, kind) => {
		el.innerHTML = ""

		switch ( kind ) {
			case 'success':
				el.classList.remove('btn-warning')
				el.classList.add('btn-success')
				el.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-ok-sign"></span> Imagem selecionada com sucesso')
			break
			case 'warning':
				el.classList.remove('btn-success')
				el.classList.add('btn-warning')
				el.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-cloud-upload"></span> Clique aqui para selecionar a imagem')
			break
		}
	}

	EventHandler.bind(inputImage, 'change', () => {
		try {
			imageError.innerHTML = ""
			const { files } = inputImage

			if ( files.length > 0 ) {
				//isValidImage(files[0])
				updateBtn(btnAddImage, 'success')
			} else {
				updateBtn(btnAddImage, 'warning')
				imageError.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">Selecione a imagem do cliente</section>`)
			}
		} catch ( error ) {
			updateBtn(btnAddImage, 'warning')
			imageError.innerHTML = ""
			imageError.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error.message}</section>`)
		}
	})

	// ===============================> (FIM) DADOS INICIAS <==================================

	// ===============================> (INÍCIO) DADOS ENDEREÇO <==============================

	/*
	* API dos correios para buscar endereço de acordo com o CEP
	*/ 
	const cep = document.getElementById('cep')

	EventHandler.bind(cep, 'blur', () => {
		util.getAddressByCEP(cep)
		.then( res => {
			const { data } = res
			const msg = document.querySelector('#cep-msg')
			msg.innerHTML = ""

			if ( !data.erro ) {
				msg.innerHTML = '<img src="/painel/build/images/loading.gif" width="30" />'
				
				//Preenche os campos de endereço
				document.getElementById('endereco').value = data.logradouro
				document.getElementById('bairro').value = data.bairro
				document.getElementById('cidade').value = data.localidade
				document.getElementById('uf').value = data.uf
				document.getElementById('numero').focus()
				input.format(cep, true)
				msg.innerHTML = ""
			} else {
				document.getElementById('endereco').value = "CEP NÃO ENCONTRADO"
				document.getElementById('numero').value = ""
				document.getElementById('complemento').value = ""
				document.getElementById('bairro').value = ""
				document.getElementById('cidade').value = ""
				document.getElementById('uf').value = ""
				input.format(cep, false)
			}
		})
		.catch( err => {
			input.format(cep, false)
			toastr.options = { "positionClass": "toast-bottom-right" }
			toastr.error('Erro ao recuperar dados do CEP')
		})
	})

	const numero = document.getElementById('numero')

	EventHandler.bind(numero, 'keyup', () => input.only('number', numero))

	// ===============================> (FIM) DADOS ENDEREÇO <=================================

	// ===============================> (INÍCIO) INFORMAÇÕES FINANCEIRAS <=====================

	/*
	* Validação de DV (Agência/Conta)
	*/
	
	const banco = document.getElementById('banco')
	const agencia = document.getElementById('agencia')
	const conta = document.getElementById('conta')
	const op = document.getElementById('op')
	const operacoes_permitidas = ['001', '002', '003', '006', '007', '013', '022']
	
	//Cria o campo OPERACAO caso o banco seja CAIXA
	EventHandler.bind(banco, 'change', () => {
		if ( banco.value == '104' ) {
			if ( !op.hasChildNodes() ) {
				let html = "<label>Operação: <span class='required-alert'>*</span></label><input id='opCaixa' class='form-control' maxlength='3' />"
				op.insertAdjacentHTML('afterbegin', html)
				op.style.display = "block"
			}
		} else {
			if ( op.hasChildNodes() ) {
				op.innerHTML = ""
				op.style.display = "none"
			}
		}
	}, false)

	//Valida a Operação da CAIXA
	const validateOpCaixa = (e) => {
		const target = e.target
		const id = target.id
		const tag = target.tagName
		
		if ( id == 'opCaixa' && tag == 'INPUT' ) {
			EventHandler.bind(opCaixa, 'blur', () => input.format(target, operacoes_permitidas.indexOf(opCaixa.value) != -1) )
		}
	}

	EventHandler.bind(document, 'click', validateOpCaixa)

	//Validação de agência
	EventHandler.bind(agencia, 'blur', () => input.format(agencia, util.validateDV(banco, agencia.value)))

	//Validação de conta
	EventHandler.bind(conta, 'blur', () => {
		let value = null
		
		switch (banco.value) {
			case '104':
				value = agencia.value.substring(0, agencia.value.length - 1) + opCaixa.value + format.str_pad('000000000', conta.value, 'l')
				input.format(conta, util.validateDV(banco, value))
				break
			case '341':
				value = `${agencia.value.substring(0, agencia.value.length - 1)}${conta.value}`
				input.format(conta, util.validateDV(banco, value))
				break
			default:
				input.format(conta, util.validateDV(banco, conta.value))
				break
		}
	});

	/*
	* Formatando mensalidade
	*/
	const mensalidade = document.getElementById('mensalidade')
	const valorMensalidade = document.getElementById('valorMensalidade')
	const formCadastro = document.forms[0]
	
	EventHandler.bind(formCadastro, 'change', (event) => {
		const { target } = event
		const { name, value } = target

		if ( name === 'tipoMensalidade' ) {
			if ( value === '1' ) {
				mensalidade.maxLength = 3
				mensalidade.value = ''
				valorMensalidade.innerHTML = format.FormatMoney(mensalidade.value * (mensalidade.value/100), 'BRL')
				valorMensalidade.parentNode.style.cssText = 'display:inline-block'
				VMasker(mensalidade).unMask()
			} else {
				mensalidade.maxLength = 8
				mensalidade.value = valor_mensalidade_cliente.innerHTML !== '' ? valor_mensalidade_cliente.innerHTML : 0
				valorMensalidade.parentNode.style.display = 'none'
				VMasker(mensalidade).maskMoney()
			}
			mensalidade.focus()
		}
		
	})

	setTimeout(() => {
		parameters.getSalarioMinimo().then( res => {
			const salario = res.data.SALARIO_MINIMO[0] * 1
			valorMensalidade.innerHTML = format.FormatMoney(salario * (mensalidade.value/100), 'BRL')

			EventHandler.bind(mensalidade, 'keyup', () => {
				input.only('number', mensalidade)
				valorMensalidade.innerHTML = format.FormatMoney(salario * (mensalidade.value/100), 'BRL')
			})
		})
	}, 1200)

	/*
	* Tarifas
	*/

	//Padrão/Personalizada
	const fieldsTarifas = document.querySelectorAll('.tarifas')
	const tipoTarifa = document.getElementById('tipoTarifa')
	const DisableElement = (el, boolean) => Array.from(el).map( input => input.disabled = boolean )
	
	if ( tipoTarifa.value === "1" ) DisableElement(fieldsTarifas, true)
	
	EventHandler.bind(tipoTarifa, 'change', () => DisableElement(fieldsTarifas, tipoTarifa.value === "1" ? true : false))

	setTimeout(() => {
		parameters.getTarifas()
		.then( tarifas => {
			const { data } = tarifas
			document.getElementById('bb').value = format.FormatMoney(data.BB_18[0], 'BRL')
			document.getElementById('bb17').value = format.FormatMoney(data.BB_1704[0], 'BRL')
			document.getElementById('bb1711').value = format.FormatMoney(data.BB_1711[0], 'BRL')
			document.getElementById('bb1705').value = format.FormatMoney(data.BB_1705[0], 'BRL')
			document.getElementById('bblqr').value = format.FormatMoney(data.BB_LQR[0], 'BRL')
			document.getElementById('brdct').value = format.FormatMoney(data.BRADESCO[0], 'BRL')
			document.getElementById('cefint').value = format.FormatMoney(data.CEF_AGENCIA[0], 'BRL')
			document.getElementById('cefagn').value = format.FormatMoney(data.CEF_AUTOAT[0], 'BRL')
			document.getElementById('cefcomp').value = format.FormatMoney(data.CEF_COMPENSACAO[0], 'BRL')
			document.getElementById('ceflot').value = format.FormatMoney(data.CEF_CT[0], 'BRL')
			document.getElementById('cefct').value = format.FormatMoney(data.CEF_LOTERIAS[0], 'BRL')
		})
	}, 500)

	// =================================> (FIM) INFORMAÇÕES FINANCEIRAS <======================

	// ===============================> (INÍCIO) DADOS DO CONTATO <============================

	/*
	* Telefone
	*/
	let telefones = []
	const foneList = document.getElementById('list-telefones')
	const qtdeTelefones = document.getElementById('qtdeDeTelefones')	
	qtdeTelefones.innerHTML = telefones.length

	/*
	* Modal telefone
	*/
	
	//Máscara
	const tipo = document.getElementById('tipoTelefone')
	const numero_telefone = document.getElementById('numero-telefone')
	
	EventHandler.bind(tipo, 'change', () => VMasker(numero_telefone).maskPattern(tipo.value == '1' ? "(99) 9999-9999" : "(99) 9.9999-9999"))

	//Event handler
	const desc = document.getElementById('descricao-telefone')
	const error_telefone = document.getElementById('msg-error-telefone')
	const btnAddTelefone = document.getElementById('cadastrar-telefone')

	EventHandler.bind(btnAddTelefone, 'click', () => {
		//Dados que vem do formulário
		const dadosTelefone = {
			descricao: desc.value,
			tipo: tipo.value == '1' ? 'Fixo' : 'Celular',
			numero: VMasker.toNumber(numero_telefone.value)
		}
		
		const inputs = document.querySelectorAll('#modalCadastroTelefone input, #modalCadastroTelefone select')
		//Verificando se os campos foram preenchidos
		Array.from(inputs).map( input => {
			if ( input.value === '' ) {
				error_telefone.innerHTML = ""
				error_telefone.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 10px 0;">Todos os campos são obrigatórios</div>`)	
				return
			}
		})

		if ( (dadosTelefone.tipo == 'Fixo' && dadosTelefone.numero.length != 10) || (dadosTelefone.tipo == 'Celular' && dadosTelefone.numero.length != 11) ) {
			error_telefone.innerHTML = ""
			error_telefone.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 10px 0;">Preencha o campo telefone corretamente</div>`)
		} else {
			//Limpa os erros
			error_telefone.innerHTML = ""
			foneList.innerHTML = ""
			qtdeTelefones.innerHTML = ""
			//Insere os dados de telefone no array
			dadosTelefone.numero = numero_telefone.value
			telefones.push(dadosTelefone)
			//Apaga os dados dos campos
			Array.from(inputs).map( input => input.value = "" )
			
			qtdeTelefones.insertAdjacentHTML('beforeend', `<span>${telefones.length}</span>`)
			//Cria a tabela
			let table = '<table id ="added-phones" class="table table-condensed" style="margin: 0 !important;">'

			telefones.map( (telefone, i)  => {
				table += `<tr id="${i}"><td style="padding: 10px !important;">`
				table += `<span>${telefone.descricao}</span>`
				table += `<button name="deleteTelBtn" type="button" class="btn btn-default btn-sm pull-right glyphicon glyphicon-remove" style="margin: 0 0 0 10px;"></button>`
				table += `<span class="pull-right">${telefone.numero}</span>`
				table += `</td></tr>`
			})

			foneList.insertAdjacentHTML('beforeend', table)
		}
	})
	
	/*
	* Modal email
	*/
	
	let emails = []
	const emailList = document.getElementById('list-emails')
	const qtdeEmails = document.getElementById('qtdeDeEmails')
	qtdeEmails.innerHTML = emails.length

	const mail_desc = document.getElementById('descricao-email')
	const email = document.getElementById('email')
	const error_mail = document.getElementById('msg-error-email')
	const btnAddMail = document.getElementById('cadastrar-email')

	//Event handler
	EventHandler.bind(btnAddMail, 'click', () => {
		//Dados que vem do formulário
		let dadosEmail = { descricao: mail_desc.value, email: email.value }

		const inputs = document.querySelectorAll('#modalCadastroEmail input')
		//Verificando se os campos foram preenchidos
		Array.from(inputs).map( input => {
			if ( input.value === '' ) {
				error_mail.innerHTML = ""
				error_mail.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 10px 0;">Todos os campos são obrigatórios</div>`)	
				return
			}
		})

		if ( !util.validateMail(dadosEmail.email) ) {
			error_mail.innerHTML = ""
			error_mail.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 10px 0;">Insira um email válido</div>`)
		} else {
			//Limpa os erros
			error_mail.innerHTML = ""
			emailList.innerHTML = ""
			qtdeEmails.innerHTML = ""
			//Apaga os dados dos campos
			Array.from(inputs).map( input => input.value = "" )
			//Insere os dados de telefone no array
			emails.push(dadosEmail)
			
			qtdeEmails.insertAdjacentHTML('beforeend', `<span>${emails.length}</span>`)
			//Cria a tabela
			let mail_table = '<table id="added-mails" class="table table-condensed" margin="0 !important;">'
			
			emails.map( email => {
				mail_table += `<tr><td style="padding: 10px !important;">`
				mail_table += `<span>${email.descricao}</span>`
				mail_table += `<button name="deleteMailBtn" type="button" class="btn btn-default btn-sm pull-right glyphicon glyphicon-remove" style="margin: 0 0 0 10px;"></button>`
				mail_table += `<span class="pull-right">${email.email}</span>`
				mail_table += `</td></tr>`
			})

			emailList.insertAdjacentHTML('beforeend', mail_table)
		}
	})

	//Deletar telefone
	const phoneRemove = (event) => {
		const { target } = event
		const { tagName, name } = target

		if ( tagName == "BUTTON" && name == "deleteTelBtn" ) {
			if ( target.parentNode ) {
				const params = target.parentNode.querySelectorAll('span')
				const desc = params[0].innerHTML
				const phone = params[1].innerHTML
				telefones = telefones.filter( data => !(data.descricao == desc && data.numero == phone) )
				target.parentNode.remove(target.parentNode)
				qtdeTelefones.innerHTML = telefones.length
			}
		}
	}
	
	EventHandler.bind(document, 'click', phoneRemove)
	
	//Deletar email
	const mailRemove = (event) => {
		const { target } = event
		const { tagName, name } = target

		if ( tagName == "BUTTON" && name == "deleteMailBtn" ) {
			if ( target.parentNode ) {
				const params = target.parentNode.querySelectorAll('span')
				const desc = params[0].innerHTML
				const mail = params[1].innerHTML
				emails = emails.filter( data => !(data.descricao == desc && data.email == mail) ) 
				target.parentNode.remove(target.parentNode)
				qtdeEmails.innerHTML = emails.length
			}
		}
	}
	
	EventHandler.bind(document, 'click', mailRemove)

	// ===============================> (FIM) DADOS DO CONTATO <===============================

	// [INÍCIO] ==============================> CASO A PÁGINA SEJA PARA EDITAR O CLIENTE <=====

	setTimeout(() => {
		if ( isEditScreen ) {
			//Código do sacado
			const cod = window.location.href.toString().substr(-3)
			//Preenche os campos
			customer.getDataByCodSac(cod)
			.then( customer_info => {
				const { data } = customer_info
				dadosiniciais.style.display = 'block'				

				/*
				* Dados iniciais
				*/
				tipodoc.value = data.TPDOCSAC[0]
				codsac.value = format.str_pad('00000', cod, 'l')
				documento.value = data.DOCSAC[0]
				VMasker(documento).maskPattern(tipodoc.value === "1" ? "999.999.999-99" : "99.999.999/9999-99")
				area_atuacao.value = data.AREA_ATUACAO[0]
				siglaCliente.value = data.CLI_SIGLA[0].toString().toUpperCase()
				nomeCliente.value = data.NOMSAC[0]
				responsavel.value = data.RESPONSAVEL[0]
				tipoData.value = `${data.DTNASCSAC_DIA[0]}/${data.DTNASCSAC_MES[0]}/${data.DTNASCSAC_ANO[0]}`
				site.value = data.SITE[0]
				if ( data.RETORNO_POR_EMAIL[0] === '1' ) document.getElementById('retorno-por-email').checked = true
				if ( data.CNAB240[0] === '1' ) document.getElementById('retorno-cnab240').checked = true
				
				const date = new Date(data.DATA_ASSOCIACAO[0])
				clienteDesde.value = new Date(date.setDate(date.getDate() + 1)).toLocaleDateString('pt-BR')
				repasse.value = data.REPASSE[0]
				
				const status = document.getElementById('status')
				status.value = data.STATUS[0]

				/*
				* Endereço
				*/
				cep.value = data.CEP[0]
				endereco.value = data.ENDSAC[0].split(', ')[0]
				numero.value = data.ENDSAC[0].split(', ')[1].split(' ')[0]
				
				const comp = data.ENDSAC[0].split(', ')[1].split(' - ')[0].split(' ')
				complemento.value = (comp.length > 1) ? comp.slice(1).join(' ') : ''
				bairro.value = data.ENDSAC[0].split(' - ')[1]
				cidade.value = data.CIDSAC[0]
				uf.value = data.UFSAC[0].trim()
				pontoReferencia.value = data.DICAEND[0]

				/*
				* Informações financeiras
				*/
				//BANCO
				banco.value = data.BANCO[0]
				agencia.value = data.AGENCIA[0]
				conta.value = data.CONTA_CORRENTE[0]
				//Cria o campo operação caso o banco seja CAIXA
				if ( banco.value == '104') {
					const op = document.getElementById('op')
					const html = "<label>Operação: <span class='required-alert'>*</span></label><input id='opCaixa' class='form-control' maxlength='3' />"
					
					op.insertAdjacentHTML('afterbegin', html)
					op.style.display = "block"

					document.getElementById('opCaixa').value = data.OPERACAO[0]
				}
				valor_mensalidade_cliente.innerHTML = data.MENSALIDADE[0]
				mensalidade.value = data.MENSALIDADE[0]
				if ( data.TIPO_MENSALIDADE[0] === '2' ) {
					valorMensalidade.parentNode.style.display = 'none'
					VMasker(mensalidade).maskMoney()
				}
				//Isenção da mensalidade
				Array.from(document.querySelectorAll(`input[name=isento-mensalidade]`))
					.filter( radio => radio.value == data.ISENTO_MENSALIDADE[0])[0]
					.checked = true
				//Isenção da mensalidade
				Array.from(document.querySelectorAll(`input[name=isento-debito-automatico]`))
					.filter( radio => radio.value == data.ISENTO_DEBITO_AUTOMATICO[0])[0]
					.checked = true
				//Isenção do substituto tributário
				Array.from(document.querySelectorAll(`input[name=isento-sub-trib]`))
					.filter( radio => radio.value == data.SUBSTITUTO_TRIBUTARIO[0])[0]
					.checked = true
				//Tipo da mensalidade
				Array.from(document.querySelectorAll('input[name=tipoMensalidade]'))
					.filter( radio => radio.value == data.TIPO_MENSALIDADE[0] )[0]
					.checked = true

				//TIPO DE TARIFA (PADRÃO/PERSONALIZADA)
				tipoTarifa.value = data.TIPO_TARIFA[0]
				DisableElement(fieldsTarifas, tipoTarifa.value === "1" ? true : false)

				/*
				* Informações de contato
				*/
				data.NOMCON = data.NOMCON.filter( name => name )
				if ( data.NOMCON.length > 0 ) {
					//TELEFONES
					for ( let i = 0, len = data.NOMCON.length ; i < len ; i++  ) {
						if ( typeof data.FONECON[i] == 'string' ) telefones.push({descricao: data.NOMCON[i], numero: data.FONECON[i]})
					}
					//Limpa os erros
					qtdeTelefones.innerHTML = ""
					qtdeTelefones.insertAdjacentHTML('beforeend', `<span>${telefones.length}</span>`)
					//Cria a tabela
					let table = '<table id="added-phones" class="table table-condensed" style="margin: 0 !important;">'
					
					for ( let i = 0, len = telefones.length; i < len; i++ ) {
						table += `<tr><td style="padding: 10px !important;">`
						table += `<span>${telefones[i].descricao}</span>`
						table += `<button name="deleteTelBtn" type="button" class="btn btn-default btn-sm pull-right glyphicon glyphicon-remove" style="margin: 0 0 0 10px;"></button>`
						table += `<span class="pull-right">${telefones[i].numero}</span>`
						table += `</td></tr>`
					}

					foneList.insertAdjacentHTML('beforeend', table)

					//EMAILS
					for ( let i = 0, len = data.EMAIL.length ; i < len ; i++  ) {
						if ( typeof data.EMAIL[i] == 'string' ) emails.push({descricao: data.NOMCON[i], email: data.EMAIL[i]})
					}
					//Limpa os erros
					qtdeEmails.innerHTML = ""
					qtdeEmails.insertAdjacentHTML('beforeend', `<span>${emails.length}</span>`)
					//Cria a tabela
					let table2 = '<table id="added-mails" class="table table-condensed" style="margin: 0 !important">'
					for ( let i = 0, len = emails.length; i < len; i++ ) {
						table2 += `<tr><td style="padding: 10px !important;">`
						table2 += `<span>${emails[i].descricao}</span>`
						table2 += `<button name="deleteMailBtn" type="button" class="btn btn-default btn-sm pull-right glyphicon glyphicon-remove" style="margin: 0 0 0 10px;"></button>`
						table2 += `<span class="pull-right">${emails[i].email}</span>`
						table2 += `</td></tr>`
					}

					emailList.insertAdjacentHTML('beforeend', table2)
				}

				//TARIFAS
				setTimeout(() => {
					customer.getTarifasByCodSac(cod)
					.then( tarifas => {
						const { data } = tarifas
						
						bb17.value = format.FormatMoney(data.BB_1704[0] / 1, 'BRL')
						bb1705.value = format.FormatMoney(data.BB_1705[0] / 1, 'BRL')
						bb1711.value = format.FormatMoney(data.BB_1711[0] / 1, 'BRL')
						bb.value = format.FormatMoney(data.BB_18[0] / 1, 'BRL')
						bblqr.value = format.FormatMoney(data.BB_LQR[0] / 1, 'BRL')
						cefint.value = format.FormatMoney(data.CEF_AUTOAT[0] / 1, 'BRL')
						cefagn.value = format.FormatMoney(data.CEF_AGENCIA[0] / 1, 'BRL')
						cefcomp.value = format.FormatMoney(data.CEF_COMPENSACAO[0] / 1, 'BRL')
						ceflot.value = format.FormatMoney(data.CEF_LOTERIAS[0] / 1, 'BRL')
						cefct.value = format.FormatMoney(data.CEF_CT[0] / 1, 'BRL')
						brdct.value = format.FormatMoney(data.BRADESCO[0] / 1, 'BRL')
					})
					.catch( err => console.log(err) )
				}, 1000)
			})
			.catch( err => console.log(err) )
		}
	}, 1000)

	// [FIM] =================================> CASO A PÁGINA SEJA PARA EDITAR O CLIENTE <=================================

	// ========================> BOTAO CADASTRAR

	const btnCadastrar = document.getElementById('cadastrar-cliente');
	EventHandler.bind(btnCadastrar, 'click', () => {		
		errorArea.innerHTML = ""
		try {
			const formData = new FormData()
			const regex = /^[A-Za-z0-9-áàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ_@!#$%¨&*()+.\/\\ ]{1,45}$/
			//DADOS INICIAIS
			formData.append('TPDOCSAC', ['1', '2'].includes(tipodoc.value) ? tipodoc.value : (() => {throw "Tipo de cadastro inválido"})())
			const status = document.getElementById('status')
			formData.append('STATUS', status !== null ? status.value : 0)
			formData.append('CODSAC', codsac.value.length == 5 ? codsac.value : (() => {throw "Código do Sacado inválido"})())
			formData.append('DOCSAC',  util[tipodoc.value === '1' ? 'validarCPF' : 'validarCNPJ'](VMasker.toNumber(documento.value)) ? VMasker.toNumber(documento.value) : (() => {throw "Documento inválido"})())
			formData.append('AREA_ATUACAO', area_atuacao.value !== '' ? area_atuacao.value : (() => {throw "Área de Atuação é obrigatório"})())
			formData.append('REPASSE', repasse.value === '0' || repasse.value === '1' ? repasse.value : (() => {throw "Campo Repasse é obrigatório"})())
			
			formData.append('CLI_SIGLA', (/^[A-Z]{3}$/).test(siglaCliente.value.toUpperCase()) ? siglaCliente.value.toUpperCase() : (() => {throw "Sigla inválida"})())
			const nome = document.getElementById('nomeCliente')
			formData.append('NOMSAC', (regex).test(nome.value) ? nome.value : (() => {throw `Nome inválido`})())
			const responsavel = document.getElementById('responsavel')
			formData.append('RESPONSAVEL', (regex).test(responsavel.value) ? responsavel.value : (() => {throw `Nome do responsável inválido`})())
			formData.append('DTNASC', util.validarData(tipoData.value) ? tipoData.value : (() => {throw `${tipodoc.value === "1" ? 'Dt. Nasc' : 'Dt. de constituição'} inválida`})())
			formData.append('DATA_ASSOCIACAO', util.validarData(clienteDesde.value) ? clienteDesde.value : (() => {throw "'Cliente desde' inválido"})())
			const site = document.getElementById('site')
			formData.append('SITE', (site.value.length !== 0) ? site.value : (() => {throw "Site inválido"})())
			if ( inputImage.files.length > 0 ) {
				isValidImage(inputImage.files[0])
				formData.append('fileToUpload', inputImage.files[0])
			}
			//DADOS DE LOCALIZAÇÃO
			formData.append('CEP', (/^[0-9]{8}$/).test(VMasker.toNumber(cep.value)) ? VMasker.toNumber(cep.value) : (() => {throw "CEP inválido"})())
			/*
			* [INÍCIO] ENDEREÇO
			*/
			if ( !(regex).test(endereco.value) ) throw "Endereço inválido"
			if ( !(regex).test(bairro.value) ) throw "Bairro inválido"
			if ( numero.value.length === 0 ) throw "Número inválido"
			formData.append('ENDSAC', `${endereco.value}, ${numero.value} ${complemento.value} - ${bairro.value}`.substring(0, 60))
			formData.append('CIDSAC', (regex).test(cidade.value) ? cidade.value : (() => {throw "Cidade inválida"})())
			formData.append('UFSAC', (/^[A-Z]{2}$/).test(uf.value) ? uf.value : (() => {throw "UF inválida"})())
			formData.append('DICAEND', document.getElementById('pontoReferencia').value) //NÃO OBRIGATÓRIO
			/*
			* [FIM] ENDEREÇO
			*/
			/*
			* [INÍCIO] INFORMAÇÕES FINANCEIRAS
			*/
			const bancos_permitidos = ['001', '104', '237', '341']
			const agencia = document.getElementById('agencia')
			const conta = document.getElementById('conta')
			const opCaixa = document.getElementById('opCaixa')
			let validacaoConta = null

			formData.append('BANCO', bancos_permitidos.includes(banco.value) ? banco.value : (() => {throw "Banco inválido"})())
			formData.append('AGENCIA', util.validateDV(banco, agencia.value) ? VMasker.toNumber(agencia.value) : (() => {throw "Agência inválida"})())

			if ( banco.value == '104' ) {
				formData.append('OPERACAO', operacoes_permitidas.includes(opCaixa.value) ? opCaixa.value : (() => {throw "Operação inválida"})())
				validacaoConta = `${agencia.value.substring(0, agencia.value.length - 1)}${opCaixa.value}${format.str_pad('000000000', conta.value, 'l')}`
			} else if ( banco.value == '341' ) {
				validacaoConta = `${agencia.value.substring(0, agencia.value.length - 1)}${conta.value}`
			} else {
				validacaoConta = conta.value
			}

			formData.append('CONTA', util.validateDV(banco, validacaoConta) ? conta.value : (() => {throw "Conta inválida"})())
			const tipo_mensalidade = document.querySelector('input[name=tipoMensalidade]:checked').value
			formData.append('TIPO_MENSALIDADE', ['1', '2'].includes(tipo_mensalidade) ? tipo_mensalidade : (() => {throw "Tipo de Mensalidade inválida"})())
			if ( tipo_mensalidade === '1' ) {
				mensalidade.value > 0 ? mensalidade.value : (() => {throw "Mensalidade inválida"})()
				formData.append('MENSALIDADE', mensalidade.value)
			} else {
				const fmted_mensalidade = mensalidade.value.replace('.', '').replace(',', '.')
				formData.append('MENSALIDADE', fmted_mensalidade)
			}
			/*
			* [FIM] INFORMAÇÕES FINANCEIRAS
			*/

			/*
			* [INÍCIO] ISENÇÕES MENSALIDADES/SUBSTITUTO TRIBUTÁRIO
			*/
			//Mensalidades
			formData.append('ISENTO_MENSALIDADE', document.querySelector('input[name=isento-mensalidade]:checked').value)
			//Débito Automático
			formData.append('ISENTO_DEBITO_AUTOMATICO', document.querySelector('input[name=isento-debito-automatico]:checked').value)
			//Substituto Tributário
			formData.append('ISENTO_SUBSTITUTO_TRIBUTARIO', document.querySelector('input[name=isento-sub-trib]:checked').value)
			/*
			* [FIM] ISENÇÕES MENSALIDADES/SUBSTITUTO TRIBUTÁRIO
			*/
			formData.append('TIPO_TARIFA', isNaN(tipoTarifa.value) ? false : tipoTarifa.value)
			formData.append('BANCO_BRASIL_1704', format.number_format(bb17.value, 2, '.', '')/100)
			formData.append('BANCO_BRASIL_1705', format.number_format(bb1705.value, 2, '.', '')/100)
			formData.append('BANCO_BRASIL_1711', format.number_format(bb1711.value, 2, '.', '')/100)
			formData.append('BANCO_BRASIL_18', format.number_format(bb.value, 2, '.', '')/100)
			formData.append('BB_LQR', format.number_format(bblqr.value, 2, '.', '')/100)
			formData.append('CEF_AUTO_AT', format.number_format(cefint.value, 2, '.', '')/100)
			formData.append('CEF_AGENCIA', format.number_format(cefagn.value, 2, '.', '')/100)
			formData.append('CEF_COMPENSACAO', format.number_format(cefcomp.value, 2, '.', '')/100)
			formData.append('CEF_LOTERIAS', format.number_format(ceflot.value, 2, '.', '')/100)
			formData.append('CEF_CT', format.number_format(cefct.value, 2, '.', '')/100)
			formData.append('BRD', format.number_format(brdct.value, 2, '.', '')/100)
			//DADOS DE CONTATO
			formData.append('TELEFONES', telefones.length > 0 ? JSON.stringify(telefones) : (() => {throw 'Insira um telefone'})())
			formData.append('EMAILS', emails.length > 0 ? JSON.stringify(emails) : (() => {throw 'Insira um email'})())
			//DADOS DE ENVIO
			formData.append('RETORNO_POR_EMAIL', document.getElementById('retorno-por-email').checked ? '1' : '0')
			formData.append('CNAB240', document.getElementById('retorno-cnab240').checked ? '1' : '0')
			//DADOS PADRÕES DO BANCO ADM77777
			formData.append('PESSOA_ENTREGA', nome.value.length !== 0 ? nome.value.split(" ")[0] : false)
			formData.append('NOMSAC_PESQUISA', nome.value.length !== 0 ? nome.value.toUpperCase() : false)
			formData.append('REPASSE_VARIACAO', parseInt(codsac.value))
			formData.append('USUARIO', `sty ${codsac.value}`)
			formData.append('SENHA', `sty ${codsac.value}`)
			formData.append('COBRANCA', '0')
			formData.append('TPDESC', '0')
			formData.append('VLDESC', '0,000')
			formData.append('DIADESC', '0')
			formData.append('ENDCOB', '1')
			formData.append('END_CED', 'N')
			formData.append('END_CED2', 'N')
			formData.append('CODFOR', '0')
			formData.append('ENTREGA', '1')
			formData.append('PAIS', '0')
			formData.append('PAIS_2', '0')
			formData.append('CODCONV', '0')
			formData.append('REPASSE_TARIFA', '0,000')
			formData.append('LOGWEB', '1')
			//Formatando os botões
			const mainText = isEditScreen ? '<span class="glyphicon glyphicon-edit"></span> Concluir edição' : '<span class="glyphicon glyphicon-plus-sign"></span> Cadastrar'
			const btnText = isEditScreen ? 'Realizando alterações...' : 'Cadastrando cliente...'
			const btnContent = `<img src="/painel/build/images/loading.gif" width="20" /> ${btnText}`
			btnCadastrar.setAttribute('class', 'btn btn-default')
			btnCadastrar.innerHTML = ""
			btnCadastrar.insertAdjacentHTML('beforeend', btnContent)
			btnCadastrar.disabled = true
			//Requisição
			customer[isEditScreen ? 'update' : 'create'](formData)
			.then( res => {
				btnCadastrar.setAttribute('class', 'btn btn-primary')
				btnCadastrar.innerHTML = ""
				btnCadastrar.insertAdjacentHTML('beforeend', mainText)
				btnCadastrar.disabled = false
				const { stycombr_status, validador_status, IMAGE_2VIA_ERROR, CUSTOMER_PATH_ERROR, CT_CREATE_ERROR } = res.data
				const TIMEOUT = 5000
				const redirect = (pathname) => window.location.href = pathname
				toastr.options = { "positionClass": "toast-bottom-right", "progressBar": true, "timeOut": TIMEOUT }
				toastr.options.onclick = () => redirect("/painel/list/customers")
				toastr.success(isEditScreen ? "Cadastro atualizado com sucesso" : "Cliente cadastrado com sucesso")
				if ( validador_status ) {
					toastr.error(validador_status)
				}
				if ( IMAGE_2VIA_ERROR ) {
					toastr.error(IMAGE_2VIA_ERROR)
				}
				if ( CT_CREATE_ERROR ) {
					toastr.error(CT_CREATE_ERROR)
				}
				if ( CUSTOMER_PATH_ERROR ) {
					toastr.error(CUSTOMER_PATH_ERROR)
				}
				if ( stycombr_status ) {
					toastr.error(stycombr_status)
				}
				setTimeout(() => redirect("/painel/list/customers"), TIMEOUT)
			})
			.catch( err => {
				errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err.response.data.error || err}</section>`) 
			})
		} catch ( e ) {
			errorArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${e}</section>`)
		}
	}, false)
})();