import { Base } from "./Base";

export class Categoria extends Base {
    static categoriasEndpoint: string = 'http://localhost/api-endpoints/api/categorias/';

    constructor() {
        super();
    }

    async getCategorias(): Promise<any> {
        const response = await this.fetchData({
            url: Categoria.categoriasEndpoint,
            method: 'GET'
        });
        return response;
    }

    static categoriasTemplate({ fetchedCategorias }: { fetchedCategorias: any[] }): string {
        return fetchedCategorias.map(categoria =>
            `<option value="${categoria['id_categoria']}">
                ${categoria['categoria']}
            </option>`
        ).join('');
    }

    async printCategorias(place: HTMLElement): Promise<void> {
        const categoriasResponse = await this.getCategorias();
        const content = Categoria.categoriasTemplate({ fetchedCategorias: categoriasResponse.content });
        place.outerHTML = content;
    }
}