const axios = require('axios')

module.exports = (() => {

    const getADM77777 = () => {
        return new Promise((resolve, reject) => {
            axios(`/painel/build/php/exportar/get-adm77777.php`)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const initADM77777 = () => {
        const config = {}

        return new Promise((resolve, reject) => {
            axios.post(`/painel/build/php/exportar/init-export-adm77777.php`, {}, config)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        getADM77777: () => getADM77777(),
        initADM77777: () => initADM77777()
    }

})()