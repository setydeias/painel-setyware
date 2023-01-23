const axios = require('axios')

module.exports = (() => {

    const send = (obj) => {
        return new Promise((resolve, reject) => {
            axios({
				url: '/painel/build/php/adimplencia/envia-email.php',
                method: 'POST', 
                data: obj
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }
    

    const getSorteios = () => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/listar-sorteios.php',
                method: 'POST',
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const consultarSorteio = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/listar-sorteios-concurso.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const consultarBilhete = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/listar-sorteios-bilhete.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }
   
    const consultarPeriodo = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/listar-sorteios-periodo.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const consultarSorteados = () => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/listar-sorteados.php',
                method: 'POST',
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const registrarSorteio = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/registrar-sorteio.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const registrarSorteado = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/registrar-sorteado.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const deleteConcurso = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/deletar-concurso.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }


    const getConfigParams = () => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/listar-params.php',
                method: 'POST',
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }   

    const getGanhador = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/filtrar-ganhador.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const editLinkLoteriaFederal = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/editar-link-loteria-federal.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res))
            .catch(err => reject(err))
        })
    }

    const editConcurso = (data) => {
        return new Promise((resolve, reject) => {
            axios({
                url: '/painel/build/php/adimplencia/editar-concurso.php',
                method: 'POST',
                data: data,
                headers: { 'content-type': 'multipart/form-data' }
            })
            .then( res => resolve(res))
            .catch(err => reject(err))
        })
    }
    
    return {
        send: (data) => send(data),
        getSorteios: (data) => getSorteios(data),
        registrarSorteio: (data) => registrarSorteio(data),
        deleteConcurso: (data) => deleteConcurso(data),
        editLinkLoteriaFederal: (data) => editLinkLoteriaFederal(data),
        getConfigParams: () => getConfigParams(),
        editConcurso: (data) => editConcurso(data),
        consultarSorteio: (data) => consultarSorteio(data),
        consultarBilhete: (data) => consultarBilhete(data),
        consultarPeriodo: (data) => consultarPeriodo(data),
        consultarSorteados: () => consultarSorteados(),
        getGanhador: (data) => getGanhador(data),
        registrarSorteado: (data) => registrarSorteado(data)
    }

})()