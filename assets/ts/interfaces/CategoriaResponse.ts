import ApiResponse from "./ApiResponse";
import CategoriaContent from "./CategoriaContent";

export default interface categoriaResponse extends ApiResponse {
	content: CategoriaContent[]
}