const customer = require('../api/customer')
const toastr = require('toastr')
toastr.options = { "positionClass": "toast-bottom-right" }

const content = document.getElementById('customers')
const format = new Format()
let all_customers, disabled_customers, active_customers

const getCustomerData = () => {
	content.innerHTML = ""
	customer.get()
	.then( res => {
		all_customers = res.data
		disabled_customers = all_customers.filter( customer => customer.STATUS === '1' )
		active_customers = all_customers.filter( customer => customer.STATUS === '0' )

		content.innerHTML = ""
		content.insertAdjacentHTML('beforeend', `<span style="margin: 1em 0;display:inline-block;"><b>${all_customers.length}</b> clientes encontrados</span>${pushToList(all_customers)}`)
	})
	.catch( err => content.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err}</section>`))
}

getCustomerData()

const pushToList = ( data ) => {
	return data.map( list_item => {
		const { CODSAC, NOMSAC, STATUS, RETORNO_POR_EMAIL, CLI_SIGLA } = list_item
		const codsac = format.str_pad('000', CODSAC, 'l')
		const receiveByMail = RETORNO_POR_EMAIL === '0' ? '' : '<img src="/painel/build/images/message.png" width="20" alt="Este cliente recebe o retorno por email" title="Este cliente recebe o retorno por email" />'
		const [customer_context, customer_status] = STATUS === '0' ? ['success', 'ATIVO'] : ['danger', 'DESATIVADO']
		const premio = STATUS === '0' ? '<button type="button" class="btn btn-warning btn-sm editPremio" style="padding: 2px 5px;">PRÊMIO</button>' : ''

		return `
			<li class="list-group-item">
				${codsac} - ${NOMSAC} 
				<span class="pull-right">${receiveByMail} 
				<span class="label label-${customer_context}" style="margin: 0 5px;">${customer_status}</span>
				<a rel="${CODSAC}">${premio}</a>
				<a rel="${CODSAC}"><button type="button" class="btn btn-default btn-sm editCustomer" style="padding: 2px 5px;">Editar</button></a>
				<a rel="${CODSAC}"><button type="button" class="btn btn-default btn-sm removeCustomer" style="padding: 2px 5px;margin: 0 5px;color: red;">Remover</button></a></span>
			</li>

		`
	}).join('')
}

//Returns the customer data according to filter_type
const getCustomersType = ( filter_type ) => {
	switch ( filter_type ) {
		case 'all':
			return all_customers
		case 'only_active':
			return active_customers
		case 'only_desactived':
			return disabled_customers
		default:
			return all_customers
	}
}

//Filter form
const updateFilterType = (event) => {
	const { target } = event
	const { tagName, name } = target

	if ( (tagName === 'INPUT' && name === 'filter_type') || tagName === 'LABEL' ) {
		const { value } = target.querySelector('input[name=filter_type]') || target
		const filtered = getCustomersType(value)
		
		content.innerHTML = ""
		content.insertAdjacentHTML('beforeend', `<span style="margin: 1em 0;display:inline-block;"><b>${filtered.length}</b> clientes encontrados</span>${pushToList(filtered)}`)
	}
}

EventHandler.bind(document, 'click', updateFilterType)

//Search customer
const searchInput = document.querySelector('input[name=search_customer]')
searchInput.focus()

EventHandler.bind(searchInput, 'keyup', () => {
	//Captura o filtro selecionado
	const filter_selected = document.querySelector('input[name=filter_type]:checked').value
	const filtered_data = getCustomersType(filter_selected)
	const { value } = searchInput

	const pushFilteredList = ( filtered ) => {
		content.innerHTML = ""
		let text = filtered.length > 0
			? `<span style="margin: 1em 0;display:inline-block;"><b>${filtered.length}</b> clientes encontrados</span>${pushToList(filtered)}`
			: `<span style="margin: 1em 0;display:inline-block;"><span class="glyphicon glyphicon-alert"></span> Nenhum cliente encontrado</span>`
		
		content.insertAdjacentHTML('beforeend', text)
	}
	
	const filterCodOrSigla = isNaN(value)
			? filtered_data.filter( customer => customer.CLI_SIGLA.toUpperCase() === value.toUpperCase() )
			: filtered_data.filter( customer => format.str_pad('000', customer.CODSAC, 'l') === value )
	const filterByName = filtered_data.filter( customer => customer.NOMSAC.toLowerCase().indexOf(value.toLowerCase()) != -1 )
	const filtered_result = value.length === 0 ? filtered_data : value.length <= 3 ? filterCodOrSigla : filterByName
	
	pushFilteredList(filtered_result)
})

//Remover cliente
const removeCustomer = (event) => {
	const { target } = event
	const { tagName, classList, parentNode } = target

	if ( tagName == 'BUTTON' && classList.contains("removeCustomer") ) {
		
		if ( confirm('Deseja realmente excluir este cliente?') ) {
			//Código do cliente
			const cod = format.str_pad('000', parentNode.rel, 'l')	
			
			if ( cod !== undefined ) {
				//Formata o botão
				target.innerHTML = ""
				target.disabled = true
				target.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="15" /> Removendo')
				//Requisição ao script de deleção do usuário
				customer.getDataByCodSac(cod)
				.then( response => {
					const { data } = response
					const pathname = `${data.CLI_SIGLA[0]}${format.str_pad('00000', cod, 'l')}`
					
					customer.remove(pathname)
					.then( res => {
						try {
							target.innerHTML = ""
							target.disabled = false
							target.insertAdjacentHTML('beforeend', 'Remover')

							const { status } = res.data
							
							if ( res.data.validador_status ) {
								toastr.error(res.data.validador_status)
							}

							if ( res.data.image_site_error ) {
								toastr.error(res.data.image_site_error)
							}

							if ( res.data.image_2via_error ) {
								toastr.error(res.data.image_2via_error)
							}

							toastr.success(status)
							getCustomerData()
						} catch (e) {
							target.innerHTML = ""
							target.disabled = false
							target.insertAdjacentHTML('beforeend', 'Remover')
							toastr.error(`Erro ao remover cliente: ${e}`)
						}
					})
					.catch( err => {
						target.innerHTML = ""
						target.disabled = false
						target.insertAdjacentHTML('beforeend', 'Remover')
						toastr.error(`Erro ao remover cliente: ${err}`) 
					})
				})
				.catch( err => console.log(err) )
			} else {
				toastr.error(`Erro ao remover cliente`)
			}
		}
	}
}

EventHandler.bind(document, 'click', removeCustomer)

//Editar cadastro
const updateCustomer = (event) => {
	const { tagName, classList, parentNode } = event.target

	if ( (tagName == 'BUTTON' || tagName == 'SPAN') && classList.contains("editCustomer") ) {
		//Código do cliente
		const cod = format.str_pad('000', parentNode.rel, 'l')
		location.href = `/painel/edit/customer/${cod}`
	}
}

//Prêmio adimplência
const edittPremioAdimplencia = (event) => {
	const { tagName, classList, parentNode } = event.target

	if ( (tagName == 'BUTTON' || tagName == 'SPAN') && classList.contains("editPremio") ) {
		//Código do cliente
		const cod = format.str_pad('000', parentNode.rel, 'l')
		location.href = `/painel/edit/premio/${cod}`
	}
}

EventHandler.bind(document, 'click', updateCustomer)
EventHandler.bind(document, 'click', edittPremioAdimplencia)