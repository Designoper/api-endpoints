var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
export class Fetch {
    constructor() { }
    simpleFetch(url) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield fetch(url);
            const json = yield response.json();
            return json;
        });
    }
    fetchData(form) {
        return __awaiter(this, void 0, void 0, function* () {
            var _a;
            const init = {};
            const userInputs = new FormData(form);
            const sendButton = form.querySelector('button[type="submit"]');
            const method = (_a = sendButton.value.toUpperCase()) !== null && _a !== void 0 ? _a : "GET";
            const action = form.action;
            const url = new URL(action);
            const output = form.querySelector('output');
            const dialog = form.closest('dialog');
            switch (method) {
                case 'GET':
                    url.search = new URLSearchParams(userInputs).toString();
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
                const response = yield fetch(url, init);
                if (response.status === 204) {
                    this.resetForm(form, method, output, dialog);
                    return response;
                }
                const json = yield response.json();
                response.ok
                    ? this.resetForm(form, method, output, dialog)
                    : this.errorChecker(json, output);
                json.status = response.status;
                return json;
            }
            catch (error) {
                console.log(error);
            }
        });
    }
    errorChecker(response, output) {
        var _a, _b;
        if (((_a = response.validationErrors) === null || _a === void 0 ? void 0 : _a.length) > 0) {
            output.innerHTML =
                `<p>Errores de validación:</p>

				<ul>
					${response.validationErrors.map((error) => `<li>${error}</li>`).join("")}
				</ul>`;
        }
        if (((_b = response.integrityErrors) === null || _b === void 0 ? void 0 : _b.length) > 0) {
            output.innerHTML =
                `<p>Errores de integridad:</p>

				<ul>
					${response.integrityErrors.map((error) => `<li>${error}</li>`).join("")}
				</ul>`;
        }
    }
    resetForm(form, method, errorContainer, dialog) {
        form && method !== "GET" ? form.reset() : null;
        dialog ? dialog.close() : null;
        errorContainer ? errorContainer.innerHTML = "" : null;
    }
}
