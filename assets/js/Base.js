export class Base {
	constructor() { }

	async simpleFetch(url) {
		const response = await fetch(url);
		const json = await response.json();
		return json;
	}

	async fetchData(form) {

		const init = {};
		const userInputs = new FormData(form);
		let url = form.getAttribute("action");
		const method = form.getAttribute("method").toUpperCase();

		switch (method) {
			case 'GET':

				url = new URL(url);
				url.search = new URLSearchParams(userInputs);

				break;

			case 'POST':
			case 'PUT':
			case 'DELETE':

				init.method = method;
				init.body = userInputs;

				break;
		}

		try {
			const response = await fetch(url, init);

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