export class Base {
	constructor() { }

	async fetchData({
		url,
		method = 'GET',
		form
	}: {
		url: string,
		method?: string,
		form?: HTMLFormElement
	}) {

		const init: RequestInit = {};
		const userInputs = new FormData(form);

		switch (method) {
			case 'GET':

				const urlObj = new URL(url);
				urlObj.search = new URLSearchParams(userInputs as any).toString();
				url = urlObj.toString();

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
	errorChecker(response: any, errorContainer: HTMLElement) {

		if (response?.validationErrors?.length > 0) {
			errorContainer.innerHTML =
				`<p>Errores de validación:</p>

					${response.validationErrors.map((error: any) => `<li>${error}</li>`).join("")}

				</ul>`
		}

		if (response?.integrityErrors?.length > 0) {
			errorContainer.innerHTML =
				`<p>Errores de integridad:</p>

					${response.integrityErrors.map((error: any) => `<li>${error}</li>`).join("")}

                </ul>`
		}
	}
}