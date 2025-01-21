const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);

function load() {
	const main = document.querySelector('main');
	main.innerHTML = `
	<form>
		<input type='number' value='${urlParams.get('id_libro')}' name='idLibro' hidden>

		<h3>Modificando ${urlParams.get('titulo')}</h3>

		<menu>
			<li>
				<label for='titulo'>Título *</label>
				<textarea id='titulo' name='titulo' required>${urlParams.get('titulo')}</textarea>
			</li>

			<li>
				<label for='descripcion'>Descripción *</label>
				<textarea id='descripcion' name='descripcion' required>${urlParams.get('descripcion')}</textarea>
			</li>

			<li>
				<label for='paginas'>Páginas *</label>
				<input type='number' id='paginas' name='paginas' value='${urlParams.get('paginas')}' required min='1'>
			</li>

			<li>
				<label for="image">Portada</label>
				<input type="file" id="image" name="image" accept="image/*">
			</li>

			<li>
				<label for='fechaPublicacion'>Fecha de publicación *</label>
				<input type='date' id='fechaPublicacion' name='fechaPublicacion' value='${urlParams.get('fecha_publicacion')}' required>
			</li>

			<li>
				<label for='categoria'>Categoria *</label>
				<select name='idCategoria' id='categoria' required>
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
					<button type='button' value='PUT'>Guardar cambios</button>
				</li>
				<li>
					<button type='button'>Cancelar</button>
				</li>
			</menu>

		<fieldset>

	</form>`
}
load();