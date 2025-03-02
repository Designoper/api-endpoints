import { Categoria } from "./Categoria.js";

export class Libro extends Categoria {
    static librosEndpoint = 'http://localhost/api-endpoints/api/libros/';
    static librosFilterEndpoint = 'http://localhost/api-endpoints/api/libros/filter/';
    static fetchOutput = document.getElementById('fetchoutput');
    static errorContainer = document.getElementById('errorcontainer');

    constructor() {
        super();
    }

    // MARK: CRUD FUNCTIONS





    // MARK: GET LIBROS

    async getLibros() {

        const response = await this.fetchData({
            url: Libro.librosEndpoint,
            method: 'GET'
        });

        await this.printLibros(response);
    }

    // MARK: FILTER LIBROS

    async filterLibros(form) {

        const response = await this.fetchData({
            url: Libro.librosFilterEndpoint,
            method: 'GET',
            form: form
        });

        await this.printLibros(response);
    }

    // MARK: CREATE LIBRO

    async createLibro(form, errorContainer, dialog) {
        const response = await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/create/',
            method: 'POST',
            form: form
        });

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

        const response = await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/update/',
            method: 'POST',
            form: form
        });

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

        const response = await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/delete/',
            method: 'POST',
            form: form
        });

        await this.getLibros();
    }

    async deleteAllLibro(form, errorContainer, dialog) {

        const response = await this.fetchData({
            url: 'http://localhost/api-endpoints/api/libros/delete-all/',
            method: 'POST',
            form: form
        });

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

        const template = document.getElementById('libro-template');
        const output = document.getElementById('fetchoutput');
        output.innerHTML = ''; // Clear previous output

        fetchedLibros.forEach(libro => {
            const clone = template.content.cloneNode(true);
            clone.querySelector('slot[name="libro-title"]').textContent = libro.titulo;
            clone.querySelector('img').src = libro.portada;
            clone.querySelector('img').alt = 'Portada de ' + libro.titulo;
            clone.querySelector('slot[name="libro-description"]').textContent = libro.descripcion;
            clone.querySelector('slot[name="libro-pages"]').textContent = 'Páginas: ' + libro.paginas;
            clone.querySelector('slot[name="libro-date"]').textContent = 'Fecha de publicación: ' + libro.fecha_publicacion_dd_mm_yyyy;
            clone.querySelector('slot[name="libro-category"]').textContent = 'Categoría: ' + libro.categoria;
            // For libro-id, you can insert a hidden element or modify the template; here we simply set text.
            const idSlot = clone.querySelector('slot[name="libro-id"]');
            if (idSlot) {
                idSlot.textContent = libro.id_libro;
            }
            output.appendChild(clone);
        });


        return libros;
    }

    async printLibros(libros) {

        if (libros.content.length === 0) {
            Libro.fetchOutput.innerHTML = "";
            Libro.errorContainer.innerHTML = libros.message;
        }

        else {
            // const categorias = await this.getCategorias();
            const template = document.getElementById('libro-template');
            const output = document.getElementById('fetchoutput');
            output.innerHTML = ''; // Clear previous output

            libros.content.forEach(libro => {
                const clone = template.content.cloneNode(true);
                clone.querySelector('slot[name="libro-title"]').textContent = libro.titulo;
                clone.querySelector('img').src = libro.portada;
                clone.querySelector('img').alt = 'Portada de ' + libro.titulo;
                clone.querySelector('slot[name="libro-description"]').textContent = libro.descripcion;
                clone.querySelector('slot[name="libro-pages"]').textContent = 'Páginas: ' + libro.paginas;
                clone.querySelector('slot[name="libro-date"]').textContent = 'Fecha de publicación: ' + libro.fecha_publicacion_dd_mm_yyyy;
                clone.querySelector('slot[name="libro-category"]').textContent = 'Categoría: ' + libro.categoria;
                // For libro-id, you can insert a hidden element or modify the template; here we simply set text.
                const idSlot = clone.querySelector('slot[name="libro-id"]');
                if (idSlot) {
                    idSlot.textContent = libro.id_libro;
                }
                output.appendChild(clone);
            });
        }

        this.final();
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
        }
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
        }
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
            }
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
            }
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
        }
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

new Libro().getLibros();
