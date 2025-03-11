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
class Libro extends Categoria {
    constructor() {
        super();
    }
    initialize() {
        return __awaiter(this, void 0, void 0, function* () {
            yield this.getLibros();
            this.optionsDropdown();
        });
    }
    getLibros() {
        return __awaiter(this, void 0, void 0, function* () {
            yield this.getCategorias();
            const response = yield this.simpleFetch(Libro.ENDPOINT);
            this.printLibros(response);
        });
    }
    filterLibros(form) {
        return __awaiter(this, void 0, void 0, function* () {
            yield this.getCategorias();
            const response = yield this.fetchData(form);
            this.printLibros(response);
        });
    }
    createLibro(form) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData(form);
            if (response.status === 201) {
                yield this.getLibros();
            }
        });
    }
    updateLibro(form) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData(form);
            if (response.status === 200) {
                yield this.getLibros();
            }
        });
    }
    deleteLibro(form) {
        return __awaiter(this, void 0, void 0, function* () {
            const response = yield this.fetchData(form);
            if (response.status === 204) {
                yield this.getLibros();
            }
        });
    }
    static librosTemplate(fetchedLibros) {
        const dateFormatter = new Intl.DateTimeFormat('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            timeZone: 'UTC'
        });
        const libros = fetchedLibros.map(libro => {
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
                                    ${Categoria.categorias.content.map(categoria => `<option
                                            value='${categoria['id_categoria']}'
                                            ${categoria['categoria'] === libro['categoria'] ? 'selected' : ''}>
                                            ${categoria['categoria']}
                                        </option>`).join('')}
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

            </article>`;
        }).join('');
        return libros;
    }
    printLibros(libros) {
        var _a;
        if (((_a = libros.content) === null || _a === void 0 ? void 0 : _a.length) === 0) {
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
    optionsDropdown() {
        const emptyOptions = document.querySelectorAll('option:not([value])');
        emptyOptions.forEach(option => {
            this.printCategorias(option);
        });
    }
    formHandler() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const submitButton = form.querySelector('button[type="submit"]');
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
            };
        });
    }
}
Libro.ENDPOINT = new URL('http://localhost/api/libros');
Libro.DOM_ELEMENTS = {
    OUTPUT: document.getElementById('fetchoutput'),
    ERROR_CONTAINER: document.getElementById('errorcontainer')
};
(() => __awaiter(void 0, void 0, void 0, function* () {
    yield new Libro().initialize();
}))();
