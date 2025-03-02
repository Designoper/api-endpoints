var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { Base } from "./Base.js";
export class Categoria extends Base {
    constructor() {
        super();
    }
    getCategorias() {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData({
                url: Categoria.categoriasEndpoint,
                method: 'GET'
            });
            return response;
        });
    }
    static categoriasTemplate({ fetchedCategorias }) {
        return fetchedCategorias.map(categoria => `<option value="${categoria['id_categoria']}">
                ${categoria['categoria']}
            </option>`).join('');
    }
    printCategorias(place) {
        return __awaiter(this, void 0, void 0, function* () {
            const categoriasResponse = yield this.getCategorias();
            const content = Categoria.categoriasTemplate({ fetchedCategorias: categoriasResponse.content });
            place.outerHTML = content;
        });
    }
}
Categoria.categoriasEndpoint = 'http://localhost/api-endpoints/api/categorias/';
