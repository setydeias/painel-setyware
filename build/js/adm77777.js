const exportar = require('../api/exportar')
const server = require('../api/server')
const pathInput = document.querySelector('#bancoadm77777')

const getPathADM = (() => {
    exportar.getADM77777()
    .then( res => {
        const { path } = res.data
        pathInput.value = path
    })
    .catch( err => console.log(err) )
})()

const btnUpload = document.querySelector('button[name=btn-upload]')
const msg = document.querySelector('#msg')

const init = (event) => {
    event.preventDefault()
    
    msg.innerHTML = ""
    btnUpload.disabled = true
    btnUpload.innerHTML = ""
    btnUpload.setAttribute('class', 'btn btn-success')
    btnUpload.insertAdjacentHTML('beforeend', '<img src="/painel/build/images/loading.gif" width="15" /> Upload em progresso')
    
    exportar.initADM77777()
    .then( res => {
        const { data } = res
        const { success, status } = data

        //Acessa o servidor via socket para descompactar o banco
        server.getSVNParams()
        .then( res => {
            const { HOST } = res.data
            const socket = io.connect(`http://${HOST}:7774/`)
            socket.emit('unzip_adm77777')
        })
        .catch( err => console.log(err.response) )

        //Formata o botão
        btnUpload.disabled = false
        btnUpload.innerHTML = ""
        btnUpload.setAttribute('class', 'btn btn-primary')
        btnUpload.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-cloud-upload"></span> Iniciar upload')

        if ( data.rename ) {
            msg.insertAdjacentHTML('beforeend', `<section class="alert alert-warning"><span class="glyphicon glyphicon-alert"></span> ${data.rename}</section>`)
        }

        msg.insertAdjacentHTML('beforeend', `
            <section class="alert alert-${success ? 'success' : 'warning'}">
                <span class="glyphicon glyphicon-${success ? 'ok-circle' : 'remove-circle'}"></span> ${status}
            </section>
        `)
    })
    .catch( err => {
        const TIME_TO_UPDATE_BTN = 3000
        //Formata o botão
        btnUpload.innerHTML = ""
        btnUpload.setAttribute('class', 'btn btn-danger')
        btnUpload.insertAdjacentHTML('beforeend', `<span class="glyphicon glyphicon-remove-circle"></span> ${err.response.data.status || err}`)
        setTimeout(() => {
            btnUpload.disabled = false
            btnUpload.innerHTML = ""
            btnUpload.setAttribute('class', 'btn btn-primary')
            btnUpload.insertAdjacentHTML('beforeend', '<span class="glyphicon glyphicon-cloud-upload"></span> Iniciar upload')
        }, TIME_TO_UPDATE_BTN)
    })
}

EventHandler.bind(btnUpload, 'click', init)