const { error } = require('toastr');
const { getGanhador } = require('../api/adimplencia');

(() => {
	'use strict'	
	const adimplencia = require('../api/adimplencia');
	const input = require('../modules/input');
	const util = require('../modules/util');
	const mail = require('../api/adimplencia')
	const format = new Format();

	/*
	* Lista dos Prêmios sorteados
	*/
	let sorteios = [];
	let parametros = [];
	let listSorteiosTest = [];
	let ganhador = {};
	let concursoExcluir = {};
	
	//Inputs
	const linkLoteriaFederal = document.querySelector('#link-loteria-federal');
	const btnEditarLinkModal = document.getElementById('editar-link-loteria-federal');
	const btnEditarLinkLoteriaFederal = document.getElementById('btn-editar-link');
	const descricaoLink = document.getElementById('descricao-link'); 
	const msgErrorLink = document.getElementById('msg-error-link');
	const btnCloseModalLink = document.getElementById('btn-close-modal-link');
	const msgErrorModalSorteio = document.getElementById('msg-error-modalsorteio');	
	const tipoAcao = document.getElementById('tipo-acao');
	const acoes = document.getElementById('acoes');
	const acoes2 = document.getElementById('acoes2');
	const msgError =document.getElementById('msg-error');
	const listSorteios = document.getElementById('list-sorteios');
	const qtdSoteios = document.getElementById('qtdSorteios');
	const audio = document.querySelector('audio');
	const btnExcluirConcurso = document.getElementById('btn-excluir-sorteio');
	
	//Inicializacoes	
	setTimeout(() => {
		listConfigParametros();
		//listarSorteios();		
	}, 1000)

	

	//Validação de data
	VMasker(document.querySelectorAll('.maskData')).maskPattern("99/99/9999");



	//EventHandler
	EventHandler.bind(btnEditarLinkLoteriaFederal, 'click', () => {
		
		//Limpa os erros		
		msgErrorLink.innerHTML = ""		
	
		if (testeDescricaoLink()) {
			
			const dadosLink = {
				valor: descricaoLink.value,
			}
			
			//Dados que vem do formulário
			const formData = new FormData()
			formData.append('LINK', dadosLink.valor)
			//Requisição
			adimplencia['editLinkLoteriaFederal'](formData)
			.then( res => {
				if (res.status === 200) {
					descricaoLink.value = '';
					msgErrorLink.insertAdjacentHTML('beforeend', `<div class="alert alert-success" style="margin: 5px 10;">Link alterado com sucesso!</div>`)
					setLinkLoteriaFederal(dadosLink.valor);
					closeMoadalLink();					
					return;
				}	
				msgErrorLink.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não foi possível Alterar oLink!</div>`)			
			})
			.catch( err => {
				msgErrorLink.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
			})	
		}
	})


	EventHandler.bind(tipoAcao, 'change', () => {
		setAcao(tipoAcao.value);
	})

	EventHandler.bind(btnEditarLinkModal, 'click', () => {
		msgErrorLink.innerHTML = "";
		descricaoLink.value = linkLoteriaFederal.href;
	})

	//Métodos/Funções

	
	const listConfigParametros = () => {
		
		
		adimplencia['getConfigParams']()
		.then( res => {
			const { data } = res;
			
			if (data.length > 0) {
				for (let i = 0; i < data.length; i++) {
					parametros.push({
						id: data[i].id,
						descricao: data[i].descricao,
						valor: data[i].valor
					})				
				}
				loadLinkLoteriaFederal();
			}
					
		})
		.catch( e => {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não foi possível listar os sorteios: Erro: ${e}</div>`) 
		})
	}

	const setLinkLoteriaFederal = (link) => {
		linkLoteriaFederal.href = link;
	}

	const loadLinkLoteriaFederal = () => {
		if (parametros.length >= 1) {
			for (let i = 0; i < parametros.length; i++) {
				if (parametros[i].descricao === "link-loteria-federal") {
					linkLoteriaFederal.href = parametros[i].valor;
				}					
			}
		}
	}

	const closeMoadalLink = () => {
		setTimeout(() => {
			btnCloseModalLink.click();
		}, 4000);
	}

	const setAcao = (acao) => {
		
		msgError.innerHTML = "";
		acoes.innerHTML = "";
		acoes2.innerHTML = "";
		listSorteios.innerHTML = "";
		qtdSoteios.innerHTML = "";

		switch (acao) {
			case '1':
				acaoFiltrarConcurso();
				const concursoNumero = document.getElementById('concurso-numero');
				EventHandler.bind(concursoNumero, 'keyup', () => input.only('number', concursoNumero));
				concursoNumero.focus();
				break;
			case '2':
				acaoFiltrarBilhete();
				const concursoBilhete = document.getElementById('concurso-bilhete');
				EventHandler.bind(concursoBilhete, 'keyup', () => input.only('number', concursoBilhete));
				concursoBilhete.focus();
				break;

				case '3':
					acaoFiltrarPeriodo();
					const concursoDataInicial = document.getElementById('concurso-data-inicial');
					const concursoDataFinal = document.getElementById('concurso-data-final');
					//Validação de data
					VMasker(document.querySelectorAll('.maskData')).maskPattern("99/99/9999");
					EventHandler.bind(concursoDataInicial, 'blur', () => input.format(concursoDataInicial, util.validarData(concursoDataInicial.value)));
					EventHandler.bind(concursoDataFinal, 'blur', () => input.format(concursoDataFinal, util.validarData(concursoDataFinal.value)));
					concursoDataInicial.focus();
					break;
				case '4':
					consultarSorteados();
					break;
				
				case '5':
					acaoAddSorteio();
					listarSorteios();
					const concursoNumeroAdd = document.getElementById('concurso-numero');
					const concursoNumeroSorteado = document.getElementById('concurso-bilhete');
					EventHandler.bind(concursoNumeroAdd, 'keyup', () => input.only('number', concursoNumeroAdd));
					EventHandler.bind(concursoNumeroSorteado, 'keyup', () => input.only('number', concursoNumeroSorteado));	
					concursoNumeroAdd.focus();
					const concursoData = document.getElementById('concurso-data');
					VMasker(document.querySelectorAll('.maskData')).maskPattern("99/99/9999");
					EventHandler.bind(concursoData, 'blur', () => input.format(concursoData, util.validarData(concursoData.value)));
					break;
			default:
				break;
		}
	}

	const acaoFiltrarConcurso = () => {

		acoes.insertAdjacentHTML('beforeend', componentConcurso());		
	}

	const acaoFiltrarBilhete = () => {

		acoes.insertAdjacentHTML('beforeend', componentBilhete());		
	}

	const acaoFiltrarPeriodo = () => {

		acoes2.insertAdjacentHTML('beforeend', componentPeriodo());		
	}

	const acaoAddSorteio = () => {

		acoes2.insertAdjacentHTML('beforeend', componentAddSorteio());		
	}	

	const componentConcurso = () => {
		return `<div class="col-lg-3">
					<label>Concurso </label>
					<div class="input-group">
		  				<input type="text" name="concurso-numero" id="concurso-numero" class="form-control" placeholder="Nº do concurso..." maxlength="6">
		  				<span class="input-group-btn">
							<button name="btnConsultar-concurso" id="btnConsultar-concurso" class="btn btn-primary" type="button"> <span class="glyphicon glyphicon-search"></span></button>
		  				</span>
					</div>
	  			</div>`;
	}

	const componentBilhete = () => {
		return `<div class="col-lg-3">
					<label>Nº sorteado </label>
					<div class="input-group">
			  			<input type="text" name="concurso-bilhete" id="concurso-bilhete" class="form-control" placeholder="Digite o número..." maxlength="6">
			  			<span class="input-group-btn">
							<button name="btnConsultar-bilhete" id="btnConsultar-bilhete" class="btn btn-primary" type="button"> <span class="glyphicon glyphicon-search"></span></button>
			  			</span>
					</div>
	  			</div>`;
	}

	const componentPeriodo = () => {
		return `<div class="col-md-3">
					<label>Data inicial </label>
					<div class="input-group">
						<span class="input-group-addon" id=""><span class="glyphicon glyphicon-calendar"></span></span>
						<input type="text" class="form-control maskData" name="concurso-data-inicial" id="concurso-data-inicial" maxlength="10" />
					</div>
	  			</div>
				<div class="col-md-3">
				  <label>Data final </label>
				  	<div class="input-group">
				  		<span class="input-group-addon" id=""><span class="glyphicon glyphicon-calendar"></span></span>
				  			<input type="text" class="form-control maskData" name="concurso-data-final" id="concurso-data-final" maxlength="10" />
						<span class="input-group-btn" id="">
							<button name="btnConsultar-periodo" id="btnConsultar-periodo" class="btn btn-primary" type="button"> <span class="glyphicon glyphicon-search"></span> </button>
						</span>
				  	</div><br />
				</div>`;
	}

	const componentAddSorteio = () => {
		return `<div class="col-md-2">
					<label>Concurso </label>
					<div class="input-group">
			  			<input type="text" name="concurso-numero" id="concurso-numero" class="form-control" placeholder="Nº do concurso..." maxlength="6">
		 		    </div>
	  			</div>
				<div class="col-md-2">
					<label>Data</label>
					<div class="input-group">
						<input type="text" class="form-control maskData" name="concurso-data" id="concurso-data" placeholder="____/____/____" maxlength="10" />
					</div>
	  			</div>
				<div class="col-md-2">
					<label>Nº sorteado </label>
					<div class="input-group">
						<input type="text" name="concurso-bilhete" id="concurso-bilhete" class="form-control" placeholder="Digite o nº..." maxlength="6">
						<span class="input-group-btn" id="">
						<button type="button" name="add-sorteio" id="add-sorteio" class="btn btn-primary" >Add</button>
						</span>
					</div><br />
				</div>`;
	}


	//Consultar Sorteio
	const consultarConcurso = (event) => {
		const { target } = event
		const { tagName, name } = target
				

			if ( tagName == "BUTTON" && name == "btnConsultar-concurso" ) { 
				
				msgError.innerHTML = ""
				const concursoNumero = document.getElementById('concurso-numero');
				
				const dados = {
					concurso: concursoNumero
				}
				
				if (testeConcurso(dados)) {
					
					const formData = new FormData()
					formData.append('CONCURSO', dados.concurso.value);
					msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Listando concursos.</span>` );
					
					//Requisição
					adimplencia['consultarSorteio'](formData)
					.then( res => {
							const { data } = res;		
							msgError.innerHTML = "";

						if (data.length > 0) {
							sorteios = [];

							for (let i = 0; i < data.length; i++) {
								sorteios.push({
									id: data[i].id,
									concurso: data[i].concurso,
									data: data[i].data,
									bilhete: data[i].bilhete,
									ganhador: data[i].ganhador,
									contato: data[i].contato,
									email: data[i].email,
									cliente: data[i].cliente,
									unidade: data[i].unidade,
									status: data[i].status
								})				
							}
							concursoNumero.value = '';
							updateList();
							return;
						}
						concursoNumero.focus();
						msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Concurso não existe.</div>`);
						return ;	
					})
					.catch( err => {
						msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10px;">${err.response.data.error || err}</div>`) 
					})
				}								
			}
	}

	EventHandler.bind(document, 'click', consultarConcurso)

	//Consultar Sorteio
	const consultarBilhete = (event) => {
		const { target } = event
		const { tagName, name } = target
				

			if ( tagName == "BUTTON" && name == "btnConsultar-bilhete" ) { 
				
				msgError.innerHTML = ""
				const concursoBilhete = document.getElementById('concurso-bilhete');
				
				const dados = {
					bilhete: concursoBilhete
				}
				
				if (testeBilhete(dados)) {
					
					const formData = new FormData()
					formData.append('BILHETE', dados.bilhete.value);
					msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Consultando número sorteado.</span>`);
					
					//Requisição
					adimplencia['consultarBilhete'](formData)
					.then( res => {
							const { data } = res;		
							msgError.innerHTML = "";

						if (data.length > 0) {
							sorteios = [];

							for (let i = 0; i < data.length; i++) {
								sorteios.push({
									id: data[i].id,
									concurso: data[i].concurso,
									data: data[i].data,
									bilhete: data[i].bilhete,
									ganhador: data[i].ganhador,
									contato: data[i].contato,
									email: data[i].email,
									cliente: data[i].cliente,
									unidade: data[i].unidade,
									status: data[i].status
								})				
							}
							concursoBilhete.value = '';
							updateList();
							return;
						}
						concursoBilhete.focus();
						msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Bilhete não existe.</div>`);
						return ;	
					})
					.catch( err => {
						msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
					})
				}								
			}
	}

	EventHandler.bind(document, 'click', consultarBilhete)

	
	const consultaPeriodo = (event) => {
		const { target } = event
		const { tagName, name } = target
				

			if ( tagName == "BUTTON" && name == "btnConsultar-periodo" ) { 
				
				msgError.innerHTML = ""
				const dataInicial = document.getElementById('concurso-data-inicial');
				const dataFinal = document.getElementById('concurso-data-final');
				
				const dados = {
					dataInicial: dataInicial,
					dataFinal: dataFinal
				}
				
				if (testeDatas(dados)) {
					
				const formData = new FormData()
					
					msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Consultando por período.</span>`);
					formData.append('DATAINICIAL', dados.dataInicial.value);
					formData.append('DATAFINAL', dados.dataFinal.value);
					
					//Requisição
					adimplencia['consultarPeriodo'](formData)
					.then( res => {
						const { data } = res;		
						msgError.innerHTML = "";

						if (data.length > 0) {
							sorteios = [];

							for (let i = 0; i < data.length; i++) {
								sorteios.push({
									id: data[i].id,
									concurso: data[i].concurso,
									data: data[i].data,
									bilhete: data[i].bilhete,
									ganhador: data[i].ganhador,
									contato: data[i].contato,
									email: data[i].email,
									cliente: data[i].cliente,
									unidade: data[i].unidade,
									status: data[i].status
								})				
							}
							listSorteiosTest = sorteios;
							dataInicial.value = '';
							dataFinal.value = '';
							updateList();
							return;
						}
						dataInicial.focus();
						msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não há sorteios no período informado.</div>`);
						return ;	
					})
					.catch( err => {
						msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
					})
				}								
			}
	}

	EventHandler.bind(document, 'click', consultaPeriodo);

	
	const consultarSorteados = () => {
		
		msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Consultando sorteados.</span>`);
		
		//Requisição
		adimplencia['consultarSorteados']()
		.then( res => {
				const { data } = res;		
				msgError.innerHTML = "";
			if (data.length > 0) {
				sorteios = []
				for (let i = 0; i < data.length; i++) {
					sorteios.push({
						id: data[i].id,
						concurso: data[i].concurso,
						data: data[i].data,
						bilhete: data[i].bilhete,
						ganhador: data[i].ganhador,
						contato: data[i].contato,
						email: data[i].email,
						cliente: data[i].cliente,
						unidade: data[i].unidade,
						status: data[i].status
					})				
				}
				updateList();
				return;
			}
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não há sorteados até o momento.</div>`);
		})
		.catch( err => {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
		})		
	}

	const listarSorteios = () => {

		msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Listando sorteios.</span>`);
		listSorteiosTest = [];
		
		adimplencia['getSorteios']()
		.then( res => {
			const { data } = res;
			msgError.innerHTML = "";
			if (data.length > 0) {
				for (let i = 0; i < data.length; i++) {
					listSorteiosTest.push({
						id: data[i].id,
						concurso: data[i].concurso,
						data: data[i].data,
						bilhete: data[i].bilhete,
						ganhador: data[i].ganhador,
						contato: data[i].contato,
						email: data[i].email,
						cliente: data[i].cliente,
						unidade: data[i].unidade,
						status: data[i].status
					})				
				}
			}
		})
		.catch( e => {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10px;"><b>Atenção</b>! Não foi possível listar os sorteios: Erro: ${e}</div>`) 
		})
	}

	const imgLoading = () => {
		return '<img src="/painel/build/images/loading.gif" width="15" /> <b> Aguarde</b>... ';
	}

	const updateList = () => {

		listSorteios.innerHTML = ""
		
		if (sorteios.length >= 1) {
			
			//Cria a tabela
			let table = '<table id ="tabela-sorteios" class="table table-condensed" style="margin: 0 !important;">'
			
			sorteios.map( (sorteio, i)  => {
				i++
				table += `<tr id="${i}"><td style="padding: 10px !important;">`
				table += `<b>Concurso</b>: <span>${sorteio.concurso}</span><b class="pull-right" style="color: #fff;">Id: <span>${i-1}</span><span > ${sorteio.id}</span style="color: #fff;"><span>${sorteio.status}</span>${sorteio.status == 1 ? '<div style="color: green;">Sorteado <b class="glyphicon glyphicon-ok"></b></div>' : ''}</b><br />`
				table += `<b>Data</b>: <span>${ converteData2(sorteio.data) }</span><br />`
				table += `<b>Nº Sorteado</b>: <span>${sorteio.bilhete}</span><br />`
				table +=  sorteio.status == 1 ? `<b>Contemplado</b>: <span style="color: green">${sorteio.ganhador}</span><br />` : '';
				table +=  sorteio.status == 1 ? `<b>Contato</b>: <span style="color: green">${sorteio.contato}</span><br />` : '';
				table +=  sorteio.status == 1 ? `<b>E-mail</b>: <span style="color: green">${sorteio.email}</span><br />` : '';
				table +=  sorteio.status == 1 ? `<b>Cliente</b>: <span style="color: green">${sorteio.cliente}</span><br />` : '';
				table +=  sorteio.status == 1 ? `<b>Unidade</b>: <span style="color: green">${sorteio.unidade}</span><br />` : '';
								
				table +=  sorteio.status != 1 ? `<button name="excluir" id="excluir" type="button" class="btn btn-danger btn-sm pull-right glyphicon glyphicon-trash" data-toggle="modal" data-target="#modal-excluir" style="margin: 0 0 0 10px;"> Excluir</button>` : '';
				table +=  sorteio.status != 1 ? `<button name="editar" id="editar" type="button" class="btn btn-warning btn-sm pull-right glyphicon glyphicon-edit" data-toggle="modal" data-target="#modal-sorteio"  style="margin: 0 0 0 10px;"> Editar</button>` : '';		
			})			
			listSorteios.insertAdjacentHTML('beforeend', table)
		}
		
		updateQtdList();	
	}

	const updateQtdList = () => {

		qtdSoteios.innerHTML = "";
		qtdSoteios.insertAdjacentHTML('beforeend', `<span>${sorteios.length}</span>`);
	}

	const testeGanhador = (dados) => {

		msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Verificando se há ganhador.</span>`);
		const formData = new FormData();
		formData.append('NUMERO_SORTEIO', dados.bilhete.value);	
		
		adimplencia['getGanhador'](formData)
		.then( res => {
			const { data } = res;
			msgError.innerHTML ="";

			if (data.length >= 1) {
				ganhador = new Object({
					id: data[0].id,
					codigo_cliente : data[0].codigo_cliente,
					unidade: data[0].unidade,
					numero_sorteio: data[0].numero_sorteio,
					status: data[0].status,
					finalizado: data[0].finalizado,
					cliente: data[0].sigla,
					condomino: data[0].condomino,
					email: data[0].email,
					contato: data[0].contato,
					email_status: data[0].email_status,
					condomino_temp: data[0].condomino_temp,
					email_temp: data[0].email_temp,
					contato_temp: data[0].contato_temp,
					cliente_sty: data[0].cliente,
					dados_sorteio: dados
				})
				dados.ganhador = ganhador;
				exibeGanhadro(ganhador);				
				return;
			}
			registrarSorteio(dados);
			return;
						
		})
		.catch( err => {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não foi possível filtrar um ganhador: Erro: ${err}</div>`)
		})	
	}

	//Adicionar Sorteio
	const adicionarSorteio = (event) => {
		const { target } = event
		const { tagName, name } = target
		
		if ( tagName == "BUTTON" && name == "add-sorteio" ) { 
			msgError.innerHTML = ""
			
			const dados = {
				concurso: document.getElementById('concurso-numero'),
				data: document.getElementById('concurso-data'),
				bilhete: document.getElementById('concurso-bilhete'),
				ganhador: {},
				msg: msgError
			}

			if (testeCamposAddConcurso(dados) && testeSorteioExiste(dados)) {
				testeGanhador(dados);
			}
		}	
	}

	EventHandler.bind(document, 'click', adicionarSorteio)

	//Adiciona o Sorteio na lista
	const AddSorteioLista = (dados) => {
		
		sorteios = [];

		let sorteio = {
			//id: dados.ganhador.id,
			concurso: dados.concurso.value,
			data: converteData1(dados.data.value),
			bilhete: dados.bilhete.value,
			/*ganhador: dados.ganhador.condomino,
			contato: dados.ganhador.contato,
			email: dados.ganhador.email,
			cliente: dados.ganhador.sigla,
			unidade: dados.ganhador.unidade,
			status: dados.ganhador.status*/
		}
		sorteios.push(sorteio);
		addListSorteiosTest(sorteio);
		updateList();
	}

		//Adiciona o Sorteio na lista
		const AddSorteadoLista = () => {
		
			sorteios = [];
	
			let sorteio = {
				id: ganhador.id,
				concurso: ganhador.dados_sorteio.concurso.value,
				data: converteData1(ganhador.dados_sorteio.data.value),
				bilhete: ganhador.dados_sorteio.bilhete.value,
				ganhador: ganhador.condomino,
				contato: ganhador.contato,
				email: ganhador.email,
				cliente: ganhador.cliente,
				unidade: ganhador.unidade,
				status: 1
			}
			sorteios.push(sorteio);
			addListSorteiosTest(sorteio);
			updateList();
		}
	
	const setConcursoExcluir = (event) => {
	
		const { target } = event
		const { tagName, name } = target		
		
		if ( tagName == "BUTTON" && name == "excluir" ) {
			
			if ( target.parentNode ) {
			
				msgErrorModalSorteio.innerHTML = ""
				const params = target.parentNode.querySelectorAll('span');

				concursoExcluir = {
					id:  params[2].innerHTML,
					index: params[1].innerHTML,
					numero_concurso: params[0].innerHTML,
					status:  params[3].innerHTML
				}
			}
		}
	}

	EventHandler.bind(document, 'click', setConcursoExcluir)
	

	const confirmarGanhador = (event) => {
	
		const { target } = event
		const { tagName, name } = target		
		
		if ( tagName == "BUTTON" && name == "confirmar-ganhador" ) {
			
			if ( target.parentNode ) {
				audio.pause();
				registrarSorteado();
			}
		}
	}

	EventHandler.bind(document, 'click', confirmarGanhador)

	const cancelarGanhador = (event) => {
	
		const { target } = event
		const { tagName, name } = target		
		
		if ( tagName == "BUTTON" && name == "cancelar-ganhador" ) {
			
			if ( target.parentNode ) {
				
				msgError.innerHTML ="";
				ganhador ={};
				document.getElementById('concurso-bilhete').focus();
			}
		}
	}

	EventHandler.bind(document, 'click', cancelarGanhador)

	const acaoConfirmarRegistrarSorteio = (event) => {
	
		const { target } = event
		const { tagName, name } = target		
		
		if ( tagName == "BUTTON" && name == "confirmar-sorteio" ) {
			
			if ( target.parentNode ) {
				
				msgError.innerHTML ="";
				registrarSorteio(ganhador.dados_sorteio);
			}
		}
	}

	EventHandler.bind(document, 'click', acaoConfirmarRegistrarSorteio)


	const registrarSorteio = (dados) => {
		
		msgError.innerHTML = "";
		msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Registrando Sorteio.</span>`);

		const formData = new FormData()
		formData.append('CONCURSO', dados.concurso.value);
		formData.append('DATA', converteData1(dados.data.value));
		formData.append('BILHETE', dados.bilhete.value);
		
		//Requisição
		adimplencia['registrarSorteio'](formData)
		.then( res => {
			if (res.status === 200) {
				msgError.innerHTML = "";
				AddSorteioLista(dados);	
				limparCamposAddConcurso();
				return;
			}	
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não foi possível registrar o Sorteio!</div>`)			
		})
		.catch( err => {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
		})
	}
	
	
	const registrarSorteado = () => {
		
		msgError.innerHTML = "";
		msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Registrando Sorteado</span>`);

		const formData = new FormData()
		formData.append('CONCURSO', ganhador.dados_sorteio.concurso.value);
		formData.append('DATA', converteData1(ganhador.dados_sorteio.data.value));
		formData.append('BILHETE', ganhador.dados_sorteio.bilhete.value);
		formData.append('CLIENTE', ganhador.cliente);
		formData.append('UNIDADE', ganhador.unidade);
		formData.append('STATUS', 1);
		formData.append('GANHADOR', ganhador.condomino);
		formData.append('CONTATO', ganhador.contato);
		formData.append('EMAIL', ganhador.email);
		
		//Requisição
		adimplencia['registrarSorteado'](formData)
		.then( res => {
			if (res.status === 200) {				
				msgError.innerHTML = "";
				AddSorteadoLista();
				//limparCamposAddConcurso();*/
				enviaEmailContemplado(ganhador);
				ganhador ={};							
				return;
			}	
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não foi possível registrar o Sorteado.</div>`)			
		})
		.catch( err => {
			alert(JSON.stringify(err))
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;">${err.response.data.error || err}</div>`) 
		})
	}


	//Excluir Sorteio
	EventHandler.bind(btnExcluirConcurso, 'click', () => {
			
		msgError.innerHTML = `<span>${imgLoading()} Excluíndo Sorteio.</span>`;
		
		const formData = new FormData()
		formData.append('CONCURSO', concursoExcluir.numero_concurso);								
	
		//Requisição
		adimplencia['deleteConcurso'](formData)
		.then( res => {
			if (res.status === 200) {
				msgError.innerHTML = "";
				sorteios.splice(concursoExcluir.index, 1);
				updateList();
				return;
			}	
			msgError.innerHTML = `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Não foi possível excluir o concurso!</div>`;
		})
		.catch( err => {
			msgError.innerHTML = `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! Falha ao excluir o concurso!</div>`;
			//${err.response.data.error || err} 
		})	
	
	})

	const addListSorteiosTest = (dados) => {
		
		listSorteiosTest.push(dados);	
	} 

	const limparCamposAddConcurso = () => {
		document.getElementById('concurso-numero').value = "";
		document.getElementById('concurso-data').value = "";
		document.getElementById('concurso-bilhete').value = "";
		document.getElementById('concurso-numero').focus();	
	}

	const testeConcurso = (dados) => {
		if ( dados.concurso.value === '') {
			dados.msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O campo Concurso é obrigatório.</div>`)	
			dados.concurso.focus()
			return false;
		}
		return true;
	}

	const testeDatas = (dados) => {

		if ( testeDataInicial(dados) && testeDataFinal(dados) ) {
			if (testeDataValida(dados)) {
				return true 
			}
		}
		return false;
	}

	const testeDataInicial = (dados) => {

		if ( dados.dataInicial.value === '') {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O campo Data inicial é obrigatório.</div>`)	
			dados.dataInicial.focus();
			return false;
		} 
		return true;
	}

	const testeDataFinal = (dados) => {

		if ( dados.dataFinal.value === '') {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O campo Data final é obrigatório.</div>`)	
			dados.dataFinal.focus();
			return false;
		} 
		return true;
	}

	const testeDataValida = (dados) => {
	
		if ((testeDataInicialValida(dados)) && testeDataFinalValida(dados) ) {
			if (testeDataFinalMenor(dados)) {
				
				return true;
			}
		}
		return false;
	}

	const testeDataInicialValida = (dados) => {
				
		if (util.validarData(dados.dataInicial.value)) {
			return true;
		}
		msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! A data inicial é inválida.</div>`)	
		dados.dataInicial.focus();
		return false;
	}

	const testeDataFinalValida = (dados) => {
		
		if (util.validarData(dados.dataFinal.value)) {
			return true;
		}
		msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! A data final é inválida!</div>`)	
		dados.dataFinal.focus();
		return false;
	}
	
	const testeDataFinalMenor = (dados) => {

		msgError.innerHTML = "";

		let data_inicial_split = dados.dataInicial.value.split("/");
		let data_final_split = dados.dataFinal.value.split("/");

		let data_inicial = new Date(data_inicial_split[2], data_inicial_split[1], data_inicial_split[0]);
		let data_final = new Date(data_final_split[2], data_final_split[1], data_final_split[0]);
		let data = new Date();
		let data_atual = new Date(data.getFullYear(), String(data.getMonth() + 1).padStart(2, '0'), String(data.getDate()).padStart(2,'0'));

		if(data_inicial > data_atual) {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger " style="margin-top: 5px 10;">A Data inicial não pode ser maior que a data atual.</div>`);
			dataInicial.focus();
			return false;
		}
	
		if(data_final > data_atual) {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger " style="margin-top: 5px 10;">A Data final não pode ser maior que a data atual.</div>`);
			dataFinal.focus();
			return false;
		}
	
		if (data_inicial > data_final) {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger " style="margin-top: 5px 10;">A Data inicial não pode ser maior que a data final.</div>`);
			dataInicial.focus();
			return false;
		}
		return true;

	}

	const testeCamposAddConcurso = (dados) => {

		if (testeConcurso(dados) && testeData(dados) && testeBilhete(dados)) {
			return true;
		}
		return false;
	}

	const testeData = (dados) => {

		if ( dados.data.value === '') {
			dados.msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O campo Data é obrigatório.</div>`)	
			dados.data.focus();
			return false;
		}else if (!util.validarData(dados.data.value)) {
			msgError.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! A data informada é inválida.</div>`)	
			dados.data.focus();
			return false;
		} 
		return true;
	}
	
	

	const testeConcursoExiste = (dados) => {
		
		for (let i = 0; i < listSorteiosTest.length; i++) {
			
			if (dados.concurso.value == listSorteiosTest[i].concurso) {
				dados.msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O Concurso <b>${dados.concurso.value}</b> já foi sorteado em: ${converteData2(listSorteiosTest[i].data)}.</div>`)	
				dados.concurso.focus()
				return false;
			}				
		}
		return true;
	}

	const testeDataExiste = (dados) => {

		for (let i = 0; i < listSorteiosTest.length; i++) {
			
			if (converteData1(dados.data.value) == listSorteiosTest[i].data) {
				dados.msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! A data ${dados.data.value} já foi sorteada no concurso: <b>${listSorteiosTest[i].concurso}</b>.</div>`)	
				dados.data.focus()
				return false;
			}				
		}
		return true;
	}

	const testeBilhete = (dados) => {

		if ( dados.bilhete.value === '' ) {
			dados.msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O campoNº sorteado é obrigatório.</div>`)	
			dados.bilhete.focus()
			return false;
		}
		if ( dados.bilhete.value.length < 6) {
			dados.msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O Nº sorteado não pode ser menor que 6 dígitos.</div>`)	
			dados.bilhete.focus()
			return false;
		}
		return true;
	}

	const testeSorteioExiste = (dados) => {	
		
		if (testeConcursoExiste(dados) && testeDataExiste(dados)) {
			return true;
		}
		return false;
	}

	const testeDescricaoLink = () => {
		
		if ( descricaoLink.value === '') {

			msgErrorLink.insertAdjacentHTML('beforeend', `<div class="alert alert-danger" style="margin: 5px 10;"><b>Atenção</b>! O campo Descrição é obrigatório.</div>`)	
			descricaoLink.focus()
			return false;
		}
		return true;
	}

	
	const closeMoadalSorteio = () => {
		setTimeout(() => {
			btnCloseModalSorteio.click();
		}, 4000);
	}

		
	const converteData1 = (data) => {

		const novaData = data.split("/");
		  
		let d = novaData[0];
		let m = novaData[1];
		let a = novaData[2];
	
		return a + '-' + m + '-' + d;
	}

	const converteData2 = (data) => {

		const novaData = data.split("-");
		  
		let a = novaData[0];
		let m = novaData[1];
		let d = novaData[2];
	
		return d + '/' + m + '/' + a;
	}

	const exibeGanhadro = (dados) => {

		return  msgError.insertAdjacentHTML('beforeend', `
			<div class="alert alert-warning role="alert" style="margin: 5px 10;">
				<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
				${exibeInformacoesGanhador(dados)}
			</div>
		`);	
	}

	const exibeInformacoesGanhador = (dados) => {

		switch (dados.status) {
			case '0':

				return `<span class="sr-only">Error:</span> O número <b>${dados.numero_sorteio}</b></span> foi Sorteado. 
					Porém, não contemplado, pois o status da unidade se encontra <b>DESATIVADO</b>. Verifique se os dados informados 
					acima conferem e confirme o sorteios.<hr/>
					${exibeDadosGanhador(dados)}
					${exibeAcoesGanhador(dados)}
				`;
			case '1':
				return `<span class="sr-only">Error:</span> O número <b>${dados.numero_sorteio}</b></span> foi Sorteado. 
					Porém, não contemplado, pois o status da unidade se encontra em <b>ANALISE</b>. Verifique se os dados informados 
					acima conferem, analise o motivo da pendência e confirme o sorteio.<hr/>
					${exibeDadosGanhador(dados)}
					${exibeAcoesGanhador(dados)}
				`;
			case '2':
				audio.play();
				return `<span class="sr-only">Error:</span> O número <b>${dados.numero_sorteio}</b></span> foi Sorteado. 
					Verifique se os dados informados acima conferem, confirme a ADIMPLÊNCIA e só depois confirme o GANHADOR :<hr/> <span class="pull-right"><img src="/painel/build/images/game-football.gif" width="130" /></span>
					${exibeDadosGanhador(dados)}
					${exibeAcoesGanhador(dados)}
				`;
	
			default:
				break;
		}
	}

	const exibeDadosGanhador = (dados) => {		
	
		return `Sigla: ${dados.cliente}<br />
				Nº de sorteio: <b>${dados.numero_sorteio}</b><br/>		
				Unidade: ${dados.unidade}<br />
				${dados.condomino != null ? dadosGanhador(dados) : dadosGanhadorTemp(dados)}
			`;
	}

	const dadosGanhador = (dados) => {
		return `
			${dados.condomino != null ? 'Ganhador: ' + dados.condomino + '<br />' : ''}
			${dados.contato != null ? 'Contato: ' + dados.contato + '<br />' : ''}
			${dados.email != null ? 'E-mail: ' + dados.email + '<br />' : ''}
		`;
	}

	const dadosGanhadorTemp = (dados) => {
		return `
			${dados.condomino_temp != null ? '<b>Dados Temp</b>:' + dados.condomino_temp + '<br />' : ''}
			${dados.contato_temp != null ? 'Contato: ' + dados.contato_temp + '<br />' : ''}
			${dados.email_temp != null ? 'E-mail: '  + dados.email_temp + '<br />' : '' }
		`;
	}

	const exibeAcoesGanhador = (dados) => {
		
		switch (dados.status) {
			case '0':
				return `
					${acaoCancelarGanhador()}
					${acaoConfirmarSorteio()}
				`;
			case '1':
				return `
					${acaoCancelarGanhador()}
					${acaoConfirmarSorteio()}
				`;
			case '2':
				return `
					${acaoCancelarGanhador()}
					<button name="confirmar-ganhador" id="confirmar-ganhador" type="button" class="btn btn-success btn-sm glyphicon glyphicon-ok-circle"> Confirmar</button>
				`;
		
			default:
				break;
		}
	}

	const acaoCancelarGanhador = () => {
		audio.pause();
		return `
			<br /><button name="cancelar-ganhador" id="calcelar-ganhador" type="button" class="btn btn-danger btn-sm glyphicon glyphicon-remove"> Cancelar</button>
		`;
	}

	const acaoConfirmarSorteio = () => {
		return `
			<button type="button" id="confirmar-sorteio" name="confirmar-sorteio" class="btn btn-success btn-sm glyphicon glyphicon-ok-circle"> Confirmar</button>
		`;
	}

	function enviaEmailContemplado(dados) {
		
		msgError.innerHTML = "";
		msgError.insertAdjacentHTML('beforeend', `<span>${imgLoading()} Enviando e-mail de contemplação.</span>`);

		alert(linkLoteriaFederal.href)

		mail.send({ 
			acao: "confirmarSorteado",
			customerName: dados.condomino,
			customerMail: dados.email,
			numeroSorteio: dados.dados_sorteio.bilhete.value,
			concurso: dados.dados_sorteio.concurso.value,
			data_concurso: dados.dados_sorteio.data.value,
			unidade: dados.unidade,
			cliente: dados.cliente_sty,
			link_loteria: linkLoteriaFederal.href
		})
		.then( res => {
			const { data } = res
			const { error } = data
			
			msgError.innerHTML = "";
			
			//Verifica se há erros de processamento
			if ( error ) {
				return msgError.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error}</section>`) 
			}
			alert("E-mail de Contemplação enviado com sucesso!");
		
		})
		.catch( err => {
			msgError.innerHTML = ""
			msgError.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err.message}</section>`) 
		})
	}	

})();