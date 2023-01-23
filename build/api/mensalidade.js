const axios = require('axios')

module.exports = (() => {

    const write = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
				url: 'build/php/mensalidades/write-mensalidade.php',
                method: 'POST', 
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }
    
    return {
        write: (data) => write(data)
    }
})()