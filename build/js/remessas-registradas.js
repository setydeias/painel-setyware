const server = require('../api/server')
const remessa = require('../api/remessa')
const content = document.getElementById('contents')
content.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="30" />')

//Ao carregar a página
remessa.listRemessaRegistrada()
.then( res => {
	try {
		//Joga as remessas retornadas em uma lista, se houverem
		content.innerHTML = ""
		const  { data } = res
		const { success, status } = data
		
		if ( success ) {
			const { fileName } = status
			
			//Título
			content.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-list-alt"></span> Remessas disponíveis para processamento (${fileName.length})</h4>`)

			//Criando a lista
			content.insertAdjacentHTML('beforeend', '<label for="toggle"><input type="checkbox" name="toggle" checked="checked" /> marcar/desmarcar todos</label>')
			content.insertAdjacentHTML('beforeend', '<ul class="list-group" id="shipping-to-process"></ul>')
			const ul = document.getElementById('shipping-to-process')
			
			const appendItem = (el, name) => {
				let list = `<li class="list-group-item"><input type="checkbox" name="remessas" checked="checked" value="${name}" /> ${name}<li>`

				el.insertAdjacentHTML('beforeend', list)
			}

			//Populando a lista de remessas
			fileName.map( el => appendItem(ul, el) )
			//Caminho dos arquivos de reposição
			server.getParameters()
			.then( res => {
				const { PASTA_ARQUIVOS_REPOSICAO_BASE } = res.data.dir
				const pathToGetReplacementFiles = `
					<div class="row">
						<div class="form-group col-md-8">
							<div class="input-group">
								<div class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span> &nbsp;&nbsp;Pasta dos arquivos de reposição da base</div>
								<input type="text" disabled="disabled" class="form-control" value="${PASTA_ARQUIVOS_REPOSICAO_BASE[0]}" id="pathreplacementfiles" name="pathreplacementfiles" />
							</div>
						</div>
					</div>
				`
				content.insertAdjacentHTML('beforeend', pathToGetReplacementFiles)
				//Insere checkbox para checar se o arquivo vai baixar títulos com tarifa de manutenção de título vencido
				content.insertAdjacentHTML('beforeend', '<label><input type="checkbox" name="baixar-titulos" /> Baixar títulos passíveis de cobrança de tarifa de manutenção de título vencido</label><br /><br />')
				//Inserindo o botão para processar
				content.insertAdjacentHTML('beforeend', '<button type="button" id="create-rem" class="btn btn-primary"><span class="glyphicon glyphicon-cloud-download"></span> Gerar Remessas</button>')
			})
		} else {
			content.insertAdjacentHTML('afterbegin', '<h4><span class="glyphicon glyphicon-alert"></span> Nenhuma remessa encontrada</h4>')
		}
	} catch (e) {
		content.innerHTML = ""
		content.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-alert"></span> ${e}</h4>`)
	}
})

/*
* Event delegation
*/

const toggleCheckbox = (e) => {
	const { target } = e
	const { tagName, name } = target

	if ( tagName == "INPUT" && name == "toggle" ) {
		const { checked } = document.querySelector('input[name=toggle]')
		Array.from(document.querySelectorAll('input[name=remessas]')).map( checkbox => checkbox.checked = checked )
	}
}

EventHandler.bind(document, 'click', toggleCheckbox)

