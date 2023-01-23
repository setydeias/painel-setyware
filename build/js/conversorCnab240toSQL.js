const customer = require('../api/customer')
const conversor = require('../api/conversor')
const selectInput = document.querySelector('#customers-list')
const format = new Format()

/*
* Obtém a lista de clientes da Setydeias
*/
const getCustomers = () => {
	customer.get()
	.then( customers => {
		const options = customers.data.map( customer => {
			const { CLI_SIGLA, CODSAC, NOMSAC } = customer
			return `<option value="${CLI_SIGLA}${format.str_pad('00000', CODSAC, 'l')}">${CODSAC} - ${NOMSAC}</option>`
		})
		
		selectInput.insertAdjacentHTML('beforeend', options.join(''))
	})
	.catch( err => console.log(err) )
}

getCustomers()

/*
* Manipulação do arquivo para conversão
*/

const file = document.getElementById('file')
const panel = document.getElementById('info-panel')
file.style.display = 'none'
panel.style.display = 'none'
const name = document.getElementById('nome-arquivo')
const size = document.getElementById('tamanho-arquivo')
const qtde = document.getElementById('qtde-titulos')

const handleFiles = (evt) => {
	const file = evt.target.files[0]

	if ( file ) {
			panel.style.display = 'block'
			const reader = new FileReader()
			
			EventHandler.bind(reader, 'load', (e) => {
				let content = e.target.result
				name.innerHTML = ""
				size.innerHTML = ""
				qtde.innerHTML = ""
				name.insertAdjacentHTML('beforeend', `<div>${file.name}</div>`)
				size.insertAdjacentHTML('beforeend', `<div>${file.size} Kb</div>`)
				//Quantidade de registros
				const linhas = content.split("\n")
				const qtdeRegistros = Math.floor((linhas.length - 4) / 3)
				qtde.insertAdjacentHTML('beforeend', `<div>${qtdeRegistros}</div>`)
			})
			
			reader.readAsText(file)
	} else {
			panel.style.display = 'none'
	}
}

EventHandler.bind(file, 'change', handleFiles)

//Ao clicar no botão, ativar o input FILE
const btnSelect = document.getElementById('select-file')
EventHandler.bind(btnSelect, 'click', () => file.click())

/*
* Submissão da conversão
*/
const btnConverter = document.getElementById('btn-converter')
const error = document.getElementById('error-area')
const pathTo = document.getElementById('path-to')

EventHandler.bind(btnConverter, 'click', () => {
	error.innerHTML = ""
	
	if ( pathTo.value == "" ) {
		return error.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Informe o diretório de destino do arquivo convertido')
	} else if ( selectInput.value == "" ) {
		return error.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Selecione o cliente para a captura dos dados')
	} else if ( !file.files.length > 0 ) {
		return error.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Selecione o arquivo para a conversão</div>')
	} else {
		error.innerHTML = ""
		let reader = new FileReader()

		EventHandler.bind(reader, 'load', () => {
			//Requisição AJAX
			conversor.cnab240ToSQL({pathTo: pathTo, reader: reader, customer: selectInput})
			.then( result => {
				error.innerHTML = ""
				try {
					const { data } = result
					const { message, not_found } = data

					//Mensagem de sucesso
					error.insertAdjacentHTML('beforeend', `<div class="alert alert-success">${message}</div>`)
					//Se houver clientes não encontrados
					if ( not_found.length > 0 ) {
						const list = not_found.map( customer => `<li class="list-group-item">${customer.DOCSAC} - ${customer.NOMSAC}</li>` )
						error.insertAdjacentHTML('beforeend', `<h4>Usuários não encontrados (${list.length})</h4><ul class="list-group">${list.join('')}</ul>`)
					}
				} catch ( err ) {
					error.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">Houve algum problema ao converter o arquivo, verifique o erro ou tente novamente</div>`)
				}
			})
			.catch( err => {
				error.innerHTML = ""
				error.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${err}</div>`)
			})
		})

		reader.readAsText(file.files[0])
	}
})