import { Base } from "./Base.js";

export class Categoria extends Base {
	static categoriasEndpoint = 'http://localhost/api-endpoints/api/categorias/';

	constructor() {
		super();
	}

	async getCategorias() {

		const response = await this.fetchData({
			url: Categoria.categoriasEndpoint,
			method: 'GET'
		});

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

	async printCategorias({
		place,
	}) {
		const categorias = await this.getCategorias();

		const content = Categoria.categoriasTemplate({
			fetchedCategorias: categorias.content,
		});

		place.outerHTML = content;
	}
}
