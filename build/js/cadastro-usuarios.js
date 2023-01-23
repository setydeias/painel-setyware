const usuarios = require('../api/usuarios')
const validation = require('../modules/validation')

const msg = document.querySelector('#msg')
const btn = document.querySelector('button[name=btn-action]')

const addUser = () => {
    msg.innerHTML = ""
    const fd = new FormData()
    const inputs = Array.from(document.querySelectorAll('input[type=text], input[type=radio]:checked, input[type=password]'))
    inputs.forEach( input => fd.append(input.name, input.value) )
    
    if ( fileBtn.files[0] ) {
        fd.append('avatar', fileBtn.files[0])
    }
    
    validation.usuario(fd)
    .then( res => {
        usuarios.add(fd)
        .then( res => {
            const { data } = res
            const { success, status } = data

            if ( !success ) {
                //Mensagem de erro
                return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${status}</div>`)
            }

            //Limpa os campos
            inputs.map( input => input.value = (/^(text|password)$/).test(input.type) ? '' : input.value )
            const avatar = document.querySelector('#avatar-place')
            avatar.innerHTML = ""
            fileBtn.value = ""

            //Mensagem de sucesso
            return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-success">${status}</div>`)
        })
        .catch( err => console.log(err) )
    })
    .catch( err => msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${err}</div>`) )
}

EventHandler.bind(btn, 'click', addUser)