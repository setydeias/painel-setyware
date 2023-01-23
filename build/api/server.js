const axios = require('axios')
const socket = io.connect('http://179.188.38.39:7774/')

module.exports = (() => {

    const sendRetorno = (status) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/server/change-server-send-status.php',
                data: { update: status }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateConversorStatus = (status) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/server/change-convert-file-status.php',
                data: { stmt: status }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateDirectories = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/diretorios/change-dir.php',
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateSalario = (salario) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: 'build/php/mensalidades/change-salario.php',
                data: { salario }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateTaxes = (taxes) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/tarifas/change-tarifas.php',
                data: taxes
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getParameters = () => {
        return new Promise((resolve, reject) => {
            axios('/painel/build/php/get-parametros.php')
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const login = (user) => {
        return new Promise((resolve, reject) => {
            axios({
                method: 'POST',
                url: '/painel/build/php/login.php',
                data: user
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const sendProcessedFiles = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/transf/ftp_transfer.php',
                method: 'POST',
                data: { data }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const sendProcessedFilesByMail = (checked) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/transf/mail_transfer.php',
                method: 'POST',
                data: { unlink_files: checked }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const addConvenioCobranca = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/add-convenio-processamento.php',
                method: 'POST',
                data: { obj }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const removeConvenioCobranca = (convenio) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/convenios/delete-convenio-processamento.php',
                method: 'POST',
                data: { convenio }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateSVNparams = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/server/update-svn-params.php',
                method: 'POST',
                data: { data }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateMailParams = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/server/update-mail-params.php',
                method: 'POST',
                data: { data }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const exportContaTransitoria = (customers) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/server/export-conta-transitoria.php',
                method: 'POST',
                data: { customers }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getSVNParams = () => {
        return new Promise((resolve, reject) => {
            axios(`/painel/build/php/server/get-svn-params.php`)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const syncCustomerDatabases = () => {
        return new Promise((resolve, reject) => {
            socket.emit('zip_customer_databases')

            socket.on('init_sync_database', () => {
                axios(`/painel/build/php/server/sync-customer-databases.php`)
                .then( res => resolve(res) )
                .catch( err => reject(err) )
            })
        })
    }
    
    const extractCustomerDatabases = () => {
        return new Promise((resolve, reject) => {
            axios(`/painel/build/php/server/extract-customer-databases.php`)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const getMasterPassword = () => {
        return new Promise((resolve, reject) => {
            axios(`/painel/build/php/server/get-master-pass.php`)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const updateMasterPassword = (newPassword) => {
        return new Promise((resolve, reject) => {
            axios({
                url: `/painel/build/php/server/update-master-pass.php`,
                method: 'POST',
                data: { newPassword }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    return {
        sendRetorno: (status) => sendRetorno(status),
        updateConversorStatus: (status) => updateConversorStatus(status),
        updateDirectories: (obj) => updateDirectories(obj),
        updateSalario: (salario) => updateSalario(salario),
        updateTaxes: (taxes) => updateTaxes(taxes),
        getParameters: () => getParameters(),
        login: (user) => login(user),
        sendProcessedFiles: (checked) => sendProcessedFiles(checked),
        sendProcessedFilesByMail: (checked) => sendProcessedFilesByMail(checked),
        addConvenioCobranca: (obj) => addConvenioCobranca(obj),
        removeConvenioCobranca: (convenio) => removeConvenioCobranca(convenio),
        updateSVNparams: (data) => updateSVNparams(data),
        updateMailParams: (data) => updateMailParams(data),
        exportContaTransitoria: (customers) => exportContaTransitoria(customers),
        getSVNParams: () => getSVNParams(),
        syncCustomerDatabases: () => syncCustomerDatabases(),
        extractCustomerDatabases: () => extractCustomerDatabases(),
        getMasterPassword: () => getMasterPassword(),
        updateMasterPassword: (newPassword) => updateMasterPassword(newPassword)
    }
})()