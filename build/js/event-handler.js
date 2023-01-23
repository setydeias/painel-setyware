const EventHandler = {
    bind: (el, ev, fn, boolean = false)  => {
        if ( window.addEventListener ) {
            el.addEventListener(ev, fn, boolean)
        } else if ( window.attachEvent ) {
            el.attachEvent(`on${ev}`, fn)
        } else {
            el[`on${ev}`] = fn
        }
    },

    unbind: (el, ev, fn, boolean = false) => {
        if ( window.addEventListener ) {
            el.removeEventListener(ev, fn, boolean)  
        } else if ( window.attachEvent ) {
            el.detachEvent(`on${ev}`, fn)
        } else {
            el[`on${ev}`] = null
        }
    },

    stop: (ev) => {
        const e = ev || window.event
        e.cancelBubble = true
        if ( e.stopPropagation ) e.stopPropagation()
    }
}