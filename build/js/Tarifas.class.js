function Tarifas() {}

Tarifas.prototype.get = function(url, data) {

	var dataToArray = (data) ? {request_method: 'POST', data: {type: data}} : {request_method: 'GET'};

	return new Promise(function(resolve, reject){
		AjaxRequest.init({
			url: url,
			method: dataToArray.request_method,
			data: (data) ? {type: dataToArray.data.type} : ''
		}).then(function(res){
			try {
				resolve(JSON.parse(res));
			} catch (e) {
				console.log(new Error('Não foi possível recuperar o JSON'));
			}
		}, function(err){
			console.log(err);
		});
	});
};

Tarifas.prototype.getTaxByCodSac = function(url, data) {

	var dataToArray = (data) ? {request_method: 'POST', data: {type: data}} : {request_method: 'GET'};

	return new Promise(function(resolve, reject){
		AjaxRequest.init({
			method: dataToArray.request_method,
			data: (data) ? {type: dataToArray.data.type} : '',
			url: url
		}).then(function(res){
			try {
				resolve(JSON.parse(res));
			} catch (e) {
				console.log(new Error('Não foi possível recuperar o JSON'));
			}
		}, function(err){
			console.log(err);
		});
	});
};