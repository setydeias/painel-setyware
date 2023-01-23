const retornos = require('../api/retornos')
const content = document.getElementById('content')
const form = document.getElementById('processing')

const destroyInput = (el) => {
	while ( el.firstChild ) el.removeChild(el.firstChild)
}

//Retorna os arquivos de retorno disponíveis
retornos.list()
.then( res => {
	//Esconde o form
	form.style.display = 'none'
	GetRetFiles(res.data)
})
.catch(err => console.log(new Error(`Houve um erro no processamento: ${err}`)) )

//Cria a lista de arquivos de retorno na tela
const GetRetFiles = (json) => {
	const { file, size }= json
	const len = file.length
	const ul = document.getElementById('retornos')
	let hasPayment, icon, style
	
	if ( len > 0 ) {
		form.style.display = 'block'
		ul.insertAdjacentHTML('beforebegin', `<h4><span class="glyphicon glyphicon-list-alt"></span> Retornos disponíveis para processamento (${len})</h4>`)

		const fileList = file.map( (file, i) => {
			//Se não houver pagamento
			//size[i] vem como `false`
			if ( size[i] === false ) {
				icon = 'glyphicon glyphicon-ban-circle'
				hasPayment = ' Sem pagamentos'
				style = 'background:#F24141;'
			} else {
				icon = 'glyphicon glyphicon-cloud-download'
				hasPayment = ` ${size[i].toString()} Kb`
				style = 'background:#444;'
			}

			//Nome do arquivo e ícone para saber se houve pagamento
			const list = `
				<li class="list-group-item">
					<span>${file}</span>
					<span class="badge pull-right" style="${style}">
						<span class="${icon}"></span>
						<span>${hasPayment}</span>
					</span>
				</li>
			`
			return list
		})

		ul.insertAdjacentHTML('beforeend', fileList.join(''))
	} else {
		form.style.display = 'none'
		content.insertAdjacentHTML('beforeend', '<h4><span class="glyphicon glyphicon-alert"></span> Nenhum arquivo de retorno disponível para processamento</h4>')
	}
}

let writeCt = '02'

EventHandler.bind(document, 'click', (event) => {
	const { tagName, name, value } = event.target

	if ( tagName == "INPUT" && name == "ct" ) {
		const elInputCt = document.querySelector('#input-ct')

		switch ( value ) {
			//Cria os campos
			case '01':
				if ( elInputCt.hasChildNodes() === false ) {
					const date = new Date()
					const weekDay = date.toString().substring(0, 3).trim()

					let dataTransferencia = date.toLocaleDateString('pt-BR')
					dataTransferencia = weekDay == 'Sat' ? new Date(date.getTime() + (2 * 24 * 60 * 60 * 1000)).toLocaleDateString('pt-BR')
						: weekDay == 'Sun' ? new Date(date.getTime() + (1 * 24 * 60 * 60 * 1000)).toLocaleDateString('pt-BR')
						: dataTransferencia
					
					let dataEvento = new Date(date.getTime() - (1 * 24 * 60 * 60 * 1000)).toLocaleDateString('pt-BR')
					dataEvento = weekDay == 'Sun' ? new Date(date.getTime() - (2 * 24 * 60 * 60 * 1000)).toLocaleDateString('pt-BR')
						: weekDay == 'Mon' ? new Date(date.getTime() - (3 * 24 * 60 * 60 * 1000)).toLocaleDateString('pt-BR')
						: dataEvento

					const inputTransferencia = `<span>Data da transferência:</span> <input type="text" id="dataTrasnf" name="dataTransf" class="form-control" style="width:200px" value="${dataTransferencia}" />`
					const inputEvento = `<span>Data do evento:</span> <input type="text" id="dataEvent" name="dataEvent" class="form-control" style="width:200px" value="${dataEvento}" />`

					elInputCt.insertAdjacentHTML('beforeend', `${inputTransferencia}${inputEvento}`)
				}
				break;
			case '02':
				//Destrói os campos existentes
				destroyInput(elInputCt)
				break
		}
		
		writeCt = value
	}
})

const elDuplicidade = document.querySelector('#input-duplicidade')
let checkDuplicidade = '2'

EventHandler.bind(document, 'click', (event) => {
	const { tagName, name, value } = event.target

	if ( tagName == "INPUT" && name == "duplicidades" ) {
		switch ( value ) {
			//Cria os campos
			case '1':
				if (elDuplicidade.hasChildNodes() === false) {
					const input = `
						<span>Qtde. de dias para a checagem:</span> 
						<input type="text" id="diasChecagemDuplicidade" name="diasChecagemDuplicidade" style="width:50px" class="form-control" value="30" />
					`
					elDuplicidade.insertAdjacentHTML('beforeend', input)
				}
				break;
			//Destrói os campos existentes
			case '2':
				destroyInput(elDuplicidade)
				break;
		}

		checkDuplicidade = value
	}
})