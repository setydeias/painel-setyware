const axios = require('axios')

module.exports = (() => {

    const cnab240ToSQL = (obj) => {
        return new Promise((resolve, reject) => {
            const { pathTo, reader, customer } = obj

            axios({
                method: 'POST',
                url: '/painel/build/php/converterRemessas.php',
                data: { path: pathTo.value, file_content: reader.result, customer_pathname: customer.value }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const cnab400To240 = (obj) => {
        return new Promise((resolve, reject) => {
            const { pathTo, reader, tipo_convenio } = obj

            axios({
                method: 'POST',
				url: '/painel/build/php/converterFiles.php',
				data: { path: pathTo.value, file: reader.result, tipoconvenio: tipo_convenio }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        cnab240ToSQL: (obj) => cnab240ToSQL(obj),
        cnab400To240: (obj) => cnab400To240(obj)
    }
})()