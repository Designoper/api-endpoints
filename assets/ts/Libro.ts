import { Categoria } from "./Categoria.js";

export class Libro extends Categoria {
    static librosEndpoint: string = 'http://localhost/api-endpoints/api/libros/';
    static librosFilterEndpoint: string = 'http://localhost/api-endpoints/api/libros/filter/';
    static fetchOutput: HTMLElement | null = document.getElementById('fetchoutput');
    static errorContainer: HTMLElement | null = document.getElementById('errorcontainer');

    constructor() {
        super();
    }

    async getLibros(): Promise<any> {
        const response = await this.fetchData({
            url: Libro.librosEndpoint,
            method: 'GET',
        });
        await this.printLibros(response);
    }

    async filterLibros(form: HTMLFormElement): Promise<any> {
        const response = await this.fetchData({
            url: Libro.librosFilterEndpoint,
            method: 'GET',
            form: form
        });
        await this.printLibros(response);
    }

    async createLibro(form: HTMLFormElement, errorContainer: HTMLOutputElement, dialog: HTMLDialogElement): Promise<any> {
        const response = await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/create/',
            method: 'POST',
            form: form
        });

        if (response.ok) {
            this.resetForm(form, errorContainer, dialog);
            await this.getLibros();
        } else {
            this.errorChecker(response, errorContainer);
        }
    }

    async updateLibro(form: HTMLFormElement, errorContainer: HTMLOutputElement, dialog: HTMLDialogElement): Promise<any> {
        const response = await this.fetchData({
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
            await this.getLibros();
        } else {
            this.errorChecker(response, errorContainer);
        }
    }

    async deleteLibro(form: HTMLFormElement, errorContainer: HTMLOutputElement, dialog: HTMLDialogElement): Promise<void> {
        await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/delete/',
            method: 'POST',
            form: form
        });
        await this.getLibros();
    }

    async deleteAllLibro(form: HTMLFormElement, errorContainer: HTMLOutputElement, dialog: HTMLDialogElement): Promise<any> {
        const response = await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/delete-all/',
            method: 'POST',
            form: form
        });

        if (response.ok) {
            this.resetForm(form, errorContainer, dialog);
            await this.getLibros();
        } else {
            this.errorChecker(response, errorContainer);
        }
    }

    resetForm(form: HTMLFormElement, errorContainer: HTMLOutputElement, dialog: HTMLDialogElement): void {
        form.reset();
        dialog.close();
        errorContainer.innerHTML = "";
    }

    static librosTemplate(fetchedLibros: any[], fetchedCategorias: any[]): string {
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
                                    ${fetchedCategorias.map(categoria =>
            `<option value="${categoria['id_categoria']}" ${categoria['categoria'] === libro['categoria'] ? 'selected' : ''}>
                                            ${categoria['categoria']}
                                        </option>`).join('')
            }
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

    async printLibros(libros: any): Promise<void> {
        if (!libros.content || libros.content.length === 0) {
            if (Libro.fetchOutput) Libro.fetchOutput.innerHTML = "";
            if (Libro.errorContainer) Libro.errorContainer.innerHTML = libros.message;
        } else {
            const categorias = await this.getCategorias();
            const content = Libro.librosTemplate(libros.content, categorias.content);
            if (Libro.fetchOutput) Libro.fetchOutput.innerHTML = content;
            if (Libro.errorContainer) Libro.errorContainer.innerHTML = "";
        }
        this.final();
    }

    optionsDropdown(): void {
        const emptyOptions = document.querySelectorAll('option:not([value])');
        emptyOptions.forEach(option => {
            this.printCategorias(option as HTMLElement);
        });
    }

    filterButton(): void {
        const button = document.querySelector('[value="GET"]') as HTMLButtonElement;
        const form = button.closest('form') as HTMLFormElement;
        form.onsubmit = () => {
            this.filterLibros(form);
        };
    }

    postButton(): void {
        const button = document.querySelector('[value="POST"]') as HTMLButtonElement;
        const form = button.closest('form') as HTMLFormElement;
        const dialog = button.closest('dialog') as HTMLDialogElement;
        const output = form.querySelector('output') as HTMLOutputElement;
        button.onclick = () => {
            if (form.reportValidity()) {
                this.createLibro(form, output, dialog);
            }
        };
    }

    putButton(): void {
        const putButtons = document.querySelectorAll('[value="PUT"]') as NodeListOf<HTMLButtonElement>;
        putButtons.forEach(button => {
            button.onclick = () => {
                const form = button.closest('form') as HTMLFormElement;
                const dialog = button.closest('dialog') as HTMLDialogElement;
                const output = form.querySelector('output') as HTMLOutputElement;
                if (form.reportValidity()) {
                    this.updateLibro(form, output, dialog);
                }
            };
        });
    }

    deleteButton(): void {
        const deleteButtons = document.querySelectorAll('[value="DELETE"]') as NodeListOf<HTMLButtonElement>;
        deleteButtons.forEach(button => {
            button.onclick = () => {
                const form = button.closest('form') as HTMLFormElement;
                const dialog = button.closest('dialog') as HTMLDialogElement;
                const output = form.querySelector('output') as HTMLOutputElement;
                if (form.reportValidity()) {
                    this.deleteLibro(form, output, dialog);
                }
            };
        });
    }

    deleteAllButton(): void {
        const button = document.querySelector('[value="DELETE_ALL"]') as HTMLButtonElement;
        button.onclick = () => {
            const form = button.closest('form') as HTMLFormElement;
            const dialog = button.closest('dialog') as HTMLDialogElement;
            const output = form.querySelector('output') as HTMLOutputElement;
            if (form.reportValidity()) {
                this.deleteAllLibro(form, output, dialog);
            }
        };
    }

    final(): void {
        this.optionsDropdown();
        this.filterButton();
        this.postButton();
        this.putButton();
        this.deleteButton();
        this.deleteAllButton();
    }
}

new Libro().getLibros();