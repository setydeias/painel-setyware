module.exports = (() => {

    const format = (el, boolean) => {
        el.parentNode.classList.add( boolean ? "has-success"  : "has-error" )
        el.parentNode.classList.remove( boolean ? "has-error" : "has-success" )
    }

    const onlyNumbers = (el) => el.value = el.value.replace(/[A-Za-z]/g, '')
    const onlyStrings = (el) => el.value = el.value.replace(/\d/g, '')
    const only = (type, el) => {
        switch ( type ) {
            case 'number':
                return onlyNumbers(el)
                break
            case 'string':
                return onlyStrings(el)
                break
        }
    }

    return {
        format: (el, boolean) => format(el, boolean),
        only: (type, el) => only(type, el)
    }

})()