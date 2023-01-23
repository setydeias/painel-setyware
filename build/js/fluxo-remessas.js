;(() => {

	//Área que carrega o conteúdo
	let form = document.getElementById('form')
	  , content = document.getElementById('remessas')
	  , error = document.getElementById('error')

	//Campos radio
	let radioButton = "<label class='radio-inline'><input type='radio' name='tipoFiltro' id='all-ships' checked='checked' /> Tudo</label>"
	radioButton += "<label class='radio-inline'><input type='radio' name='tipoFiltro' id='ships-per-customer' /> Por cliente</label>"
	radioButton += "<label class='radio-inline'><input type='radio' name='tipoFiltro' id='ships-per-date' /> Por data</label>"
	radioButton += "<label class='radio-inline'><input type='radio' name='tipoFiltro' id='ships-per-status' /> Por status</label><div id='form-request'></div><hr />"

	let RequestList = (data) => {
		
		let req = data || {url: '/painel/build/php/remessas/get-remessas-fluxo.php', method: 'GET', loading: content, loadImg: '<center><img src="/painel/build/images/loading.gif" width="30" style="margin:0;" /></center>'}
		  , CreateShippList = (obj) => { //Função que cria a lista
				let sigla = obj.SIGLA
				  , numero_remessa = obj.NUM_REMESSA
				  , descricao = obj.DESC_STATUS
				  , status, li = ""

				//Insere as remessas retornadas
				for ( let i = 0, len = sigla.length ; i < len ; i++ ) {
					//Capturando o STATUS da Remessa
					status = descricao[i] == "ENTREGUE" ? 'online' : 'offline'
					//Montando a li
					li += `<a href='../remessa/${sigla[i].toLowerCase()}/${numero_remessa[i]}'><li class='list-group-item'>${sigla[i]+numero_remessa[i]}<span class='pull-right'>${descricao[i]} <img src='/painel/build/images/${status}.png' width='10' alt='status' style='margin:0 5px;' /></span></li></a>`
				}

				return li
			}

		AjaxRequest.init(req).then((res) => {
			let result = JSON.parse(res)
			try {
				Dom.ClearElement([content, error])
				let ul = Dom.CreateNodeElement('ul', '', {class: 'list-group'})
				  , len = result.SIGLA.length

				if ( len > 0 ) {
					//Insere a lista retornada pela função CreateShipList
					//Preenchendo a UL
					ul.insertAdjacentHTML('afterbegin', CreateShippList(result))
					content.appendChild(ul)
					if ( !form.hasChildNodes() ) {
						form.insertAdjacentHTML('afterbegin', radioButton)
					}
				} else {
					content.insertAdjacentHTML('afterbegin', '<h4><span class="glyphicon glyphicon-alert"></span> Nenhuma remessa encontrada</h4>')
				}

			} catch (e) {
				Dom.ClearElement([error])

				error.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-alert"></span> ${result.error}</h4>`)
			}
		}, (err) => console.log(new Error('Erro ao retornar informações: '+ err)))
	}

	RequestList();

	

	//Verifica que tipo de requisição de remessas retornar
	let HandleRequest = (e) => {

		if ( e.target.id !== "" && e.target.tagName == "INPUT" && e.target.name == "tipoFiltro") {
			
			let id = e.target.id
			  , form = document.getElementById('form-request')
			  , html = ""
			  , data = {loading: content, loadImg: '<center><img src="/painel/build/images/loading.gif" width="30" style="margin:0;" /></center>'}

			Dom.ClearElement([form]);

			switch ( id ) {
				case 'all-ships':
					RequestList()
				break
				case 'ships-per-customer':
					html = "<form class='form-inline' style='margin: 20px 0;'>"
					html += "<div class='form-group'>"
					html += "<label for='sigla-customer'>Sigla:</label>"
					html += " <input type='text' name='sigla-customer' id='sigla-customer' class='form-control' maxlength='3' />"
					html += "</div>"
					html += " <button id='btn-filter' name='customer' type='button' class='btn btn-primary btn-sm'><span class='glyphicon glyphicon-search'></span> Buscar</button>"
					html += "</form>"
					form.insertAdjacentHTML('afterbegin', html)
				break
				case 'ships-per-date':
					html = "<form class='form-inline' style='margin: 20px 0;'>"
					html += "<div class='form-group'>"
					html += "<label for='de'>De:</label>"
					html += " <input type='text' class='form-control' id='de'>"
					html += "</div>"
					html += "<div class='form-group' style='padding: 0 0 0 5px;'>"
					html += " <label for='ate'>Até:</label>"
					html += " <input type='email' class='form-control' id='ate'>"
					html += "</div>"
					html += " <button id='btn-filter' name='date' type='button' class='btn btn-primary btn-sm'><span class='glyphicon glyphicon-search'></span> Buscar</button>"
					html += "</form>"
					form.insertAdjacentHTML('afterbegin', html)
					VMasker([document.getElementById('de'), document.getElementById('ate')]).maskPattern("99/99/9999")
				break
				case 'ships-per-status':
					html = "<form class='form-inline' style='margin: 20px 0;'>"
					html += "<div class='form-group'>"
					html += "<label for='de'>Status:</label>"
					html += " <select class='form-control' id='status' name='status'>"
					html += "<option value='1'>[SETYDEIAS] EM PROCESSAMENTO</option>"
					html += "<option value='2'>[STL] EM PROCESSAMENTO</option>"
					html += "<option value='3'>[STL] IMPRESSÃO</option>"
					html += "<option value='4'>[STL] ENVELOPAMENTO</option>"
					html += "<option value='5'>[STL] AGUARDANDO COLETA</option>"
					html += "<option value='6'>[ENTREGA] AGENTE ENTREGADOR</option>"
					html += "<option value='7'>ENTREGUE</option>"
					html += "</select>"
					html += "</div>"
					html += " <button id='btn-filter' name='status' type='button' class='btn btn-primary btn-sm'><span class='glyphicon glyphicon-search'></span> Buscar</button>"
					html += "</form>"
					form.insertAdjacentHTML('afterbegin', html)
				break;
				default:
					RequestList()
				break;
			}

			//Botão que inicia a requisição
			let btn = document.getElementById('btn-filter')

			if ( btn ) {
				let GetNewRequest = () => {
				  	switch ( btn.name ) {
						case 'customer':
							data.url = '/painel/build/php/remessas/get-remessas-fluxo-customer.php'
							data.method = 'POST'
							data.data = {customer: document.getElementById('sigla-customer').value}
						break;
						case 'date':
							data.url = '/painel/build/php/remessas/get-remessas-fluxo-date.php'
							data.method = 'POST'
							data.data = {de: document.getElementById('de').value, ate: document.getElementById('ate').value}
						break;
						case 'status':
							data.url = '/painel/build/php/remessas/get-remessas-fluxo-status.php'
							data.method = 'POST'
							data.data = {cod_status: document.getElementById('status').value}
						break;
					}

					RequestList(data)
				};

				btn.addEventListener('click', GetNewRequest, false)
			}

		}

	}

	window.addEventListener('click', HandleRequest, false)

})();