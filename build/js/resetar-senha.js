const _2via = require('../api/2via')
const usuario = document.getElementById('usuario')
const msg = document.getElementById('msg')
const btn = document.getElementById('btn-search')
const found = document.getElementById('found')

const ResetPassword = () => {
    msg.innerHTML = ''
    if ( usuario.value.length != 8 ) {
        found.innerHTML = ''
        const alert = '<div class="alert alert-danger" role="alert">O campo usuário deve conter 8 caracteres</div>'
        msg.insertAdjacentHTML('afterbegin', alert)
    } else {
        found.innerHTML = ''
        found.insertAdjacentHTML('beforeend', '<br /><img src="/painel/build/images/loading.gif" width="30" />')
        //Requisição ao servidor
        _2via.getUserByCod(usuario.value.toLowerCase())
        .then( res => {

            msg.innerHTML = '' //Limpa se houver erro
            found.innerHTML = ''

            const { success, error, data } = res.data
            
            if ( !success ) {
                const alert = `<div class="alert alert-danger" role="alert">${error}</div>`
                msg.insertAdjacentHTML('afterbegin', alert)

                return false
            }
            
            let html = "<br/><h4><span class='glyphicon glyphicon-info-sign'></span> Dados do usuário encontrado</h4><hr />";
            html += !data.COD_AUX ? '' : `Código auxiliar: <b>${data.COD_AUX}</b><br />`
            html += !data.DOCUMENTO ? '' : `Documento: <b>${data.DOCUMENTO}</b><br />`
            html += `Nome do pagador: <b>${data.NOME}</b><br />`
            html += `Endereço: <b>${data.ENDERECO}</b><br /><br />`
            html += '<button class="btn btn-warning" type="button" id="reset-password"><span class="glyphicon glyphicon-refresh"></span> Resetar senha</button>'

            found.insertAdjacentHTML('beforeend', html)

            EventHandler.bind(document.getElementById('reset-password'), 'click', () => {
                //Se pressionar OK no confirm
                //Faz requisição Ajax para o script que reseta a senha
                _2via.removeUserByCod(usuario.value.toLowerCase())
                .then( response => {
                    msg.innerHTML = ''
                    const { error, status } = response.data
                    
                    if ( error ) {
                        return msg.insertAdjacentHTML('afterbegin', `<div class="alert alert-danger" role="alert">${error}</div>`)
                    }

                    alert(status)
                    window.location.reload()
                }).catch( error => msg.insertAdjacentHTML('afterbegin', `<div class="alert alert-danger" role="alert">${error}</div>`) )
            })
        }).catch( err => console.log(err) )
    }
}

//Ao clicar no botão
EventHandler.bind(btn, 'click', ResetPassword)

//Ao teclar ENTER no teclado
const checkKeyCode = (e) => {
    if ( e.which == 13 || e.keyCode == 13 ) {
        e.preventDefault()
        ResetPassword()
    }
}

EventHandler.bind(usuario, 'keydown', checkKeyCode)