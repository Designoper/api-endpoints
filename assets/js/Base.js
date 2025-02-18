export class Base {
	constructor() { }

	async fetchData({
		url,
		method = 'GET',
		form
	}) {

		let fetchOptions = {};

		switch (method) {
			case 'GET':
				url = new URL(url);
				const params = new FormData(form);
				url.search = new URLSearchParams(params).toString();

				break;

			case 'POST':
			case 'PUT':
			case 'DELETE':
				const formData = new FormData(form);

				fetchOptions = {
					method: method,
					body: formData
				};

				break;

				// fetchOptions = {
				// 	method: method,
				// 	body: JSON.stringify(data),
				// 	headers: {
				// 		'Content-Type': 'application/json'
				// 	},
				// }
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