const axios = require('axios')
const contentArea = document.querySelector('#conversor-content')

const renderView = (view) => {
    axios(`/painel/build/php/views/${view}.php`)
    .then( res => {
        const { data } = res
        const script = document.createElement('script')
        script.src = `/painel/dist/${view}.js`

        contentArea.innerHTML = ""
        contentArea.insertAdjacentHTML('beforeend', data)
        contentArea.appendChild(script)
    })
    .catch( err => console.log(err) )
}

renderView('conversorCnab400to240')

EventHandler.bind(document, 'click', (event) => {
    event.stopPropagation()
    const target = event.target

    if ( target.nodeName == 'A' && target.classList.contains('link-menu') ) {
        renderView(target.rel)
    }

})