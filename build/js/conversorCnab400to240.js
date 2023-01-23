const conversor = require('../api/conversor')
const file = document.getElementById('file')
file.style.display = 'none'
const panel = document.getElementById('info-panel')
panel.style.display = 'none'
const name = document.getElementById('nome-arquivo')
const size = document.getElementById('tamanho-arquivo')
const qtde = document.getElementById('qtde-titulos')
const tipoconvenio = document.getElementsByName('tipoconvenio')

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

					let linhas = content.split("\n")
					let convenio = linhas[0].substr(40, 6)

					convenio == "000000"  ? tipoconvenio[1].checked = true : tipoconvenio[0].checked = true

					//Quantidade de registros
					const qtdeRegistros = Math.floor((linhas.length - 2) / 2)
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

const btnConverter = document.getElementById('btn-converter')
const error = document.getElementById('error-area')
const pathTo = document.getElementById('path-to')

btnConverter.addEventListener('click', () => {
	error.innerHTML = ""
	let convenioLength
	//Capturando o valor do radiobox
	for ( let i = 0, len = tipoconvenio.length ; i < len ; i++ ) {
		if ( tipoconvenio[i].checked === true ) convenioLength = tipoconvenio[i].value
	}

	if ( pathTo.value == "" ) {
		return error.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Informe o diretório de destino do arquivo convertido')
	} else if ( !file.files.length > 0 ) {
		return error.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Selecione o arquivo para a conversão</div>')
	} else {
		error.innerHTML = ""
		let reader = new FileReader()

		EventHandler.bind(reader, 'load', () => {
			conversor.cnab400To240({pathTo: pathTo, reader: reader, tipo_convenio: convenioLength})
			.then( result => {
					error.innerHTML = ""
					try {
						const { data } = result
						const { alert, status } = data

						error.insertAdjacentHTML('beforeend', `<div class="alert alert-${alert}">${status}</div>`)
					} catch ( err ) {
						error.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">Houve algum problema ao converter o arquivo, verifique o erro ou tente novamente</div>`)
					}
			})
			.catch( err => {
				error.innerHTML = ""
				error.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${err}</div>`)
			})
		})

		reader.readAsText(file.files[0], 'ISO-8859-1')
	}
}, false)