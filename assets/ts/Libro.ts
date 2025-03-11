import { Categoria } from "./Categoria";
import LibroContent from "./interfaces/LibroContent";
import LibroResponse from "./interfaces/LibroResponse";

class Libro extends Categoria {
    static ENDPOINT = new URL('http://localhost/api/libros');

    static DOM_ELEMENTS = {
        OUTPUT: document.getElementById('fetchoutput') as HTMLOutputElement,
        ERROR_CONTAINER: document.getElementById('errorcontainer') as HTMLOutputElement
    };

    constructor() {
        super();
        this.initialize();
    }

    async initialize() {
        await this.getLibros();
        this.optionsDropdown();
    }

    // MARK: CRUD FUNCTIONS

    async getLibros() {
        await this.getCategorias();
        const response: LibroResponse = await this.simpleFetch(Libro.ENDPOINT);
        this.printLibros(response);
    }

    async filterLibros(form: HTMLFormElement) {
        await this.getCategorias();
        const response: LibroResponse = await this.fetchData(form);
        this.printLibros(response);
    }

    async createLibro(form: HTMLFormElement) {
        const response = await this.fetchData(form);
        if (response.status === 201) {
            await this.getLibros();
        }
    }

    async updateLibro(form: HTMLFormElement) {
        const response: LibroResponse = await this.fetchData(form);
        if (response.status === 200) {
            await this.getLibros();
        }
    }

    async deleteLibro(form: HTMLFormElement) {
        const response: LibroResponse = await this.fetchData(form);
        if (response.status === 204) {
            await this.getLibros();
        }
    }

    // MARK: LIBRO TEMPLATE

    static librosTemplate(fetchedLibros: LibroContent[]) {
        const dateFormatter = new Intl.DateTimeFormat('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            timeZone: 'UTC'
        });

        const libros = fetchedLibros.map(libro => {
            // Create date in UTC to avoid timezone offset
            const fecha = new Date(libro.fecha_publicacion + 'T00:00:00Z');
            const fechaFormateada = dateFormatter.format(fecha);

            return `<article>

                <h3>${libro.titulo}</h3>
                <img src="${libro.portada}" alt="Portada de ${libro.titulo}" loading="lazy">
                <p>${libro.descripcion}</p>
                <p>Páginas: ${libro.paginas}</p>
                <p>Fecha de publicación: ${fechaFormateada}</p>
                <p>Categoria: ${libro.categoria}</p>

                <menu>
                    <li>
                        <button type='button' commandfor="modificar-dialog-${libro.id_libro}" command="show-modal">Modificar</button>
                    </li>
                    <li>
                        <button type='button' commandfor="eliminar-dialog-${libro.id_libro}" command="show-modal">Eliminar</button>
                    </li>
                </menu>

                <dialog id="modificar-dialog-${libro.id_libro}">

                    <form action="${Libro.ENDPOINT}/${libro.id_libro}">

                        <h3>Modificando ${libro.titulo}</h3>

                        <menu>
                            <li>
                                <label for='titulo'>Título *</label>
                                <textarea id='titulo' name='titulo' required>${libro.titulo}</textarea>
                            </li>

                            <li>
                                <label for='descripcion'>Descripción *</label>
                                <textarea id='descripcion' name='descripcion' required>${libro.descripcion}</textarea>
                            </li>

                            <li>
                                <label for='paginas'>Páginas *</label>
                                <input type='number' id='paginas' name='paginas' value='${libro.paginas}' required min='1'>
                            </li>

                            <li>
								<label for="portada">Portada</label>
								<input type="file" id="portada" name="portada" accept="image/*">
							</li>

                            <li>
                                <input type="checkbox" id="eliminar_portada" name="eliminar_portada" value="">
								<label for="eliminar_portada">Eliminar portada actual</label>
							</li>

                            <li>
                                <label for='fecha_publicacion'>Fecha de publicación *</label>
                                <input type='date' id='fecha_publicacion' name='fecha_publicacion' value='${libro.fecha_publicacion}' required>
                            </li>

                            <li>
                                <label for='categoria'>Categoria *</label>
                                <select name='id_categoria' id='categoria' required>
                                    ${Categoria.categorias.content.map(categoria =>
                `<option
                                            value='${categoria['id_categoria']}'
                                            ${categoria['categoria'] === libro['categoria'] ? 'selected' : ''}>
                                            ${categoria['categoria']}
                                        </option>`
            ).join('')}
                                </select>
                            </li>
                        </menu>

                        <fieldset>

                            <menu>
                                <li>
                                    <button type="submit" value='PUT'>Guardar cambios</button>
                                </li>
                                <li>
                                    <button type='button' commandfor="modificar-dialog-${libro.id_libro}" command="close">Cancelar</button>
                                </li>
                            </menu>

                        </fieldset>

                        <output></output>

                    </form>
                </dialog>

                <dialog id="eliminar-dialog-${libro['id_libro']}">

                <form action="${Libro.ENDPOINT}/${libro['id_libro']}">

                    <p>¿Seguro que quiere eliminar ${libro['titulo']}?</p>

                    <fieldset>

                        <menu>
                            <li>
                                <button type="submit" value='DELETE'>Sí, eliminar</button>
                            </li>
                            <li>
                                <button type='button' commandfor="eliminar-dialog-${libro['id_libro']}" command="close">Cancelar</button>
                            </li>
                        </menu>

                    </fieldset>
                </form>

                </dialog>

            </article>`
        }).join('');

        return libros;
    }

    // MARK: PRINT LIBROS

    printLibros(libros: LibroResponse) {

        if (libros.content?.length === 0) {
            Libro.DOM_ELEMENTS.OUTPUT.innerHTML = "";
            Libro.DOM_ELEMENTS.ERROR_CONTAINER.innerHTML = libros.message;
        }

        else {
            const content = Libro.librosTemplate(libros.content);
            Libro.DOM_ELEMENTS.OUTPUT.innerHTML = content;
            Libro.DOM_ELEMENTS.ERROR_CONTAINER.innerHTML = "";
        }

        this.formHandler();
    }

    optionsDropdown(): void {
        const emptyOptions: NodeListOf<HTMLOptionElement> = document.querySelectorAll('option:not([value])');

        emptyOptions.forEach(option => {
            this.printCategorias(option);
        });
    }

    formHandler() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {

            const submitButton = form.querySelector('button[type="submit"]') as HTMLButtonElement;
            const httpMethod = submitButton.value;

            form.onsubmit = (e) => {
                e.preventDefault();
                switch (httpMethod) {
                    case 'GET':
                        this.filterLibros(form);
                        break;
                    case 'POST':
                        this.createLibro(form);
                        break;
                    case 'PUT':
                        this.updateLibro(form);
                        break;
                    case 'DELETE':
                        this.deleteLibro(form);
                }
            }
        });
    }
}

(async () => {
    await new Libro().initialize();
})();
