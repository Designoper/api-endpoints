import { Base } from "./Base.js";

export class Categoria extends Base {
	static categoriasEndpoint = 'http://localhost/api-libros/controllers/CategoriaController.php';
	static categoriasCache;

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

	async setCategoriasCache() {
		const response = await this.getCategorias();
		Categoria.categoriasCache = response;
	}

	getCategoriasCache() {
		return Categoria.categoriasCache;
	}

	static categoriasTemplate({
		fetchedCategorias,
		libroCategoria = null
	}) {

		const categorias = fetchedCategorias.map(categoria =>
			`<option
				value='${categoria['id_categoria']}'
				${categoria['categoria'] === libroCategoria ? 'selected' : ''}>
				${categoria['categoria']}
			</option>`
		).join('');

		return categorias;
	}

	printCategorias({
		place,
		libroCategoria = null
	}) {
		const categorias = this.getCategoriasCache();

		const content = Categoria.categoriasTemplate({
			fetchedCategorias: categorias.content,
			libroCategoria: libroCategoria
		});

		place.outerHTML = content;
	}
}
