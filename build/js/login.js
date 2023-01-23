const server = require('../api/server')

const login = (e) => {
    e.preventDefault()
    const user = document.getElementById('user')
    const pass = document.getElementById('pass')
    const error = document.getElementById('error-area')
    error.innerHTML = ""

    if ( user.value.length != '' && pass.value.length != '' ) {
        server.login({ user: user.value.toLowerCase(), password: pass.value })
        .then( res => {
            const { success, message } = res.data
            
            !success 
                ? error.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${message ? message : res.data}</div>`)
                : window.location.href = "/painel/index"
        })
        .catch(err => error.insertAdjacentHTML('beforeend', `<div class="alert alert-danger">${err.response}</div>`) )
    } else {
        error.insertAdjacentHTML('beforeend', '<div class="alert alert-danger">Preencha todos os campos</div>')
    }
}

const loginBtn = document.getElementById('login')
EventHandler.bind(loginBtn, 'click', login)

EventHandler.bind(document, 'keypress', (e) => {
    if ( e.keyCode == 13 ) login(e)
})