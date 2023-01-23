
const modal = (content) => `
    <section id="modal-area" class="modal" style="display:block;">
        <section class="modal-content">
            <span class="close exit-action">&times;</span>
            ${content}
        </section>
    </section>
`

module.exports = (() => {

    const open = (content) => modal(content)

    const close = () => {
        const el = document.querySelector('.modal')
        if ( el ) {
            document.body.removeChild(el)
        }
    }

    return {
        open: (content) => open(content),
        close: () => close()
    }

})()