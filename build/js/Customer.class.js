//Objeto
function Customer(){}
//Prototype functions

/*
* Dígito verificador
*/	
Customer.prototype.DigitoModulo11 = function(number, type, banco) {
	var sum = 0, dv, dvToReturn;
	switch (type) {
		case "agencia" :
            sum = banco != "341" ? this.LoopBase9(number) : this.LoopBase2(number);
			dv = this.GetDV(sum, banco);
			dvToReturn = dv;
            return dvToReturn;
        case "conta":
            sum = banco != "341" ? this.LoopBase9(number) : this.LoopBase2(number);
            dv = this.GetDV(sum, banco);
            dvToReturn = dv;
            return dvToReturn;
		case "cnpj":
			return this.ValidarCNPJ(number);
		case "cpf":
			return this.ValidarCPF(number);
		default:
			throw new Error("Não foi passado o parâmetro esperado");
	}
};

// ===================> (INÍCIO) Funções auxiliares módulo 11 <===================

//Loop cálculo módulo 11 (base 9)
Customer.prototype.LoopBase9 = function(n) {
	var sum = 0,
      aux = 2,
      i = n.length - 1;
	
    for ( ; i >= 0 ; i-- ) {
		sum += (n[i] * aux);
		aux = aux == 9 ? 2 : ++aux;
	}

	return sum;
};

//Loop cálculo módulo 10 (base 2)
Customer.prototype.LoopBase2 = function(n) {
    var sum = 0,
      aux = 2,
      i = n.length - 1;

    for ( ; i >= 0 ; i-- ) {
        sum += (this.SumDigit(n[i] * aux));
        aux = aux == 2 ? 1 : 2;
    }

    return sum;
};

//Sem base
Customer.prototype.LoopNoBase = function(n) {
	var sum = 0, aux = 2;
	for (var i = n.length - 1; i >= 0; i--) {
		sum += (n[i] * aux);
		aux++;
	}

	return sum;
};

Customer.prototype.SumDigit = function(d) {
    var digit,
      len = d.toString().length,
      i = 0,
      sum = 0;

    if ( d >= 10 ) {
        for ( ; i < len ; i++ ) {
            sum += parseInt(d.toString().charAt(i));
        }

        digit = sum;
    } else {
        digit = d;
    }

    return digit;
};

//Obtém o dígito verificador da agencia
Customer.prototype.GetDV = function(sum, banco) {
    const divisor = banco != '341' ? 11 : 10
    const resto = sum % divisor
	let dv = divisor - resto

    switch ( banco ) {
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
};

// ===================> (FIM) Funções auxiliares módulo 11 <===================

/*
* Verifica se o dígito verificador informado é igual ao correto
*/

Customer.prototype.ValidateDV = function(obj, type, bank) {
	var value,
	  data,
	  dvToCompare,
      banco = bank || null;

	//Define as posições que irão para a validação
	if (type == "agencia" || type == "conta") {
        value = typeof obj == 'object' ? obj.value : obj;
        data = value.substring(0, value.length - 1);
        dvToCompare = value.substring(value.length - 1);
	} else if (type == "cpf" || type == "cnpj") {
		data = VMasker.toNumber(obj.value);
	}

	var dv = this.DigitoModulo11(data, type, banco);
	
	if ( type == "agencia" || type == "conta" ) {
		if ( dvToCompare == `${dv}` ) {
			return true;
		} else {
			return false;
		}
	} else {
		return dv;
	}
};

/*
* Formata os campos de acordo com a validação
*/
Customer.prototype.FormatField = function(bool, obj) {
    obj.parentNode.classList.add( bool ? "has-success"  : "has-error" )
    obj.parentNode.classList.remove( bool ? "has-error" : "has-success" )
}

//Validação de data
Customer.prototype.ValidarData = function() {
    var aAr = typeof (arguments[0]) == "string" ? arguments[0].split("/") : arguments,
        lDay = parseInt(aAr[0]), lMon = parseInt(aAr[1]), lYear = parseInt(aAr[2]),
        BiY = (lYear % 4 === 0 && lYear % 100 !== 0) || lYear % 400 === 0,
        MT = [1, BiY ? -1 : -2, 1, 0, 1, 0, 1, 1, 0, 1, 0, 1];
    return lMon <= 12 && lMon > 0 && lDay <= MT[lMon - 1] + 30 && lDay > 0;
}

//Apenas letras
Customer.prototype.LimitarApenas = function(type, el){
	var regex;
	
	if (type == "number") {
		regex = /[A-Za-z]/g;
	} else if (type == "string") {
		regex = /\d/g;
	}

	el.value = el.value.replace(regex, '');
};

//Validar email
Customer.prototype.validateEmail = function(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
};

//Validar CPF
Customer.prototype.ValidarCPF = function(cpf) {
	cpf = cpf.replace(/[^\d]+/g, "");    
    // Elimina CPFs invalidos conhecidos    
    if ( cpf.length != 11 || 
        cpf === "" ||
        cpf == "00000000000" || 
        cpf == "11111111111" || 
        cpf == "22222222222" || 
        cpf == "33333333333" || 
        cpf == "44444444444" || 
        cpf == "55555555555" || 
        cpf == "66666666666" || 
        cpf == "77777777777" || 
        cpf == "88888888888" || 
        cpf == "99999999999" ) {
        return false;
    }
    // Valida 1o digito 
    var add = 0,
      i = 0;    
    for ( ; i < 9 ; i++ ) {       
        add += parseInt(cpf.charAt(i)) * (10 - i);  
    }
    var rev = 11 - (add % 11);  
    if (rev == 10 || rev == 11) {
        rev = 0;    
    }
    if (rev != parseInt(cpf.charAt(9))) {
        return false;       
    }
    // Valida 2o digito 
    add = i = 0;    
    for ( ; i < 10 ; i++ ) {
        add += parseInt(cpf.charAt(i)) * (11 - i);
    }  
    rev = 11 - (add % 11);  
    if (rev == 10 || rev == 11) {
        rev = 0;    
    }
    if (rev != parseInt(cpf.charAt(10))) {
        return false;       
    }
    return true;   
};

//Validar CNPJ
Customer.prototype.ValidarCNPJ = function(cnpj) {
	cnpj = cnpj.replace(/[^\d]+/g, "");
    // Elimina CNPJs invalidos conhecidos
    if (cnpj.length != 14 ||
        cnpj === "" ||
        cnpj == "00000000000000" || 
        cnpj == "11111111111111" || 
        cnpj == "22222222222222" || 
        cnpj == "33333333333333" || 
        cnpj == "44444444444444" || 
        cnpj == "55555555555555" || 
        cnpj == "66666666666666" || 
        cnpj == "77777777777777" || 
        cnpj == "88888888888888" || 
        cnpj == "99999999999999") {
        return false;
    }
         
    // Valida DVs
    var tamanho = cnpj.length - 2,
      numeros = cnpj.substring(0,tamanho),
      digitos = cnpj.substring(tamanho),
      soma = 0,
      pos = tamanho - 7,
      i = tamanho;

    for ( ; i >= 1 ; i-- ) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) {
            pos = 9;
        }
    }
    var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0)) {
        return false;
    }    
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1))
          return false;
           
    return true;
};