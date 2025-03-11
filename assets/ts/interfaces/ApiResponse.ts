export default interface ApiResponse {
	status: number;
	message: string;
	validationErrors?: string[],
	integrityErrors?: string[]
}