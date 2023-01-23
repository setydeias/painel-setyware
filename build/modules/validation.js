const util = require('./util')
const format = new Format()
const allowed_banks = ['001', '104', '237', '341']
const allowed_convenio_types = ['1', '2']
const allowed_op = util.cefAllowedOperations()

module.exports = (() => {

    const fmtAccountToValidate = (data) => {
        const { banco, agencia, conta, op } = data
        
        switch ( banco ) {
            case '104':
                return `${agencia.substring(0, agencia.length - 1)}${op}${format.str_pad('000000000', conta, 'l')}`
            case '341':
                return `${agencia.substring(0, agencia.length - 1)}${conta}`
            default:
                return conta
        }
    }

    const convenio = (data) => {
        return new Promise((resolve, reject) => {
            const { banco, agencia, conta, op, convenio, carteira, variacao, tipo_convenio, customer } = data
            const account = fmtAccountToValidate({ banco, agencia, conta, op })
            
            if ( !allowed_banks.includes(banco) ) {
                return reject('Banco não permitido')
            }
            
            if ( !util.validateDV(banco, agencia) ) {
                return reject('Agência inválida')
            }

            if ( !util.validateDV(banco, account) ) {
                return reject('Conta inválida')
            }

            if ( banco === '104' && !allowed_op.includes(op) ) {
                return reject('Operação inválida')
            }
        
            if ( convenio.length < 6 || convenio.length > 7 ) {
                return reject('Número do convênio inválido')
            }

            if ( !allowed_convenio_types.includes(tipo_convenio) ) {
                return reject('Tipo de convênio inválido')
            }
            
            if ( isNaN(carteira) || carteira.length === 0 || carteira.length > 2 ) {
                return reject('Carteira inválida')
            }

            if ( banco !== '104' && (isNaN(variacao) || variacao.length === 0 || variacao.length > 3) ) {
                return reject('Variação inválida')
            }

            if ( tipo_convenio === '2' && customer === '' ) {
                return reject('Convênio próprio requer ao menos um cliente selecionado')
            }

            resolve(data)
        })
    }

    const isValidImage = (file) => {
        const { name, size } = file
        const splited_name = name.split('.')
        const extension = splited_name[splited_name.length - 1]
        
        if ( !(/^(jpg|jpeg|gif|png)$/).test(extension) ) {
            throw 'Extensão inválida, insira uma imagem'
        }

        if ( util.convertBtoMB(size) > 5 ) {
            throw 'Tamanho máximo da imagem é de 5MB'
        }

        return file
    }

    const usuario = (data) => {
        return new Promise((resolve, reject) => {
            for ( let value of data.entries() ) {
                const [key, valor] = value
                
                if ( key === 'usuario' && (valor === '' || !(/^[a-zA-Z0-9 ]{1,15}$/).test(valor)) ) {
                    return reject('Usuário inválido')
                }

                if ( key === 'nome' && (valor === '' || valor.length > 30) ) {
                    return reject('Nome inválido')
                }
    
                if ( key === 'sexo' && !(/^(m|f)$/).test(valor) ) {
                    return reject('Sexo inválido')
                }
    
                if ( key === 'password' && (valor.length < 6 || valor.length > 30) ) {
                    return reject('Senha inválida')
                }
            }

            resolve(data)
        })
    }

    return {
        convenio: (data) => convenio(data),
        isValidImage: (file) => isValidImage(file),
        usuario: (data) => usuario(data)
    }

})()