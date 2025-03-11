export class Fetch {
	constructor() { }

	async simpleFetch(url: URL): Promise<any> {
		const response = await fetch(url);
		const json: Promise<any> = await response.json();
		return json;
	}

	async fetchData(form: HTMLFormElement): Promise<any> {

		const init: RequestInit = {};
		const userInputs = new FormData(form);
		const sendButton = form.querySelector('button[type="submit"]') as HTMLButtonElement;
		const method = sendButton.value.toUpperCase() ?? "GET";
		const action = form.action;
		const url = new URL(action);

		const output = form.querySelector('output') as HTMLOutputElement;
		const dialog = form.closest('dialog') as HTMLDialogElement;

		switch (method) {
			case 'GET':
				url.search = new URLSearchParams(userInputs);
				break;

			case 'POST':
			case 'PUT':
				init.method = 'POST';
				init.body = userInputs;
				break;

			case 'DELETE':
				init.method = 'DELETE';
		}

		try {
			const response = await fetch(url, init);

			if (response.status === 204) {
				this.resetForm(form, method, output, dialog);
				return response;
			}

			const json = await response.json();

			response.ok
			? this.resetForm(form, method, output, dialog)
			: this.errorChecker(json, output);

			json.status = response.status;
			return json;
		}

		catch (error: unknown) {
			console.log(error);
		}
	}

	errorChecker(response:any, output: HTMLOutputElement): void {
		if (response.validationErrors?.length > 0) {
			output.innerHTML =
				`<p>Errores de validación:</p>

				<ul>
					${response.validationErrors.map((error: any) => `<li>${error}</li>`).join("")}
				</ul>`
		}

		if (response.integrityErrors?.length > 0) {
			output.innerHTML =
				`<p>Errores de integridad:</p>

				<ul>
					${response.integrityErrors.map((error: any) => `<li>${error}</li>`).join("")}
				</ul>`
		}
	}

	resetForm(form: HTMLFormElement, method: string, errorContainer: HTMLOutputElement, dialog: HTMLDialogElement): void {
		form && method !== "GET" ? form.reset() : null;
		dialog ? dialog.close() : null;
		errorContainer ? errorContainer.innerHTML = "" : null;
	}
}