import { Categoria } from "./Categoria.js";

class Libro extends Categoria {
    static librosEndpoint = 'http://localhost/api/libros/';
    static fetchOutput = document.getElementById('fetchoutput');
    static errorContainer = document.getElementById('errorcontainer');

    constructor() {
        super();
        this.getLibros();
    }

    // MARK: CRUD FUNCTIONS





    // MARK: GET LIBROS

    async getLibros() {
        const response = await this.simpleFetch(Libro.librosEndpoint);
        await this.printLibros(response);
    }

    // MARK: FILTER LIBROS

    async filterLibros(form, dialog) {

        const response = await this.fetchData(form);
        await this.printLibros(response);

        this.resetForm({
            dialog: dialog
        });
    }

    // MARK: CREATE LIBRO

    async createLibro(form, errorContainer, dialog) {
        const response = await this.fetchData(form);

        if (response.ok) {
            this.resetForm({
                form: form,
                errorContainer: errorContainer,
                dialog: dialog
            });

            await this.getLibros();
        }

        if (!response.ok) {
            this.errorChecker(response, errorContainer);
        }
    }

    // MARK: UPDATE LIBRO

    async updateLibro(form, errorContainer, dialog) {

        const response = await this.fetchData(form);

        if (response.status === 204) {
            this.resetForm({
                form: form,
                errorContainer: errorContainer,
                dialog: dialog
            });

            return;
        }

        if (response.ok) {
            this.resetForm({
                form: form,
                errorContainer: errorContainer,
                dialog: dialog
            });

            await this.getLibros();
        }

        if (!response.ok) {
            this.errorChecker(response, errorContainer);
        }
    }

    // MARK: DELETE LIBRO

    async deleteLibro(form) {
        await this.fetchData(form);
        await this.getLibros();
    }

    // MARK: DELETE ALL LIBRO

    async deleteAllLibro(form, errorContainer, dialog) {

        const response = await this.fetchData(form);

        if (response.ok) {
            this.resetForm({
                form: form,
                errorContainer: errorContainer,
                dialog: dialog
            });

            await this.getLibros();
        }

        if (!response.ok) {
            this.errorChecker(response, errorContainer);
        }
    }

    resetForm({
        form,
        errorContainer,
        dialog
    }) {

        form ? form.reset() : null;
        dialog ? dialog.close() : null;
        errorContainer ? errorContainer.innerHTML = "" : null;
    }

    static librosTemplate(fetchedLibros, fetchedCategorias) {

        const libros = fetchedLibros.map(libro =>
            `<article>

                <h3>${libro['titulo']}</h3>
                <img src="${libro['portada']}" alt="Portada de ${libro['titulo']}" loading="lazy">
                <p>${libro['descripcion']}</p>
                <p>Páginas: ${libro['paginas']}</p>
                <p>Fecha de publicación: ${libro['fecha_publicacion_dd_mm_yyyy']}</p>
                <p>Categoria: ${libro['categoria']}</p>

                <menu>
                    <li>
                        <button type='button' commandfor="modificar-dialog-${libro['id_libro']}" command="show-modal">Modificar</button>
                    </li>
                    <li>
                        <button type='button' commandfor="eliminar-dialog-${libro['id_libro']}" command="show-modal">Eliminar</button>
                    </li>
                </menu>

                <dialog id="modificar-dialog-${libro['id_libro']}">

                    <form action="http://localhost/api/libros/update/" method="POST">
                        <input type='number' value='${libro['id_libro']}' name='id_libro' hidden>

                        <h3>Modificando ${libro['titulo']}</h3>

                        <menu>
                            <li>
                                <label for='titulo'>Título *</label>
                                <textarea id='titulo' name='titulo' required>${libro['titulo']}</textarea>
                            </li>

                            <li>
                                <label for='descripcion'>Descripción *</label>
                                <textarea id='descripcion' name='descripcion' required>${libro['descripcion']}</textarea>
                            </li>

                            <li>
                                <label for='paginas'>Páginas *</label>
                                <input type='number' id='paginas' name='paginas' value='${libro['paginas']}' required min='1'>
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
                                <input type='date' id='fecha_publicacion' name='fecha_publicacion' value='${libro['fecha_publicacion']}' required>
                            </li>

                            <li>
                                <label for='categoria'>Categoria *</label>
                                <select name='id_categoria' id='categoria' required>
                                    <option value=''>Seleccione una categoria...</option>
                                    ${fetchedCategorias.map(categoria =>
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
                                    <button value='PUT'>Guardar cambios</button>
                                </li>
                                <li>
                                    <button type='button' commandfor="modificar-dialog-${libro['id_libro']}" command="close">Cancelar</button>
                                </li>
                            </menu>

                        </fieldset>

                        <output></output>

                    </form>
                </dialog>

                <dialog id="eliminar-dialog-${libro['id_libro']}">

                <form action="http://localhost/api/libros/delete/" method="POST">

                    <p>¿Seguro que quiere eliminar ${libro['titulo']}?</p>

                    <input type='number' value='${libro['id_libro']}' name='id_libro' hidden>

                    <fieldset>

                        <menu>
                            <li>
                                <button value='DELETE'>Sí, eliminar</button>
                            </li>
                            <li>
                                <button type='button' commandfor="eliminar-dialog-${libro['id_libro']}" command="close">Cancelar</button>
                            </li>
                        </menu>

                    </fieldset>
                </form>

                </dialog>

            </article>`
        ).join('');

        return libros;
    }

    async printLibros(libros) {

        if (libros.content.length === 0) {
            Libro.fetchOutput.innerHTML = "";
            Libro.errorContainer.innerHTML = libros.message;
        }

        else {
            const categorias = await this.getCategorias();
            const content = Libro.librosTemplate(libros.content, categorias.content);
            Libro.fetchOutput.innerHTML = content;
            Libro.errorContainer.innerHTML = "";
        }

        this.final();
    }

    optionsDropdown() {
        const emptyOptions = document.querySelectorAll('option:not([value])');

        emptyOptions.forEach(option => {
            this.printCategorias(option);
        });
    }

    formHandler() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {

            const submitButton = form.querySelector('button');
            const httpMethod = submitButton.getAttribute('value');
            const output = form.querySelector('output');
            const dialog = form.closest('dialog');

            form.onsubmit = (e) => {
                e.preventDefault();
                switch (httpMethod) {
                    case 'GET':
                        this.filterLibros(form, dialog);
                        break;
                    case 'POST':
                        this.createLibro(form, output, dialog);
                        break;
                    case 'PUT':
                        this.updateLibro(form, output, dialog);
                        break;
                    case 'DELETE':
                        this.deleteLibro(form);
                        break;
                    case 'DELETE_ALL':
                        this.deleteAllLibro(form, output, dialog);
                }
            }
        });
    }

    final() {
        this.optionsDropdown();
        this.formHandler();
    }
}

new Libro();
