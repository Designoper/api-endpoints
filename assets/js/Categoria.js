var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { Fetch } from "./Fetch";
export class Categoria extends Fetch {
    constructor() {
        super();
    }
    getCategorias() {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.simpleFetch(Categoria.categoriasEndpoint);
            Categoria.categorias = response;
        });
    }
    categoriasTemplate(fetchedCategorias) {
        const categorias = fetchedCategorias.map(categoria => `<option
				value='${categoria['id_categoria']}'>
				${categoria['categoria']}
			</option>`).join('');
        return categorias;
    }
    printCategorias(place) {
        const content = this.categoriasTemplate(Categoria.categorias.content);
        place.outerHTML = content;
    }
}
Categoria.categoriasEndpoint = new URL('http://localhost/api/categorias');
