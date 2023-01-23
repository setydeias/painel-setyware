const axios = require('axios')

module.exports = (() => {

    const getUserByCod = (cod) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/2via/get-user-2via.php',
                method: 'POST',
                data: { usuario: cod }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const removeUserByCod = (cod) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/2via/reset-password-2via.php',
                method: 'POST',
                data: { usuario: cod }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        getUserByCod: (cod) => getUserByCod(cod),
        removeUserByCod: (cod) => removeUserByCod(cod),
    }
})()