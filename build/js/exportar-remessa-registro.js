;(() => {
    'use strict'
    const axios = require('axios')
    const format = new Format()
    const list = document.querySelector('#shipping-area')
    const error = document.querySelector('.error')
    list.insertAdjacentHTML('beforeend', "<img src='/painel/build/images/loading.gif' width='30' alt='loading' />")

    const fmtTarget = (obj) => {
        const { target, className, content, disabled } = obj

        target.innerHTML = ""
        target.setAttribute('class', `btn btn-${className}`)
        target.insertAdjacentHTML('afterbegin', content)
        target.disabled = disabled
    }

    //Listagem de remessas
    axios('/painel/build/php/remessas/list-remessa-registrada-database.php')
    .then( remessas => {
        list.innerHTML = ""
        const { data } = remessas
        const qtde_remessas = data.length
        const qtde_remessa_area = document.querySelector('#total-shipping')
        if ( qtde_remessas === 0 ) return qtde_remessa_area.insertAdjacentHTML('beforeend', '<h4><span class="glyphicon glyphicon-alert"></span> Nenhuma remessa encontrada</h4>')

        data.forEach( remessa => {
            const li = `
                <li class="list-group-item">
                    <input type="checkbox" name="nome_remessa" checked="checked" value="${remessa.NOME_REM}" />
                    ${remessa.NOME_REM}
                    <span class="pull-right badge-warning"><i class="glyphicon glyphicon-calendar"></i> ${remessa.DATA_PROCESSAMENTO} às ${remessa.HORA_PROCESSAMENTO}</span>
                    <span class="pull-right badge-success">R$ ${format.number_format(remessa.VALOR_TOTAL, 2, ',', '.')}</span>
                    <span class="pull-right badge-info">${remessa.QTDE_TITULOS} títulos</span>
                </li>
            `
            list.insertAdjacentHTML('beforeend', li) 
        })
        qtde_remessa_area.insertAdjacentHTML('beforeend', `<h4>Remessas disponíveis para exportação (${qtde_remessas})</h4>`)
        list.insertAdjacentHTML('afterbegin', '<li><label for="toggle-rem"><input type="checkbox" checked="checked" name="toggle-rem" /> marcar/desmarcar todos</label></li>')
        list.insertAdjacentHTML('afterend', '<section><button class="btn btn-primary" id="export-files-btn"><i class="glyphicon glyphicon-export"></i> Exportar arquivos</button></section>')
    })
    .catch( err => {
        list.insertAdjacentHTML('beforeend', '<h4><span class="glyphicon glyphicon-alert"></span> Falha na conexão</h4>')
    })

    const toggleCheckbox = (e) => {
        e.stopPropagation()
        const { target } = e
        const { tagName, name } = target

        if ( tagName == "INPUT" && name == "toggle-rem" ) {
            const toggle = document.querySelector('input[name=toggle-rem]').checked
            const checkboxes = Array.from(document.querySelectorAll('input[name=nome_remessa]'))
            checkboxes.map( checkbox => checkbox.checked = toggle )
        }
    }

    const exportShipping = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/remessas/exportar-remessa-registrada.php',
                method: 'POST',
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    EventHandler.bind(document, 'click', toggleCheckbox)

    //Export flow
    const exportFlow = (data) => {

        const { target, remessas } = data

        exportShipping(data)
        .then( res => {
            const { data } = res

            if ( data.error ) {
                const remessas = data.files.join(', ')
                list.insertAdjacentHTML('beforeend', `<section style="margin:10px 0 0 0;" class="alert alert-danger">${data.message}: <b>${remessas}</b></section>`)
            }

            target.setAttribute('class', 'btn btn-success')
            target.innerHTML = ""
            target.insertAdjacentHTML('beforeend', '<i class="glyphicon glyphicon-ok-sign"></i> Remessas exportadas com sucesso')
        })
        .catch( err => {
            if ( !err.response ) {
                return list.insertAdjacentHTML('beforeend', `<h4>Erro ao exportar remessas: ${err}</h4>`) 
            }

            const { status, data } = err.response

            switch ( status ) {
                case 500:
                    fmtTarget({ 
                        target: target,
                        className: 'danger',
                        content: data.message, 
                        disabled: true
                    })
                    
                    setTimeout(() => {
                        fmtTarget({ 
                            target: target,
                            className: 'warning',
                            content: '<img src="/painel/build/images/loading.gif" width="15" alt="loading" /> Exportando sem efetuar backup', 
                            disabled: true
                        })
                        
                        exportFlow({ target: target, remessas: remessas, backup: false });
                    }, 3000)
                    break;
            }
        })
    }

    //Event delegation
    const handleExport = (e) => {
        e.stopPropagation()
        error.innerHTML = ""
        const { target } = e
        const { tagName, id } = target
        const remessas = Array.from(document.querySelectorAll('input[name=nome_remessa]:checked')).map( remessa => remessa.value )
        
        if ( tagName == "BUTTON" && id == "export-files-btn" ) {
            if ( !remessas.length ) {
                fmtTarget({ 
                    target: target,
                    className: 'danger',
                    content: '<span class="glyphicon glyphicon-remove-sign"></span> Nenhuma remessa foi selecionada', 
                    disabled: true
                })
                setTimeout(() => {
                    fmtTarget({ 
                        target: target,
                        className: 'primary',
                        content: '<i class="glyphicon glyphicon-export"></i> Exportar arquivos',
                        disabled: false
                    })
                }, 3000)
            }
            
            fmtTarget({ 
                target: target,
                className: 'warning',
                content: '<img src="/painel/build/images/loading.gif" width="15" alt="loading" /> Exportando remessas', 
                disabled: true
            })

            exportFlow({ target: target, remessas: remessas, backup: true });
        }
    }

    EventHandler.bind(document, 'click', handleExport)

})()