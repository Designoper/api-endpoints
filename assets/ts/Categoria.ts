import { Fetch } from "./Fetch.ts";

interface CategoriaResponse {
	status: number;
	message: string;
	content?: CategoriaContent
	validationErrors?: string[],
	integrityErrors?: string[]
}

interface CategoriaContent {
	id_categoria: number,
	categoria: string
}

export class Categoria extends Fetch {
	static categoriasEndpoint = new URL('http://localhost/api/categorias');
	static categorias: CategoriaContent;

	constructor() {
		super();
	}

	async getCategorias() {
		const response: CategoriaResponse = await this.simpleFetch(Categoria.categoriasEndpoint);
		Categoria.categorias = response.content as CategoriaContent;
	}

	categoriasTemplate(fetchedCategorias: CategoriaContent): string {

		const categorias = fetchedCategorias.map(categoria =>
			`<option
				value='${categoria['id_categoria']}'>
				${categoria['categoria']}
			</option>`
		).join('');

		return categorias;
	}

	printCategorias(place: HTMLOptionElement) {
		const content = this.categoriasTemplate(Categoria.categorias);
		place.outerHTML = content;
	}
}
