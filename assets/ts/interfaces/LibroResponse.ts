import ApiResponse from "./ApiResponse";
import LibroContent from "./LibroContent";

export default interface LibroResponse extends ApiResponse {
	content?: LibroContent
}