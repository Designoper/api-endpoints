import { Fetch } from "./Fetch";
import CategoriaContent from "./interfaces/CategoriaContent";
import CategoriaResponse from "./interfaces/CategoriaResponse";

export class Categoria extends Fetch {
	static categoriasEndpoint = new URL('http://localhost/api/categorias');
	static categorias: CategoriaResponse;

	constructor() {
		super();
	}

	async getCategorias() {
		const response: CategoriaResponse = await this.simpleFetch(Categoria.categoriasEndpoint);
		Categoria.categorias = response;
	}

	categoriasTemplate(fetchedCategorias: CategoriaContent[]): string {

		const categorias = fetchedCategorias.map(categoria =>
			`<option
				value='${categoria['id_categoria']}'>
				${categoria['categoria']}
			</option>`
		).join('');

		return categorias;
	}

	printCategorias(place: HTMLOptionElement) {
		const content = this.categoriasTemplate(Categoria.categorias.content);
		place.outerHTML = content;
	}
}
