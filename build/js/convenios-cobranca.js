const axios = require('axios')
const customer = require('../api/customer')
const convenios = require('../api/convenios')
const format = new Format()
const throwError = (el, error) => el.nextElementSibling.innerHTML = error
const clearError = el => el.nextElementSibling.innerHTML = ""

/*
* Renderização de views
*/
const render = (banco) => {
	const container = document.querySelector('#convenio-form')

	if ( banco === '' ) return container.innerHTML = ""

	axios(`/painel/build/php/convenios/${banco}-view.php`)
	.then( view => {
		container.innerHTML = view.data 
		
		const tipoMulta = document.querySelector('select[name=tipo-multa]')
		const tipoJuros = document.querySelector('select[name=tipo-juros]')
		EventHandler.unbind(tipoMulta, 'change', handleContext)
		EventHandler.unbind(tipoJuros, 'change', handleContext)

		EventHandler.bind(tipoMulta, 'change', handleContext)
		EventHandler.bind(tipoJuros, 'change', handleContext)
	})
}

const banco = document.querySelector('select[name=banco]')
EventHandler.bind(banco, 'change', () => render(banco.value))

/*
* Manuseamento dos campos Multa e Juros
*/
const handleContext = (event) => {
	const { target } = event
	const { tagName, name } = target

	if ( tagName == "SELECT" && (/^tipo-(multa|juros)$/).test(name) ) {
		const input = document.querySelector(`input[name=${name.split('-')[1]}]`)
		const disabled = target.value === '0' ? true : false
		input.disabled = disabled
	}
}

/*
* Obtendo a lista de clientes
*/
const getCustomers = () => {
	customer.get()
	.then( customers => {
		const { data } = customers
		const pathnames = data
			.filter( customer => customer.STATUS === '0' )
			.map( customer  => `
				<option value="${customer.CLI_SIGLA}${format.str_pad('00000', customer.CODSAC, 'l')}">
				${customer.CLI_SIGLA}${format.str_pad('00000', customer.CODSAC, 'l')}
				</option>
			`)
		
		const select = document.querySelector('select[name=cliente]')
		select.insertAdjacentHTML('afterbegin', `<option></option>${pathnames.sort().join('')}`)
	})
	.catch( err => console.log(err) )
}

getCustomers()

/*
* Cadastrar
*/
const cadastrar = (event) => {
	const { target } = event
	const { tagName, name } = target
	const fields = Array.from(document.querySelectorAll('select, input[type=text]:not(:disabled)'))
	const padrao = document.querySelector('input[type=checkbox]')

	const fmtTarget = (obj) => {
		const { className, content, disabled } = obj

		target.innerHTML = ""
		target.setAttribute('class', `btn btn-${className}`)
		target.insertAdjacentHTML('afterbegin', content)
		target.disabled = disabled
	}

	if ( tagName == "BUTTON" && name == "btn-cadastrar" ) {
		fmtTarget({ className: 'warning', content: '<img src="/painel/build/images/loading.gif" alt="loading" width="15" /> Efetuando cadastro', disabled: true})
		const data = {}
		
		Array.from(fields).map( field => {
			const { name, value } = field
			
			if ( value === '' ) {
				fmtTarget({ className: 'primary', content: 'Cadastrar', disabled: false})
				throw JSON.stringify({[name]: 'Obrigatório'})
			} else {
				clearError(field)
			}

			data[name] = value
		})

		//Define se o convênio é padrão
		data.padrao = padrao.checked
		
		convenios.add(data)
		.then( res => {
			Array.from(fields).map( field => field.value = '' )
			padrao.disabled = true
			fmtTarget({ className: 'success', content: '<span class="glyphicon glyphicon-ok-sign"></span> Cadastrado com sucesso', disabled: true})
		})
		.catch( err => console.log(err) )
	}
}

EventHandler.bind(document, 'click', cadastrar)

//Tratamento de erros
EventHandler.bind(window, 'error', (event) => {
	const error = JSON.parse(event.error)
	const name = Object.keys(error)[0]
	const element = document.querySelector(`[name=${name}]`)
	element.focus()
	throwError(element, error[name])
})
