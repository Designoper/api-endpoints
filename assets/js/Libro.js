var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
import { Categoria } from "./Categoria";
export class Libro extends Categoria {
    constructor() {
        super();
    }
    getLibros() {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData({
                url: Libro.librosEndpoint,
                method: 'GET',
            });
            yield this.printLibros(response);
        });
    }
    filterLibros(form) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData({
                url: Libro.librosFilterEndpoint,
                method: 'GET',
                form: form
            });
            yield this.printLibros(response);
        });
    }
    createLibro(form, errorContainer, dialog) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData({
                url: 'http://localhost/api-endpoints/api/libros/create/',
                method: 'POST',
                form: form
            });
            if (response.ok) {
                this.resetForm(form, errorContainer, dialog);
                yield this.getLibros();
            }
            else {
                this.errorChecker(response, errorContainer);
            }
        });
    }
    updateLibro(form, errorContainer, dialog) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData({
                url: 'http://localhost/api-endpoints/api/libros/update/',
                method: 'POST',
                form: form
            });
            if (response.status === 204) {
                this.resetForm(form, errorContainer, dialog);
                return;
            }
            if (response.ok) {
                this.resetForm(form, errorContainer, dialog);
                yield this.getLibros();
            }
            else {
                this.errorChecker(response, errorContainer);
            }
        });
    }
    deleteLibro(form, errorContainer, dialog) {
        return __awaiter(this, void 0, void 0, function* () {
            yield this.fetchData({
                url: 'http://localhost/api-endpoints/api/libros/delete/',
                method: 'POST',
                form: form
            });
            yield this.getLibros();
        });
    }
    deleteAllLibro(form, errorContainer, dialog) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData({
                url: 'http://localhost/api-endpoints/api/libros/delete-all/',
                method: 'POST',
                form: form
            });
            if (response.ok) {
                this.resetForm(form, errorContainer, dialog);
                yield this.getLibros();
            }
            else {
                this.errorChecker(response, errorContainer);
            }
        });
    }
    resetForm(form, errorContainer, dialog) {
        form.reset();
        dialog.close();
        errorContainer.innerHTML = "";
    }
    static librosTemplate(fetchedLibros, fetchedCategorias) {
        return fetchedLibros.map(libro => `
            <article>
                <h3>${libro['titulo']}</h3>
                <img src="${libro['portada']}" alt="Portada de ${libro['titulo']}" loading="lazy">
                <p>${libro['descripcion']}</p>
                <p>Páginas: ${libro['paginas']}</p>
                <p>Fecha de publicación: ${libro['fecha_publicacion_dd_mm_yyyy']}</p>
                <p>Categoria: ${libro['categoria']}</p>

                <menu>
                    <li>
                        <button type="button" commandfor="modificar-dialog-${libro['id_libro']}" command="show-modal">
                            Modificar
                        </button>
                    </li>
                    <li>
                        <button type="button" commandfor="eliminar-dialog-${libro['id_libro']}" command="show-modal">
                            Eliminar
                        </button>
                    </li>
                </menu>

                <dialog id="modificar-dialog-${libro['id_libro']}">
                    <form>
                        <input type="number" value="${libro['id_libro']}" name="id_libro" hidden>
                        <h3>Modificando ${libro['titulo']}</h3>
                        <menu>
                            <li>
                                <label for="titulo">Título *</label>
                                <textarea id="titulo" name="titulo" required>${libro['titulo']}</textarea>
                            </li>
                            <li>
                                <label for="descripcion">Descripción *</label>
                                <textarea id="descripcion" name="descripcion" required>${libro['descripcion']}</textarea>
                            </li>
                            <li>
                                <label for="paginas">Páginas *</label>
                                <input type="number" id="paginas" name="paginas" value="${libro['paginas']}" required min="1">
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
                                <label for="fecha_publicacion">Fecha de publicación *</label>
                                <input type="date" id="fecha_publicacion" name="fecha_publicacion" value="${libro['fecha_publicacion']}" required>
                            </li>
                            <li>
                                <label for="categoria">Categoria *</label>
                                <select name="id_categoria" id="categoria" required>
                                    <option value="">Seleccione una categoria...</option>
                                    ${fetchedCategorias.map(categoria => `<option value="${categoria['id_categoria']}" ${categoria['categoria'] === libro['categoria'] ? 'selected' : ''}>
                                            ${categoria['categoria']}
                                        </option>`).join('')}
                                </select>
                            </li>
                        </menu>
                        <fieldset>
                            <menu>
                                <li>
                                    <button type="button" value="PUT">Guardar cambios</button>
                                </li>
                                <li>
                                    <button type="button" commandfor="modificar-dialog-${libro['id_libro']}" command="close">
                                        Cancelar
                                    </button>
                                </li>
                            </menu>
                        </fieldset>
                        <output></output>
                    </form>
                </dialog>

                <dialog id="eliminar-dialog-${libro['id_libro']}">
                    <form>
                        <p>¿Seguro que quiere eliminar ${libro['titulo']}?</p>
                        <input type="number" value="${libro['id_libro']}" name="id_libro" hidden>
                        <fieldset>
                            <menu>
                                <li>
                                    <button type="button" value="DELETE">Sí, eliminar</button>
                                </li>
                                <li>
                                    <button type="button" commandfor="eliminar-dialog-${libro['id_libro']}" command="close">
                                        Cancelar
                                    </button>
                                </li>
                            </menu>
                        </fieldset>
                    </form>
                </dialog>
            </article>
        `).join('');
    }
    printLibros(libros) {
        return __awaiter(this, void 0, void 0, function* () {
            if (!libros.content || libros.content.length === 0) {
                if (Libro.fetchOutput)
                    Libro.fetchOutput.innerHTML = "";
                if (Libro.errorContainer)
                    Libro.errorContainer.innerHTML = libros.message;
            }
            else {
                const categorias = yield this.getCategorias();
                const content = Libro.librosTemplate(libros.content, categorias.content);
                if (Libro.fetchOutput)
                    Libro.fetchOutput.innerHTML = content;
                if (Libro.errorContainer)
                    Libro.errorContainer.innerHTML = "";
            }
            this.final();
        });
    }
    optionsDropdown() {
        const emptyOptions = document.querySelectorAll('option:not([value])');
        emptyOptions.forEach(option => {
            this.printCategorias(option);
        });
    }
    filterButton() {
        const button = document.querySelector('[value="GET"]');
        const form = button.closest('form');
        form.onsubmit = () => {
            this.filterLibros(form);
        };
    }
    postButton() {
        const button = document.querySelector('[value="POST"]');
        const form = button.closest('form');
        const dialog = button.closest('dialog');
        const output = form.querySelector('output');
        button.onclick = () => {
            if (form.reportValidity()) {
                this.createLibro(form, output, dialog);
            }
        };
    }
    putButton() {
        const putButtons = document.querySelectorAll('[value="PUT"]');
        putButtons.forEach(button => {
            button.onclick = () => {
                const form = button.closest('form');
                const dialog = button.closest('dialog');
                const output = form.querySelector('output');
                if (form.reportValidity()) {
                    this.updateLibro(form, output, dialog);
                }
            };
        });
    }
    deleteButton() {
        const deleteButtons = document.querySelectorAll('[value="DELETE"]');
        deleteButtons.forEach(button => {
            button.onclick = () => {
                const form = button.closest('form');
                const dialog = button.closest('dialog');
                const output = form.querySelector('output');
                if (form.reportValidity()) {
                    this.deleteLibro(form, output, dialog);
                }
            };
        });
    }
    deleteAllButton() {
        const button = document.querySelector('[value="DELETE_ALL"]');
        button.onclick = () => {
            const form = button.closest('form');
            const dialog = button.closest('dialog');
            const output = form.querySelector('output');
            if (form.reportValidity()) {
                this.deleteAllLibro(form, output, dialog);
            }
        };
    }
    final() {
        this.optionsDropdown();
        this.filterButton();
        this.postButton();
        this.putButton();
        this.deleteButton();
        this.deleteAllButton();
    }
}
Libro.librosEndpoint = 'http://localhost/api-endpoints/api/libros/';
Libro.librosFilterEndpoint = 'http://localhost/api-endpoints/api/libros/filter/';
Libro.fetchOutput = document.getElementById('fetchoutput');
Libro.errorContainer = document.getElementById('errorcontainer');
new Libro().getLibros();
