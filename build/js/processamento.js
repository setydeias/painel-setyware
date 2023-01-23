const server = require('../api/server')
const msg = document.getElementById('msg')
const unlinkFiles = document.getElementById('unlink-files')
const btnSendMail = document.getElementById('btnSendMail')
const btnTransfFiles = document.getElementById('btnTransfer')
const print = document.getElementById('btnPrint')

/*
* Report area
*/
const reportArea = document.querySelector('.report-toggle')
const inputsSelecteds = document.querySelectorAll('input[name=customer_collapse_input]:checked').length
reportArea.insertAdjacentHTML('beforeend', `<b>${inputsSelecteds}</b> registros selecionados de <b>${inputsSelecteds}</b>`)

/*
* Toggle clientes selecionados
*/
const toggleSelectdCustomers = (event) => {
	const { checked } = event.target
	const customers = document.querySelectorAll('input[name=customer_collapse_input]')
	reportArea.innerHTML = ""
	reportArea.insertAdjacentHTML('beforeend', `<b>${checked ? customers.length : '0'}</b> registros selecionados de <b>${customers.length}</b>`)

	Array.from( customers ).map( input => input.checked = checked )
}

const toggleInput = document.querySelector('input[name=toggle-selected-customers]')
EventHandler.bind(toggleInput, 'change', toggleSelectdCustomers)

/*
* Toggle report
*/
const toggleReport = (event) => {
	const { target } = event

	if ( target.name === 'customer_collapse_input' ) {
		const total = document.querySelectorAll('input[name=customer_collapse_input]').length
		const selected = document.querySelectorAll('input[name=customer_collapse_input]:checked').length
		reportArea.innerHTML = ""
		reportArea.insertAdjacentHTML('beforeend', `<b>${selected}</b> registros selecionados de <b>${total}</b>`)
	}
}

EventHandler.bind(document, 'click', toggleReport)

/*
* Transferir arquivos para o servidor nas nuvens
*/
const transferFiles = () => {

	msg.innerHTML = ""
	msg.insertAdjacentHTML("beforeend", "<br /><img src='/painel/build/images/loading.gif' width='30' alt='loading' />")

	const selected_customers = document.querySelectorAll('.customer_collapse input[name=customer_collapse_input]:checked')
	const filesInfo = Array.from(selected_customers).map( input => {
		const { value } = input
		const data = value.split('-')
		const { checked } = unlinkFiles
		const [customer, date, convenio] = [...data]
		
		return { customer, date, convenio } 
	})

	server.sendProcessedFiles({ filesInfo, unlinkFiles: unlinkFiles.checked })
	.then( res => {
		try {
			msg.innerHTML = ""
			btnTransfFiles.disabled = false
			btnSendMail.disabled = false
			print.disabled = false
			const { filesSent, filesNotSent, mailSent, mailNotSent } = res.data
			msg.insertAdjacentHTML("beforeend", `<br />`)
			//Arquivos enviados
			if ( filesSent.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-success hdn'>(${filesSent.length}) Arquivos enviados com sucesso: ${filesSent.join(', ')}</div>`)
			//Arquivos não enviados
			if ( filesNotSent.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-danger hdn'>(${filesNotSent.length}) Erro ao enviar, tente novamente: ${filesNotSent.join(', ')}</div>`)
			//Emails enviados
			if ( mailSent.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-info hdn'>(${mailSent.length}) Emails enviados: ${mailSent.join(', ')}</div>`)
			//Emails não enviados
			if ( mailNotSent.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-danger hdn'>(${mailNotSent.length}) Erro ao enviar email, tente novamente: ${mailNotSent.join(', ')}</div>`)
		} catch (e) {
			console.log(e)
		}
	}).catch( err => console.log(err) )
}

EventHandler.bind(btnTransfFiles, 'click', () => {
	btnTransfFiles.disabled = true
	btnSendMail.disabled = true
	print.disabled = true
	transferFiles()
})

/*
* Enviar arquivos por email
*/
const sendMail = () => {

	msg.innerHTML = ""
	msg.insertAdjacentHTML("beforeend", "<br />><img src='/painel/build/images/loading.gif' width='30' alt='loading' />")
	
	const { checked } = unlinkFiles
	
	server.sendProcessedFilesByMail(checked)
	.then( res => {
		try {
			msg.innerHTML = ""
			btnSendMail.disabled = false
			btnTransfFiles.disabled = false
			print.disabled = false

			const { mailSent, notSent, withoutMail } = res.data
			msg.insertAdjacentHTML("beforeend", `<br />`)
			//Emails enviados
			if ( mailSent.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-success hdn'>Email enviado com sucesso para: [${mailSent.join(', ')}]</div>`)
			//Cliente sem email
			if ( withoutMail.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-info hdn'>Cliente sem email cadastrado: [${withoutMail.join(', ')}]</div>`)
			//Problemas ao enviar email
			if ( notSent.length > 0 ) msg.insertAdjacentHTML("beforeend", `<div class='alert alert-danger hdn'>Problema ao enviar email para estes clientes: [${notSent.join(', ')}]</div>`)
		} catch (e) {
			console.log(e)
		}
	}).catch( err => console.log(err) )
}

EventHandler.bind(btnSendMail, 'click', () => {
	btnTransfFiles.disabled = true
	btnSendMail.disabled = true
	print.disabled = true
	sendMail()
})

/*
* Imprimir relatório
*/ 

EventHandler.bind(print, 'click', () => window.print())