//Verificar a ação do clique no botão para iniciar o processamento
const init = (e) => {
	const target = e.target
	const tagName = target.tagName
	const idTarget = target.id

	if ( tagName == "BUTTON" && idTarget == "create-rem" ) {
		const remessas = Array.from(document.querySelectorAll('input[name=remessas]:checked')).map( checkbox => checkbox.value )
		const baixarTitulosMTV = document.querySelector('input[name=baixar-titulos]').checked
		const pathReplacementFiles = document.querySelector('input[name=pathreplacementfiles]').value
		content.innerHTML = ""
		content.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="30" />')

		remessa.processingRemessaRegistrada({ remessas, baixarTitulosMTV, pathReplacementFiles })
		.then( res => {
			content.innerHTML = ""
			try {
				const { data } = res
				const { error, FILES, TITULOS_TO_PRESCRIBE, CONVENIOS_NOT_SETTED } = data
				
				if ( !error ) {
					/*
					* ARQUIVOS PROCESSADOS
					*/
					
					//Painel com tabela que possue os dados da remessa
					const createPanel = (dom, el) => {
						//Checando o banco para definir a imagem
						const banco = el.file.split('_')[3]
						const style = "style='margin: 0 10px 0 0'"
						let image = ""
						switch ( banco ) {
							case '001':
								image = `<img src='/painel/build/images/bb-conv.jpg' width='30' class='img-circle' ${style} />`
								break;
							case '104':
								image = `<img src='/painel/build/images/cef-conv.jpg' width='30' class='img-circle' ${style} />`
								break;
							case '237':
								image = `<img src='/painel/build/images/brd.png' width='30' class='img-circle' ${style} />`
								break;
							default:
								image = `BANCO INDEFINIDO`
								break;
						}

						let panel = `<div class="panel panel-default"><div class="panel-heading">${image} ${el.file}</div>`
						panel += `<div class="panel-body">`
						panel += `<table class="table table-condensed"><thead><tr>`
						panel += `<td><span class="glyphicon glyphicon-ok-circle" style="color:#0E7322;"></span> Qtde. de Entradas</td>`
						panel += `<td><span class="glyphicon glyphicon-refresh" style="color:#069;"></span> Qtde. de Alterações de Vencimento</td>`
						panel += `<td><span class="glyphicon glyphicon-arrow-down" style="color:#09f;"></span> Qtde. de Baixas</td>`
						panel += `<td><span class="glyphicon glyphicon-plus-sign" style="color:orange;"></span> Qtde. de outras movimentações</td></tr></thead>`
						panel += `<tbody><tr><td>${el.titulos_to_entry}</td><td>${el.titulos_to_change}</td><td>${el.titulos_to_drop}</td><td>`
						panel += `${el.titulos_codmov_undefined}</td></tr></tbody></table></div></div>`

						dom.insertAdjacentHTML('beforeend', panel)
					}

					//Mostrar títulos a prescrever
					const billingToPrescribe = (dom, to_prescribe) => {
						if ( Object.keys(to_prescribe).length > 0 ) {
							let callout = `<div class="bs-callout bs-callout-danger" style="background:#fff">`
							callout += `<h4>Títulos a prescrever</h4>`
							callout += Object.keys(to_prescribe).map( convenio => 
								`Convênio <b>${convenio}</b> possui <b>${to_prescribe[convenio]}</b> títulos a prescrever` ).join('<br />')
							callout += `</div>`

							dom.insertAdjacentHTML('beforeend', callout)
						}
					}

					//Mostra convênios que não foram configurados para o processamento
					const showConveniosNotSetted = (dom, convenios) => {
						let callout = `<div class="bs-callout bs-callout-success" style="background:#fff">`
						callout += `<h4>Convênios não configurados</h4>`
						callout += `Os seguintes convênios foram encontrados no processamento porém não foram processados, pois, não estão configurados:<br />`
						callout += `<h3>${convenios.join(', ')}</h3>`
						callout += `</div>`

						dom.insertAdjacentHTML('beforeend', callout)
					}
					
					//Título
					content.insertAdjacentHTML('beforeend', `<h4><span class="glyphicon glyphicon-list-alt"></span> Relatório de arquivos gerados (${FILES.length})`)
					//Listando os arquivos
					FILES.map((el) => createPanel(content, el))
					//Títulos a prescrever
					billingToPrescribe(content, TITULOS_TO_PRESCRIBE)
					//Convênios não configurados
					if ( CONVENIOS_NOT_SETTED ) showConveniosNotSetted(content, CONVENIOS_NOT_SETTED)
				} else {
					content.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-alert"></span> ${error}</h4>`)	
				}
			} catch (e) {
				content.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-alert"></span> Erro no processamento: ${e}</h4>`)
			}
		}).catch((e) => {
			content.insertAdjacentHTML('afterbegin', `<h4><span class="glyphicon glyphicon-alert"></span> ${e}</h4>`)
		})
	}
}

EventHandler.bind(document, 'click', init)