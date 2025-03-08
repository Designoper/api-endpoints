import { Fetch } from "./Fetch.js";

export class Categoria extends Fetch {
	static categoriasEndpoint = 'http://localhost/api/categorias/';
	static categorias;

	constructor() {
		super();
		this.getCategorias();
	}

	async getCategorias() {
		const response = await this.simpleFetch(Categoria.categoriasEndpoint);
		Categoria.categorias = response.content;
	}

	static categoriasTemplate(fetchedCategorias) {

		const categorias = fetchedCategorias.map(categoria =>
			`<option
				value='${categoria['id_categoria']}'>
				${categoria['categoria']}
			</option>`
		).join('');

		return categorias;
	}

	printCategorias(place) {
		const content = Categoria.categoriasTemplate(Categoria.categorias);
		place.outerHTML = content;
	}
}
