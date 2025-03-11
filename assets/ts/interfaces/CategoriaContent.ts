import ApiResponse from "./ApiResponse";

export default interface CategoriaContent extends ApiResponse {
	id_categoria: number,
	categoria: string
}