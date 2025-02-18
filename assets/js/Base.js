export class Base {
	constructor() { }

	async fetchData({
		url,
		method = 'GET',
		data = {},
		form
	}) {

		let fetchOptions = {};

		switch (method) {
			case 'GET':
				url = new URL(url);
				url.search = new URLSearchParams(data);
				break;

			case 'POST':
				const formData = new FormData(form);

				fetchOptions = {
					method: method,
					body: formData
				};

				break;

			case 'PUT':
			case 'DELETE':
				fetchOptions = {
					method: method,
					body: JSON.stringify(data),
					headers: {
						'Content-Type': 'application/json'
					},
				}
		}

		try {
			const response = await fetch(url, fetchOptions);

			if (response.status === 204) {
				return response;
			}

			const json = await response.json();
			json.status = response.status;
			json.ok = response.ok;
			return json;
		}

		catch (error) {
			console.log(error);
		}
	}

	formValidityChecker(form) {
		if (!form.reportValidity()) {
			throw new Error('Los datos del formulario no son válidos');
		}
	}

	// collectInputs(form) {
	// 	this.formValidityChecker(form);

	// 	const data = {};
	// 	const inputs = form.querySelectorAll('[name]');

	// 	inputs.forEach(input => {
	// 		if (input.getAttribute('type') === 'file') {
	// 			data[input.name] = input.files[0];
	// 		}
	// 		else data[input.name] = input.value;
	// 	});

	// 	return data;
	// }

	errorChecker(response, errorContainer) {
		if (response?.validationErrors?.length > 0) {
			errorContainer.innerHTML =
				`<p>Errores de validación:</p>

				<ul>
					${response.validationErrors.map(error => `<li>${error}</li>`).join("")}
				</ul>`
		}

		if (response?.integrityErrors?.length > 0) {
			errorContainer.innerHTML =
				`<p>Errores de integridad:</p>

                <ul>
                    ${response.integrityErrors.map(error => `<li>${error}</li>`).join("")}
                </ul>`
		}
	}
}