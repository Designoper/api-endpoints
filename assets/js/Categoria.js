import { Fetch } from "./Fetch.js";

export class Categoria extends Fetch {
	static categoriasEndpoint = 'http://localhost/api/categorias/';

	constructor() {
		super();
	}

	async getCategorias() {
		const response = this.simpleFetch(Categoria.categoriasEndpoint);
		return response;
	}

	static categoriasTemplate({
		fetchedCategorias,
	}) {

		const categorias = fetchedCategorias.map(categoria =>
			`<option
				value='${categoria['id_categoria']}'>
				${categoria['categoria']}
			</option>`
		).join('');

		return categorias;
	}

	async printCategorias(place) {
		const categorias = await this.getCategorias();

		const content = Categoria.categoriasTemplate({
			fetchedCategorias: categorias.content,
		});

		place.outerHTML = content;
	}
}
