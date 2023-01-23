const usuarios = require('../api/usuarios')
const modal = require('../modules/modal')
const validation = require('../modules/validation')
const isEditScreen = window.location.pathname.indexOf('edit') !== -1
let { pathname } = window.location
pathname = pathname.split('/')
const user_id = isEditScreen ? pathname[pathname.length - 1] : null

const usersList = (() => {

    usuarios.get()
    .then( res => {
        const { data } = res
        const userArea = document.querySelector('#usuarios')

        if ( !userArea ) {
            return false
        }
        
        if ( data.length > 0 ) {
            const list = data.map( user => `
                <li class="list-group-item">
                    <span class="glyphicon glyphicon-user"></span> ${user.USUARIO}
                    <a href="/painel/users/edit/${user.ID_USUARIOS}">
                        <button class="btn btn-default btn-sm pull-right" style="padding:3px 5px;">
                            <span class="glyphicon glyphicon-edit"></span> Editar
                        </button>
                    </a>
                </li>
            ` ).sort().join('')
            userArea.insertAdjacentHTML('beforeend', `<ul class="list-group">${list}</ul>`)
        } else {
            userArea.insertAdjacentHTML('beforeend', `<h4><span class="glyphicon glyphicon-alert"></span> Nenhum usuário encontrado</h4>`)
        }
            
    })
    .catch( err => console.log(err) )

})()

//Adicionar foto
const btnSelectFile = document.querySelector('#avatar')
const fileBtn = document.querySelector('form input[type=file]')

if ( btnSelectFile && fileBtn ) {
    EventHandler.bind(btnSelectFile, 'click', () => fileBtn.click())
}

EventHandler.bind(fileBtn, 'change', (event) => {
    const { files } = fileBtn
    const reader = new FileReader()

    reader.onloadend = () => {
        const img = document.createElement('img')
        img.src = reader.result
        img.alt = "Foto do perfil"
    
        const avatar = document.querySelector('#avatar-place')
        avatar.innerHTML = ""
        avatar.insertAdjacentElement('beforeend', img)
        avatar.insertAdjacentHTML('beforeend', `
            <div>
                <span title="Descartar imagem" class="glyphicon glyphicon-trash" id="descartar-imagem"></span>
            </div>
        `)
    }

    if ( files[0] ) {
        reader.readAsDataURL(files[0])
    }
})

//Remove a imagem selecionada
const removeImage = (event) => {
    const { target } = event
    const { tagName, id } = target
    
    if ( tagName === 'SPAN' && id === 'descartar-imagem' ) {
        
        if ( isEditScreen ) {
            let content = `
                <section>
                    <h3>Deseja remover a foto do perfil?</h3>
                    <hr />
                    <p>
                        <button class="btn btn-primary sure"><span class="glyphicon glyphicon-ok-sign"></span> Remover</button>
                        <button class="btn btn-danger exit-action"><span class="glyphicon glyphicon-remove"></span> Cancelar</button>
                    </p>
                </section>
            `
            return document.body.insertAdjacentHTML('beforeend', modal.open(content))
        }
        
        const avatar = target.parentNode.parentNode
        avatar.innerHTML = ""
        const fileBtn = document.querySelector('form input[type=file]')
        if ( fileBtn ) {
            fileBtn.value = ""
        }
    }
}
EventHandler.bind(document, 'click', removeImage)

const execContext = (event) => {
    const { target } = event
    const { classList } = target

    //Remove a foto do banco
    if ( isEditScreen && classList.contains('sure') ) {
        let { pathname } = window.location
        pathname = pathname.split('/')
        const user_id = pathname[pathname.length - 1]
        usuarios.removePhoto(user_id)
        .then( res => {
            toastr.success('Foto do perfil removida com sucesso') 
            modal.close()
            const avatar = document.querySelector('#avatar-place')
            avatar.innerHTML = ""
            fileBtn.value = ""
        })
        .catch( err => {
            toastr.error('Erro ao remover foto, tente novamente') 
            modal.close()
        })
    }

    //Close modal
    if ( classList.contains('exit-action') ) {
        modal.close()
    }
}
EventHandler.bind(document, 'click', execContext)

//Adicionar ou Editar usuário
const msg = document.querySelector('#msg')
const btn = document.querySelector('button[name=btn-action]')

const exec = () => {
    msg.innerHTML = ""
    const fd = new FormData()
    const inputs = Array.from(document.querySelectorAll('input[type=text], input[type=radio]:checked, input[type=password]'))
    inputs.forEach( input => fd.append(input.name, input.value) )
    
    if ( fileBtn.files[0] ) {
        fd.append('avatar', fileBtn.files[0])
    }
    
    validation.usuario(fd)
    .then( res => {

        if ( isEditScreen ) {
            fd.append('id_usuario', user_id)
        }

        usuarios[isEditScreen ? 'edit' : 'add'](fd)
        .then( res => {
            const { data } = res
            const { success, status } = data

            if ( !success ) {
                //Mensagem de erro
                return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${status}</div>`)
            }

            //Limpa os campos
            if ( !isEditScreen ) {
                inputs.map( input => input.value = (/^(text|password)$/).test(input.type) ? '' : input.value )
                const avatar = document.querySelector('#avatar-place')
                avatar.innerHTML = ""
                fileBtn.value = ""
            }
            
            //Mensagem de sucesso
            return msg.insertAdjacentHTML('beforeend', `<div class="alert alert-success">${status}</div>`)
        })
        .catch( err => console.log(err) )
    })
    .catch( err => msg.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${err}</div>`) )
}

EventHandler.bind(btn, 'click', exec)