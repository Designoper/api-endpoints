var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
export class Base {
    constructor() { }
    fetchData(_a) {
        return __awaiter(this, arguments, void 0, function* ({ url, method = 'GET', form }) {
            const init = {};
            const userInputs = new FormData(form);
            switch (method) {
                case 'GET':
                    const urlObj = new URL(url);
                    urlObj.search = new URLSearchParams(userInputs).toString();
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
                const response = yield fetch(url, init);
                if (response.status === 204) {
                    return response;
                }
                const json = yield response.json();
                json.status = response.status;
                json.ok = response.ok;
                return json;
            }
            catch (error) {
                console.log(error);
            }
        });
    }
    errorChecker(response, errorContainer) {
        var _a, _b;
        if (((_a = response === null || response === void 0 ? void 0 : response.validationErrors) === null || _a === void 0 ? void 0 : _a.length) > 0) {
            errorContainer.innerHTML =
                `<p>Errores de validación:</p>

					${response.validationErrors.map((error) => `<li>${error}</li>`).join("")}

				</ul>`;
        }
        if (((_b = response === null || response === void 0 ? void 0 : response.integrityErrors) === null || _b === void 0 ? void 0 : _b.length) > 0) {
            errorContainer.innerHTML =
                `<p>Errores de integridad:</p>

					${response.integrityErrors.map((error) => `<li>${error}</li>`).join("")}

                </ul>`;
        }
    }
}
