const customer = require('../api/customer')
const server = require('../api/server')
const msg = document.querySelector('#msg')
let total_customers

//Load list
const loadCustomerList = (() => {
    customer.get()
    .then( res => {
        const { data } = res
        total_customers = data.filter( customer => customer.STATUS === '0' )
        const customers = total_customers.map( customer => `
            <li class="list-group-item">
                <label>
                    <input type="checkbox" checked="checked" name="customer" value="${customer.CLI_SIGLA}" />
                    ${customer.CLI_SIGLA} - ${customer.NOMSAC}
                </label>
            </li>
        ` )
        
        const customerArea = document.querySelector('#customer-area')
        const content = `
            <p>
                <label>
                    <input type="checkbox" name="toggle-checkbox" checked="checked" />
                    Marcar/Desmarcar todos
                </label>
            </p>
            <p>
                <b>${total_customers.length}</b> clientes selecionados de <b>${total_customers.length}</b>
            </p>
            <ul class="list-group">${customers.sort().join('')}</ul>
            <section style="margin:10px 0">
                <button type="button" name="btn-export" class="btn btn-primary"><span class="glyphicon glyphicon-cloud-upload"></span> Iniciar exportação</button>
            </section>
        `
        customerArea.innerHTML = ""
        customerArea.insertAdjacentHTML('beforeend', content)
    })
    .catch( err => {
        msg.innerHTML = ""
        msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err}</section>`)
    })
})()

//Toggle all checkboxes
const toggleCheckbox = (e) => {
    const { target } = e
    const { name } = target

    if ( name === 'toggle-checkbox' ) {
        const { checked } = target
        const customers = document.querySelectorAll('input[name=customer]')
        const total_customers_area = document.querySelector('#customer-area p:nth-child(2)')
        total_customers_area.innerHTML = ""
        total_customers_area.insertAdjacentHTML('beforeend', `<b>${checked ? total_customers.length : '0'}</b> clientes selecionados de <b>${total_customers.length}</b>`)
        Array.from(customers).map( input => input.checked = checked )
    }
}

EventHandler.bind(document, 'click', toggleCheckbox)

//Toggle a checkbox
const toggle = (e) => {
    const { target } = e
    const { name } = target

    if ( name === 'customer' ) {
        const customers = document.querySelectorAll('input[name=customer]:checked')
        const total_customers_area = document.querySelector('#customer-area p:nth-child(2)')
        total_customers_area.innerHTML = ""
        total_customers_area.insertAdjacentHTML('beforeend', `<b>${customers.length}</b> clientes selecionados de <b>${total_customers.length}</b>`)
    }
}

EventHandler.bind(document, 'click', toggle)

//Iniciar exportação
const init = (e) => {
    const { target } = e
    const { name } = target

    if ( name === 'btn-export' ) {
        target.disabled = true
        msg.innerHTML = ""
        
        const TIME_TO_FORMAT = 3000
        const customers = Array.from(document.querySelectorAll('input[name=customer]:checked'))
            .map( input => input.value )
        
        if ( customers.length === 0 ) {
            target.disabled = false
            return msg.insertAdjacentHTML('beforeend', '<section class="alert alert-info">Selecione ao menos 1 cliente para a exportação</section>')
        }
        
        const sse = new EventSource('/painel/build/php/server/sse-export-ct.php')

        sse.onmessage = event => {
            const data = JSON.parse(event.data)
            target.innerHTML = ''
            target.setAttribute('class', 'btn btn-primary')
            target.insertAdjacentHTML('beforeend', `<img src="/painel/build/images/loading.gif" width="20" /> [${data.customer}] Exportando...`)
        }

        target.innerHTML = ""
        target.setAttribute('class', 'btn btn-warning')
        target.insertAdjacentHTML('beforeend', `<img src="/painel/build/images/loading.gif" width="20" /> Iniciando exportação...`)

        server.exportContaTransitoria(customers)
        .then( res => {
            //Encerra a conexão com o Event Source
            sse.close()
            //Formata o botão
            target.innerHTML = ""
            target.setAttribute('class', 'btn btn-success')
            target.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-ok-sign"></span> Processo finalizado`)
            setTimeout(() => {
                target.disabled = false
                target.innerHTML = ""
                target.setAttribute('class', 'btn btn-primary')
                target.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-cloud-upload"></span> Iniciar exportação`)
            }, TIME_TO_FORMAT)

            //Insere a mensagem
            const { data } = res
            const { not_found, success, failure } = data
            
            let content

            if ( success.length > 0 ) {
                content = `
                    <section class="alert alert-success">
                        (${success.length}) Contas Transitórias exportadas: <b>${success.join(', ')}</b>
                    </section>
                `
                msg.insertAdjacentHTML('beforeend', content)
            }

            if ( not_found.length > 0 ) {
                content = `
                    <section class="alert alert-warning">
                        (${not_found.length}) Contas Transitórias não encontradas: <b>${not_found.join(', ')}</b>
                    </section>
                `
                msg.insertAdjacentHTML('beforeend', content)
            }

            if ( failure.length > 0 ) {
                content = `
                    <section class="alert alert-danger">
                        (${failure.length}) Arquivos não transferidos: <b>${failure.join(', ')}</b>
                    </section>
                `
                msg.insertAdjacentHTML('beforeend', content)
            }
        })
        .catch( err => {
            //Encerra a conexão com o Event Source
            sse.close()
            //Formata o botão
            target.innerHTML = ""
            target.setAttribute('class', 'btn btn-danger')
            target.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-remove-sign"></span> Ocorreu um erro inesperado`)
            setTimeout(() => {
                target.disabled = false
                target.innerHTML = ""
                target.setAttribute('class', 'btn btn-primary')
                target.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-cloud-upload"></span> Iniciar exportação`)
            }, TIME_TO_FORMAT)
            msg.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${err || err.response.data.error}</section>`)
        })
    }
}

EventHandler.bind(document, 'click', init)