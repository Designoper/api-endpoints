export default interface LibroContent {
	id_libro: number,
	titulo: string,
	portada: URL,
	descripcion: string,
	paginas: number,
	fecha_publicacion: Date,
	categoria: string
}