//Modules
const usuarios = require('../api/usuarios')
const modal = require('../modules/modal')
//Get user id
const pathname = window.location.pathname.split('/')
const user_id = pathname[pathname.length - 1]

const getUserData = (() => {
    usuarios.getById(user_id)
    .then( res => {
        const { data } = res
        const customer = data[0]

        const usuario = document.querySelector('input[name=usuario]')
        const nome = document.querySelector('input[name=nome]')
        const sexo = document.querySelectorAll('input[name=sexo]')

        usuario.value = customer.USUARIO
        nome.value = customer.NOME
        Array.from(sexo).forEach( input => input.checked = input.value === customer.SEXO )
        
        if ( customer.FOTO && customer.FOTO !== '' ) {
            const src = `/painel/build/images/perfil/${customer.FOTO}`
            const avatar = document.querySelector('#avatar-place')
            avatar.innerHTML = ""
            avatar.insertAdjacentHTML('beforeend', `<img src="${src}" alt="Foto do perfil" />`)
            avatar.insertAdjacentHTML('beforeend', `
                <div>
                    <span title="Descartar imagem" class="glyphicon glyphicon-trash" id="descartar-imagem"></span>
                </div>
            `)
        }
    })
    .catch( err => console.log(err) )
})()

//Remover usuário
const openModal = (event) => {
    const { target } = event
    const { classList } = target

    if ( classList.contains('remove-user') ) {
        let content = `
            <section>
                <h3>Deseja remover o usuário?</h3>
                <hr />
                <p>
                    <button type="button" class="btn btn-primary sure-remove"><span class="glyphicon glyphicon-ok-sign"></span> Remover</button>
                    <button type="button" class="btn btn-danger exit-action"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                </p>
            </section>
        `
        return document.body.insertAdjacentHTML('beforeend', modal.open(content))       
    }

    if ( classList.contains('change-password') ) {
        let content = `
            <section>
                <h3>Alterar senha</h3>
                <hr />
                <form>
                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Senha atual:</label>
                            <input type="password" name="password" class="form-control" maxlength="32" />
                        </div>
                        <div class="form-group col-md-3">
                            <div class="form-group">
                                <label>Nova senha:</label>
                                <input type="password" name="new_password" class="form-control" maxlength="32" />
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <div class="form-group">
                                <label>Confirmar nova senha:</label>
                                <input type="password" name="sure_password" class="form-control" maxlength="32" placeholder="Repita a Nova senha" />
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary sure-change"><span class="glyphicon glyphicon-edit"></span> Alterar</button>
                    <button type="button" class="btn btn-danger exit-action"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                    <div style="margin:10px 0 0 0;" id="change-password-msg"></div>
                </form>
            </section>
        `
        return document.body.insertAdjacentHTML('beforeend', modal.open(content))   
    }
}

EventHandler.bind(document, 'click', openModal)

const sureContext = (event) => {
    const { target } = event
    const { classList } = target

    if ( classList.contains('sure-remove') ) {
        usuarios.remove(user_id)
        .then( res => {
            const { data } = res
            const { success, status } = data

            if ( data.redirect ) {
                window.location.href = "/painel/login"
            }

            if ( !success ) {
                return toastr.error(status)
            }

            toastr.success(status)
            setTimeout(() => window.location.href = "/painel/users", 3000)
        })
        .catch( err => toastr.error(`${err}`) )
    }

    if ( classList.contains('sure-change') ) {
        const inputs = Array.from(target.parentNode.querySelectorAll('input[type=password]'))
        const msg = document.querySelector('#change-password-msg')
        msg.innerHTML = ""
        
        const data = {}
        for ( let i = 0, { length } = inputs; i < length; i++ ) {
            if ( inputs[i].value.length < 6 ) {
                msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">As senhas devem conter ao menos 6 dígitos</div>`)
                return
            }
            
            Object.assign(data, {[inputs[i].name]: inputs[i].value}) 
        }
        
        if ( data.new_password !== data.sure_password ) {
            return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">As novas senhas devem ser correspondentes</div>`)
        }

        usuarios.changePassword(Object.assign(data, { user_id }))
        .then( res => {
            const { success, status } = res.data

            if ( !success ) {
                return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${status}</div>`)
            }

            toastr.success(status)
            modal.close()
        })
        .catch( err => {
            return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${err.response.data || err}</div>`)
        })
    }
}

EventHandler.bind(document, 'click', sureContext)