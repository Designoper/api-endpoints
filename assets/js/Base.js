export class Base {
	constructor() { }

	async fetchData({
		url,
		method = 'GET',
		form
	}) {

		const fetchOptions = {};
		const userInputs = new FormData(form);

		switch (method) {
			case 'GET':

				url = new URL(url);
				url.search = new URLSearchParams(userInputs).toString();

				break;

			case 'POST':
			case 'PUT':
			case 'DELETE':

				fetchOptions.method = method;
				fetchOptions.body = userInputs;

				break;
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