const mensalidade = require('../api/mensalidade')
const customer = require('../api/customer')
const util = require('../modules/util')
const dataMensalidade = document.getElementById('data-mensalidade')
const msg = document.getElementById('msg')
const textArea = document.querySelector('#customer-area span:nth-child(2)')
let total_customers

//Máscara da data
VMasker(dataMensalidade).maskPattern("99/99/9999")

//Carregando a lista de clientes
customer.get()
.then( res => {
	total_customers = res.data.filter( customer => customer.STATUS === '0' )
	textArea.innerHTML = `<b>${total_customers.length}</b> clientes selecionados de <b>${total_customers.length}</b>`
	const listItens = total_customers.map( customer => `
		<li class="list-group-item">
			<label>
				<input type="checkbox" name="customers" value="${customer.CLI_SIGLA}" checked="checked" />
				${customer.CLI_SIGLA} - ${customer.NOMSAC}
			</label>
		</li>
	`)
	
	document.querySelector('#customer-list').insertAdjacentHTML('beforeend', `
		<li class="list-group-item">
			<label>
				<input type="checkbox" checked="checked" name="toggle-all" /> <b>Marcar/Desmarcar todos</b>
			</label>
		</li>
		${listItens.sort().join('')}
	`)
}).catch( err => {
	msg.innerHTML = ""
	msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err}</section>`)
})

//Toggle send mail
const mailArea = document.querySelector('#send-mail-area')
const createRecibo = document.querySelector('input[name=create-recibo]')
const sendAttach = document.querySelector('input[name=enviar-anexo]')
mailArea.style.display = 'none'

EventHandler.bind(createRecibo, 'change', (event) => {
	if ( event.target.checked ) {
		return mailArea.style.display = 'block'
	}

	sendAttach.checked = false
	mailArea.style.display = 'none'
})

//Toggle all customer
const toggleAllSelected = (e) => {
	const { target } = e
	const { tagName, name } = target

	if ( tagName == "INPUT" && name == "toggle-all" ) {
		const customers = document.querySelectorAll('input[name=customers]')
		const text = `<b>${target.checked ? total_customers.length : '0'}</b> clientes selecionados de <b>${total_customers.length}</b>`
		textArea.innerHTML = text

		Array.from(customers).map( input => input.checked = target.checked )
	}
}
EventHandler.bind(document, 'click', toggleAllSelected)

//Toggle customer input
const updateCustomerQtde = (e) => {
	const { target } = e
	const { tagName, name } = target

	if ( tagName == "INPUT" && name == "customers" ) {
		const customers = document.querySelectorAll('input[name=customers]:checked').length
		const text = `<b>${customers}</b> clientes selecionados de <b>${total_customers.length}</b>`
		textArea.innerHTML = text
	}
}
EventHandler.bind(document, 'click', updateCustomerQtde)

const btn = document.querySelector('#edit-ct')
EventHandler.bind(btn, 'click', () => {
	msg.innerHTML = ""
	if ( dataMensalidade.value == "" ) {
		return msg.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">O campo de data é obrigatório</div>')
	} else if ( !util.validarData(dataMensalidade.value) ) {
		return msg.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Insira uma data válida</div>')
	} else {
		const writeInCT = document.querySelector('input[name=write-ct]')
		const selectedCustomers = Array.from(document.querySelectorAll('input[name=customers]:checked')).map( input => input.value )
		
		//Verifica se existe alguma opção selecionada
		if ( writeInCT.checked === false && createRecibo.checked === false && sendAttach.checked === false ) {
			return msg.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Selecione alguma operação</div>')
		}

		//Verifica se algum cliente foi selecionado
		if ( selectedCustomers.length === 0 ) {
			return msg.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Selecione algum cliente</div>')
		}

		const progressBar = document.querySelector('#progress-bar')
		progressBar.innerHTML = ""
		const sse = new EventSource('/painel/build/php/mensalidades/sse-status-processing.php')
		let content = `
			<div class="progress">
				<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
					Iniciando processamento...
				</div>
			</div>
		`
		progressBar.insertAdjacentHTML('beforeend', content)

		sse.onmessage = (event) => {
			const data = JSON.parse(event.data)
			const { customer, total_processed, total_customers } = data
			const percent = Math.ceil(total_processed / total_customers * 100)
			const toLoad = 100 - percent
			content = `
				<div class="progress">
					<div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100" style="width: ${percent}%">
						${ toLoad < 50 ? `${customer} ${percent}%` : '' }
					</div>
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: ${toLoad}%">
						${ toLoad >= 50 ? `${customer} ${percent}%` : '' }
					</div>
				</div>
			`
			
			progressBar.innerHTML = ""
			progressBar.insertAdjacentHTML('beforeend', content)
		}
		
		mensalidade.write({ 
			dataMensalidade: dataMensalidade.value,
			writeInCT: writeInCT.checked,
			createRecibo: createRecibo.checked,
			sendAttach: sendAttach.checked,
			customerList: selectedCustomers
		})
		.then( res => {
			const { data } = res
			const { writed_ct, ct_not_found, created, not_created, mail_send, mail_not_send, error } = data
			sse.close()
			msg.innerHTML = ""
			progressBar.innerHTML = ""
			//Atualiza o progress bar
			content = `
				<div class="progress">
					<div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
						Fim do processamento
					</div>
				</div>
			`
			progressBar.insertAdjacentHTML('beforeend', content)
			//Verifica se há erros de processamento
			if ( error ) {
				msg.innerHTML = ""
				return msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${error}</section>`) 
			}
			//Conta transitórias escritas
			writed_ct.length > 0 
				? msg.insertAdjacentHTML('beforeend', `<section class="alert alert-success">(${writed_ct.length}) Mensalidades lançadas com sucesso: ${writed_ct.join(', ')}</section>`)
				: msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">Nenhuma mensalidade foi lançada</section>`)
			//Recibos criados
			if ( created.length > 0 ) {
				msg.insertAdjacentHTML('beforeend', `<section class="alert alert-success">(${created.length}) Recibos criados: ${created.join(', ')}</section>`)
			}
			//Arquivos não escritos na pasta de destino
			if ( not_created.length > 0 ) {
				msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">(${not_created.length}) Os seguintes arquivos não foram encaminhados para a sua pasta de destino:<br/> <b>${not_created.join('</b>,<br /><b>')}</section>`)
			}
			//Contas transitórias não encontradas
			if ( ct_not_found.length > 0 ) { 
				msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">(${ct_not_found.length}) Conta transitórias não encontradas: ${ct_not_found.join(', ')}</section>`)
			}
			//Emails enviados
			if ( mail_send.length > 0 ) { 
				msg.insertAdjacentHTML('beforeend', `<section class="alert alert-success">(${mail_send.length}) Clientes que receberam o recibo por email: <b>${mail_send.join(', ')}</b></section>`)
			}
			//Emails não enviados
			if ( mail_not_send.length > 0 ) { 
				msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">(${mail_not_send.length}) Clientes que não receberam o recibo por email: <b>${mail_not_send.join(', ')}</b></section>`)
			}
		})
		.catch( err => {
			msg.innerHTML = ""
			msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err.message}</section>`) 
		})
	}
})