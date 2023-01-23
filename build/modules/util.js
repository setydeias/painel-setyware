const axios = require('axios')

module.exports = (() => {

    const validarData = (data) => {
        var aAr = data.split("/"),
        lDay = parseInt(aAr[0]), lMon = parseInt(aAr[1]), lYear = parseInt(aAr[2]),
        BiY = (lYear % 4 === 0 && lYear % 100 !== 0) || lYear % 400 === 0,
        MT = [1, BiY ? -1 : -2, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1];
        return lMon <= 12 && lMon > 0 && lDay <= MT[lMon - 1] + 30 && lDay > 0;
    }

    const validarCPF = (cpf) => {
        cpf = cpf.replace(/[^\d]+/g, "");   
        // Elimina CPFs invalidos conhecidos    
        if ( cpf.length != 11 || cpf === "" || cpf == "00000000000" || cpf == "11111111111" || 
            cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || 
            cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" ) 
        {
            return false
        }
        // Valida 1o digito 
        let add = 0
        for ( let i = 0 ; i < 9 ; i++ ) {       
            add += parseInt(cpf.charAt(i)) * (10 - i)
        }
        let rev = 11 - (add % 11); 
        if (rev == 10 || rev == 11) rev = 0
        if (rev != parseInt(cpf.charAt(9))) return false
        // Valida 2o digito 
        add = i = 0  
        for ( ; i < 10 ; i++ ) add += parseInt(cpf.charAt(i)) * (11 - i)
        rev = 11 - (add % 11)
        if (rev == 10 || rev == 11) rev = 0
        if (rev != parseInt(cpf.charAt(10))) return false
        return true
    }

    const validarCNPJ = (cnpj) => {
        cnpj = cnpj.replace(/[^\d]+/g, "");
        // Elimina CNPJs invalidos conhecidos
        if ( cnpj.length != 14 || cnpj === "" || cnpj == "00000000000000" || cnpj == "11111111111111" || cnpj == "22222222222222" || 
            cnpj == "33333333333333" || cnpj == "44444444444444" || cnpj == "55555555555555" || cnpj == "66666666666666" || 
            cnpj == "77777777777777" || cnpj == "88888888888888" || cnpj == "99999999999999" ) 
        {
            return false
        }
             
        // Valida DVs
        let tamanho = cnpj.length - 2
        let numeros = cnpj.substring(0, tamanho)
        let soma = 0
        let pos = tamanho - 7
        let i = tamanho
        const digitos = cnpj.substring(tamanho)
    
        for ( ; i >= 1 ; i-- ) {
            soma += numeros.charAt(tamanho - i) * pos--
            if (pos < 2) pos = 9
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11
        if (resultado != digitos.charAt(0)) return false
        tamanho = tamanho + 1
        numeros = cnpj.substring(0, tamanho)
        soma = 0
        pos = tamanho - 7
        for ( i = tamanho; i >= 1; i-- ) {
          soma += numeros.charAt(tamanho - i) * pos--
          if (pos < 2) pos = 9
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11
        if (resultado != digitos.charAt(1)) return false
               
        return true
    }

    const getDV = (bank, context) => {

        //Loop na base 9
        const loopBase9 = (number) => {
            let sum = 0
	
            for ( let i = number.length - 1, aux = 2 ; i >= 0 ; i-- ) {
                sum += (number[i] * aux)
                aux = aux == 9 ? 2 : ++aux
            }

            return sum
        }

        //Loop na base 2
        const loopeBase2 = (number) => {
            let sum = 0

            //Soma os algarismos do dado número
            const sumAlgarisms = (d) => {
                let len = d.toString().length, sum = 0

                if ( d >= 10 ) {
                    for ( i = 0 ; i < len ; i++ ) sum += parseInt(d.toString().charAt(i))
                    return sum
                } else {
                    return d
                }
            }

            for ( let i = number.length - 1, aux = 2 ; i >= 0 ; i-- ) {
                sum += sumAlgarisms(number[i] * aux)
                aux = aux == 2 ? 1 : 2
            }

            return sum;
        }

        //Regas de DV dos bancos
        const dvBankRules = (amount, bank) => {
            const divisor = bank != '341' ? 11 : 10
            const resto = amount % divisor
            let dv = divisor - resto
        
            switch ( bank ) {
                case "001":
                    dv = dv == 10 ? "X" : dv < 10 ? dv : dv > 10 ? "0" : ""
                break
                case "104":
                    dv = (dv >= 10) ? "0" : dv
                break
                case "237":
                    dv = dv >= 10 ? "0" : dv
                break
                case "341":
                    dv = dv >= 10 ? "0" : dv
                break
                default:
                    dv = false
                break
            }
        
            return dv
        }

        const sum = bank != "341" ? loopBase9(context) : loopeBase2(context)
        return dvBankRules(sum, bank)
    }

    const validateDV = (bank, context) => {
        if ( bank === undefined || context === undefined || context.length === 0 ) {
            return false
        }

        const contextData = context.substring(0, context.length - 1)
        const contextDV = context.substring(context.length - 1)
        const dv = getDV(bank.value || bank, contextData)
        return contextDV == dv
    }

    const validateMail = (mail) => {
        const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        return regex.test(mail)
    }

    const getAddressByCEP = (cep) => {
        return new Promise((resolve, reject) => {
            axios(`http://viacep.com.br/ws/${cep.value}/json/`)
            .then( res => resolve(res) )
            .catch( err => reject(err) )
        })
    }

    const convertBtoMB = (b) => b * 0.000001

    const cefAllowedOperations = () => {
        return ['001', '002', '003', '006', '007', '013', '022']
    }

    return {
        validarData: (data) => validarData(data),
        validarCPF: (cpf) => validarCPF(cpf),
        validarCNPJ: (cnpj) => validarCNPJ(cnpj),
        validateDV: (bank, context) => validateDV(bank, context),
        validateMail: (mail) => validateMail(mail),
        getAddressByCEP: (cep) => getAddressByCEP(cep),
        convertBtoMB: (b) => convertBtoMB(b),
        cefAllowedOperations: () => cefAllowedOperations()
    }

})()