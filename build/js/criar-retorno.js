const customers = require('../api/customer')
const convenios = require('../api/convenios')
const retornos = require('../api/retornos')
const server = require('../api/server')
const messageArea = document.querySelector('#message-area')
const customer = document.querySelector('select[name=customer]')
const infoArea = document.querySelector('#info-area')

//Preenche a lista de clientes
const getCustomers = (() => {
    customers.get()
    .then( res => {
        const { data } = res
        const options = data
            .filter( customer => customer.STATUS === '0' )
            .map( customer => `<option value="${customer.CLI_SIGLA}">${customer.CLI_SIGLA} - ${customer.NOMSAC}</option>` )
        
        
        customer.insertAdjacentHTML('beforeend', `<option></option>${options.sort().join('')}`)
    })
    .catch( err => console.log(err) )
})()

const getConvByCustomer = (event) => {
    const { value } = event.target
    const convenio = document.querySelector('select[name=convenio]')

    if ( value === '' ) {
        infoArea.style.display = "none"
        return
    }

    infoArea.style.display = "block"
    convenios.get()
    .then( res => {
        const { data } = res
        const styConvs = data
            .filter( conv => conv.MANTENEDOR === 'STY' )
            .map( conv => `<option value="${conv.CONVENIO}">${conv.MANTENEDOR} - ${conv.BANCO} - ${conv.CONVENIO}</option>` ).join('')
        const ownConvs = data
            .filter( conv => conv.MANTENEDOR === value.toUpperCase() )
            .map( conv => `<option value="${conv.CONVENIO}">${conv.MANTENEDOR} - ${conv.BANCO} - ${conv.CONVENIO}</option>` ).join('')
        
        convenio.innerHTML = ""
        convenio.insertAdjacentHTML('beforeend', `<option></option>${styConvs}${ownConvs.length > 0 ? `<option>---------</option>${ownConvs}` : ''}`)
    })
}

//Retorna os convênios de acordo com o cliente selecionado
EventHandler.bind(customer, 'change', getConvByCustomer)

//Formata os campos em formato data
VMasker(document.querySelectorAll('.date-mask')).maskPattern('99/99/9999')

//Criação do retorno
const btnCreateRetorno = document.querySelector('button[name=btn-criar-retorno]')
EventHandler.bind(btnCreateRetorno, 'click', (event) => {
    event.preventDefault()
    messageArea.innerHTML = ""
    messageArea.insertAdjacentHTML('beforeend', '<section class="alert alert-info"><img src="/painel/build/images/loading.gif" width="30" /> Processo em execução</section>')

    const data = {}
    Array.from(document.querySelectorAll('input, select'))
        .map( field => Object.assign(data, {[field.name]: field.value}) )
    
    retornos.create(data)
    .then( res => {
        const { data } = res
        const { success } = data
        let created_files = '', failed_files = '', exceptions = ''
        messageArea.innerHTML = ""
        
        if ( !success ) {
            return messageArea.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${data.message || data}</section>`)
        }

        if ( data.data.CREATED_FILES ) {
            created_files = `
                <section class="row">
                    <section class="col-md-12">
                        <p>Arquivos gerados:</p>
                        <ul class="list-group">
                            ${data.data.CREATED_FILES.map( filename => `<li class="list-group-item">${filename}</li>` ).join('')}
                        </ul>
                    </section>
                </section>
            `
        }

        if ( data.data.FAILED_FILES ) {
            failed_files = `
                <section class="row">
                    <section class="col-md-12">
                        <p>Arquivos gerados com falha:</p>
                        <ul class="list-group">
                            ${data.data.FAILED_FILES.map( filename => `<li class="list-group-item list-group-item-danger">${filename}</li>` ).join('')}
                        </ul>
                    </section>
                </section>
            `
        }

        if ( data.data.exceptions ) {
            exceptions = `
                <section class="row">
                    <section class="col-md-12">
                        <p>Convênios não encontrados:</p>
                        <ul class="list-group">
                            ${data.data.exceptions.map( exception => `<li class="list-group-item list-group-item-danger">${exception}</li>` ).join('')}
                        </ul>
                    </section>
                </section>
            `
        }

        messageArea.insertAdjacentHTML('beforeend', `${created_files}${failed_files}${exceptions}`)
        messageArea.insertAdjacentHTML('beforeend', data.data.CREATED_FILES.length > 0 ? '<button type="button" name="btn-send-files" class="btn btn-success"><span class="glyphicon glyphicon-cloud-upload"></span> Transferir para Servidor nas Nuvens</button>' : '')
    })
    .catch( err => {
        messageArea.innerHTML = ""
        messageArea.insertAdjacentHTML('beforeend', `${err}`) 
    })
})

//Enviar arquivos para o servidor
const sendFiles = (event) => {
    const { target } = event
    const { name } = target

    if ( name === 'btn-send-files' ) {
        target.innerHTML = ""
        target.disabled = true
        target.setAttribute('class', 'btn btn-info')
        target.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="20" /> Transferindo arquivos')
        
        server.sendProcessedFiles({ unlinkFiles: true })
        .then( res => {
            target.setAttribute('class', 'btn btn-success')
            target.innerHTML = ""
            target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-ok-circle"></span> Arquivos transferidos com sucesso')
        })
        .catch( err => {
            target.setAttribute('class', 'btn btn-danger')
            target.innerHTML = ""
            target.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-remove-sign"></span> Erro ao transferir arquivos')
        })
    }
}

EventHandler.bind(document, 'click', sendFiles)
