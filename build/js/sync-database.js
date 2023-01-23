const { syncCustomerDatabases, extractCustomerDatabases } = require('../api/server')
const message = document.querySelector('#message')

const btnSync = document.querySelector('button[name=btn-sync]')
const sync = () => {
    message.innerHTML = ""
    btnSync.disabled = true
    btnSync.innerHTML = ""
    btnSync.setAttribute('class', 'btn btn-warning')
    btnSync.insertAdjacentHTML('beforeend', `<img src="/painel/build/images/loading.gif" width="20" /> Sincronizando`)

    syncCustomerDatabases()
    .then( res => {
        const TIME_TO_INIT_EXTRACT = 3000
        btnSync.disabled = true
        btnSync.innerHTML = ""
        btnSync.setAttribute('class', 'btn btn-success')
        btnSync.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-ok-circle"></span> Bancos de dados sincronizados`)
        
        setTimeout(() => {
            btnSync.disabled = true
            btnSync.innerHTML = ""
            btnSync.setAttribute('class', 'btn btn-warning')
            btnSync.insertAdjacentHTML('beforeend', `<img src="/painel/build/images/loading.gif" width="20" /> Extraindo bancos de dados`)
            
            extractCustomerDatabases()
            .then( res => {
                btnSync.disabled = false
                btnSync.innerHTML = ""
                btnSync.setAttribute('class', 'btn btn-primary')
                btnSync.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-refresh"></span> Sincronizar`)

                const { data } = res
                const { sync, not_sync } = data

                if ( sync.length > 0 ) {
                    message.insertAdjacentHTML('beforeend', `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    (${sync.length}) Bancos sincronizados: ${sync.join(', ')}
                                </div>
                            </div>
                        </div>
                    `)
                }

                if ( not_sync.length > 0 ) {
                    message.insertAdjacentHTML('beforeend', `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    (${not_sync.length}) Bancos NÃO sincronizados: ${not_sync.join(', ')}
                                </div>
                            </div>
                        </div>
                    `)
                }
            })
            .catch( err => {
                message.innerHTML = ""
                message.insertAdjacentHTML('beforeend', `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-danger">
                                    Erro ao efetuar sincronização: ${err.response.data || err}
                                </div>
                            </div>
                        </div>
                    `)
            })
        }, TIME_TO_INIT_EXTRACT);
    })
    .catch( err => console.log(err.response) )
}

EventHandler.bind(btnSync, 'click', sync)