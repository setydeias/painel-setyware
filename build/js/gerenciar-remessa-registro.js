const customer = require('../api/customer')
const setrem = require('../api/setrem')
const util = require('../modules/util')
const toastr = require('toastr')
toastr.options = { "positionClass": "toast-bottom-right" }

VMasker(document.querySelectorAll('.date')).maskPattern("99/99/9999")
const error = document.querySelector('.error-area')
const customerInput = document.querySelector('select[name=selected-customer]')

const getCustomers = () => {
    customer.get()
    .then( res => {
        const html = res.data.map( customer => `<option value="${customer.CLI_SIGLA}">${customer.CLI_SIGLA}</option>` )
        customerInput.insertAdjacentHTML('afterbegin', `<option></option>${html.sort()}`)
    })
    .catch( err => {
        error.innerHTML = ""
        error.insertAdjacentHTML('beforebegin', `<section class="alert alert-danger">${err}</section>`)
    })
}

const filterContent = document.querySelector('#filter-content')
filterContent.insertAdjacentHTML('beforeend', '<br /><img src="/painel/build/images/loading.gif" width="30" />')

const getLastShipping = () => {
    setrem.list()
    .then( res => {
        filterContent.innerHTML = ""
        const remessas = res.data
        const li = remessas.map( remessa => `
            <li class="list-group-item" value="${remessa.ID_REGISTRO}">
                ${remessa.NOME_REM}
                <span class="pull-right">
                    <a rel="download-file"><span class="glyphicon glyphicon-cloud-download" title="Baixar arquivo" style="margin:0 10px;"></span></a>
                    <a rel="export-file"><span class="glyphicon glyphicon-share-alt" title="Tornar arquivo disponível para exportação"></span></a>
                </span>
                <span class="pull-right">
                    <span class="badge" style="background:#D9534F">${remessa.QTDE_TITULOS} títulos</span>
                    <span class="badge" style="background:#D6AE40">Processada em ${remessa.DATA_PROCESSAMENTO} às ${remessa.HORA_PROCESSAMENTO}</span>
                </span>
            </li>` )
        const content = `<hr /><h4>Últimas ${remessas.length} remessas processadas</h4><ul class="list-group">${li.join('')}</ul>`
        filterContent.insertAdjacentHTML('beforeend', content)
    })
    .catch( err => {
        error.innerHTML = ""
        error.insertAdjacentHTML('beforebegin', `<section class="alert alert-danger">${err}</section>`)
    })
}

getCustomers()
getLastShipping()

//Download do arquivo
const downloadFile = (e) => {
    const { target } = e
    const { parentNode } = target
    const { value } = parentNode.parentNode.parentNode
    parentNode.innerHTML = ""
    parentNode.insertAdjacentHTML('afterbegin', `<a><img src="/painel/build/images/loading.gif" style="margin:0 10px" width="15" /></a>`)
    
    setrem.downloadFile(value)
    .then( res => {
        const { success, message } = res.data
        parentNode.innerHTML = ""
        parentNode.parentNode.insertAdjacentHTML('afterbegin', `<a rel="download-file"><span class="glyphicon glyphicon-cloud-download" title="Baixar arquivo" style="margin:0 10px;"></span></a>`)
        toastr[success ? 'success' : 'error'](message)
    })
    .catch( err => {
        parentNode.innerHTML = ""
        parentNode.parentNode.insertAdjacentHTML('afterbegin', `<a rel="download-file"><span class="glyphicon glyphicon-cloud-download" title="Baixar arquivo" style="margin:0 10px;"></span></a>`)
        toastr.error(err)
    })
}

//Tornar disponível para exportação
const makeFileAvailable = (e) => {
    const { target } = e
    const { parentNode } = target
    const { value } = parentNode.parentNode.parentNode
    parentNode.innerHTML = ""
    parentNode.insertAdjacentHTML('afterbegin', `<a><img src="/painel/build/images/loading.gif" width="15" /></a>`)
    
    setrem.makeFileAvailable(value)
    .then( res => {
        const { success, message } = res.data
        parentNode.innerHTML = ""
        parentNode.parentNode.insertAdjacentHTML('beforeend', `<a rel="export-file"><span class="glyphicon glyphicon-share-alt" title="Tornar arquivo disponível para exportação"></span></a>`)
        toastr[success ? 'success' : 'error'](message)
    })
    .catch( err => {
        parentNode.innerHTML = ""
        parentNode.parentNode.insertAdjacentHTML('beforeend', `<a rel="export-file"><span class="glyphicon glyphicon-share-alt" title="Tornar arquivo disponível para exportação"></span></a>`)
        toastr.error(err)
    })
}

const handleFunctions = (e) => {
    
    const { tagName, parentNode } = e.target

    if ( tagName == "SPAN" && parentNode.rel == "download-file" ) downloadFile(e)
    if ( tagName == "SPAN" && parentNode.rel == "export-file" ) makeFileAvailable(e)
}

EventHandler.bind(document, 'click', handleFunctions)

//FORM



const filterShipping = () => {
    const de = document.querySelector('[name=de]').value
    const ate = document.querySelector('[name=ate]').value
    error.innerHTML = ""

    if ( customerInput.value == "" && de == "" && ate == "" ) {
        return error.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">Informe um filtro</section>')
    }

    if ( ate.length > 0 && de.length === 0 ) {
        return error.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">Informe o campo "De"</section>')
    }

    if ( de.length > 0 && !util.validarData(de) ) {
        return error.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">"De" deve ser uma data válida</section>')
    }

    if ( ate.length > 0 && !util.validarData(ate) ) {
        return error.insertAdjacentHTML('beforeend', '<section class="alert alert-danger">"Até" deve ser uma data válida</section>')
    }

    filterContent.innerHTML = ""
    filterContent.insertAdjacentHTML('beforeend', '<br /><img src="/painel/build/images/loading.gif" width="30" />')

    setrem.listBy({ customer: customerInput.value, de: de, ate: ate })
    .then( res => {
        filterContent.innerHTML = ""
        const { error, message } = res.data

        if ( error ) {
            return filterContent.insertAdjacentHTML('beforeend', `<section class="alert alert-danger">${message}</section>`)
        }

        const remessas = res.data
        const li = remessas.map( remessa => `
            <li class="list-group-item" value="${remessa.ID_REGISTRO}">
                ${remessa.NOME_REM}
                <span class="pull-right">
                    <a rel="download-file"><span class="glyphicon glyphicon-cloud-download" title="Baixar arquivo" style="margin:0 10px;"></span></a>
                    <a rel="export-file"><span class="glyphicon glyphicon-share-alt" title="Tornar arquivo disponível para exportação"></span></a>
                </span>
                <span class="pull-right">
                    <span class="badge" style="background:#D9534F">${remessa.QTDE_TITULOS} títulos</span>
                    <span class="badge" style="background:#D6AE40">Processada em ${remessa.DATA_PROCESSAMENTO} às ${remessa.HORA_PROCESSAMENTO}</span>
                </span>
            </li>` )
        const content = `<hr /><h4>${remessas.length} remessas encotradas</h4><ul class="list-group">${li.join('')}</ul>`
        filterContent.insertAdjacentHTML('beforeend', content)
    })
    .catch( err => console.log(err) )
}

const btn = document.querySelector('[name=btn-filter]')
EventHandler.bind(btn, 'click', filterShipping)