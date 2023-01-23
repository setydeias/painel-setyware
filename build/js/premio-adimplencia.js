(() => {
	'use strict'
	const customer = require('../api/customer')
	const parameters = require('../api/parameters')
	const mail = require('../api/adimplencia')
	const input = require('../modules/input')
	const util = require('../modules/util')
	const format = new Format()
	const { pathname } = window.location
	const isEditScreen = pathname.indexOf('/painel/edit/premio') !== -1

	const regex = /^[A-Za-z0-9-áàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ_@!#$%¨&*()+.\/\\ ]{1,45}$/

	// ===============================> (INÍCIO) DADOS INICIAS <===============================
	
	//Código do cliente
	const codsac = document.getElementById('codsac')

	/*
	* Unidade
	*/
	let unidades = []
	const qtdUnidades = document.getElementById('qtdUnidades')
	qtdUnidades.innerHTML = unidades.length
	let numerosorteioselecionado = '';

	setTimeout(() => {
		if ( !isEditScreen ) {
			parameters.getGenID('SACADOS')
			.then( res => codsac.value = res.data.CODSAC )
			.catch( err => codsac.value = `error: ${err}`)
		}
	}, 900)

	/*
	* Sigla (uppercase)
	*/
	const siglaCliente = document.getElementById('siglaCliente')

	EventHandler.bind(siglaCliente, 'keyup', () => {
		input.only('string', siglaCliente)
		siglaCliente.value = siglaCliente.value.toUpperCase()
	}, false)

	EventHandler.bind(siglaCliente, 'blur', () => input.format(siglaCliente, /^[A-Z]{3}$/))


	setTimeout(() => {
		if ( isEditScreen ) {
			//Código do sacado
			const cod = window.location.href.toString().substr(-3)
			//Preenche os campos
			customer.getDataByCodSac(cod)
			.then( customer_info => {
				const { data } = customer_info
				/*
				* Dados iniciais
				*/
				codsac.value = format.str_pad('000', cod, '1')
				siglaCliente.value = data.CLI_SIGLA[0].toString().toUpperCase()
				nomeCliente.value = data.NOMSAC[0]
				listartUnidades()
			})
			.catch( err => console.log(err) )
		}
	}, 1000)

	
	
	//Event handler
	const descricaoUnidade = document.getElementById('unidade-descricao')
	const btnAddUnidade = document.getElementById('add-unidade');
	const error_unidade = document.getElementById('msg-error-unidade')
	const listUnidades = document.getElementById('list-unidades')	
	const acaoLista = document.getElementById('acaoLista')
	//const acao_Add_Unidade = document.getElementById('acao-add-unidade');

	const AddUnidadeTemp = () => {
		
		//Insere os dados no array
		unidades.push({
			id: '',
			codigo_cliente: codsac.value,
			sigla_Cliente: siglaCliente.value,
			unidade: descricaoUnidade.value,
			numero_sorteio: '',
			ativo: 0,
			finalizado: 0
		})			
				
		descricaoUnidade.value = ""
		descricaoUnidade.focus();
		updateList();			
	}
	
	//Deletar Unidade
	const unidRemove = (event) => {
		const { target } = event
		const { tagName, name } = target

		if ( tagName == "BUTTON" && name == "deleteUnidade" ) {
			if ( target.parentNode ) {
				const params = target.parentNode.querySelectorAll('span')
				const desc = params[0].innerHTML
				
				const formData = new FormData()

				formData.append('CODSAC', codsac.value.length == 3 ? codsac.value : (() => {error_unidade.innerHTML = ""; throw "Código do Sacado inválido"})())
				formData.append('UNIDADE', (regex).test(desc) ? desc : (() => {error_unidade.innerHTML = ""; throw `Unidade inválida`})())
				
				//Requisição
				customer['deleteUnidadeTemp'](formData)
				.then( res => {
					if (res.status === 200) {
						unidades.splice(unidades.findIndex(unidade => unidade.unidade === desc), 1);
						target.parentNode.remove(target.parentNode)
						return;
					}	
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não foi possível deletar a Unidade!</div>`)			
				})
				.catch( err => {
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">fFalha ao excluir a unidade!</div>`)
					//${err.response.data.error || err} 
				})
			}
		}
	}
	
	EventHandler.bind(document, 'click', unidRemove)

	//Gerar os números de sorteio
	const btnAcaoGerarNunmeroSorteio = (event) => {
		const { target } = event
		const { tagName, name } = target

		error_unidade.innerHTML = ""

		try {
			if ( tagName == "BUTTON" && name == "btn-ordenar" ) {
				gerarNumeroSorteio();
			}
		} catch ( e ) {
			error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${e}</div>`)
		}
	}
	
	EventHandler.bind(document, 'click', btnAcaoGerarNunmeroSorteio)
	
	//Listar de A a Z
	const eventListarAaZ = (event) => {
		const { target } = event
		const { tagName, name } = target

		try {
			if ( tagName == "BUTTON" && name == "btn-listar-AaZ" ) {	
				listarAaZ();
				updateList();
			}
		} catch ( e ) {
			error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${e}</div>`)
		}
	}

	EventHandler.bind(document, 'click', eventListarAaZ)

	//Listar de Z a A
	const listarZaA = (event) => {
		const { target } = event
		const { tagName, name } = target

		try {
			if ( tagName == "BUTTON" && name == "btn-listar-ZaA" ) {
				
				unidades.sort((a, b) => a.unidade > b.unidade ? 1 : -1);
				unidades.reverse();
				updateList();
			}
		} catch ( e ) {
			error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${e}</div>`)
		}
	}
	
	EventHandler.bind(document, 'click', listarZaA)


	//Ativar a unidade
	const ativarUnidade = (event) => {
		const { target } = event
		const { tagName, name } = target

		
		if ( tagName == "BUTTON" && name == "ativar" ) {

			if ( target.parentNode ) {
				const params = target.parentNode.querySelectorAll('span')
				const desc = params[0].innerHTML
				
				const dados = {
					acao: 'ativar',
					condomino: params[6].innerHTML,
					numero: params[1].innerHTML,
					email: params[7].innerHTML,
					contato: params[8].innerHTML,
					condominio: nomeCliente.value,
					unidade: params[0].innerHTML
				}


				const formData = new FormData()

				formData.append('CODSAC', codsac.value.length == 3 ? codsac.value : (() => {error_unidade.innerHTML = ""; throw "Código do Sacado inválido"})())
				formData.append('UNIDADE', (regex).test(desc) ? desc : (() => {error_unidade.innerHTML = ""; throw `Unidade inválida`})())
				formData.append('CONDOMINO', dados.condomino)
				formData.append('EMAIL', dados.email)
				formData.append('CONTATO', dados.contato)
				//Requisição
				customer['ativarUnidadeTemp'](formData)
				.then( res => {
					if (res.status === 200) {
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].ativo = 2;
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].condomino = dados.condomino;
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].email = dados.email;
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].contato = dados.contato;
						
						updateList();
						enviaEmail(dados);							
						return;
					}	
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não foi possível ativar a Unidade!</div>`)			
				})
				.catch( err => {
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
				})							
			}			
		}
	}
	
	EventHandler.bind(document, 'click', ativarUnidade)

	//Desativar a unidade
	const desativarUnidade = (event) => {
		const { target } = event
		const { tagName, name } = target

		
		if ( tagName == "BUTTON" && name == "desativar" ) {

			if ( target.parentNode ) {
				const params = target.parentNode.querySelectorAll('span')
				const desc = params[0].innerHTML
				
				const dados = {
					acao: 'desativar',
					condomino: params[2].innerHTML,
					numero: params[1].innerHTML,
					email: params[3].innerHTML,
					contato: params[4].innerHTML,
					condominio: nomeCliente.value,
					unidade: params[0].innerHTML
				}

				const formData = new FormData()

				formData.append('CODSAC', codsac.value.length == 3 ? codsac.value : (() => {error_unidade.innerHTML = ""; throw "Código do Sacado inválido"})())
				formData.append('UNIDADE', (regex).test(desc) ? desc : (() => {error_unidade.innerHTML = ""; throw `Unidade inválida`})())
				
				//Requisição
				customer['desativarUnidadeTemp'](formData)
				.then( res => {
					if (res.status === 200) {
						enviaEmail(dados);	
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].ativo = 0;
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].condomino = "";
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].email = "";
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].email_status = null;
						unidades[unidades.findIndex(unidade => unidade.unidade === desc)].contato = "";
						updateList();
						return;
					}	
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não foi possível desativar a Unidade!</div>`)			
				})
				.catch( err => {
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
				})
			}			
		}
	}
	
	EventHandler.bind(document, 'click', desativarUnidade)



//Solicitar o número de sorteio
const solicitarNumero = (event) => {
	const { target } = event
	const { tagName, name } = target

	
	if ( tagName == "BUTTON" && name == "enviarNumero" ) {

		if ( target.parentNode ) {
			const params = target.parentNode.querySelectorAll('span')
			const dados = {
				acao: 'solicitarNumero',
				condomino: params[2].innerHTML,
				numero: params[1].innerHTML,
				email: params[3].innerHTML,
				contato: params[4].innerHTML,
				condominio: nomeCliente.value,
				unidade: params[0].innerHTML 
			}
			enviaEmail(dados);
		}			
	}
}

EventHandler.bind(document, 'click', solicitarNumero)


	//Finalizar a unidade
	const finalizarUnidade = (event) => {
		const { target } = event
		const { tagName, name } = target

		error_unidade.innerHTML = '';

		if ( tagName == "BUTTON" && name == "btn-finalizar" ) {

			if ( target.parentNode ) {				
				
				try {
						
					gerarNumeroSorteio();
					
					for (let i = 0; i < unidades.length; i++) {
						
						const formData = new FormData();
					
						formData.append('CODSAC', unidades[i].codigo_cliente)
						formData.append('UNIDADE', unidades[i].unidade)
						formData.append('NUMERO_SORTEIO', unidades[i].numero_sorteio)
						
						//Requisição
						const retorno = customer['updateUnidadeTemp'](formData);		
					}
					desabilitaAcaoAddUnidade();
					statusAcaoLista(true);
					listartUnidades();
				} catch (error) {
					error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não foi possível finalizar as unidades!</div>`) 
				}
			}			
		}
	}
	
	EventHandler.bind(document, 'click', finalizarUnidade)
	
	//Adicionar unidade
	const adicionarUnidade = (event) => {
		const { target } = event
		const { tagName, name } = target
				

			if ( tagName == "BUTTON" && name == "add-unidade" ) { 

				error_unidade.innerHTML = ""

			if ( descricaoUnidade.value === '' ) {

				error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">O capo Descrição é obrigatório</div>`)	
				descricaoUnidade.focus()
				return
			}

			if (unidades.find(element => element.unidade === descricaoUnidade.value) !== undefined ) {

				error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Unidade já adicionada!</div>`)	
				descricaoUnidade.focus()
				return
			}

			const formData = new FormData()

			formData.append('CODSAC', codsac.value.length == 3 ? codsac.value : (() => {error_unidade.innerHTML = ""; throw "Código do Sacado inválido"})())
			formData.append('UNIDADE', (regex).test(descricaoUnidade.value) ? descricaoUnidade.value : (() => {error_unidade.innerHTML = ""; throw `Unidade inválida`})())
			formData.append('CLI_SIGLA', (/^[A-Z]{3}$/).test(siglaCliente.value.toUpperCase()) ? siglaCliente.value.toUpperCase() : (() => {throw "Sigla inválida"})())
			//Requisição
			customer['createUnidadeTemp'](formData)
			.then( res => {
				if (res.status === 200) {
					AddUnidadeTemp();
					descricaoUnidade.focus();
					return;
				}	
				error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não foi possível adicionar a Unidade!</div>`)			
			})
			.catch( err => {
				error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
			})

		}

	}

	EventHandler.bind(document, 'click', adicionarUnidade)

	const listartUnidades = () => {
		
		const formData = new FormData()
		formData.append('CODSAC', codsac.value)
		unidades = [];
		customer['getUnidadeTemp'](formData)
		.then( res => {

			const { data } = res;
			
			if (data.length > 0) {
				for (let i = 0; i < data.length; i++) {
						unidades.push({
						unidade: data[i].unidade,
						codigo_cliente: data[i].codigo_cliente,
						id: data[i].id,
						numero_sorteio: data[i].numero_sorteio,
						ativo: data[i].status,
						finalizado: data[i].finalizado,
						condomino: data[i].condomino,
						email: data[i].email,
						contato: data[i].contato,
						email_status: data[i].email_status,
						condomino_temp: data[i].condomino_temp,
						email_temp: data[i].email_temp,
						contato_temp: data[i].contato_temp
					})

				}
				updateList();
			}
					
		})
		.catch( e => {
			error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${e}</div>`) 
		})
	}

	const updateList = () => {

		listUnidades.innerHTML = ""

		if (unidades.length >= 1) {
			
			if (unidades[0].finalizado == 1) {
				listarAaZ(); 
			}
			
			//Cria a tabela
			let table = '<table id ="tabela-unidades" class="table table-condensed" style="margin: 0 !important;">'
			
			unidades.map( (unidade, i)  => {
				i++
				table += `<tr id="${i}"><td style="padding: 10px !important;">`
				table += `<b>Unidade</b>: <span>${unidade.unidade}</span><br />`	

				
				if (unidade.numero_sorteio) {

					table += `<b>Nº de sorteio</b>: <span>${unidade.numero_sorteio}</span><br />`
					
					if (unidade.finalizado == 1) {
						unidade.condomino !== null ? table += `<b>Condômino</b>: <span>${unidade.condomino}</span><br />` : table += `<b>Condômino</b>: <span></span><br />`
						unidade.email !== null ? table += `<b>Email</b>: <span>${unidade.email}</span><br />` : table += `<b>Email</b>: <span></span><br />` 
						unidade.contato !== null ? table += `<b>Contato</b>: <span>${unidade.contato}</span><br />` : table += `<b>Contato</b>: <span></span><br />`
						
						if (unidade.ativo == 2) {
							table += `<button name="editContato" id="editContato" type="button" class="btn btn-warning btn-sm pull-right glyphicon glyphicon-user" data-toggle="modal" data-target="#modalContato"  style="margin: 0 0 0 10px;"> Etitar</button>`									
							table += `<button name="enviarNumero" id="enviarNumero" type="button" class="btn btn-success btn-sm pull-right glyphicon glyphicon-envelope"  style="margin: 0 0 0 10px;"> Enviar nº</button>`	
							table += `<button name="desativar" id="desativar" type="button" class="btn btn-danger btn-sm pull-right" style="margin: 0 0 0 10px;"> Desativar</button>`
						}else {
							unidade.email_status == 1 ? table += `<b>Email-Status: </b> <span style="color: green;">Confirmado</span><br />`
							: table += `<b>Email-Status: </b> <span style="color: red;">Pendente</span><br />`
							table += `<b style="color: #778899;">Informações Temporárias</b><br />`			  
							unidade.condomino_temp !== null ? table += `<b style="color: #778899">Condômino:</b> <span> ${unidade.condomino_temp}</span><br />` : table += `<b style="color: #778899">Condômino:</b> <span></span><br />`
							unidade.email_temp !== null ? table += `<b style="color: #778899">E-mail:</b> <span>${unidade.email_temp}</span><br />` : table += `<b style="color: #778899">E-mail:</b> <span></span><br />`	
							unidade.contato_temp !== null ? table += `<b style="color: #778899">Contato:</b> <span>${unidade.contato_temp}</span></br />` : table += `<b style="color: #778899">Contato:</b> <span></span></br />`		
							table += `<button name="ativar" id="ativar" type="button" class="btn btn-success btn-sm pull-right glyphicon " style="margin: 0 0 0 10px;"> Ativar</button>`
						}
					}	
					
				}else {
					table += `<button name="deleteUnidade" id="deleteUnidade" type="button" class="btn btn-default btn-sm pull-right glyphicon glyphicon-trash" style="margin: 0 0 0 10px;"></button>`
				}		
				table += `</td></tr>`
			})
			
			listUnidades.insertAdjacentHTML('beforeend', table)
		}
		
		updateQtdList();
		acaoAddUnidade();		
	}
	
	const updateQtdList = () => {

		qtdUnidades.innerHTML = ""
		qtdUnidades.insertAdjacentHTML('beforeend', `<span>${unidades.length}</span>`)
	}

	const acaoAddUnidade = () => {

		if (unidades[0].finalizado == 1) {
			desabilitaAcaoAddUnidade();
			statusAcaoLista(true);		
			return;
		}
		habilitaAcaoAddUnidade();
		statusAcaoLista(false);						
	}	

	const desabilitaAcaoAddUnidade = () => {
		descricaoUnidade.disabled = true;
		btnAddUnidade.disabled = true;
	}

	const habilitaAcaoAddUnidade = () => {
		descricaoUnidade.disabled = false;
		btnAddUnidade.disabled = false;
	}

	const statusAcaoLista = (finalizado) => {
		
		acaoLista.innerHTML = '';
		
		if (!finalizado) {
			acaoLista.insertAdjacentHTML('beforeend', '<button type="button" name="btn-listar-AaZ" id="btn-listar-AaZ" style="margin-right: 5px" class="btn btn-primary" ><span class="glyphicon glyphicon-arrow-down"></span> AaZ</button>');
			acaoLista.insertAdjacentHTML('beforeend', '<button type="button" name="btn-listar-ZaA" id="btn-listar-ZaA" style="margin-right: 5px" class="btn btn-primary" ><span class="glyphicon glyphicon-arrow-down"></span> ZaA</button>');
			acaoLista.insertAdjacentHTML('beforeend', '<button type="button" name="btn-ordenar" id="btn-ordenar" style="margin-right: 5px" class="btn btn-primary" ><span class="glyphicon glyphicon-refresh"></span> Gerar nº</button>');
			acaoLista.insertAdjacentHTML('beforeend', '<button type="button" name="btn-finalizar" id="btn-finalizar" class="btn btn-danger" ><span class="glyphicon glyphicon-floppy-saved"></span> Finalizar</button>');		
		}
	}

	const gerarNumeroSorteio = () => {
		if ( unidades.length < 1) {
			error_unidade.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não ha Unidades a serem ordenas</div>`)	
			descricaoUnidade.focus()
			return
		}
		
		unidades.sort((a, b) => a.unidade > b.unidade ? 1 : -1);

		unidades.map( (unidade, i)  => {
			i++			
			unidade.numero_sorteio = codsac.value + i.toString().padStart(3, '0');
		})
		updateList();
	}

	const listarAaZ = () => {
		unidades.sort((a, b) => a.unidade > b.unidade ? 1 : -1);		
	}

	
	const descricaocondomino = document.getElementById('descricao-condomino');
	const email = document.getElementById('email');
	const numerotelefone = document.getElementById('numero-telefone');
	const tipo = document.getElementById('tipoTelefone')

	EventHandler.bind(tipo, 'change', () => VMasker(numerotelefone).maskPattern(tipo.value == '1' ? "(99) 9999-9999" : "(99) 9.9999-9999"))

	const contato = (event) => {
		
		const { target } = event
		const { tagName, name } = target		
		
		if ( tagName == "BUTTON" && name == "editContato" ) {
			
			numerosorteioselecionado = '';
			btncadastrarcontato.disabled = false;
			tipo.value = 0;

			if ( target.parentNode ) {
				error_contato.innerHTML = ""
				const params = target.parentNode.querySelectorAll('span')
				numerosorteioselecionado = params[1].innerHTML
				descricaocondomino.value = params[2].innerHTML
				email.value = params[3].innerHTML
				numerotelefone.value =  params[4].innerHTML
			}
		}
	}

	EventHandler.bind(document, 'click', contato)


	const btncadastrarcontato = document.getElementById('cadastrar-contato');
	const error_contato = document.getElementById('msg-error-contato');

	EventHandler.bind(btncadastrarcontato, 'click', () => { 
		
		//Limpa os erros		
		error_contato.innerHTML = ""
		//Dados que vem do formulário
		const dadosContato = {
			descricao: descricaocondomino.value,
			email: email.value,
			tipo: tipo.value,
			numero: numerotelefone.value
		}

	
		if (testeDadosContato(dadosContato)) {

			const inputs = document.querySelectorAll('#modalContato input, #modalContato select')
					
			const formData = new FormData()

			formData.append('NUMEROSORTEIO', numerosorteioselecionado)
			formData.append('CONDOMINO', dadosContato.descricao)
			formData.append('EMAIL', dadosContato.email)
			formData.append('CONTATO', dadosContato.numero)

			//Requisição
			customer['confirmaContato'](formData)
			.then( res => {
				if (res.status === 200) {
					error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-success" style="margin: 10px 0;">Contato confirmado com sucesso!</div>`)
					//Apaga os dados dos campos
					Array.from(inputs).map( input => input.value = "" )		
					btncadastrarcontato.disabled = true
					listartUnidades()
					return;
				}	
				error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Não foi possível Confirmar o contato na unidade desejada!</div>`)			
			})
			.catch( err => {
				error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
			})	
		}
	})

	function testeAtivar(params) {

		if (params.condomino.length > 0 && params.email.length > 0 && params.contato.length > 0) {
			return true;
		}
		alert('ATENÇÃO: Favor informar todos os dados para o contato.');
	}

	function testeDescricaoCondomino(descricao) {
		
		if (descricao.length > 0 && descricao.length > 4) {
			return true; 
		}			
		descricaocondomino.focus();	
		error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">A descrição do Condômino deve conter cinco ou mais caracteres.</div>`)
		return false;
	}

	function testeEmail(emailparam) {

		if (emailparam.length > 0 && util.validateMail(emailparam)) {
			return true;
		}
		email.focus();
		error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">E-mail inválido.</div>`)
		return false;
	}

	function testeTipoTelefone(param) {
		if (param !== '0') {
			return true;
		}
		error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">Favor informar o Tipo de telefone.</div>`)
		return false;
	}

	function testeNumeroContato(numero) {
		
		const apenasnumeros = VMasker.toNumber(numero)		
	
		if (apenasnumeros.length > 0 && apenasnumeros.length > 9) {
			
			if (testeDDD(apenasnumeros.substr(0, 2))) {
				return true;
			}
			
			numerotelefone.focus();
			error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">O DDD informado é inválido.</div>`)	
			return false;
		}

		numerotelefone.focus();
		error_contato.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">O Número informado é inválido.</div>`)
		return false;
	}

	function testeDDD(ddd) {
		
		const listDdd = [
			'11', '12', '13', '14', '15', '16', '17', '18', '19', '21', 
			'22', '24', '27', '28', '31', '32', '33', '34', '35', '37', 
			'38', '41', '42', '43', '44', '45', '46', '47', '48', '49', 
			'51', '53', '54', '55', '61', '62', '63', '64', '65', '66', 
			'67', '68', '69', '71', '73', '74', '75', '77', '79', '81', 
			'82', '83', '84', '85', '86', '87', '88', '89', '91', '92', 
			'93', '94', '95', '96', '97', '98', '99'
		]

		for (let i = 0; i < listDdd.length; i++) {
			if (ddd === listDdd[i]) {				
				return true;
			}  	
		}		
		return false;
	}

	function testeDadosContato(dados) {
		if (testeDescricaoCondomino(dados.descricao) && 
			testeEmail(dados.email) && 
			testeTipoTelefone(dados.tipo) &&
			testeNumeroContato(dados.numero)) {
			return true;
		}
		return false;
	}


	function enviaEmail(dados) {
		
		dados.acao === 'solicitarNumero' ? document.getElementById('enviarNumero').innerText = ' Enviando...' : '';
		
		mail.send({ 
			acao: dados.acao,
			customerName: dados.condomino,
			customerMail: dados.email,
			numeroSorteio: dados.numero,
			condominio: dados.condominio,
			unidade: dados.unidade
		})
		.then( res => {
			const { data } = res
			const { error } = data
			
			
			//Verifica se há erros de processamento
			if ( error ) {
				error_contato.innerHTML = ""
				document.getElementById('enviarNumero').innerText = ' Enviar nº';
				return error_contato.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error}</section>`) 
			}
			document.getElementById('enviarNumero').innerText = ' Enviar nº';
			alert("E-mail enviado com sucesso!");
		
		})
		.catch( err => {
			error_contato.innerHTML = ""
			error_contato.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err.message}</section>`) 
		})
	}	

})();